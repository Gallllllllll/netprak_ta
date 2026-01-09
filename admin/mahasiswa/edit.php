<?php
session_start();
require "../../config/connection.php";

// cek login admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = ?");
$stmt->execute([$id]);
$mahasiswa = $stmt->fetch();
if(!$mahasiswa) {
    header("Location: index.php");
    exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if($nama && $nim && $username) {
        // cek NIM unik
        $cek_nim = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ? AND id <> ?");
        $cek_nim->execute([$nim, $id]);
        if($cek_nim->rowCount() > 0){
            $error = "NIM '$nim' sudah dipakai mahasiswa lain!";
        } else {
            if($password) {
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE mahasiswa SET nama=?, nim=?, prodi=?, kelas=?, nomor_telepon=?, email=?, username=?, password=? WHERE id=?");
                $stmt->execute([$nama,$nim,$prodi,$kelas,$nomor_telepon,$email,$username,$password_hashed,$id]);
            } else {
                $stmt = $pdo->prepare("UPDATE mahasiswa SET nama=?, nim=?, prodi=?, kelas=?, nomor_telepon=?, email=?, username=? WHERE id=?");
                $stmt->execute([$nama,$nim,$prodi,$kelas,$nomor_telepon,$email,$username,$id]);
            }
            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Nama, NIM, dan username wajib diisi!";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Mahasiswa</title>
<link rel="stylesheet" href="../../style.css">
</head>
<body>
<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="content">
    <h1>Edit Mahasiswa</h1>
    <?php if($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <label>Nama:</label><br>
        <input type="text" name="nama" value="<?= htmlspecialchars($mahasiswa['nama'] ?? ''); ?>" required><br>

        <label>NIM:</label><br>
        <input type="text" name="nim" value="<?= htmlspecialchars($mahasiswa['nim'] ?? ''); ?>" required><br>

        <label>Prodi:</label><br>
        <select name="prodi">
            <option value="">-- Pilih Prodi --</option>
            <option value="Seni Kuliner" <?= ($mahasiswa['prodi'] ?? '') === 'Seni Kuliner' ? 'selected' : ''; ?>>Seni Kuliner</option>
            <option value="Teknologi Informasi" <?= ($mahasiswa['prodi'] ?? '') === 'Teknologi Informasi' ? 'selected' : ''; ?>>Teknologi Informasi</option>
            <option value="Perhotelan" <?= ($mahasiswa['prodi'] ?? '') === 'Perhotelan' ? 'selected' : ''; ?>>Perhotelan</option>
        </select><br>

        <label>Kelas:</label><br>
        <input type="text" name="kelas" value="<?= htmlspecialchars($mahasiswa['kelas'] ?? ''); ?>"><br>

        <label>Nomor Telepon:</label><br>
        <input type="text" name="nomor_telepon" value="<?= htmlspecialchars($mahasiswa['nomor_telepon'] ?? ''); ?>"><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($mahasiswa['email'] ?? ''); ?>"><br>

        <label>Username:</label><br>
        <input type="text" name="username" value="<?= htmlspecialchars($mahasiswa['username'] ?? ''); ?>" required><br>

        <label>Password (kosongkan kalau tidak diubah):</label><br>
        <input type="password" name="password"><br><br>

        <button type="submit" class="btn">Update</button>
    </form>
</div>
</body>
</html>
