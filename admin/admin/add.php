<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($nama && $username && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin (nama, username, email, password) VALUES (?,?,?,?)");
        $stmt->execute([$nama, $username, $email, $hash]);
        header("Location: index.php");
        exit;
    } else {
        $error = "Nama, username, dan password wajib diisi";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Tambah Admin</title></head>
<body>
<?php include '../sidebar.php'; ?>

<div class="content">
<h1>Tambah Admin</h1>

<?php if ($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>

<form method="post">
    Nama <br><input type="text" name="nama"><br>
    Username <br><input type="text" name="username"><br>
    Email <br><input type="email" name="email"><br>
    Password <br><input type="password" name="password"><br><br>
    <button class="btn">Simpan</button>
</form>
</div>
</body>
</html>
