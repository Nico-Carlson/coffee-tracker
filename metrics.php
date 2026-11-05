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
                ROUND(AVG(b.waterGrams / NULLIF(b.coffeeGrams, 0)), 1) AS avgRatio
         FROM tblBrew b
         JOIN tblCoffee c ON c.coffeeId = b.coffeeId
         WHERE b.userID = :uid AND b.brewDate >= :since
         GROUP BY c.roast
         ORDER BY COUNT(*) DESC'
    );
    $rs->execute([':uid' => $_SESSION['userID'], ':since' => $since]);
    $byRoast = $rs->fetchAll();

    $rs = $pdo->prepare(
        'SELECT COUNT(*) AS brews,
                ROUND(AVG(rating), 2) AS avgRating,
                ROUND(AVG(waterGrams / NULLIF(coffeeGrams, 0)), 2) AS avgRatio,
                ROUND(AVG(waterGrams / NULLIF(totalSeconds, 0)), 2) AS avgFlow
         FROM tblBrew
         WHERE userID = :uid AND brewDate >= :since'
    );
    $rs->execute([':uid' => $_SESSION['userID'], ':since' => $since]);
    $kpi = $rs->fetch();

    $rs = $pdo->prepare(
        'SELECT c.coffeeName, c.roaster, c.roast,
                COUNT(*) AS brews,
                ROUND(AVG(b.rating), 2) AS avgRating,
                ROUND(AVG(b.waterGrams / NULLIF(b.coffeeGrams, 0)), 2) AS avgRatio,
                ROUND(AVG(b.waterGrams / NULLIF(b.totalSeconds, 0)), 2) AS avgFlow
         FROM tblBrew b
         JOIN tblCoffee c ON c.coffeeId = b.coffeeId
         WHERE b.userID = :uid AND b.brewDate >= :since
         GROUP BY c.coffeeId, c.coffeeName, c.roaster, c.roast
         ORDER BY avgRating DESC, brews DESC, c.coffeeName'
    );
    $rs->execute([':uid' => $_SESSION['userID'], ':since' => $since]);
    $byCoffee = $rs->fetchAll();
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
    <div class="page-head">
    <h1>Metrics</h1>
    <p><?= e($ranges[$range]) ?></p>
</div>

<div class="kpi-grid">
    <div class="kpi">
        <span class="kpi-value"><?= (int) $kpi['brews'] ?></span>
        <span class="kpi-label">Brews</span>
    </div>
    <div class="kpi">
        <span class="kpi-value"><?= $kpi['avgRating'] !== null ? e((string) $kpi['avgRating']) . ' / 5' : '—' ?></span>
        <span class="kpi-label">Avg rating</span>
    </div>
    <div class="kpi">
        <span class="kpi-value"><?= $kpi['avgRatio'] !== null ? '1:' . e((string) $kpi['avgRatio']) : '—' ?></span>
        <span class="kpi-label">Avg ratio</span>
    </div>
    <div class="kpi">
        <span class="kpi-value"><?= $kpi['avgFlow'] !== null ? e((string) $kpi['avgFlow']) . ' g/s' : '—' ?></span>
        <span class="kpi-label">Avg flow rate</span>
    </div>
</div>

<div class="range-filter">
    <?php foreach ($ranges as $key => $label) : ?>
        <a class="btn" href="metrics.php?range=<?= e((string) $key) ?>"
           <?= (string) $key === $range ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<h2 class="section-title">By roast</h2>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Roast</th>
                <th>Brews</th>
                <th>Avg rating</th>
                <th>Avg ratio</th>
            </tr>
        </thead>
        <tbody>
          <?php if (!$byRoast) : ?>
            <tn><td colspan="4" class="empty">No brews in this range.</td></tr>
          <?php endif; ?>
            <?php foreach ($byRoast as $row) : ?>
                <tr>
                    <td><?= e($row['roast']) ?></td>
                    <td><?= (int) $row['brews'] ?></td>
                    <td><?= e($row['avgRating']) ?> / 5</td>
                    <td>1:<?= e($row['avgRatio']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h2 class="section-title">By coffee</h2>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Coffee</th>
                <th>Roaster</th>
                <th>Roast</th>
                <th>Brews</th>
                <th>Avg rating</th>
                <th>Avg ratio</th>
                <th>Avg flow</th>
            </tr>
        </thead>
        <tbody>
          <?php if (!$byCoffee) : ?>
              <tr><td colspan="7" class="empty">No coffees in this range.</td></tr>
          <?php endif; ?>
            <?php foreach ($byCoffee as $row) : ?>
                <tr>
                    <td class="coffee-name"><?= e($row['coffeeName']) ?></td>
                    <td><?= e($row['roaster']) ?></td>
                    <td><?= e($row['roast']) ?></td>
                    <td><?= (int) $row['brews'] ?></td>
                    <td><?= e((string) $row['avgRating']) ?> / 5</td>
                    <td><?= $row['avgRatio'] !== null ? '1:' . e((string) $row['avgRatio']) : '—' ?></td>
                    <td><?= $row['avgFlow'] !== null ? e((string) $row['avgFlow']) . ' g/s' : '—' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
      
</main>
</body>
</html>
