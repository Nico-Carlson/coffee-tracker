<?php
// require the connection file (also starts the session)
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */

require __DIR__ . '/includes/auth.php';
requireLogin();

try {
    $rs = $pdo->prepare(
        'SELECT b.brewID, c.coffeeName, b.coffeeGrams, b.waterGrams, b.brewDate, b.waterTemp,
                b.grindSize, b.bloomTime, b.totalSeconds, b.rating, b.brewNotes
         FROM tblBrew b
         JOIN tblCoffee c ON c.coffeeId = b.coffeeId
         WHERE b.userID = :uid
         ORDER BY b.brewDate DESC, b.brewID DESC'
    );
    $rs->execute([':uid' => $_SESSION['userID']]);
    $rows = $rs->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['errMessage'] = "Couldn't load your brews.";
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
    <title>Brew History</title>
</head>
<body>
<?php
$activePage = 'history';
require __DIR__ . '/includes/brewNav.php';
?>

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
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td><?= e(date('M j, Y', strtotime($row['brewDate']))) ?></td>
                        <td class="coffee-name"><?= e($row['coffeeName']) ?></td>
                        <td><?= (int) $row['coffeeGrams'] ?>g</td>
                        <td><?= $row['waterGrams'] !== null ? (int) $row['waterGrams'] . 'g' : '—' ?>
                            <?= $row['waterTemp'] !== null ? 'at ' . (int) $row['waterTemp'] . '°F' : '' ?></td>
                        <td><?= e($row['grindSize']) ?></td>
                        <td><?= formatBrewTime($row['totalSeconds']) ?></td>
                        <td class="rating"><?= (int) $row['rating'] ?> / 5</td>
                        <td><?= e($row['brewNotes']) ?></td>
                        <td>
                            <form method="post" action="delete/deleteBrew.php"
                                  onsubmit="return confirm('Delete this brew?');">
                                <input type="hidden" name="brewID" value="<?= (int) $row['brewID'] ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                            <form method="post" action="editBrew.php">
                                <input type="hidden" name="brewID" value="<?= (int) $row['brewID'] ?>">
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
