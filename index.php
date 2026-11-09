<?php

// require db_connection
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */

// if the user was sent here with a bad login after trying to view the members page show a specific error
$errormessage = '';
if (isset($_GET['error']) && $_GET['error'] === 'falseLoginStatus') {
    $errormessage = '<p class="error">You are not logged in yet. Please log in first or make a new user.</p>';
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>Home</title>
</head>
<body>
<main class="center">
<?php require __DIR__ . '/includes/signInNav.php'; ?>
<h1>Home</h1>
<?php echo $errormessage; ?>
<h3>Welcome. Please log in or create an account to continue</h3>
</main>
</body>
</html>
