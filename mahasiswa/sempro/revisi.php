<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

// cek role mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ".base_url('login.php'));
    exit;
}

$id = $_GET['id'] ?? 0;
$upload_dir = "../../uploads/sempro/";

// ambil data pengajuan
$stmt = $pdo->prepare("SELECT * FROM pengajuan_sempro WHERE id=? AND mahasiswa_id=?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Pengajuan Seminar Proposal tidak ditemukan.");

// mapping file
$files = [
    'file_pendaftaran' => [
        'db' => 'file_pendaftaran',
        'status' => 'status_file_pendaftaran',
        'label' => 'File Pendaftaran'
    ],
    'file_persetujuan' => [
        'db' => 'file_persetujuan',
        'status' => 'status_file_persetujuan',
        'label' => 'Lembar Persetujuan'
    ],
    'file_buku_konsultasi' => [
        'db' => 'file_buku_konsultasi',
        'status' => 'status_file_buku_konsultasi',
        'label' => 'Buku Konsultasi'
    ],
];

// proses upload revisi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $updates = [];

    foreach ($files as $input => $f) {

        // hanya boleh upload jika status file = revisi
        if (($data[$f['status']] ?? '') === 'revisi' && !empty($_FILES[$input]['name'])) {

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = time() . '_' . basename($_FILES[$input]['name']);
            $target = $upload_dir . $filename;

            if (move_uploaded_file($_FILES[$input]['tmp_name'], $target)) {
                $updates[] = "{$f['db']} = " . $pdo->quote($filename);
                $updates[] = "{$f['status']} = 'diajukan'";
            }
        }
    }

    if ($updates) {
        $sql = "
            UPDATE pengajuan_sempro
            SET " . implode(', ', $updates) . ",
                status = 'diajukan'
            WHERE id=?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    header("Location: status.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Revisi Seminar Proposal</title>
<link rel="stylesheet" href="../../style.css">
<style>
/* ====== Style mengikuti style utama ====== */
body {
    font-family: Arial, sans-serif;
    background:#f4f6f8;
}
.container {
    display:flex;
    min-height:100vh;
}
.main-content {
    flex:1;
    padding:20px;
}
.card {
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 6px rgba(0,0,0,.1);
    margin-bottom:20px;
}
form {
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 6px rgba(0,0,0,.1);
    max-width:650px;
    margin:auto;
}
label {
    display:block;
    margin-top:15px;
    font-weight:bold;
}
input[type=file] {
    width:100%;
    padding:10px;
    margin-top:6px;
    border-radius:4px;
    border:1px solid #ccc;
}
button {
    margin-top:20px;
    padding:10px 20px;
    background:#28a745;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}
button:hover {
    background:#218838;
}
.alert {
    background:#fff3cd;
    padding:15px;
    border-radius:6px;
    margin-bottom:15px;
    border:1px solid #ffeeba;
}
</style>
</head>
<body>

<div class="container">

    <?php include "../sidebar.php"; ?>

    <div class="main-content">
        <h1>Revisi Pengajuan Seminar Proposal</h1>

        <form method="POST" enctype="multipart/form-data">

        <?php
        $ada_revisi = false;

        foreach ($files as $input => $f):
            if (($data[$f['status']] ?? '') === 'revisi'):
                $ada_revisi = true;
        ?>
            <label><?= htmlspecialchars($f['label']) ?></label>
            <input type="file" name="<?= $input ?>" accept=".pdf" required>
            <small>Format: PDF</small><br>

        <?php
            endif;
        endforeach;

        if (!$ada_revisi):
        ?>
            <div class="alert">Tidak ada dokumen yang perlu direvisi.</div>
        <?php else: ?>
            <button type="submit">Upload Revisi</button>
        <?php endif; ?>

        </form>
    </div>
</div>

</body>
</html>
