<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? null;
    $status_all = $_POST['status'] ?? [];
    $catatan_all = $_POST['catatan_file'] ?? [];  // catatan per file (array)
    $catatan_keseluruhan = $_POST['catatan'] ?? '';  // catatan keseluruhan

    if (!$id || empty($status_all)) {
        die("Data tidak lengkap.");
    }

    $files = ['pendaftaran', 'persetujuan', 'buku_konsultasi'];

    foreach ($files as $key) {
        $status_field  = "status_file_$key";
        $catatan_field = "catatan_file_$key";

        $status  = $status_all[$key] ?? 'diajukan';
        $catatan = $catatan_all[$key] ?? '';

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

    // hitung status keseluruhan
    $stmt = $pdo->prepare("SELECT * FROM pengajuan_sempro WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $statuses = [
        $data['status_file_pendaftaran'] ?? 'diajukan',
        $data['status_file_persetujuan'] ?? 'diajukan',
        $data['status_file_buku_konsultasi'] ?? 'diajukan'
    ];

    if (in_array('ditolak', $statuses)) {
        $overall_status = 'ditolak';
    } elseif (in_array('revisi', $statuses)) {
        $overall_status = 'revisi';
    } elseif ($statuses[0] === 'disetujui' && $statuses[1] === 'disetujui' && $statuses[2] === 'disetujui') {
        $overall_status = 'disetujui';
    } else {
        $overall_status = 'diajukan';
    }

    // update status + catatan keseluruhan
    $pdo->prepare("
        UPDATE pengajuan_sempro 
        SET status = ?, catatan = ?
        WHERE id = ?
    ")->execute([$overall_status, $catatan_keseluruhan, $id]);

    header("Location: index.php?id=$id");
    exit;
}
