<?php
// activare afisare erori
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// pornire sesiune cu setari securitate
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict'
]);

require_once __DIR__ . '/../functions/send_email.php';

// mesaje feedback
$successMessage = '';
$errorMessage = '';

// CSRF token
if (empty($_SESSION['csrf_token_contact'])) {
    $_SESSION['csrf_token_contact'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token_contact'];

// procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // verificare CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token_contact'], $_POST['csrf_token'])) {
        $errorMessage = 'Cerere invalida (CSRF).';
    } else {
        // honeypot + verificare timp minim
        $honeypot = trim($_POST['website'] ?? '');
        $ts = isset($_POST['ts']) ? (int)$_POST['ts'] : 0;

        if ($honeypot !== '') {
            $errorMessage = 'Spam detectat.';
        } elseif ($ts > time() || (time() - $ts) < 3) {
            $errorMessage = 'Cerere suspecta (prea rapid).';
        } else {
            $name    = trim($_POST['name'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if ($name && $email && $message) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errorMessage = 'Email invalid.';
                } else {
                    // sanitizare date
                    $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                    $safe_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
                    $safe_message = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

                    // destinatar
                    $destinatar = 'tmbogdan15@gmail.com';

                    $success = send_email($destinatar, 'contact', [
                        'name'    => $safe_name,
                        'email'   => $safe_email,
                        'message' => $safe_message
                    ]);

                    if ($success) {
                        $successMessage = "Mesajul tau a fost trimis cu succes!";
                        unset($_SESSION['csrf_token_contact']);
                    } else {
                        $errorMessage = "Eroare la trimiterea mesajului.";
                    }
                }
            } else {
                $errorMessage = "Toate campurile sunt obligatorii.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Contact GymBear</title>
<link rel="stylesheet" href="/style/style.css">
<style>
/* stil formular contact */
body { font-family: Arial, sans-serif; }
input, textarea { width: 300px; padding: 8px; margin: 5px 0; }
button { padding: 10px 20px; }
.success { color: green; }
.error { color: red; }

.contact-page { justify-content:center; align-items:center; min-height:80vh; padding:20px; box-sizing:border-box; }
.contact-page h1 { text-align:left; color:#c70000; margin-bottom:25px; font-size:28px; }
.contact-page form { background:#292626; padding:30px 25px; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,0.3); width:100%; max-width:400px; display:flex; flex-direction:column; }

.contact-page input[type="text"], .contact-page input[type="email"], .contact-page textarea {
    background:#111; color:#fff; border:1px solid #c70000; border-radius:6px; padding:12px; margin-bottom:15px; font-size:16px; transition:0.3s;
}
.contact-page input:focus, .contact-page textarea:focus { outline:none; border-color:#ff2e2e; box-shadow:0 0 5px rgba(255,46,46,0.5); }

.contact-page textarea { min-height:100px; resize:vertical; }
.contact-page button { background-color:#c70000; color:#fff; border:none; padding:12px; border-radius:6px; font-size:16px; cursor:pointer; transition:0.3s; }
.contact-page button:hover { background-color:#ff2e2e; }

.contact-page .success { color:#27ae60; text-align:center; margin-bottom:15px; font-weight:bold; }
.contact-page .error { color:#e74c3c; text-align:center; margin-bottom:15px; font-weight:bold; }
</style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="contact-page">
    <h1>Contact</h1>

    <?php if($successMessage): ?>
        <p class="success"><?php echo $successMessage; ?></p>
    <?php endif; ?>

    <?php if($errorMessage): ?>
        <p class="error"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <!-- formular contact -->
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <input type="hidden" name="ts" value="<?= time() ?>">

        <!-- honeypot -->
        <div class="honeypot" style="position:absolute;left:-9999px;visibility:hidden;">
            <label>Website (lasa gol)</label>
            <input type="text" name="website" autocomplete="off">
        </div>

        <input type="text" name="name" placeholder="Nume" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : '' ?>"><br>
        <input type="email" name="email" placeholder="Email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>"><br>
        <textarea name="message" placeholder="Mesaj" required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8') : '' ?></textarea><br>
        <button type="submit">Trimite mesaj</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
