<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$pengajuan_id = $_POST['pengajuan_id'] ?? null; // ID pengajuan_semhas
$dosen_id     = $_POST['dosen_id'] ?? null;

if (!$pengajuan_id || !$dosen_id) {
    die("Data tidak lengkap");
}

/* ===============================
   VALIDASI SEMHAS
================================ */
$cekSemhas = $pdo->prepare("
    SELECT id 
    FROM pengajuan_semhas 
    WHERE id = ?
");
$cekSemhas->execute([$pengajuan_id]);

if (!$cekSemhas->fetch()) {
    die("Pengajuan SEMHAS tidak ditemukan.");
}

/* ===============================
   CEGAH PENGUJI DOBEL
================================ */
$cek = $pdo->prepare("
    SELECT id 
    FROM tim_semhas
    WHERE pengajuan_id = ? AND peran = 'penguji'
");
$cek->execute([$pengajuan_id]);

if ($cek->fetch()) {
    die("Penguji sudah ditetapkan.");
}

/* ===============================
   SIMPAN PENGUJI
================================ */
$stmt = $pdo->prepare("
    INSERT INTO tim_semhas (pengajuan_id, dosen_id, peran)
    VALUES (?, ?, 'penguji')
");
$stmt->execute([$pengajuan_id, $dosen_id]);

header("Location: index.php?id=" . $pengajuan_id);
exit;
