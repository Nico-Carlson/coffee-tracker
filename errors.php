<?php
// require the connection file (also starts the session)
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */
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
    <div class="page-head">
        <h1>Errors</h1>
    </div>
    <p>
        <?php echo $_SESSION['errMessage'] ?? 'Something went wrong.'; ?>
    </p>
</main>
</body>
</html>

<?php unset($_SESSION['errMessage']);