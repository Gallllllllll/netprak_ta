<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

/* ===============================
   CEK LOGIN MAHASISWA
================================ */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ".base_url('login.php'));
    exit;
}

$pesan_error  = '';
$boleh_upload = false;

/* ===============================
   CEK PENGAJUAN TA TERAKHIR
================================ */
$stmt = $pdo->prepare("
    SELECT id, judul_ta, status
    FROM pengajuan_ta
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$_SESSION['user']['id']]);
$ta = $stmt->fetch(PDO::FETCH_ASSOC);

/* ===============================
   VALIDASI ALUR TA
================================ */
if (!$ta) {
    $pesan_error = "Anda belum mengajukan Tugas Akhir.\nSilakan ajukan Tugas Akhir terlebih dahulu.";
} elseif ($ta['status'] !== 'disetujui') {
    $pesan_error = "Pengajuan Tugas Akhir belum selesai.\nStatus saat ini:{$ta['status']}Silakan selesaikan proses pengajuan TA terlebih dahulu.";
} else {
    /* ===============================
       CEK PERSETUJUAN DOSBING
    ================================ */
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM dosbing_ta
        WHERE pengajuan_id = ?
        AND status_persetujuan = 'disetujui'
    ");
    $stmt->execute([$ta['id']]);
    if ($stmt->fetchColumn() < 2) {
        $pesan_error = "Pengajuan Seminar Proposal belum dapat dilakukan.\nMenunggu persetujuan dari Dosen Pembimbing 1 dan 2.";
    } else {
        /* ===============================
           CEK SUDAH AJUKAN SEMPRO
        ================================ */
        $cek = $pdo->prepare("
            SELECT id 
            FROM pengajuan_sempro 
            WHERE mahasiswa_id = ?
            LIMIT 1
        ");
        $cek->execute([$_SESSION['user']['id']]);
        if ($cek->rowCount() > 0) {
            $pesan_error = "Anda sudah pernah mengajukan Seminar Proposal.";
        } else {
            $boleh_upload = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pengajuan Seminar Proposal</title>
<link rel="stylesheet" href="<?= base_url('style.css') ?>">
<style>
body { margin:0; font-family: Arial, sans-serif; }
.wrapper { display:flex; min-height:100vh; }

.sidebar {
    width:220px;
    background:#2c3e50;
    color:#fff;
    padding:20px;
}
.sidebar a { color:#fff; text-decoration:none; display:block; margin-bottom:10px; }
.sidebar a:hover { text-decoration:underline; }

.container {
    flex:1;
    padding:30px;
    background:#f4f4f4;
}
.card {
    background:#fff;
    padding:25px;
    max-width:650px;
    border-radius:8px;
    box-shadow:0 2px 6px rgba(0,0,0,.1);
}
.alert { background:#fff3cd; color:#856404; padding:15px; border-radius:6px; margin-bottom:15px; }
input, button { width:100%; padding:10px; margin-top:8px; }
button { background:#007bff; color:#fff; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0056b3; }
label { margin-top:12px; display:block; font-weight:bold; }
hr { margin:20px 0; }
</style>
</head>
<body>

<div class="wrapper">

    <!-- INCLUDE SIDEBAR -->
    <?php include "../sidebar.php"; ?>

    <div class="container">
        <div class="card">
            <h2>Pengajuan Seminar Proposal</h2>

            <?php
            if ($pesan_error) {
                echo '<div class="alert">'.nl2br(htmlspecialchars($pesan_error)).'</div>';
            } elseif ($boleh_upload) {
            ?>
                <form action="simpan.php" method="POST" enctype="multipart/form-data">

                    <label>Form Pendaftaran Seminar Proposal (PDF)</label>
                    <input type="file" name="file_pendaftaran" accept="application/pdf" required>

                    <label>Lembar Persetujuan Proposal TA (PDF)</label>
                    <input type="file" name="file_persetujuan" accept="application/pdf" required>

                    <label>Buku Konsultasi Tugas Akhir (PDF)</label>
                    <input type="file" name="file_konsultasi" accept="application/pdf" required>

                    <button type="submit">Ajukan Seminar Proposal</button>

                </form>
            <?php
            } else {
                if (!$pesan_error) {
                    $pesan_error = "Pengajuan Seminar Proposal belum bisa dilakukan saat ini.";
                }
                echo '<div class="alert">'.nl2br(htmlspecialchars($pesan_error)).'</div>';
            }

            ?>

            <?php if ($ta): ?>
                <hr>
                <p><b>Judul Tugas Akhir:</b><br><?= htmlspecialchars($ta['judul_ta']) ?></p>
                <p><b>Status TA:</b> <?= htmlspecialchars($ta['status']) ?></p>
            <?php endif; ?>

        </div>
    </div>

</div>
</body>
</html>
