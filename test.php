<?php
// test.php

// データベース接続
$mysqli = new mysqli('localhost', 'yusei', 'yukidaruma', 'CSexp1DB');
if ($mysqli->connect_error) {
    die("DB接続エラー: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8");

// GETパラメータ取得
$keyword = $_GET['keyword'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));

$addr = $zip = $kana = '';
if (preg_match("/^[0-9]+$/", $keyword)) {
    $zip = $keyword;
} elseif (preg_match("/^[ァ-ヴー・]+$/u", $keyword)) {
    $kana = $keyword;
} else {
    $addr = $keyword;
}

$LIMIT = 10;
$offset = ($page - 1) * $LIMIT;

// SQLクエリ準備
$addr = "%$addr%";
$kana = "%$kana%";
$zip = "%$zip%";

$count = 0;
$c_query = "SELECT COUNT(*) FROM zipZenkoku WHERE CONCAT(addr1, addr2, addr3, kana1, kana2, kana3) LIKE ? AND CONCAT(kana1, kana2, kana3) LIKE ? AND zip LIKE ?";
if ($c_stmt = $mysqli->prepare($c_query)) {
    $c_stmt->bind_param("sss", $addr, $kana, $zip);
    $c_stmt->execute();
    $c_stmt->bind_result($count);
    $c_stmt->fetch();
    $c_stmt->close();
}
$max_page = max(1, ceil($count / $LIMIT));

// データ取得クエリ
$results = [];
$query = "SELECT addr2, addr3, zip FROM zipZenkoku WHERE CONCAT(addr1, addr2, addr3, kana1, kana2, kana3) LIKE ? AND CONCAT(kana1, kana2, kana3) LIKE ? AND zip LIKE ? LIMIT ?, ?";
if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param("sssii", $addr, $kana, $zip, $offset, $LIMIT);
    $stmt->execute();
    $stmt->bind_result($addr2, $addr3, $zipcode);
    while ($stmt->fetch()) {
        $results[] = [$addr2, $addr3, $zipcode];
    }
    $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>住所検索 - test.php</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

<div class="container">
  <h1 class="text-primary mb-4">住所検索</h1>

  <form action="test.php" method="GET" class="mb-4">
    <div class="row g-2">
      <div class="col-auto">
        <input type="text" name="keyword" class="form-control" placeholder="住所・カナ・郵便番号" value="<?= htmlspecialchars($keyword) ?>">
      </div>
      <input type="hidden" name="page" value="1">
      <div class="col-auto">
        <input type="submit" value="検索" class="btn btn-primary">
      </div>
    </div>
  </form>

  <?php if ($keyword !== ''): ?>
    <h5 class="mb-3 text-secondary"><?= htmlspecialchars($keyword) ?> の検索結果</h5>
    <?php if (count($results) > 0): ?>
      <ul class="list-group mb-4">
        <?php foreach ($results as [$addr2, $addr3, $zip]) : ?>
          <li class="list-group-item d-flex justify-content-between">
            <span><?= htmlspecialchars($addr2 . ' ' . $addr3) ?></span>
            <span class="text-muted"><?= htmlspecialchars($zip) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="alert alert-warning">該当するデータが見つかりませんでした。</div>
    <?php endif; ?>

    <nav>
      <ul class="pagination">
        <?php for ($i = 1; $i <= $max_page; $i++): ?>
          <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
            <a class="page-link" href="test.php?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>

</body>
</html>
