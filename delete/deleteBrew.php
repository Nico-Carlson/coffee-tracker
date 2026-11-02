<?php
// lives in delete/, so everything in the project root is reached with ../
require __DIR__ . '/../includes/db_connect.php';
/** @var PDO $pdo */
require __DIR__ . '/../includes/auth.php';
requireLogin('../');

// only accept POST with a valid id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['brewID']) || !ctype_digit($_POST['brewID'])) {
    header("Location:../brewHistory.php");
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM tblBrew WHERE brewID = :id AND userID = :uid");
    $stmt->execute([':id' => (int)$_POST['brewID'], ':uid' => $_SESSION['userID']]);
    header("Location:../brewHistory.php");
    exit();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['errMessage'] = "Error deleting brew.<br><a href='brewHistory.php'>Back to history</a>";
    header("Location:../errors.php");
    exit();
}
