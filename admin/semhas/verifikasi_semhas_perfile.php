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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id            = $_POST['id'] ?? null;
    $status_all    = $_POST['status'] ?? [];
    $catatan_file  = $_POST['catatan_file'] ?? [];
    $catatan_all   = $_POST['catatan'] ?? null; // ✅ STRING

    if (!$id || empty($status_all)) {
        die("Data tidak lengkap.");
    }

    // ===============================
    // SIMPAN CATATAN KESELURUHAN
    // ===============================
    $stmt = $pdo->prepare("
        UPDATE pengajuan_semhas 
        SET catatan = ?
        WHERE id = ?
    ");
    $stmt->execute([$catatan_all, $id]);

    // ===============================
    // DAFTAR FILE SEMHAS
    // ===============================
    $files = [
        'berita_acara',
        'persetujuan_laporan',
        'pendaftaran_ujian',
        'buku_konsultasi'
    ];

    // ===============================
    // UPDATE STATUS PER FILE
    // ===============================
    foreach ($files as $key) {

        $status_field  = "status_file_$key";
        $catatan_field = "catatan_file_$key";

        $status  = $status_all[$key] ?? 'diajukan';
        $catatan = $catatan_file[$key] ?? '';

        if (!in_array($status, ['diajukan','revisi','disetujui','ditolak'])) {
            die("Status tidak valid.");
        }

        $stmt = $pdo->prepare("
            UPDATE pengajuan_semhas 
            SET $status_field = ?, $catatan_field = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $catatan, $id]);
    }

    // ===============================
    // AMBIL DATA TERBARU
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
    // UPDATE STATUS KESELURUHAN
    // ===============================
    $stmt = $pdo->prepare("
        UPDATE pengajuan_semhas 
        SET status = ?
        WHERE id = ?
    ");
    $stmt->execute([$overall_status, $id]);

    // ===============================
    // REDIRECT
    // ===============================
    header("Location: index.php?id=$id");
    exit;
}
