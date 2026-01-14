<?php
session_start();
require "../../config/connection.php";

// cek role admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){
    header("Location: ../../login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $id = $_POST['id'] ?? null;
    $status_all = $_POST['status'] ?? [];
    $catatan_all = $_POST['catatan'] ?? [];

    if(!$id || empty($status_all)){
        die("Data tidak lengkap.");
    }

    $files = ['pendaftaran','persetujuan','buku_konsultasi'];

    foreach($files as $key){
        $status_field = "status_file_{$key}";
        $catatan_field = "catatan_file_{$key}";
        $status = $status_all[$key] ?? 'pending';
        $catatan = $catatan_all[$key] ?? '';

        $stmt = $pdo->prepare("UPDATE pengajuan_sempro SET $status_field=?, $catatan_field=? WHERE id=?");
        $stmt->execute([$status, $catatan, $id]);
    }

    // update status keseluruhan pengajuan
    $stmt = $pdo->prepare("SELECT * FROM pengajuan_sempro WHERE id=?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    $all_approved = (
        ($data['status_file_pendaftaran']??'')==='disetujui' &&
        ($data['status_file_persetujuan']??'')==='disetujui' &&
        ($data['status_file_buku_konsultasi']??'')==='disetujui'
    );

    $overall_status = $all_approved ? 'disetujui' : 'revisi';
    $pdo->prepare("UPDATE pengajuan_sempro SET status=? WHERE id=?")->execute([$overall_status, $id]);

    header("Location: index.php?id=$id");
    exit;
}
