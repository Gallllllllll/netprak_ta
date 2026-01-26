<?php
session_start();
require "../../config/connection.php";

// ===============================
// CEK ROLE ADMIN
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Method tidak valid");
}

$id = $_POST['id'] ?? null;
$status_all = $_POST['status'] ?? [];
$catatan_file = $_POST['catatan_file'] ?? [];
$catatan_keseluruhan = trim($_POST['catatan'] ?? '');

if (!$id) {
    die("ID tidak ditemukan.");
}

// ===============================
// DAFTAR FILE SEMHAS (VALIDASI KOLOM)
// ===============================
$files = [
    'berita_acara',
    'persetujuan_laporan',
    'pendaftaran_ujian',
    'buku_konsultasi'
];

// ===============================
// UPDATE STATUS & CATATAN PER FILE
// ===============================
foreach ($files as $key) {

    if (!isset($status_all[$key])) {
        continue;
    }

    $status = $status_all[$key];
    $catatan = trim($catatan_file[$key] ?? '');

    if (!in_array($status, ['diajukan', 'revisi', 'disetujui', 'ditolak'])) {
        continue;
    }

    $status_field = "status_file_$key";
    $catatan_field = "catatan_file_$key";

    $stmt = $pdo->prepare("
        UPDATE pengajuan_semhas
        SET $status_field = ?, $catatan_field = ?
        WHERE id = ?
    ");
    $stmt->execute([$status, $catatan, $id]);
}

// ===============================
// AMBIL STATUS TERBARU
// ===============================
$stmt = $pdo->prepare("SELECT * FROM pengajuan_semhas WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// ===============================
// HITUNG STATUS KESELURUHAN
// ===============================
$statuses = [
    $data['status_file_berita_acara'],
    $data['status_file_persetujuan_laporan'],
    $data['status_file_pendaftaran_ujian'],
    $data['status_file_buku_konsultasi']
];

if (in_array('ditolak', $statuses)) {
    $overall_status = 'ditolak';
} elseif (count(array_unique($statuses)) === 1 && $statuses[0] === 'disetujui') {
    $overall_status = 'disetujui';
} elseif (in_array('revisi', $statuses)) {
    $overall_status = 'revisi';
} else {
    $overall_status = 'diajukan';
}

// ===============================
// UPDATE STATUS & CATATAN KESELURUHAN
// ===============================
$stmt = $pdo->prepare("
    UPDATE pengajuan_semhas
    SET status = ?, catatan = ?
    WHERE id = ?
");
$stmt->execute([$overall_status, $catatan_keseluruhan, $id]);

// ===============================
// REDIRECT BALIK KE DETAIL
// ===============================
header("Location: index.php?id=" . $id);
exit;
