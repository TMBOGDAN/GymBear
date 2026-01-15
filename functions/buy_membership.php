<?php
session_start();
require_once __DIR__ . '/../db.php';

// Verificăm dacă utilizatorul e logat
if(!isset($_SESSION['user_id'])) {
    die("Trebuie să fii logat ca să cumperi un abonament.");
}

$user_id = $_SESSION['user_id'];

// Preluăm id-ul abonamentului trimis prin GET sau POST
$abonament_id = $_GET['id'] ?? $_POST['id'] ?? null;
if(!$abonament_id) die("Abonament invalid.");

// Luăm detaliile abonamentului
$stmt = $conn->prepare("SELECT nume, durata FROM abonamente WHERE id=?");
$stmt->bind_param("i", $abonament_id);
$stmt->execute();
$result = $stmt->get_result();
$abonament = $result->fetch_assoc();
if(!$abonament) die("Abonament inexistent.");

// Calculăm data de expirare
$durata = intval($abonament['durata']); // presupunem că e în zile
$today = date('Y-m-d');
$subscription_end = date('Y-m-d', strtotime("+$durata days", strtotime($today)));

// Actualizăm contul utilizatorului
$stmt = $conn->prepare("
    UPDATE accounts 
    SET subscription_name=?, subscription_end=? 
    WHERE id=?
");
$stmt->bind_param("ssi", $abonament['nume'], $subscription_end, $user_id);
$stmt->execute();

// Redirecționăm înapoi la pagina membership
header("Location: membership.php?success=1");
exit;
