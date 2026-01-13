<?php
session_start();
require "../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: ../login.php");
    exit;
}

// hitung total mahasiswa bimbingan
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT pengajuan_id)
    FROM dosbing_ta
    WHERE dosen_id = ?
");
$stmt->execute([$_SESSION['user']['id']]);
$total = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Dosen</title>

<link rel="stylesheet" href="../style.css">

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
}

.container {
    display: flex;
    min-height: 100vh;
}


/* HEADER */
.header {
    margin-bottom: 25px;
}
.header h2 {
    margin: 0;
}
.header p {
    color: #666;
    margin-top: 5px;
}

/* DASHBOARD CARD */
.dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
}

.card {
    background: #fff;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 4px 10px rgba(0,0,0,.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card .info {
    display: flex;
    flex-direction: column;
}

.card .info span {
    color: #777;
    font-size: 14px;
}

.card .info h3 {
    margin: 5px 0 0;
    font-size: 28px;
}

.icon {
    font-size: 42px;
    opacity: .2;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .content {
        padding: 20px;
    }
}
</style>
</head>
<body>

<div class="container">

    <!-- SIDEBAR -->
    <?php include "sidebar.php"; ?>

    <!-- CONTENT -->
    <div class="main-content">

        <div class="header">
            <h2>Dashboard Dosen</h2>
            <p>Selamat datang, <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Dosen') ?></p>
        </div>

        <div class="dashboard">
            <div class="card">
                <div class="info">
                    <span>Total Mahasiswa Bimbingan</span>
                    <h3><?= $total ?></h3>
                </div>
                <div class="icon">🎓</div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
