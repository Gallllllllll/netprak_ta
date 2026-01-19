<?php
session_start();
require "../../config/connection.php";

// cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? null;
    $status_all = $_POST['status'] ?? [];
    $catatan_all = $_POST['catatan'] ?? [];

    if (!$id || empty($status_all)) {
        die("Data tidak lengkap.");
    }

    // daftar file sempro
    $files = ['pendaftaran', 'persetujuan', 'buku_konsultasi'];

    /* ========================
       UPDATE STATUS PER FILE
    ======================== */
    foreach ($files as $key) {

        $status_field  = "status_file_$key";
        $catatan_field = "catatan_file_$key";

        // DEFAULT STATUS = diajukan (AMAN ENUM)
        $status  = $status_all[$key] ?? 'diajukan';
        $catatan = $catatan_all[$key] ?? '';

        // validasi enum biar aman
        $allowed_status = ['diajukan','revisi','disetujui','ditolak'];
        if (!in_array($status, $allowed_status)) {
            $status = 'diajukan';
        }

        $stmt = $pdo->prepare("
            UPDATE pengajuan_sempro 
            SET $status_field = ?, $catatan_field = ? 
            WHERE id = ?
        ");
        $stmt->execute([$status, $catatan, $id]);
    }

    /* ========================
       HITUNG STATUS KESELURUHAN
    ======================== */
    $stmt = $pdo->prepare("SELECT * FROM pengajuan_sempro WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $statuses = [
        $data['status_file_pendaftaran'],
        $data['status_file_persetujuan'],
        $data['status_file_buku_konsultasi']
    ];

    if (in_array('ditolak', $statuses)) {
        $overall_status = 'ditolak';
    } elseif (in_array('revisi', $statuses)) {
        $overall_status = 'revisi';
    } elseif (
        $statuses[0] === 'disetujui' &&
        $statuses[1] === 'disetujui' &&
        $statuses[2] === 'disetujui'
    ) {
        $overall_status = 'disetujui';
    } else {
        $overall_status = 'diajukan';
    }

    $pdo->prepare("
        UPDATE pengajuan_sempro 
        SET status = ? 
        WHERE id = ?
    ")->execute([$overall_status, $id]);

    header("Location: index.php?id=$id");
    exit;
}
