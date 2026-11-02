<?php
// require the connection file (also starts the session)
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */

require __DIR__ . '/includes/auth.php'; requireLogin();

$uid = $_SESSION['userID'];

try {
    $rs = $pdo->prepare("SELECT brewID, coffeeType, coffeeGrams, waterGrams, brewDate, waterTemp, grindSize,
                                bloomTime, totalSeconds, rating, brewNotes
                         FROM tblBrew WHERE userID = :uid ORDER BY brewDate DESC, brewID DESC");
    $rs->execute([':uid' => $_SESSION['userID']]);
    $rows = $rs->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['errMessage'] = "Couldn't load your brews.";
    header("Location: errors.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>Brew History</title>
</head>
<body>
<?php $activePage = 'history'; require __DIR__ . '/includes/brewNav.php'; ?>

<main>
    <div class="page-head">
        <h1>Brew history</h1>
        <p>Every pour over you've logged, newest first.</p>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Coffee</th>
                    <th>Dose</th>
                    <th>Water</th>
                    <th>Grind</th>
                    <th>Time</th>
                    <th>Rating</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- placeholder rows: replace with a loop over query results later -->

                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($row['brewDate'])) ?></td>
                        <td class="coffee-name"><?= htmlspecialchars($row['coffeeType']) ?></td>
                        <td><?= (int)$row['coffeeGrams'] ?>g</td>
                        <td><?= $row['waterGrams'] !== null ? (int)$row['waterGrams'] . 'g' : '—' ?>
                            <?= $row['waterTemp'] !== null ? 'at ' . (int)$row['waterTemp'] . '°F' : '' ?></td>
                        <td><?= htmlspecialchars($row['grindSize']) ?></td>
                        <td><?= formatBrewTime($row['totalSeconds']) ?></td>
                        <td class="rating"><?= (int)$row['rating'] ?> / 5</td>
                        <td><?= htmlspecialchars($row['brewNotes']) ?></td>
                        <td>
                            <form method="post" action="delete/deleteBrew.php"
                                  onsubmit="return confirm('Delete this brew?');">
                                <input type="hidden" name="brewID" value="<?= (int)$row['brewID'] ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                            <form method="post" action="editBrew.php">
                                <input type="hidden" name="brewID" value="<?= (int)$row['brewID'] ?>">
                                <button type="submit" class="edit-btn">Edit</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</main>
</body>
</html>
