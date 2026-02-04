<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true
]);

require_once '../db.php';

// escape XSS
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// verificare autentificare
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token invalid");
    }

    // update email
    if (isset($_POST['update_profile'])) {
        $email = trim($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email invalid.";
        } else {
            $stmt = $conn->prepare("UPDATE accounts SET email=? WHERE id=?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $stmt->close();

            $success = "Profil actualizat cu succes.";
        }
    }

    // schimbare parola
    if (isset($_POST['update_password'])) {
        $new_password = $_POST['new_password'];

        if (strlen($new_password) < 6) {
            $error = "Parola trebuie sa aiba minim 6 caractere.";
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE accounts SET password_hash=? WHERE id=?");
            $stmt->bind_param("si", $hash, $user_id);
            $stmt->execute();
            $stmt->close();

            $success = "Parola a fost schimbata.";
        }
    }

    // incheiere abonament
    if (isset($_POST['end_subscription'])) {
        $stmt = $conn->prepare("
            UPDATE accounts
            SET subscription_name = NULL,
                subscription_end = NULL
            WHERE id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        $success = "Abonamentul a fost incheiat.";
    }

    // anulare sedinta
    if (isset($_POST['cancel_session'])) {
        $sedinta_id = (int)$_POST['session_id'];

        $stmt = $conn->prepare("DELETE FROM sedinte WHERE id=? AND id_utilizator=?");
        $stmt->bind_param("ii", $sedinta_id, $user_id);
        $stmt->execute();
        $stmt->close();

        header("Location: account.php");
        exit;
    }

    // regenereaza CSRF
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $csrf_token = $_SESSION['csrf_token'];
}


$stmt = $conn->prepare("
    SELECT username, email, subscription_name, subscription_end
    FROM accounts WHERE id=?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();


$stmt = $conn->prepare("
    SELECT id, data_sedinta, ora_sedinta, status
    FROM sedinte
    WHERE id_utilizator=?
      AND CONCAT(data_sedinta,' ',IFNULL(ora_sedinta,'00:00:00')) >= NOW()
    ORDER BY data_sedinta, ora_sedinta
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sedinte = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Contul meu</title>
<link rel="stylesheet" href="/style/style.css">

<style>
.btn-small {
    padding: 6px 14px;
    font-size: 0.85rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none; 
    cursor: pointer;
    }
</style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="content">

<?php if ($error): ?>
    <p style="color:red"><?= e($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green"><?= e($success) ?></p>
<?php endif; ?>


<div id="profil" class="section">
<h2>Profil</h2>

<p><strong>Username:</strong> <?= e($user['username']) ?></p>

<p><strong>Abonament:</strong>
<?php if ($user['subscription_name']): ?>
    <?= e($user['subscription_name']) ?> (pana la <?= date('d.m.Y', strtotime($user['subscription_end'])) ?>)
<?php else: ?>
    Nu ai abonament
<?php endif; ?>
</p>

<form method="post">
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
<input type="email" name="email" value="<?= e($user['email']) ?>" required>
<br><br>
<button name="update_profile" class="btn btn-small">Salveaza</button>
</form>

<h3>Schimba parola</h3>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
<input type="password" name="new_password" required minlength="6">
<br><br>
<button name="update_password" class="btn btn-small">Schimba parola</button>
</form>

<?php if ($user['subscription_name']): ?>
<form method="post" onsubmit="return confirm('Sigur vrei sa inchei abonamentul?');">
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
<button name="end_subscription" class="btn btn-small">Incheie abonament</button>
</form>
<?php endif; ?>

<br>
<a href="logout.php" class="btn btn-small">Logout</a>
</div>


<div id="sedinte" class="section">
<h2>Sedinte viitoare</h2>

<?php if ($sedinte->num_rows === 0): ?>
<p>Nu ai sedinte programate.</p>
<?php endif; ?>

<?php while ($s = $sedinte->fetch_assoc()): ?>
<form method="post" onsubmit="return confirm('Anulezi sedinta?');">
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
<input type="hidden" name="session_id" value="<?= $s['id'] ?>">

<strong>
<?= date('d.m.Y', strtotime($s['data_sedinta'])) ?>
<?= date('H:i', strtotime($s['ora_sedinta'])) ?>
</strong>
 – <?= e($s['status']) ?>

<button name="cancel_session" class="btn btn-small">Anuleaza</button>
</form>
<?php endwhile; ?>
</div>

</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
