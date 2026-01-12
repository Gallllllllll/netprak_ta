<?php
session_start();
require "../../config/connection.php";

// cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip      = trim($_POST['nip']);
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if ($nip && $nama && $username && $password) {

        // cek NIP atau username sudah ada
        $cek = $pdo->prepare("SELECT id FROM admin WHERE nip = ? OR username = ?");
        $cek->execute([$nip, $username]);

        if ($cek->rowCount() > 0) {
            $error = "NIP atau Username sudah terdaftar";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO admin (nip, nama, username, email, password)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nip, $nama, $username, $email, $hash]);

            header("Location: index.php");
            exit;
        }
    } else {
        $error = "NIP, Nama, Username, dan Password wajib diisi";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Admin</title>

<link rel="stylesheet" href="/coba/style.css">

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    background: #f4f6f9;
}
.content {
    padding: 20px;
}
form {
    max-width: 500px;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
}
label {
    font-weight: bold;
    display: block;
    margin-top: 12px;
}
input {
    width: 100%;
    padding: 8px;
    margin-top: 4px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
button {
    margin-top: 20px;
    padding: 10px 15px;
    background: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    opacity: 0.9;
}
.error {
    color: red;
    margin-bottom: 10px;
}
</style>
</head>
<body>

<?php include '../sidebar.php'; ?>

<div class="content">
    <h1>Tambah Admin</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <label>NIP</label>
        <input type="text" name="nip" required>

        <label>Nama</label>
        <input type="text" name="nama" required>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email">

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Simpan</button>
    </form>
</div>

</body>
</html>
