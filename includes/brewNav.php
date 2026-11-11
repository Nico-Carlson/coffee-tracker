<?php

/*
 * brew section navbar
 * set $activePage before requiring this file:
 *   $activePage = 'history' | 'new' | 'coffees' | 'metrics';
 *   require __DIR__ . '/includes/brewNav.php';
 *
 * NOTE: links are relative to the PAGE being viewed (not to this file),
 * so they have no ../ as long as the pages that include this nav are in the project root.
 */
$activePage = $activePage ?? '';
?>
<nav class="brew-nav">
    <span class="brew-brand">Pour Overs</span>

    <div class="brew-links">
        <a href="brewHistory.php" <?= $activePage === 'history' ? 'aria-current="page"' : '' ?>>Brew history</a>
        <a href="newBrew.php" <?= $activePage === 'new' ? 'aria-current="page"' : '' ?>>New brew</a>
        <a href="coffees.php" <?= $activePage === 'coffees' ? 'aria-current="page"' : '' ?>>Coffees</a>
        <a href="metrics.php" <?= $activePage === 'metrics' ? 'aria-current="page"' : '' ?>>Metrics</a>
    </div>

    <div class="brew-side">
        <a href="index.php">Main page</a>
        <form class="logout-form" action="logout.php" method="post">
            <?= csrfField() ?>
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</nav>
