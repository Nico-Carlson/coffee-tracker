<?php
// require db_connection
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */
/**
 * @return string
 * @var string $dbStatus
 */


// if the user was sent here with a bad login after trying to view the members page show a specific error
$errormessage = "";
if (isset($_GET['error']) && $_GET['error'] == 'falseLoginStatus'){
// display a message to the user printed in the body
$errormessage = "<p style='color: red;'>You are not logged in yet. Please log in first or make a new user.</p>";
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>Home</title>
</head>
<body>
<?php require __DIR__ . '/includes/signInNav.php';?>
<main class="center">
<h1>Home</h1>
<?php echo($errormessage);?>
<h3>Welcome. Please log in or create an account to continue</h3>

</body>
</html>
