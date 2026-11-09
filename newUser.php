<?php

// require the connection file
require __DIR__ . '/includes/db_connect.php';
/** @var PDO $pdo */

// once the user's input is in POST send to db
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = post('firstName');
    $lastName  = post('lastName');
    $username  = post('username');
    $password  = $_POST['password'] ?? '';
    if (!is_string($password)) {
        $password = '';
    }

    if ($username === '' || strlen($password) < 8 || strlen($password) > 72) {
        header('Location: newUser.php?error=invalid');
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
        if ($e->getCode() === '23000') {
            header("Location: newUser.php?error=duplicate");
            exit();
        }
        error_log($e->getMessage());
        $_SESSION['errMessage'] = "Sorry, your account couldn't be created.";
        header("Location: errors.php");
        exit();
    }
}

// the POST branch always exits, so everything below is the form view
$errorMessage = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'duplicate') {
        $errorMessage = 'Username is already taken, please try another.';
    } elseif ($_GET['error'] === 'invalid') {
        $errorMessage = 'Enter a username and a password of 8 to 72 characters.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/coffee.css">
    <title>New User</title>
</head>
<body>
<main class="center">
<?php require __DIR__ . '/includes/signInNav.php'; ?>
<h1>New User</h1>
<?php if ($errorMessage !== '') : ?>
    <p class="error"><?= e($errorMessage) ?></p>
<?php endif; ?>
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
</main>
</body>
</html>
