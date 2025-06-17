<html>
  <head>
    <meta charset="UTF-8">
    <title>科学科実験Aサンプルプログラム</title>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <!--Google Maps API -->
    <script src ="https://maps.googleapis.com/maps/api/js?key=&callback=initMap" async defer></script>
    
    <style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      /*マップ */
      #map{
        height: 500px;
        width: 100%;
        margin-top: 20px;
      }

      /*予測バー設定*/
      .ui-autocomplete {
        max-height: 200px;    
        overflow-y: auto;      
        overflow-x: hidden;   
        z-index: 1000;         
      }
    </style>


    <!--suggest Ajax -->
    <script>
      $(function() {
        $("#keyword").autocomplete({
          source:function(req,res) {
            $.ajax({
              url:"suggest_ajax.php",
              type:"GET",
              dataType:"json",
              data:{ term: req.term },
              success: function(data){
                res(data);
              },
              error: function(xhr, ts, err){
                res(['']);
              }
            });
          }
        });
      });
    </script>
  </head>


  <body>
    <h1>住所検索</h1>

    <form action="Map.php" method="GET">
      <input type="text" name="keyword" id="keyword">
      <input type="hidden" name="page" value="1">
      <input type="submit" value="検索">
    </form>
    
<?php
# 初期設定
$mysqli = new mysqli('localhost', 'yusei', 'yukidaruma', 'CSexp1DB');

# mysqlとの接続
if ($mysqli->connect_error) {
  echo $mysqli->connect_error;
  exit();
} else {
  $mysqli->set_charset("utf8");
}

# クエリの受け取り
$keyword = $_GET['keyword'];
# keywordクエリの中身が何もなかった場合終了
if (!isset($keyword) || empty($keyword)) {
  exit();
}

echo "$keyword". "の検索結果";
echo "<br>";

# クエリの受け取り
$page = $_GET['page'];

# pageクエリの中身が何もなかった場合、もしくはpage番号が負の数であれば1を入れる
if (!isset($page) || $page < 0) {
  $page = 1;
}


# 入力データの形式を判定
$addr = "";
$zip = "";
$kana = "";
if (preg_match("/^[0-9]+$/", $keyword)) {
  $zip = $keyword;
# 興味がある方は正規表現を用いてカタカナを場合分けする方法を調べてみてください
} else if (preg_match("/^[ァ-ヴー・]+$/u", $keyword)){
  $kana = $keyword;
} else {
  $addr = $keyword;
}

$LIMIT =10;

# CONCATを用いて入力されたものを結合して検索
$query = "SELECT addr2, addr3, zip FROM zipZenkoku WHERE CONCAT(addr1, addr2, addr3,kana1,kana2,kana3) like ? AND CONCAT(kana1, kana2, kana3) like ? AND zip like ? LIMIT ?, $LIMIT";

$addr = '%'.$addr.'%';
$zip =      $zip.'%';
$kana = '%'.$kana.'%';

# offset値を訂正してください
$offset = ($page-1)*$LIMIT;

# 該当検索結果の総数をカウント
$c_query = "SELECT COUNT(*) FROM zipZenkoku WHERE CONCAT(addr1, addr2, addr3,kana1,kana2,kana3) like ? AND CONCAT(kana1, kana2, kana3) like ? AND zip like ?";
if ($c_stmt = $mysqli->prepare($c_query)) {
  $c_stmt->bind_param("sss", $addr, $kana, $zip);
  $c_stmt->execute();
  $c_stmt->bind_result($count);
  $c_stmt->fetch();
  $c_stmt->close();
}
if ($count > 0) {
  $max_page = ceil($count /$LIMIT);
} else {
  $max_page = 1;
}

# オフセット含めて10件のみ検索
$address = [];
if ($stmt = $mysqli->prepare($query)) {
  $stmt->bind_param("sssi", $addr, $kana, $zip, $offset);
  $stmt->execute();
  $stmt->bind_result($addr2, $addr3, $zipcode);
  while ($stmt->fetch()) {
    $address[] = $addr1 . $addr2 . $addr3;
    echo "$addr2 $addr3 $zipcode";
    echo "<br>";
  }
  $stmt->close();
} else {
  echo "db error";
}

#ページリンク
echo "<hr><div>ページ: ";
for ($i = 1; $i <= $max_page; $i++) {
  if ($i == $page) {
    echo "<strong>$i</strong> "; // 現在ページは太字
  } else {
    echo "<a href='Map.php?page=$i&keyword=" . $_REQUEST["keyword"] . "'>$i</a> ";
  }
}
echo "</div>";


# mysqlとの接続をやめる
$mysqli->close();

?>

    <h2>Google Maps</h2>

    <!-- 地図表示部分 -->
    <div id="map"></div>

    <!-- 住所によるマップの設定　-->
    <script>
      const addresses = <?php echo json_encode($address, JSON_UNESCAPED_UNICODE); ?>;

      function initMap() { 
        const map = new google.maps.Map(document.getElementById("map"), {
          center: { lat: 34.9769, lng: 138.3831 },
          zoom: 9,
        });

        const geocoder = new google.maps.Geocoder();

        addresses.forEach(addr => {
          geocoder.geocode({ address: addr }, (results, status) => {
            if (status === 'OK') {
              const marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location,
                title: addr
              });
            } else {
              console.warn("ジオコーディング失敗: " + addr + " - " + status);
            }
          });
        });
      }
      </script>

    </div>
  </body>

</html>