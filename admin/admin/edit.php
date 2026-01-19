<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

/* CEK LOGIN */
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

/* HANDLE UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE admin 
            SET nama=?, username=?, email=?, password=? 
            WHERE id=?
        ");
        $stmt->execute([$nama,$username,$email,$hash,$id]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE admin 
            SET nama=?, username=?, email=? 
            WHERE id=?
        ");
        $stmt->execute([$nama,$username,$email,$id]);
    }

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Edit Admin</title>

<style>
/* CARD */
.form-card {
    background: #fff;
    padding: 24px;
    border-radius: 16px;
    border: 1px solid #f1dcdc;
}

/* FORM ROW */
.form-group {
    display: grid;
    grid-template-columns: 160px 1fr;
    align-items: center;
    gap: 16px;
    margin-bottom: 14px;
    padding-right: 30px;
}

label {
    font-weight: 700;
    font-size: 14px;
}

input {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #ddd;
    font-size: 14px;
}

input:focus {
    outline: none;
    border-color: #FF983D;
}

/* BUTTON */
.form-actions {
    display: flex;
    gap: 12px;
    margin-left: 176px;
}

.btn {
    background: linear-gradient(135deg, #FF74C7, #FF983D);
    color: #fff;
    border: none;
    padding: 12px 22px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
}

.btn.secondary {
    background: #e5e7eb;
    color: #374151;
}

.btn:hover {
    opacity: 0.9;
}

/* RESPONSIVE */
@media (max-width:600px){
    .form-group {
        grid-template-columns: 1fr;
    }
    .form-actions {
        margin-left: 0;
        flex-direction: column;
    }
}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">

    <div class="dashboard-header">
        <h1>Edit Admin</h1>
        <p>Perbarui data akun admin</p>
    </div>

    <div class="form-card">

        <form method="POST">

            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama"
                       value="<?= htmlspecialchars($admin['nama']) ?>" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username"
                       value="<?= htmlspecialchars($admin['username']) ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Kosongkan jika tidak diubah">
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn secondary">Batal</a>
                <button type="submit" class="btn">Update</button>
            </div>

        </form>

    </div>
</div>

</body>
</html>
