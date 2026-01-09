<?php
session_start();
require "../config/connection.php";

if ($_SESSION['user']['role'] !== 'dosen') {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM dosbing_ta
    WHERE dosen_id = ?
");
$stmt->execute([$_SESSION['user']['id']]);
$total = $stmt->fetchColumn();
?>

<?php include "sidebar.php"; ?>

<div class="content">
    <h2>Dashboard Dosen</h2>
    <p>Total Mahasiswa Bimbingan: <b><?= $total ?></b></p>
</div>
