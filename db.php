<?php
// Activare afisare erori pentru debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Setari conexiune MySQL
$servername = "";
$username   = "";
$password   = "";
$dbname     = "";

// Creare conexiune
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificare conexiune
if ($conn->connect_error) {
    die("❌ Conexiune esuata: " . $conn->connect_error);
}

?>
