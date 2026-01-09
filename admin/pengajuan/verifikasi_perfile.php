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

    // update masing-masing file
    foreach($status_all as $file => $status){
        $catatan = $catatan_all[$file] ?? '';
        $status_field = "status_$file";
        $catatan_field = "catatan_$file";

        $stmt = $pdo->prepare("UPDATE pengajuan_ta SET $status_field=?, $catatan_field=? WHERE id=?");
        $stmt->execute([$status, $catatan, $id]);
    }

    // update status keseluruhan pengajuan
    $stmt = $pdo->prepare("SELECT * FROM pengajuan_ta WHERE id=?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    $all_approved = (
        ($data['status_bukti_pembayaran']??'')==='disetujui' &&
        ($data['status_formulir_pendaftaran']??'')==='disetujui' &&
        ($data['status_transkrip_nilai']??'')==='disetujui' &&
        ($data['status_bukti_magang']??'')==='disetujui'
    );

    $overall_status = $all_approved ? 'disetujui' : 'revisi';
    $pdo->prepare("UPDATE pengajuan_ta SET status=? WHERE id=?")->execute([$overall_status, $id]);

    header("Location: detail.php?id=$id");
}
