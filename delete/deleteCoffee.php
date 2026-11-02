<?php
// lives in delete/, so everything in the project root is reached with ../
require __DIR__ . '/../includes/db_connect.php';
/** @var PDO $pdo */
require __DIR__ . '/../includes/auth.php';
requireLogin('../');

// only accept POST with a valid id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['coffeeId']) || !ctype_digit($_POST['coffeeId'])) {
    header("Location:../coffees.php");
    exit();
}

try {
    // userID in the WHERE means nobody can delete another user's coffee
    $stmt = $pdo->prepare("DELETE FROM tblCoffee WHERE coffeeId = :id AND userID = :uid");
    $stmt->execute([
        ':id'  => (int)$_POST['coffeeId'],
        ':uid' => $_SESSION['userID'],
    ]);
    header("Location:../coffees.php");
    exit();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['errMessage'] = "Error deleting coffee.<br><a href='coffees.php'>Back to coffees</a>";
    header("Location:../errors.php");
    exit();
}
