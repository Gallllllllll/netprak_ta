<?php
session_start();
require "../../config/connection.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $nim = trim($_POST['nim']);
    $prodi = trim($_POST['prodi']);
    $kelas = trim($_POST['kelas']);
    $nomor_telepon = trim($_POST['nomor_telepon']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    if($nama && $nim && $username && $_POST['password']) {
        $stmt = $pdo->prepare("INSERT INTO mahasiswa (nama,nim,prodi,kelas,nomor_telepon,username,password) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$nama,$nim,$prodi,$kelas,$nomor_telepon,$username,$password]);
        header("Location: index.php");
        exit;
    } else {
        $error = "Nama, NIM, username dan password wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Mahasiswa</title>
<link rel="stylesheet" href="../../style.css">
</head>
<body>
<?php include '../sidebar.php'; ?>

<div class="content">
    <h1>Tambah Mahasiswa</h1>
    <?php if($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <label>Nama:</label><br>
        <input type="text" name="nama" required><br>
        <label>NIM:</label><br>
        <input type="text" name="nim" required><br>
        <label>Email:</label><br>
        <input type="text" name="email" required><br>
        <label>Prodi:</label><br>
            <select name="prodi">
                <option value="">-- Pilih Prodi --</option>
                <option value="Seni Kuliner">Seni Kuliner</option>
                <option value="Teknologi Informasi">Teknologi Informasi</option>
                <option value="Perhotelan">Perhotelan</option>
            </select><br>

        <label>Kelas:</label><br>
        <input type="text" name="kelas"><br>
        <label>Nomor Telepon:</label><br>
        <input type="text" name="nomor_telepon"><br>
        <label>Username:</label><br>
        <input type="text" name="username" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit" class="btn">Simpan</button>
    </form>
</div>
</body>
</html>
