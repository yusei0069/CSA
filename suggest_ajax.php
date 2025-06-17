<?php
# DB接続
$mysqli = new mysqli('localhost', 'yusei', 'yukidaruma', 'CSexp1DB');
$mysqli->set_charset("utf8");

# 入力取得
$term = $_GET["term"] ?? "";
$suggest = [];


# 入力データの形式を判定
$addr = "";
$zip = "";
$kana = "";
if (preg_match("/^[0-9]+$/", $term)) {
  $zip = $term;
# 興味がある方は正規表現を用いてカタカナを場合分けする方法を調べてみてください
} else if (preg_match("/^[ァ-ヴー・]+$/u", $term)){
  $kana = $term;
} else {
  $addr = $term;
}

#表示件数
$LIMIT =1000;

# CONCATを用いて入力されたものを結合して検索
$query = "SELECT addr2, addr3, zip FROM zipZenkoku WHERE CONCAT(addr1, addr2, addr3,kana1,kana2,kana3) like ? AND CONCAT(kana1, kana2, kana3) like ? AND zip like ? LIMIT $LIMIT";

$addr = '%'.$addr.'%';
$zip =  $zip.'%';
$kana = '%'.$kana.'%';


if ($stmt = $mysqli->prepare($query)) {
  $stmt->bind_param("sss", $addr, $kana, $zip);
  $stmt->execute();
  $stmt->bind_result($addr2,$addr3,$zipcode);
  while ($stmt->fetch()) {
    $suggest[] = $addr2 . "" . $addr3;
  }
  $stmt->close();
}

# JSON形式で返す（jQuery側が受け取って候補表示）
echo json_encode($suggest);
?>
