<?php
session_start();
require "../config/connection.php";

// cek login admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user']['username'];
$total_pengajuan = $pdo->query("SELECT COUNT(*) FROM pengajuan_ta")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin</title>
<link rel="stylesheet" href="../style.css">
<style>
.container { display: flex; min-height: 100vh; }
.sidebar { width: 220px; background: #333; color: white; padding: 20px; }
.sidebar ul { list-style: none; padding: 0; }
.sidebar ul li { margin: 15px 0; }
.sidebar ul li a { color: white; text-decoration: none; display: block; padding: 8px; border-radius: 5px; }
.sidebar ul li a:hover { background: #444; }
.content { flex: 1; padding: 20px; background: #f5f5f5; }
h1 { margin-top: 0; }
</style>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <h1>Selamat datang, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>Ini adalah dashboard admin. Gunakan menu di sidebar untuk mengelola sistem.</p>
        <p>Total Pengajuan TA: <b><?= $total_pengajuan ?></b></p>
    </div>
</div>
</body>
</html>
