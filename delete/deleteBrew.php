<?php

// lives in delete/, so everything in the project root is reached with ../
require __DIR__ . '/../includes/db_connect.php';
/** @var PDO $pdo */
require __DIR__ . '/../includes/auth.php';
requireLogin('../');

// only accept POST with a valid id
$brewID = $_POST['brewID'] ?? null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($brewID) || !ctype_digit($brewID)) {
    header('Location: ../brewHistory.php');
    exit();
}

if (!csrfValid()) {
    $_SESSION['errMessage'] = "Your form expired. Please try again.<br><a href='brewHistory.php'>Back to history</a>";
    header('Location: ../errors.php');
    exit();
}

try {
    $stmt = $pdo->prepare('DELETE FROM tblBrew WHERE brewID = :id AND userID = :uid');
    $stmt->execute([':id' => (int) $brewID, ':uid' => $_SESSION['userID']]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    // errors.php is in the project root, so this link has no ../
    $_SESSION['errMessage'] = "Error deleting brew.<br><a href='brewHistory.php'>Back to history</a>";
    header('Location: ../errors.php');
    exit();
}

header('Location: ../brewHistory.php');
exit();
