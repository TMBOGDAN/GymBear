<?php
include 'db.php';

// Preluare abonamente
$sql = "SELECT nume, pret, ora_start, ora_end, facilitati, descriere, imagine_url FROM abonamente";
$result = $conn->query($sql);
if ($result === false) {
    error_log("SQL error (abonamente): " . $conn->error);
    $result = (object) ['num_rows' => 0];
}

// Preluare antrenori
$sql_antrenori = "SELECT nume, poza, descriere FROM antrenori";
$result_antrenori = $conn->query($sql_antrenori);

// Afisare erori pentru debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gym Bear</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- Sectiune banner -->
<div class="banner">
    <img src="resources/images/banner12.png" alt="Gym Banner" class="banner-img">
</div>

<!-- Sectiune despre sala -->
<section id="despre">
    <h2>Despre Sala Noastra</h2>
    <p>
        Descopera Gym Bear – locul unde performanta intalneste profesionalismul!
        Echipamente de ultima generatie, spatii moderne si antrenori certificati gata sa te ghideze pas cu pas. 
        Indiferent daca vrei sa slabesti, sa te tonifiezi sau sa iti cresti masa musculara, 
        la noi gasesti tot ce ai nevoie pentru un antrenament eficient si sigur.
    </p>
</section>

<!-- Sectiune servicii -->
<section id="servicii">
    <h2>Serviciile Noastre</h2>
    <div class="services">
        <!-- Card Cardio -->
        <div class="card">
            <img src="resources/images/cardio.jpg" alt="Cardio" class="card-img"> 
            <h3>Cardio</h3>
            <p>Banda, bicicleta, stepper – pentru o inima sanatoasa si energie.</p>
        </div>
        <!-- Card Forta -->
        <div class="card">
            <img src="resources/images/strenght.jpg" alt="Forta" class="card-img">
            <h3>Forta</h3>
            <p>Antrenamente cu greutati si exercitii functionale pentru tonifiere.</p>
        </div>
        <!-- Card Fitness de grup -->
        <div class="card">
            <img src="resources/images/grup.png" alt="Fitness" class="card-img">
            <h3>Fitness de grup</h3>
            <p>Zumba, HIIT, yoga – clase dinamice si distractive.</p>
        </div>
    </div>
</section>

<!-- Sectiune abonamente -->
<section id="abonamente">
    <h2>Abonamente</h2>
    <div class="membership-plans">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $img = $row['imagine_url'] ?: "/GymBear/resources/images/default.jpg"; ?>
                <!-- Card abonament -->
                <a href="/pages/membership.php?tip=<?= urlencode($row['nume']) ?>" class="card-link">
                    <div class="card">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($row['nume']) ?>_membership" class="card-img">
                        <h3><?= htmlspecialchars($row['nume']) ?></h3>
                        <ul>
                            <li><strong>Pret:</strong> <?= htmlspecialchars($row['pret']) ?> Lei / Luna</li>
                            <li><strong>Orar Acces:</strong> <?= htmlspecialchars($row['ora_start']) ?> - <?= htmlspecialchars($row['ora_end']) ?></li>
                            <li><strong>Facilitati Cheie:</strong> <?= htmlspecialchars($row['facilitati']) ?></li>
                        </ul>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nu exista abonamente disponibile momentan.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Sectiune antrenori -->
<section id="antrenori">
    <h2>Antrenorii Nostri</h2>
    <div class="trainers">
        <?php if ($result_antrenori && $result_antrenori->num_rows > 0): ?>
            <?php while ($row = $result_antrenori->fetch_assoc()): 
                $img = !empty($row['poza']) ? '/' . ltrim($row['poza'], '/') : '/resources/images/default_trainer.jpg'; ?>
                <!-- Card antrenor -->
                <a href="/pages/trainers.php" class="card-link">
                    <div class="card">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($row['nume']) ?>" class="card-img">
                        <h3><?= htmlspecialchars($row['nume']) ?></h3>
                        <p><?= htmlspecialchars($row['descriere']) ?></p>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Momentan nu exista antrenori disponibili.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
