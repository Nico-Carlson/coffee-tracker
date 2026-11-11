<?php
// require the connection file (also starts the session)
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */

require __DIR__ . '/includes/auth.php';
requireLogin();

$st = $pdo->prepare('SELECT coffeeId, coffeeName FROM tblCoffee WHERE userID = :uid ORDER BY coffeeName');
$st->execute([':uid' => $_SESSION['userID']]);
$coffees = $st->fetchAll(PDO::FETCH_ASSOC);

// id => name, with ids forced to int so the strict check in readBrewInput() matches
$coffeeNames = [];
foreach ($coffees as $row) {
    $coffeeNames[(int) $row['coffeeId']] = $row['coffeeName'];
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $_SESSION['errMessage'] = "Your form expired. Please try again.<br><a href='newBrew.php'>New Brew</a>";
        header('Location: errors.php');
        exit();
    }
    [$data, $errors] = readBrewInput(array_keys($coffeeNames));

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO tblBrew (userID, coffeeId, coffeeGrams, waterGrams, brewDate,
                                      waterTemp, grindSize, bloomTime, totalSeconds, rating, brewNotes)
                 VALUES (:userID, :coffeeId, :coffeeGrams, :waterGrams, :brewDate,
                         :waterTemp, :grindSize, :bloomTime, :totalSeconds, :rating, :brewNotes)'
            );
            $stmt->execute($data + [':userID' => $_SESSION['userID']]);
            header('Location: brewHistory.php');
            exit();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $_SESSION['errMessage'] = "Sorry, your brew couldn't be saved.<br><a href='newBrew.php'>New Brew</a>";
            header('Location: errors.php');
            exit();
        }
    }
}

// get the current date
$today = date('Y-m-d');
$selectedCoffeeId = (int) ($_POST['coffeeId'] ?? 0);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>New Brew</title>
</head>
<body>

<?php
$activePage = 'new';
require __DIR__ . '/includes/brewNav.php';
?>

<main>
    <div class='page-head'>
        <h1>New brew</h1>
        <p>Write down what you did while it's fresh, so you can repeat the good cups.</p>
    </div>


    <?php if ($errors) : ?>
    <div class="panel error"><ul>
        <?php foreach ($errors as $err) : ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <form class='panel' action='newBrew.php' method='POST'>
        <?= csrfField() ?>
        <div class='form-grid'>


            <div class='field'>
                <label for='coffeeId'>Coffee</label>

                    <?php if (!$coffeeNames) : ?>
                        <p class="hint">Add a coffee first on the <a href="coffees.php">Coffees</a> page.</p>
                    <?php endif; ?>

                    <select id="coffeeId" name="coffeeId">
                        <?php foreach ($coffeeNames as $id => $name) : ?>
                            <option value="<?= e($id) ?>" <?= $selectedCoffeeId === $id ? 'selected' : '' ?>><?= e($name) ?></option>
                        <?php endforeach; ?>
                    </select>
            </div>

            <div class='field'>
                <label for='brewDate'>Date</label>
                <input type="date" id="brewDate" name="brewDate" value="<?= e($_POST['brewDate'] ?? $today) ?>">
            </div>

            <div class='field'>
                <label for='coffeeGrams'>Coffee (g)</label>
                <input type="number" id="coffeeGrams" name="coffeeGrams" value="<?= e($_POST['coffeeGrams'] ?? 15) ?>">
            </div>

            <div class='field'>
                <label for='waterGrams'>Water (g)</label>
                <input type='number' id='waterGrams' name='waterGrams' step='1' value='225'>
            </div>

            <div class='field'>
                <label for='waterTemp'>Water temperature (°F)</label>
                <input type='number' id='waterTemp' name='waterTemp' value='202'>
            </div>

            <div class='field'>
                <label for='grindSize'>Grind size</label>
                <select id='grindSize' name='grindSize'>
                    <option>Fine</option>
                    <option>Medium-fine</option>
                    <option>Medium</option>
                    <option>Medium-coarse</option>
                    <option>Coarse</option>
                </select>
            </div>

            <div class='field'>
                <label for='bloomTime'>Bloom time (seconds)</label>
                <input type='number' id='bloomTime' name='bloomTime' value='45'>
            </div>

            <div class='field'>
                <label for='totalTime'>Total brew time</label>
                <input type="text" id="totalTime" name="totalTime" value="<?= e($_POST['totalTime'] ?? '3:00') ?>">
                <span class='hint'>Minutes and seconds</span>
            </div>

            <div class='field'>
                <label for='rating'>Rating</label>
                <select id='rating' name='rating'>
                    <option value='5'>5 / 5</option>
                    <option value='4'>4 / 5</option>
                    <option value='3'>3 / 5</option>
                    <option value='2'>2 / 5</option>
                    <option value='1'>1 / 5</option>
                </select>
            </div>

            <div class='field wide'>
                <label for='brewNotes'>Tasting notes</label>
                <textarea id="brewNotes" name="brewNotes"><?= e($_POST['brewNotes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class='form-actions'>
            <button type='submit' class='btn'>Save brew</button>
        </div>
    </form>
</main>
</body>
</html>
