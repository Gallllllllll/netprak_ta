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
    $nip = trim($_POST['nip']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    if($nama && $nip && $username && $_POST['password']) {
        $stmt = $pdo->prepare("INSERT INTO dosen (nama,nip,email,username,password) VALUES (?,?,?,?,?)");
        $stmt->execute([$nama, $nip, $_POST['email'], $username, $password]);

        header("Location: index.php");
        exit;
    } else {
        $error = "Nama, NIP, username dan password wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Dosen</title>
<link rel="stylesheet" href="../../style.css">
</head>
<body>
<?php include '../sidebar.php'; ?>

<div class="content">
    <h1>Tambah Dosen</h1>
    <?php if($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <label>Nama:</label><br>
        <input type="text" name="nama" required><br>
        <label>NIP:</label><br>
        <input type="text" name="nip" required><br>
        <label>Email:</label><br>
        <input type="text" name="email" required><br>
        <label>Username:</label><br>
        <input type="text" name="username" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit" class="btn">Simpan</button>
    </form>
</div>
</body>
</html>
