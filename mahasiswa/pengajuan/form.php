<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . base_url('login.php'));
    exit;
}

$mahasiswa_id = $_SESSION['user']['id'];

$pesan_error  = '';
$boleh_upload = false;

/* ================================
   CEK SUDAH PERNAH PENGAJUAN TA
================================ */
$cek = $pdo->prepare("SELECT id, judul_ta FROM pengajuan_ta WHERE mahasiswa_id = ? LIMIT 1");
$cek->execute([$mahasiswa_id]);
$pengajuan = $cek->fetch(PDO::FETCH_ASSOC);

if ($pengajuan) {
    $pesan_error = "Anda sudah pernah mengajukan Tugas Akhir.<br>
                    Judul TA: <b>" . htmlspecialchars($pengajuan['judul_ta']) . "</b>";
} else {
    $boleh_upload = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Pengajuan TA</title>
<link rel="stylesheet" href="<?= base_url('style.css') ?>">
<style>
body { margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container { display:flex; min-height:100vh; }
.content { flex:1; padding:20px; }
form, .card { 
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    max-width:700px;
    margin:auto;
}
form h2, .card h2 { margin-top:0; margin-bottom:20px; }
form label { display:block; margin-top:15px; font-weight:bold; }
form input[type="text"],
form input[type="file"] { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:4px; }
form button { margin-top:20px; padding:12px 20px; background:#007bff; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:16px; }
form button:hover { background:#0056b3; }
.alert { background:#fff3cd; color:#856404; padding:15px; border-radius:6px; margin-bottom:15px; }
</style>
</head>
<body>

<div class="container">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/coba/mahasiswa/sidebar.php'; ?>

    <div class="main-content">
        <?php if ($pesan_error): ?>
            <div class="card">
                <h2>Pengajuan TA</h2>
                <div class="alert">
                    <?= $pesan_error ?>
                </div>
            </div>
        <?php elseif ($boleh_upload): ?>
            <form action="simpan.php" method="POST" enctype="multipart/form-data">
                <h2>Form Pengajuan Tugas Akhir</h2>

                <label for="judul">Judul TA:</label>
                <input type="text" id="judul" name="judul" required>

                <label for="bukti_pembayaran">Bukti Pembayaran:</label>
                <input type="file" name="bukti_pembayaran" required accept=".pdf,.doc,.docx">

                <label for="formulir">Formulir Pendaftaran:</label>
                <input type="file" id="formulir" name="formulir" required accept=".pdf,.doc,.docx">

                <label for="transkrip">Transkrip Nilai:</label>
                <input type="file" id="transkrip" name="transkrip" required accept=".pdf,.doc,.docx">

                <label for="magang">Bukti Kelulusan Magang:</label>
                <input type="file" id="magang" name="magang" required accept=".pdf,.doc,.docx">

                <button type="submit">Ajukan TA</button>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
