<?php
session_start();
// sterge toate variabilele de sesiune
$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
