<?php
session_start();
require_once "../../config/connection.php";

// cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// cek parameter id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// ambil data dosen
$stmt = $pdo->prepare("SELECT * FROM dosen WHERE id = ?");
$stmt->execute([$id]);
$dosen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dosen) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $nip      = trim($_POST['nip']);
    $email    = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($nama && $nip && $email && $username) {

        // update dengan / tanpa password
        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "UPDATE dosen 
                 SET nama = ?, nip = ?, email = ?, username = ?, password = ?
                 WHERE id = ?"
            );
            $stmt->execute([$nama, $nip, $email, $username, $passwordHash, $id]);
        } else {
            $stmt = $pdo->prepare(
                "UPDATE dosen 
                 SET nama = ?, nip = ?, email = ?, username = ?
                 WHERE id = ?"
            );
            $stmt->execute([$nama, $nip, $email, $username, $id]);
        }

        header("Location: index.php");
        exit;

    } else {
        $error = "Semua field wajib diisi kecuali password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Dosen</title>
<link rel="stylesheet" href="/coba/style.css">
<style>
.content { padding: 20px; }
input { width: 300px; padding: 6px; margin-bottom: 10px; }
button.btn {
    padding: 8px 15px;
    background: #007BFF;
    color: white;
    border: none;
    border-radius: 4px;
}
button.btn:hover { background: #0056b3; }
</style>
</head>
<body>

<?php require_once __DIR__ . '/../sidebar.php'; ?>

<div class="content">
    <h1>Edit Dosen</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nama</label><br>
        <input type="text" name="nama" value="<?= htmlspecialchars($dosen['nama']); ?>" required><br>

        <label>NIP</label><br>
        <input type="text" name="nip" value="<?= htmlspecialchars($dosen['nip']); ?>" required><br>

        <label>Email</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($dosen['email'] ?? ''); ?>" required><br>


        <label>Username</label><br>
        <input type="text" name="username" value="<?= htmlspecialchars($dosen['username']); ?>" required><br>

        <label>Password <small>(kosongkan jika tidak diubah)</small></label><br>
        <input type="password" name="password"><br><br>

        <button type="submit" class="btn">Update</button>
    </form>
</div>

</body>
</html>
