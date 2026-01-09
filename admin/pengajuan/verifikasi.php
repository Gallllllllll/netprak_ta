<?php
session_start();
require "../../config/connection.php";

// cek admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin') {
    header("Location: ../../login.php");
    exit;
}

// ambil data dari form
$id = $_POST['id'];
$status = $_POST['status'];
$catatan = $_POST['catatan'];
$dosen1 = $_POST['dosen1'];
$dosen2 = $_POST['dosen2'];

// 1. update status + catatan
$stmt = $pdo->prepare("UPDATE pengajuan_ta SET status=?, catatan_admin=? WHERE id=?");
$stmt->execute([$status, $catatan, $id]);

// 2. plot/update dosbing
// pastikan dosbing_ta sudah punya UNIQUE(pengajuan_id, role)
$pdo->prepare("
    INSERT INTO dosbing_ta (pengajuan_id, dosen_id, role)
    VALUES (?, ?, 'dosbing_1')
    ON DUPLICATE KEY UPDATE dosen_id=VALUES(dosen_id)
")->execute([$id, $dosen1]);

$pdo->prepare("
    INSERT INTO dosbing_ta (pengajuan_id, dosen_id, role)
    VALUES (?, ?, 'dosbing_2')
    ON DUPLICATE KEY UPDATE dosen_id=VALUES(dosen_id)
")->execute([$id, $dosen2]);

// selesai, kembali ke list pengajuan
header("Location: index.php");
exit;
