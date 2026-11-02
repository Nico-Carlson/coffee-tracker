<?php
// require the connection file (also starts the session)
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */

require __DIR__ . '/includes/auth.php'; requireLogin();

// ---------- ADD a coffee ----------
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coffeeName = post('coffeeName');
    $roast      = post('roast');

    if ($coffeeName === '') $errors[] = 'Coffee name is required.';
    if (!in_array($roast, ['Light', 'Medium', 'Dark'], true)) $errors[] = 'Pick a roast level.';

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                    "INSERT INTO tblCoffee (userID, coffeeName, roaster, roast, process, notes)
                 VALUES (:uid, :coffeeName, :roaster, :roast, :process, :notes)"
            );
            $stmt->execute([
                    ':uid'        => $_SESSION['userID'],
                    ':coffeeName' => $coffeeName,
                    ':roaster'    => post('roaster'),
                    ':roast'      => $roast,
                    ':process'    => post('process'),
                    ':notes'      => post('notes'),
            ]);
            header("Location: coffees.php");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = 'You already have a coffee with that name.';
            } else {
                error_log($e->getMessage());
                $_SESSION['errMessage'] = "Sorry, that coffee couldn't be saved.<br><a href='coffees.php'>Back to coffees</a>";
                header("Location: errors.php");
                exit();
            }
        }
    }
}
// ---------- LIST this user's coffees (brew count comes from one JOIN, not a query per card) ----------
try {
    $stmt = $pdo->prepare(
        "SELECT c.coffeeId, c.coffeeName, c.roaster, c.roast, c.process, c.notes,
                COUNT(b.brewID) AS brewCount
         FROM tblCoffee c
         LEFT JOIN tblBrew b ON b.coffeeType = c.coffeeName AND b.userID = c.userID
         WHERE c.userID = :uid
         GROUP BY c.coffeeId
         ORDER BY c.coffeeName"
    );
    $stmt->execute([':uid' => $_SESSION['userID']]);
    $coffees = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['errMessage'] = "Sorry, your coffee couldn't be saved.<br><a href='coffees.php'>Coffees</a>";
    header("Location: errors.php");
    exit();
}

$roastClass = ['Light' => 'light', 'Medium' => 'medium', 'Dark' => 'dark'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>Coffees</title>
</head>
<body>
<?php $activePage = 'coffees'; require __DIR__ . '/includes/brewNav.php'; ?>

<main>
    <div class="page-head">
        <h1>Coffees</h1>
        <p>The beans you've bought, so you know what's in the cupboard and how you brewed it.</p>
    </div>

    <div class="bag-grid">
        <?php foreach ($coffees as $c):

            $class  = 'bag ' . ($roastClass[$c['roast']] ?? 'dark');
            $brews  = (int)$c['brewCount'];
            $warn   = $brews > 0
                ? "Delete this coffee? Its $brews brew(s) stay in your history."
                : "Delete this coffee?";
        ?>
        <article class="<?= $class ?>">
            <div class="bag-head">
                <h2><?= e($c['coffeeName']) ?></h2>
                <p class="roaster"><?= e($c['roaster']) ?></p>
            </div>

            <dl class="bag-meta">
                <div><dt>Roast</dt><dd><?= e($c['roast']) ?></dd></div>
                <div><dt>Process</dt><dd><?= e($c['process']) ?></dd></div>
                <div><dt>Brews</dt><dd><?= $brews ?></dd></div>
            </dl>

            <?php if ($c['notes'] !== '' && $c['notes'] !== null): ?>
                <p class="bag-notes" title="<?= e($c['notes']) ?>"><?= e($c['notes']) ?></p>
            <?php endif; ?>

            <div class="bag-actions">
                <form method="post" action="delete/deleteCoffee.php"
                      onsubmit="return confirm('<?= $warn ?>');">
                    <input type="hidden" name="coffeeId" value="<?= (int)$c['coffeeId'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
            </div>
        </article>
        <?php endforeach; ?>

        <details class="bag-add" <?= (count($coffees) === 0 || $errors) ? 'open' : '' ?>>
            <summary>Add a coffee</summary>

            <?php if ($errors): ?>
                <div class="panel error"><ul>
                        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul></div>
            <?php endif; ?>

            <form class="panel" action="coffees.php" method="POST">
                <div class="form-grid">
                    <div class="field">
                        <label for="coffeeName">Coffee</label>
                        <input type="text" id="coffeeName" name="coffeeName" placeholder="Ethiopia" required>
                    </div>

                    <div class="field">
                        <label for="roaster">Roaster</label>
                        <input type="text" id="roaster" name="roaster" placeholder="Bird &amp; Billy">
                    </div>

                    <div class="field">
                        <label for="roast">Roast Level</label>
                        <select id="roast" name="roast">
                            <option>Light</option>
                            <option>Medium</option>
                            <option>Dark</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="process">Process</label>
                        <input type="text" id="process" name="process" placeholder="Washed">
                    </div>

                    <div class="field wide">
                        <label for="notes">Coffee notes</label>
                        <textarea id="notes" name="notes" placeholder="Bright, blueberry up front, a little thin at the end."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Save Coffee</button>
                </div>
            </form>
        </details>
    </div>
</main>
</body>
</html>
