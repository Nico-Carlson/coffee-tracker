<?php

// $toRoot = path from the calling script back to the project root.
//   root pages:           requireLogin();
//   scripts in a folder:  requireLogin('../');
function requireLogin(string $toRoot = ''): void
{
    if (empty($_SESSION['loginStatus']) || empty($_SESSION['userID'])) {
        header('Location: ' . $toRoot . 'index.php?error=falseLoginStatus');
        exit();
    }
}
