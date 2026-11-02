<?php

// this page will reset the SESSION vars and send the user back to index
session_start();
$_SESSION = [];
session_destroy();
header("Location:index.php");
exit();