<?php
// require the connection file (also starts the session)
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */

require __DIR__ . '/includes/auth.php';
requireLogin();

$ranges = ['30' => 'Last 30 days', '90' => 'Last 90 days', 'all' => 'All time'];
$range = $_GET['range'] ?? '30';
if (!is_string($range) || !array_key_exists($range, $ranges)) {
    $range = '30';
}
$since = $range === 'all'
    ? '1970-01-01'
    : date('Y-m-d', strtotime('-' . (int) $range . ' days'));

try {
    $rs = $pdo->prepare(
        'SELECT c.roast,
                COUNT(*) AS brews,
                ROUND(AVG(b.rating), 2) AS avgRating,
                ROUND(AVG(b.waterGrams / NULLIF(b.coffeeGrams, 0)), 2) AS avgRatio
         FROM tblBrew b
         JOIN tblCoffee c ON c.coffeeId = b.coffeeId
         WHERE b.userID = :uid AND b.brewDate >= :since
         GROUP BY c.roast
         ORDER BY avgRating DESC'
    );
    $rs->execute([':uid' => $_SESSION['userID'], ':since' => $since]);
    $byRoast = $rs->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['errMessage'] = "Couldn't load your metrics.";
    header('Location: errors.php');
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>Metrics</title>
</head>
<body>
<?php
$activePage = 'metrics';
require __DIR__ . '/includes/brewNav.php';
?>

<main>
  <pre><?= e(print_r($byRoast, true)) ?></pre>
</main>
</body>
</html>
