<?php
session_start();
require_once __DIR__ . '/../db.php';

$page = $_GET['page'] ?? 'abonamente';
$success = $error = "";

// export CSV pentru membrii
$today = date('Y-m-d');

if(isset($_GET['export_membri'])) {
    $role_filter = $_GET['role'] ?? '';
    $where = '';
    if($role_filter==='user' || $role_filter==='admin') $where="WHERE role='$role_filter'";

    $query = "
        SELECT id, username, email, role, subscription_name, subscription_end, created_at
        FROM accounts
        $where
        ORDER BY id DESC
    ";
    $result = $conn->query($query);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="membri.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID','Username','Email','Rol','Abonament','Valabil pana','Creat','Status']);

    while($row = $result->fetch_assoc()){
        $status = ($row['subscription_end'] && $row['subscription_end'] >= $today) ? 'Activ' : 'Inactiv';
        fputcsv($output, [
            $row['id'], 
            $row['username'], 
            $row['email'], 
            $row['role'], 
            $row['subscription_name'] ?: '-', 
            $row['subscription_end'] ?: '-', 
            $row['created_at'], 
            $status
        ]);
    }
    fclose($output);
    exit;
}

// export CSV abonamente
if(isset($_GET['export_abonamente'])) {
    $result = $conn->query("SELECT * FROM abonamente ORDER BY id DESC");

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="abonamente.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID','Nume','Pret','Durata','Facilitati','Descriere','Ora Start','Ora End','Imagine']);

    while($row = $result->fetch_assoc()){
        fputcsv($output, [
            $row['id'], $row['nume'], $row['pret'], $row['durata']??'-', $row['facilitati'], $row['descriere'], $row['ora_start'], $row['ora_end'], $row['imagine_url']
        ]);
    }
    fclose($output);
    exit;
}

// export CSV antrenori
if(isset($_GET['export_antrenori'])) {
    $result = $conn->query("SELECT * FROM antrenori ORDER BY id DESC");

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="antrenori.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID','Nume','Email','Poza','Descriere','Prezentare']);

    while($row = $result->fetch_assoc()){
        fputcsv($output, [
            $row['id'], $row['nume'], $row['email'], $row['poza'], $row['descriere'], $row['prezentare']
        ]);
    }
    fclose($output);
    exit;
}

// adaugare abonament
if (isset($_POST['add_abonament'])) {
    $stmt = $conn->prepare("
        INSERT INTO abonamente 
        (nume, pret, facilitati, descriere, ora_start, ora_end, imagine_url)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sdsssss",
        $_POST['nume'],
        $_POST['pret'],
        $_POST['facilitati'],
        $_POST['descriere'],
        $_POST['ora_start'],
        $_POST['ora_end'],
        $_POST['imagine_url']
    );
    $stmt->execute();
}

// stergere abonament
if (isset($_GET['delete_abonament'])) {
    $stmt = $conn->prepare("DELETE FROM abonamente WHERE id=?");
    $stmt->bind_param("i", $_GET['delete_abonament']);
    $stmt->execute();
}

// adaugare antrenor
if (isset($_POST['add_antrenor'])) {
    $stmt = $conn->prepare("
        INSERT INTO antrenori 
        (nume, poza, descriere, email, prezentare)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sssss",
        $_POST['nume'],
        $_POST['poza'],
        $_POST['descriere'],
        $_POST['email'],
        $_POST['prezentare']
    );
    $stmt->execute();
}

// stergere antrenor
if (isset($_GET['delete_antrenor'])) {
    $stmt = $conn->prepare("DELETE FROM antrenori WHERE id=?");
    $stmt->bind_param("i", $_GET['delete_antrenor']);
    $stmt->execute();
}

// stergere user
if (isset($_GET['delete_user'])) {
    $stmt = $conn->prepare("DELETE FROM accounts WHERE id=? AND role='user'");
    $stmt->bind_param("i", $_GET['delete_user']);
    $stmt->execute();
}

