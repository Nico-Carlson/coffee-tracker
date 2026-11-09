<?php

// this page will reset the SESSION vars and send the user back to index
session_start();
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
