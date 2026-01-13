<?php
session_start();
require "../config/connection.php";
require_once '../config/base_url.php';

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

</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1>Selamat datang, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>Ini adalah dashboard admin. Gunakan menu di sidebar untuk mengelola sistem.</p>
        <p>Total Pengajuan TA: <b><?= $total_pengajuan ?></b></p>
    </div>
</div>
</body>
</html>
