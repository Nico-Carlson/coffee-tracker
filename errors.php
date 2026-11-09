<?php
// require the connection file (also starts the session)
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */
// errMessage holds trusted HTML set by our own code. Never put user input in it.
// Read it once and clear it so a refresh does not show it again.
$errMessage = $_SESSION['errMessage'] ?? 'Something went wrong.';
unset($_SESSION['errMessage']);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>Error Page</title>
</head>
<body>
<main>
    <?php
    if (!empty($_SESSION['loginStatus'])) {
        require __DIR__ . '/includes/brewNav.php';
    } else {
        require __DIR__ . '/includes/signInNav.php';
    }
?>
    <div class="page-head">
        <h1>Errors</h1>
    </div>
    <div>
        <?php echo $errMessage; ?>
    </div>
</main>
</body>
</html>
