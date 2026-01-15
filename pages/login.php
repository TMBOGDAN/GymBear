<?php

// LOGIN.PHP - FitnessPro


// pornire sesiune în mod sigur
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,       // necesită HTTPS
    'cookie_samesite' => 'Strict'
]);

require_once '../db.php'; // corect path-ul către db.php

// dacă utilizatorul este deja logat, îl trimitem direct la account.php
if(isset($_SESSION['user_id'])){
    header("Location: account.php");
    exit;
}

// funcție de escapare generică
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// generare CSRF token dacă nu există
if(empty($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$error = '';

// procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // verificare CSRF
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
        die("Cerere invalidă!");
    }

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $parola = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $parola === '') {
        $error = "Completati toate campurile!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalid!";
    } elseif (strlen($parola) < 6) {
        $error = "Parola trebuie sa aiba cel putin 6 caractere!";
    } else {
        // prepared statement pentru siguranta SQL
        $stmt = $conn->prepare("SELECT id, password_hash, username, role FROM accounts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows === 1){
            $stmt->bind_result($id, $parola_hash, $nume, $role);
            $stmt->fetch();

            if(is_string($parola_hash) && password_verify($parola, $parola_hash)){
                // setare sesiune si regenerare ID pentru securitate
                session_regenerate_id(true);
                unset($_SESSION['csrf_token']); // token vechi invalid
                $_SESSION['user_id'] = $id;
                $_SESSION['nume'] = $nume;
                $_SESSION['role'] = $role; // rolul utilizatorului

                // redirect catre account.php
                header("Location: account.php");
                exit;
            } else {
                $error = "Parola incorecta!";
            }
        } else {
            $error = "Email-ul nu exista!";
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
    <title>Login | FitnessPro</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="login-section">
    <div class="card login-card">
        <h2>Autentificare</h2>

        <?php if($error) echo "<p class='error-message'>".e($error)."</p>"; ?>

        <form action="login.php" method="POST">
            <!-- CSRF token -->
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Parola</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <p style="margin-top:15px;">
            Nu ai cont? <a href="register.php" style="color:#c70000;">Inregistreaza-te</a>
        </p>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>
