<?php
// require the connection file
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */
/**
 * @return string
 * @var string $dbStatus
 */


// once the user's input is in POST send to db
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = post('firstName');
    $lastName  = post('lastName');
    $username  = post('username');
    $password  = $_POST['password'] ?? '';

    if ($username === '' || strlen($password) < 8) {
        header("Location: newUser.php?error=invalid");
        exit();
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO tblUser (firstName, lastName, username, password)
             VALUES (:firstName, :lastName, :username, :password)"
        );
        $stmt->execute([
            ':firstName' => $firstName,
            ':lastName'  => $lastName,
            ':username'  => $username,
            ':password'  => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            header("Location: newUser.php?error=duplicate");
            exit();
        }
        error_log($e->getMessage());
        $_SESSION['errMessage'] = "Sorry, your account couldn't be created.";
        header("Location: errors.php");
        exit();
    }
} else {
    // if there was a dup username then the url would pass this error from the catch
    $errormessage = "";
    if (isset($_GET['error'])) {
        if ($_GET['error'] === 'duplicate') {
            $errormessage = "<p style='color: red;'>Username is already taken, please try another.</p>";
        } elseif ($_GET['error'] === 'invalid') {
            $errormessage = "<p style='color: red;'>Enter a username and a password of at least 8 characters.</p>";
        }
    }

//    if POST isnt yet set show the new user form
    echo ('
    
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>New User</title>
</head>
<body>
');
    require __DIR__ . '/includes/signInNav.php';
    echo('
    <main class="center">
<h1>New User</h1>
' . $errormessage . '
<form action="newUser.php" method="POST">
    <table>
        <tr>
            <th colspan="2">
                Enter New User
            </th>
        </tr>

        <tr>
            <td>
                First Name
            </td>
            <td>
                <input type="text" name="firstName" size="25">
            </td>
        </tr>

        <tr>
            <td>
                Last Name
            </td>
            <td>
                <input type="text" name="lastName" size="25">
            </td>
        </tr>

        <tr>
            <td>
                UserName
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

</body>
</html>
    
');
}














