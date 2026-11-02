<?php
/*
 * just call this in your file to use nav
 * require __DIR__ . '/includes/signInNav.php';
 *
 * Links are relative to the PAGE being viewed, so no ../ (the pages using this are in the project root).
*/
?>
<nav class="brew-nav">

    <span class="brew-brand">Sign In Page</span>

    <div class="brew-links">
        <a href="index.php">Home</a>
        <a href="newUser.php">New User</a>
        <a href="login.php">Login</a>
    </div>

    <div class="brew-side">
        <a href="logout.php">Logout</a>
        <a href="coffees.php">Coffees</a>
    </div>
</nav>
