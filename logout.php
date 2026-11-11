<?php

// this page will reset the SESSION vars and send the user back to index
session_start();

require __DIR__ . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}
if (!csrfValid()) {
    $_SESSION['errMessage'] = "Your form expired. Please try again.<br><a href='index.php'>Home</a>";
    header('Location: errors.php');
    exit();
}

$_SESSION = [];

$params = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $params['path'],
    'domain' => $params['domain'],
    'secure' => $params['secure'],
    'httponly' => $params['httponly'],
    'samesite' => $params['samesite'] ?: 'Lax',
]);

session_destroy();
header('Location: index.php');
exit();
