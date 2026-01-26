<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$pengajuan_id = $_POST['pengajuan_id'] ?? null;
$dosen_id     = $_POST['dosen_id'] ?? null;

if (!$pengajuan_id || !$dosen_id) {
    die("Data tidak lengkap");
}

// Cegah penguji dobel
$cek = $pdo->prepare("
    SELECT id FROM tim_semhas
    WHERE pengajuan_id = ? AND peran = 'penguji'
");
$cek->execute([$pengajuan_id]);

if ($cek->fetch()) {
    die("Penguji sudah ditetapkan.");
}

// Simpan penguji
$stmt = $pdo->prepare("
    INSERT INTO tim_semhas (pengajuan_id, dosen_id, peran)
    VALUES (?, ?, 'penguji')
");
$stmt->execute([$pengajuan_id, $dosen_id]);

header("Location: index.php?id=" . $pengajuan_id);
exit;