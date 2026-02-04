<?php
session_start();
require_once __DIR__ . '/../db.php';

// verifică dacă utilizatorul este logat
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('Trebuie sa fii logat pentru a accesa pagina de abonamente!');
            window.location.href = '/pages/login.php';
          </script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// preia date utilizator
$stmt = $conn->prepare("SELECT * FROM accounts WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// preia abonamentele
$abonamentSelectat = isset($_GET['id']) ? (int)$_GET['id'] : null;
$abonamente = [];
$sql = "SELECT id, nume, pret, descriere, ora_start, ora_end, imagine_url, facilitati
        FROM abonamente
        ORDER BY id";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $abonamente[] = $row;
}

// preia abonament selectat dacă există
$abonament = null;
if ($abonamentSelectat) {
    $stmt = $conn->prepare("SELECT * FROM abonamente WHERE id=?");
    $stmt->bind_param("i", $abonamentSelectat);
    $stmt->execute();
    $abonament = $stmt->get_result()->fetch_assoc();
}

// cumpara abonament
$mesaj = "";
if (isset($_GET['buy']) && $abonament) {

    // dacă utilizatorul are deja un abonament activ
    if ($user['subscription_end'] && $user['subscription_end'] >= $today) {
        $mesaj = "Ai deja un abonament activ! Trebuie să aștepți expirarea acestuia pentru a cumpăra altul.";
    } else {
        $start = date('Y-m-d');
        $end   = date('Y-m-d', strtotime('+30 days'));
        $stmt = $conn->prepare("UPDATE accounts SET subscription_name=?, subscription_end=? WHERE id=?");
        $stmt->bind_param("ssi", $abonament['nume'], $end, $user_id);
        $stmt->execute();
        $mesaj = "Abonamentul a fost activat cu succes!";
        $user['subscription_name'] = $abonament['nume'];
        $user['subscription_end'] = $end;
    }
}


function parse_facilitati($text) {
    if (!$text) return [];
    return array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $text)));
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>GymBear – Abonamente</title>
<link rel="stylesheet" href="/style/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main>
    <h1>Alege abonamentul potrivit</h1>

    <?php if ($mesaj): ?>
        <div class="message"><?= htmlspecialchars($mesaj) ?></div>
    <?php endif; ?>

    <!-- afișează cardurile abonamentelor -->
    <div class="plans">
        <?php foreach ($abonamente as $a): ?>
            <div class="plan-card">
                <h3><?= htmlspecialchars($a['nume']) ?></h3>
                <p><?= htmlspecialchars($a['descriere']) ?></p>
                <div class="price"><?= htmlspecialchars($a['pret']) ?> Lei</div>
                <?php if ($a['ora_start'] && $a['ora_end']): ?>
                    <div class="hours"><?= date('H:i', strtotime($a['ora_start'])) ?> – <?= date('H:i', strtotime($a['ora_end'])) ?></div>
                <?php endif; ?>
                <a class="btn btn-red" href="?id=<?= $a['id'] ?>">Vezi detalii</a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- afișează detalii abonament selectat -->
    <?php if ($abonament): ?>
        <div class="details">
            <?php
            $img = !empty($abonament['imagine_url']) ? '/' . ltrim($abonament['imagine_url'], '/') : '/resources/images/default.jpg';
            ?>
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($abonament['nume']) ?>">
            <h2>Abonament <?= htmlspecialchars($abonament['nume']) ?></h2>
            <p><strong>Pret:</strong> <?= htmlspecialchars($abonament['pret']) ?> Lei</p>

            <?php if ($abonament['ora_start'] && $abonament['ora_end']): ?>
                <p><strong>Acces:</strong> <?= date('H:i', strtotime($abonament['ora_start'])) ?> – <?= date('H:i', strtotime($abonament['ora_end'])) ?></p>
            <?php endif; ?>

            <p><?= nl2br(htmlspecialchars($abonament['descriere'])) ?></p>

            <?php $facilitati = parse_facilitati($abonament['facilitati']); ?>
            <?php if ($facilitati): ?>
                <ul>
                    <?php foreach ($facilitati as $f): ?>
                        <li><?= htmlspecialchars($f) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- buton cumpără abonament -->
            <?php 
            $abonamentActiv = ($user['subscription_end'] && $user['subscription_end'] >= $today);
            ?>
            <?php if ($abonamentActiv): ?>
                <span class="btn btn-red disabled">Ai deja un abonament activ</span>
            <?php else: ?>
                <a class="btn btn-red" href="?id=<?= $abonament['id'] ?>&buy=1">Cumpără abonament</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
