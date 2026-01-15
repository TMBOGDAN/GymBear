<?php
session_start();
require_once __DIR__ . '/../db.php';

$id_utilizator = $_SESSION['user_id'] ?? null;
$success = $error = "";

// =====================
// PRELUARE ANTRENORI
// =====================
$result = $conn->query("SELECT * FROM antrenori");

// =====================
// PROCESARE FORMULAR
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_antrenor'])) {

    $id_antrenor  = intval($_POST['id_antrenor']);
    $data_sedinta = $_POST['data_sedinta'];
    $ora_sedinta  = $_POST['ora_sedinta'];

    // Verificare login
    if (!$id_utilizator) {
        $error = "Trebuie să fiți logat pentru a programa o ședință.";
    } else {
        // Verificare abonament
        $stmt = $conn->prepare("SELECT subscription_name FROM accounts WHERE id = ?");
        $stmt->bind_param("i", $id_utilizator);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if (!$user || empty($user['subscription_name'])) {
            $error = "Trebuie să aveți un abonament activ pentru a programa o ședință.";
        } else {
            // Preluare id abonament
            $stmt2 = $conn->prepare("SELECT id FROM abonamente WHERE nume = ?");
            $stmt2->bind_param("s", $user['subscription_name']);
            $stmt2->execute();
            $res_ab = $stmt2->get_result();
            $abonament = $res_ab->fetch_assoc();

            if (!$abonament) {
                $error = "Abonamentul dvs. nu este valid.";
            } else {
                $id_abonament = $abonament['id'];
                $status = 'programata';

                // verificare interval liber
                $check = $conn->prepare("
                    SELECT id FROM sedinte 
                    WHERE id_antrenor = ? 
                    AND data_sedinta = ? 
                    AND ora_sedinta = ?
                ");
                $check->bind_param("iss", $id_antrenor, $data_sedinta, $ora_sedinta);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $error = "Acest interval este deja ocupat!";
                } else {
                    $stmt_insert = $conn->prepare("
                        INSERT INTO sedinte
                        (id_antrenor, id_abonament, id_utilizator, data_sedinta, ora_sedinta, status)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt_insert->bind_param(
                        "iiisss",
                        $id_antrenor,
                        $id_abonament,
                        $id_utilizator,
                        $data_sedinta,
                        $ora_sedinta,
                        $status
                    );
                    if ($stmt_insert->execute()) {
                        $success = "Ședința a fost programată cu succes!";
                    } else {
                        $error = "A apărut o eroare la programare.";
                    }
                    $stmt_insert->close();
                }
                $check->close();
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Antrenori GymBear</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<h1>Antrenorii noștri</h1>
<div class="trainers-cards">
<?php
// afișare success/error ca pop-up
if ($success) echo "<script>alert('".addslashes($success)."');</script>";
if ($error) echo "<script>alert('".addslashes($error)."');</script>";
?>

<?php
if ($result->num_rows > 0) {
    while ($trainer = $result->fetch_assoc()) {
        echo "<div class='trainer-card'>";
        echo "<img src='../" . htmlspecialchars($trainer['poza']) . "' alt='" . htmlspecialchars($trainer['nume']) . "'>";
        echo "<h3>" . htmlspecialchars($trainer['nume']) . "</h3>";
        echo "<p>" . nl2br(htmlspecialchars($trainer['descriere'])) . "</p>";
        
        echo "<p><strong>Email:</strong> " . htmlspecialchars($trainer['email']) . "</p>";

        echo "
        <form method='POST'>
            <input type='hidden' name='id_antrenor' value='{$trainer['id']}'>
            
            <label>Data:
                <input type='date' name='data_sedinta' required>
            </label><br>

            <label>Ora:
                <input type='time' name='ora_sedinta' required>
            </label><br>

            <button type='submit'>Programează ședință</button>
        </form>
        ";

        echo "</div>";
    }
} else {
    echo "<p>Nu există antrenori disponibili momentan.</p>";
}
?>


</div>


<?php include '../includes/footer.php'; ?>

</body>


</html>
