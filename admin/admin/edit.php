<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM admin WHERE id=?");
$stmt->execute([$id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin SET nama=?, username=?, email=?, password=? WHERE id=?");
        $stmt->execute([$nama,$username,$email,$hash,$id]);
    } else {
        $stmt = $pdo->prepare("UPDATE admin SET nama=?, username=?, email=? WHERE id=?");
        $stmt->execute([$nama,$username,$email,$id]);
    }
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html>
<body>
<?php include '../sidebar.php'; ?>
<div class="content">
<h1>Edit Admin</h1>

<form method="post">
    Nama <br><input type="text" name="nama" value="<?= htmlspecialchars($admin['nama']) ?>"><br>
    Username <br><input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>"><br>
    Email <br><input type="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>"><br>
    Password (kosongkan jika tidak diubah)<br>
    <input type="password" name="password"><br><br>
    <button class="btn">Update</button>
</form>
</div>
</body>
</html>
