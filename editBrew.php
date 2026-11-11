<?php
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */

require __DIR__ . '/includes/auth.php';
requireLogin();

try {
    $st = $pdo->prepare('SELECT coffeeId, coffeeName FROM tblCoffee WHERE userID = :uid ORDER BY coffeeName');
    $st->execute([':uid' => $_SESSION['userID']]);

    // id => name, with ids forced to int so the strict check in readBrewInput() matches
    $coffeeNames = [];
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $coffeeNames[(int) $row['coffeeId']] = $row['coffeeName'];
    }

    $methodNames = [];
    foreach ($pdo->query('SELECT methodId, methodName FROM tblMethod ORDER BY methodId')->fetchAll() as $row) {
        $methodNames[(int) $row['methodId']] = $row['methodName'];
    }

    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
        if (!csrfValid()) {
            $_SESSION['errMessage'] = "Your form expired. Please try again.<br><a href='brewHistory.php'>Back to history</a>";
            header('Location: errors.php');
            exit();
        }
        $id = $_POST['brewID'] ?? '';
        if (!ctype_digit($id)) {
            header('Location: brewHistory.php');
            exit();
        }

        [$data, $errors] = readBrewInput(array_keys($coffeeNames), array_keys($methodNames));
        if (!$errors) {
            $stmt = $pdo->prepare(
                'UPDATE tblBrew SET coffeeId = :coffeeId, methodId = :methodId, coffeeGrams = :coffeeGrams,
                        waterGrams = :waterGrams, brewDate = :brewDate, waterTemp = :waterTemp,
                        grindSize = :grindSize, bloomTime = :bloomTime, totalSeconds = :totalSeconds,
                        rating = :rating, brewNotes = :brewNotes
                 WHERE brewID = :id AND userID = :uid'
            );
            $stmt->execute($data + [
                ':id'  => (int) $id,
                ':uid' => $_SESSION['userID'],
            ]);
            header('Location: brewHistory.php');
            exit();
        }
    }

    // ---------- LOAD: id comes from the Edit button (POST) or a link (GET) ----------
    $id = $_POST['brewID'] ?? $_GET['id'] ?? '';
    if (!ctype_digit((string) $id)) {
        header('Location: brewHistory.php');
        exit();
    }

    $stmt = $pdo->prepare('SELECT * FROM tblBrew WHERE brewID = :id AND userID = :uid');
    $stmt->execute([':id' => (int) $id, ':uid' => $_SESSION['userID']]);
    $brew = $stmt->fetch();
    if (!$brew) {
        header('Location: brewHistory.php');
        exit();
    }

    if ($errors) {
        $brew = array_merge($brew, $_POST);
    }
    $timeValue = $errors ? ($_POST['totalTime'] ?? '') : formatBrewTime($brew['totalSeconds']);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['errMessage'] = "Error editing brew.<br><a href='brewHistory.php'>Back to history</a>";
    header('Location: errors.php');
    exit();
}

$grinds = ['Fine', 'Medium-fine', 'Medium', 'Medium-coarse', 'Coarse'];
$selectedCoffeeId = (int) $brew['coffeeId'];
$selectedMethodId = (int) $brew['methodId'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>Edit Brew</title>
</head>
<body>
<?php
$activePage = 'history';
require __DIR__ . '/includes/brewNav.php';
?>

<main>
    <div class="page-head">
        <h1>Edit brew</h1>
        <p>Change anything that was logged wrong.</p>
    </div>


    <?php if ($errors) : ?>
        <div class="panel error"><ul>
                <?php foreach ($errors as $err) : ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul></div>
    <?php endif; ?>

    <form class="panel" action="editBrew.php" method="POST">
        <input type="hidden" name="brewID" value="<?= (int) $brew['brewID'] ?>">
        <input type="hidden" name="save" value="1">
        <?= csrfField() ?>

        <div class="form-grid">
            <div class="field">
                <label for="coffeeId">Coffee</label>
                <select id="coffeeId" name="coffeeId">
                    <?php foreach ($coffeeNames as $cid => $cname) : ?>
                        <option value="<?= e($cid) ?>" <?= $selectedCoffeeId === $cid ? 'selected' : '' ?>><?= e($cname) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class='field'>
                <label for='methodId'>Method</label>
                <select id="methodId" name="methodId">
                    <?php foreach ($methodNames as $mid => $mname) : ?>
                        <option value="<?= e($mid) ?>" <?= $selectedMethodId === $mid ? 'selected' : '' ?>><?= e($mname) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="brewDate">Date</label>
                <input type="date" id="brewDate" name="brewDate" value="<?= e(substr($brew['brewDate'], 0, 10)) ?>">
            </div>
            <div class="field">
                <label for="coffeeGrams">Coffee (g)</label>
                <input type="number" id="coffeeGrams" name="coffeeGrams" step="1" value="<?= e($brew['coffeeGrams']) ?>">
            </div>
            <div class="field">
                <label for="waterGrams">Water (g)</label>
                <input type="number" id="waterGrams" name="waterGrams" step="1" value="<?= e($brew['waterGrams']) ?>">
            </div>
            <div class="field">
                <label for="waterTemp">Water temperature (°F)</label>
                <input type="number" id="waterTemp" name="waterTemp" value="<?= e($brew['waterTemp']) ?>">
            </div>
            <div class="field">
                <label for="grindSize">Grind size</label>
                <select id="grindSize" name="grindSize">
                    <?php foreach ($grinds as $g) : ?>
                        <option value="<?= e($g) ?>" <?= $g === $brew['grindSize'] ? 'selected' : '' ?>><?= e($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="bloomTime">Bloom time (seconds)</label>
                <input type="number" id="bloomTime" name="bloomTime" value="<?= e($brew['bloomTime']) ?>">
            </div>
            <div class="field">
                <label for="totalTime">Total brew time</label>
                <input type="text" id="totalTime" name="totalTime" value="<?= e($timeValue) ?>">
                <span class="hint">Minutes and seconds</span>
            </div>
            <div class="field">
                <label for="rating">Rating</label>
                <select id="rating" name="rating">
                    <?php foreach ([5, 4, 3, 2, 1] as $r) : ?>
                        <option value="<?= $r ?>" <?= (int) $brew['rating'] === $r ? 'selected' : '' ?>><?= $r ?> / 5</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field wide">
                <label for="brewNotes">Tasting notes</label>
                <textarea id="brewNotes" name="brewNotes"><?= e($brew['brewNotes']) ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Save changes</button>
            <a href="brewHistory.php">Cancel</a>
        </div>
    </form>
</main>
</body>
</html>
