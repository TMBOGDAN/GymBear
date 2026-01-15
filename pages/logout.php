<?php
session_start();

// sterge toate variabilele de sesiune
$_SESSION = [];

// distruge sesiunea
session_destroy();

// redirectioneaza la login
header("Location: login.php");
exit;