// creare admin
if (isset($_POST['create_admin'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO accounts (username, email, password_hash, role)
        VALUES (?, ?, ?, 'admin')
    ");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
    $success = "Admin creat cu succes.";
}

// update admin
if (isset($_POST['update_admin'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE accounts SET username=?, email=?, password_hash=? WHERE role='admin'
        ");
        $stmt->bind_param("sss", $username, $email, $password);
    } else {
        $stmt = $conn->prepare("
            UPDATE accounts SET username=?, email=? WHERE role='admin'
        ");
        $stmt->bind_param("ss", $username, $email);
    }
    $stmt->execute();
    $success = "Datele adminului au fost actualizate.";
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Admin Panel</title>
<style>
/* stil simplu pentru admin */
body { background:#111; color:#fff; font-family:Arial; margin:0; }
.container { padding:10px; }
button { background:#ff9800; border:none; padding:8px 14px; cursor:pointer; }
.form-box { background:#1c1c1c; padding:15px; margin:15px 0; }
input, textarea, select { width:100%; padding:8px; margin:6px 0; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th, td { border:1px solid #333; padding:8px; }
a.delete { color:#ff4444; }
.success { color:#4caf50; }
i { color:#aaa; }
</style>
<script>
function toggleForm(id) {
    const el = document.getElementById(id);
    el.style.display = (el.style.display === 'block') ? 'none' : 'block';
}
</script>
<link rel="stylesheet" href="/style/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<nav>
    <a href="?page=abonamente" class="<?= $page==='abonamente'?'active':'' ?>">Abonamente</a>
    <a href="?page=antrenori" class="<?= $page==='antrenori'?'active':'' ?>">Antrenori</a>
    <a href="?page=membri" class="<?= $page==='membri'?'active':'' ?>">Membri</a>
</nav>

<div class="container">
<?= $success ? "<p class='success'>$success</p>" : "" ?>

<!-- pagina abonamente -->
<?php if ($page==='abonamente'): 
    $abonamente = $conn->query("SELECT * FROM abonamente ORDER BY id DESC");
?>
<h2>Abonamente</h2>
<button onclick="toggleForm('form-abonament')">➕ Adauga abonament</button>
<div id="form-abonament" class="form-box" style="display:none;">
<form method="post">
    <input name="nume" placeholder="Nume" required>
    <input name="pret" type="number" step="0.01" placeholder="Pret" required>
    <textarea name="facilitati" placeholder="Facilitati"></textarea>
    <input name="descriere" placeholder="Descriere">
    <input name="ora_start" type="time">
    <input name="ora_end" type="time">
    <input name="imagine_url" placeholder="URL imagine">
    <button name="add_abonament">Salveaza</button>
</form>
</div>

<a href="?page=abonamente&export_abonamente=1" class="btn btn--green">⬇ Export CSV</a>
<table>
<tr><th>ID</th><th>Nume</th><th>Pret</th><th>Actiuni</th></tr>
<?php while ($a = $abonamente->fetch_assoc()): ?>
<tr>
<td><?= $a['id'] ?></td>
<td><?= htmlspecialchars($a['nume']) ?></td>
<td><?= $a['pret'] ?> lei</td>
<td>
<a class="delete" href="?page=abonamente&delete_abonament=<?= $a['id'] ?>" onclick="return confirm('Stergi?')">Sterge</a>
</td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

<!-- pagina antrenori -->
<?php if ($page==='antrenori'):
    $antrenori = $conn->query("SELECT * FROM antrenori ORDER BY id DESC");
?>
<h2>Antrenori</h2>
<button onclick="toggleForm('form-antrenor')">➕ Adauga antrenor</button>
<div id="form-antrenor" class="form-box" style="display:none;">
<form method="post">
    <input name="nume" placeholder="Nume" required>
    <input name="poza" placeholder="URL poza">
    <textarea name="descriere" placeholder="Descriere"></textarea>
    <input name="email" placeholder="Email">
    <textarea name="prezentare" placeholder="Prezentare"></textarea>
    <button name="add_antrenor">Salveaza</button>
</form>
</div>
<a href="?page=antrenori&export_antrenori=1" class="btn btn--green">⬇ Export CSV</a>
<table>
<tr><th>ID</th><th>Nume</th><th>Email</th><th>Actiuni</th></tr>
<?php while ($t = $antrenori->fetch_assoc()): ?>
<tr>
<td><?= $t['id'] ?></td>
<td><?= htmlspecialchars($t['nume']) ?></td>
<td><?= htmlspecialchars($t['email']) ?></td>
<td>
<a class="delete" href="?page=antrenori&delete_antrenor=<?= $t['id'] ?>" onclick="return confirm('Stergi?')">Sterge</a>
</td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

<!-- pagina membri -->
<?php if ($page==='membri'):
$role_filter = $_GET['role'] ?? '';
$where = '';
if($role_filter==='user' || $role_filter==='admin') $where="WHERE role='$role_filter'";
$membri = $conn->query("SELECT id, username, email, role, subscription_name, subscription_end, created_at FROM accounts $where ORDER BY id DESC");
?>
<h2>Membri</h2>

<form method="get" style="margin-bottom:15px;">
<input type="hidden" name="page" value="membri">
<select name="role" onchange="this.form.submit()">
    <option value="">Toti</option>
    <option value="user" <?= $role_filter==='user'?'selected':'' ?>>User</option>
    <option value="admin" <?= $role_filter==='admin'?'selected':'' ?>>Admin</option>
</select>

<button type="submit" name="export_membri" value="1" class="btn btn--green">⬇ Export CSV</button>
</form>

<button onclick="toggleForm('form-create-admin')">➕ Creeaza Admin</button>
<div id="form-create-admin" class="form-box" style="display:none;">
<form method="post">
<input name="username" placeholder="Username" required>
<input name="email" type="email" placeholder="Email" required>
<input name="password" type="password" placeholder="Parola" required>
<button name="create_admin">Creeaza Admin</button>
</form>
</div>

<table>
<tr>
<th>ID</th><th>Username</th><th>Email</th><th>Rol</th>
<th>Abonament</th><th>Valabil pana</th><th>Creat</th><th>Actiuni</th>
</tr>
<?php while ($m = $membri->fetch_assoc()): ?>
<tr>
<td><?= $m['id'] ?></td>
<td><?= htmlspecialchars($m['username']) ?></td>
<td><?= htmlspecialchars($m['email']) ?></td>
<td><?= $m['role'] ?></td>
<td><?= $m['subscription_name'] ?: '<i>Fara abonament</i>' ?></td>
<td><?= $m['subscription_end'] ?: '-' ?></td>
<td><?= $m['created_at'] ?></td>
<td>
<?php if($m['role']=='user'): ?>
<a class="delete" href="?page=membri&delete_user=<?= $m['id'] ?>" onclick="return confirm('Stergi userul?')">Sterge</a>
<?php else: ?> — <?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
