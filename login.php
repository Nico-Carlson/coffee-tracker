<?php
// require the connection file
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */

// if the POST is set already send the user's login info to the db
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = post('username');
    $password = $_POST['password'] ?? '';
    if (!is_string($password)) {
        $password = '';
    }

    $stmt = $pdo->prepare('SELECT userID, username, password FROM tblUser WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['loginStatus'] = true;
        $_SESSION['userID']      = (int) $user['userID'];
        $_SESSION['username']    = $user['username'];
        header('Location: coffees.php');
        exit();
    }

    $_SESSION['loginStatus'] = false;
    $_SESSION['errMessage'] = '<h3>Bad Login... Please try again or make a new user</h3>';
    header('Location: errors.php');
    exit();
}
?>

<!--show the sign in if POST isnt set -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>Login</title>
</head>
<body>
<main class="center">
<?php require __DIR__ . '/includes/signInNav.php'; ?>
<h1>Login</h1>


<form action="login.php" method="POST">
    <table>
        <tr>
            <th colspan="2">Enter Credentials</th>
        </tr>
        <tr>
            <td>
                Username
            </td>
            <td>
                <input type="text" name="username" size="25" required>
            </td>
        </tr>

        <tr>
            <td>
                Password
            </td>
            <td>
                <input type="password" name="password" size="25" required>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <input type="submit" value="Enter">
            </td>
        </tr>

    </table>
</form>
</main>
</body>
</html>
