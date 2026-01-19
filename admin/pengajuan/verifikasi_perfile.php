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
    $catatan_all = $_POST['catatan'] ?? [];

    if (!$id || empty($status_all)) {
        die("Data tidak lengkap.");
    }

    // mapping input => kolom status di DB
    $files = [
        'bukti_pembayaran'      => 'status_bukti_pembayaran',
        'formulir_pendaftaran'  => 'status_formulir_pendaftaran',
        'transkrip_nilai'       => 'status_transkrip_nilai',
        'bukti_magang'          => 'status_bukti_magang'
    ];

    foreach ($status_all as $file => $status) {

        if (!isset($files[$file])) continue;

        $status_field  = $files[$file];
        $catatan_field = "catatan_$file";
        $catatan       = $catatan_all[$file] ?? '';

        // VALIDASI ENUM
        if (!in_array($status, ['proses','revisi','disetujui','ditolak'])) {
            die("Status tidak valid.");
        }

        // update status & catatan
        $stmt = $pdo->prepare("
            UPDATE pengajuan_ta 
            SET $status_field = ?, $catatan_field = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $catatan, $id]);
    }

    /* ========================
       HITUNG STATUS GLOBAL
    ======================== */
    $stmt = $pdo->prepare("
        SELECT 
            status_bukti_pembayaran,
            status_formulir_pendaftaran,
            status_transkrip_nilai,
            status_bukti_magang
        FROM pengajuan_ta
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $statuses = array_values($data);

    if (in_array('ditolak', $statuses)) {
        $overall_status = 'ditolak';
    } elseif (in_array('revisi', $statuses)) {
        $overall_status = 'revisi';
    } elseif (count(array_unique($statuses)) === 1 && $statuses[0] === 'disetujui') {
        $overall_status = 'disetujui';
    } else {
        $overall_status = 'proses';
    }

    // update status global
    $stmt = $pdo->prepare("
        UPDATE pengajuan_ta 
        SET status = ?
        WHERE id = ?
    ");
    $stmt->execute([$overall_status, $id]);

    header("Location: index.php?id=$id");
    exit;
}
?>
