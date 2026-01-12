<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){
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

    // mapping input name => kolom DB
    $files = [
        'bukti_pembayaran'=>'status_bukti_pembayaran',
        'formulir_pendaftaran'=>'status_formulir_pendaftaran',
        'transkrip_nilai'=>'status_transkrip_nilai',
        'bukti_magang'=>'status_bukti_magang'
    ];

    foreach($status_all as $file=>$status){
        $status_field = $files[$file] ?? null;
        $catatan_field = "catatan_$file";
        $catatan = $catatan_all[$file] ?? '';

        if(!$status_field) continue;

        // update status & catatan
        $stmt = $pdo->prepare("UPDATE pengajuan_ta SET $status_field=?, $catatan_field=? WHERE id=?");
        $stmt->execute([$status, $catatan, $id]);

        // jika status revisi, insert ke revisi_ta
        if($status === 'revisi'){
            $jenis_file_map = [
                'bukti_pembayaran'=>'Bukti Pembayaran',
                'formulir_pendaftaran'=>'Formulir Pendaftaran',
                'transkrip_nilai'=>'Transkrip Nilai',
                'bukti_magang'=>'Bukti Kelulusan Magang'
            ];
            $jenis_file = $jenis_file_map[$file] ?? $file;

            // cek dulu, kalau sudah ada revisi sama, jangan insert lagi
            $stmt_check = $pdo->prepare("SELECT id FROM revisi_ta WHERE pengajuan_id=? AND nama_file=?");
            $stmt_check->execute([$id, $jenis_file]);
            if(!$stmt_check->fetch()){
                $stmt2 = $pdo->prepare("INSERT INTO revisi_ta (pengajuan_id, nama_file) VALUES (?, ?)");
                $stmt2->execute([$id, $jenis_file]);
            }
        } else {
            // jika sebelumnya ada revisi tapi sekarang disetujui, hapus dari revisi_ta
            $jenis_file_map = [
                'bukti_pembayaran'=>'Bukti Pembayaran',
                'formulir_pendaftaran'=>'Formulir Pendaftaran',
                'transkrip_nilai'=>'Transkrip Nilai',
                'bukti_magang'=>'Bukti Kelulusan Magang'
            ];
            $jenis_file = $jenis_file_map[$file] ?? $file;
            $stmt_del = $pdo->prepare("DELETE FROM revisi_ta WHERE pengajuan_id=? AND nama_file=?");
            $stmt_del->execute([$id, $jenis_file]);
        }
    }

    // update status global
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
    $stmt = $pdo->prepare("UPDATE pengajuan_ta SET status=? WHERE id=?");
    $stmt->execute([$overall_status, $id]);

    header("Location: index.php?id=$id");
    exit;
}
?>
