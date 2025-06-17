<?php
// bootstrap.php - 住所検索機能付きPHPスクリプト（Bootstrapで装飾）

// DB接続
$mysqli = new mysqli('localhost', 'yusei', 'yukidaruma', 'CSexp1DB');
if ($mysqli->connect_error) {
  echo $mysqli->connect_error;
  exit();
}
$mysqli->set_charset("utf8");

// クエリ受け取り
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$LIMIT = 10;
$offset = ($page - 1) * $LIMIT;

// 検索条件を初期化
$addr = $zip = $kana = '';
if (preg_match("/^[0-9]+$/", $keyword)) {
  $zip = $keyword;
} elseif (preg_match("/^[ァ-ヴー・]+$/u", $keyword)) {
  $kana = $keyword;
} else {
  $addr = $keyword;
}

$addr = "%$addr%";
$zip = "%$zip%";
$kana = "%$kana%";

// 件数取得
$c_query = "SELECT COUNT(*) FROM zipZenkoku WHERE CONCAT(addr1, addr2, addr3, kana1, kana2, kana3) LIKE ? AND CONCAT(kana1, kana2, kana3) LIKE ? AND zip LIKE ?";
$c_stmt = $mysqli->prepare($c_query);
$c_stmt->bind_param("sss", $addr, $kana, $zip);
$c_stmt->execute();
$c_stmt->bind_result($count);
$c_stmt->fetch();
$c_stmt->close();
$max_page = $count > 0 ? ceil($count / $LIMIT) : 1;

// データ取得
$query = "SELECT addr2, addr3, zip FROM zipZenkoku WHERE CONCAT(addr1, addr2, addr3, kana1, kana2, kana3) LIKE ? AND CONCAT(kana1, kana2, kana3) LIKE ? AND zip LIKE ? LIMIT ?, $LIMIT";
$results = [];
if ($stmt = $mysqli->prepare($query)) {
  $stmt->bind_param("sssi", $addr, $kana, $zip, $offset);
  $stmt->execute();
  $stmt->bind_result($addr2, $addr3, $zipCode);
  while ($stmt->fetch()) {
    $results[] = [$addr2, $addr3, $zipCode];
  }
  $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>住所検索 - bootstrap.php</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #eaf5ea; }
    .btn-success { background-color: #28a745; }
    .pagination { justify-content: center; flex-wrap: wrap; }
  </style>
</head>
<body class="p-4">
<div class="container">
  <h1 class="text-success mb-4">住所検索</h1>
  <form action="bootstrap.php" method="GET" class="mb-4">
    <div class="row g-2">
      <div class="col-md-6 col-sm-12">
        <input type="text" name="keyword" class="form-control" placeholder="住所・カナ・郵便番号" value="<?= htmlspecialchars($keyword) ?>">
      </div>
      <input type="hidden" name="page" value="1">
      <div class="col-auto">
        <input type="submit" value="検索" class="btn btn-success">
      </div>
    </div>
  </form>

  <?php if ($keyword !== ''): ?>
    <h5 class="mb-3 text-success">"<?= htmlspecialchars($keyword) ?>" の検索結果</h5>

    <?php if (count($results) > 0): ?>
      <ul class="list-group mb-4">
        <?php foreach ($results as [$addr2, $addr3, $zip]) : ?>
          <li class="list-group-item d-flex justify-content-between">
            <span><?= htmlspecialchars($addr2 . ' ' . $addr3) ?></span>
            <span class="text-muted">〒<?= htmlspecialchars($zip) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="alert alert-warning">該当するデータが見つかりませんでした。</div>
    <?php endif; ?>

    <nav class="mb-5">
      <ul class="pagination">
        <?php for ($i = 1; $i <= $max_page; $i++): ?>
          <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
            <a class="page-link" href="bootstrap.php?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>
</body>
</html>
