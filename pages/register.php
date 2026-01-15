<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict'
]);

require_once '../db.php';

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$error = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Cerere invalidă");
    }

    $username = trim($_POST['username']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = "Completează toate câmpurile.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalid.";
    } elseif ($password !== $confirm) {
        $error = "Parolele nu coincid.";
    } elseif (strlen($password) < 6) {
        $error = "Parola trebuie să aibă cel puțin 6 caractere.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM accounts WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email deja folosit.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO accounts (username, email, password_hash) VALUES (?,?,?)"
            );
            $stmt->bind_param("sss", $username, $email, $hash);
            $stmt->execute();

            unset($_SESSION['csrf_token']);
            header("Location: login.php?registered=1");
            exit;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | FitnessPro</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="login-section">
    <div class="card login-card">
        <h2>Înregistrare</h2>

        <?php if ($error): ?>
            <p class="error-message"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= e($username) ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($email) ?>" required>
            </div>

            <div class="form-group">
                <label>Parolă</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Confirmă parola</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn">Înregistrare</button>
        </form>

        <p style="margin-top:15px;">
            Ai deja cont?
            <a href="login.php" style="color:var(--accent);">Autentifică-te</a>
        </p>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>
