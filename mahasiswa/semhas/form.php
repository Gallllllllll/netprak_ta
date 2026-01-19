<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ".base_url('login.php'));
    exit;
}

$pesan_error = '';
$boleh_upload = false;

/* ===============================
   CEK TA
================================ */
$stmt = $pdo->prepare("
    SELECT id, judul_ta, status 
    FROM pengajuan_ta 
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC LIMIT 1
");
$stmt->execute([$_SESSION['user']['id']]);
$ta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ta || $ta['status'] !== 'disetujui') {
    $pesan_error = "Tugas Akhir belum disetujui.";
} else {
    /* ===============================
       CEK SEMPRO
    ================================ */
    $cek = $pdo->prepare("
        SELECT status 
        FROM pengajuan_sempro 
        WHERE mahasiswa_id = ?
        ORDER BY created_at DESC LIMIT 1
    ");
    $cek->execute([$_SESSION['user']['id']]);
    $sempro = $cek->fetch(PDO::FETCH_ASSOC);

    if (!$sempro || $sempro['status'] !== 'disetujui') {
        $pesan_error = "Seminar Proposal belum disetujui.";
    } else {
        /* ===============================
           CEK SUDAH SEMHAS
        ================================ */
        $cek = $pdo->prepare("
            SELECT id FROM pengajuan_semhas 
            WHERE mahasiswa_id = ? LIMIT 1
        ");
        $cek->execute([$_SESSION['user']['id']]);

        if ($cek->rowCount() > 0) {
            $pesan_error = "Anda sudah mengajukan Seminar Hasil.";
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
<title>Pengajuan Seminar Hasil</title>
<style>
.card{background:#fff;padding:26px;max-width:720px;border-radius:16px}
.alert{background:#fff7ed;color:#9a3412;padding:14px;border-radius:12px}
label{margin-top:14px;display:block;font-weight:600}
input[type=file]{margin-top:6px}
button{
    margin-top:20px;width:100%;padding:12px;border:none;border-radius:14px;
    background:linear-gradient(135deg,#FF74C7,#FF983D);
    color:#fff;font-weight:600
}
small{color:#6b7280}
</style>
</head>
<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
<div class="dashboard-header">
    <h1>Pengajuan Seminar Hasil</h1>
    <p>Unggah dokumen Seminar Hasil</p>
</div>

<div class="card">

<?php if ($pesan_error): ?>
    <div class="alert"><?= htmlspecialchars($pesan_error) ?></div>

<?php elseif ($boleh_upload): ?>
<form action="simpan.php" method="POST" enctype="multipart/form-data">

    <label>Lembar Berita Acara</label>
    <input type="file" name="file_berita_acara"
           accept=".pdf,.doc,.docx" required>
    <small>Format: PDF / Word</small>

    <label>Persetujuan Laporan TA (Form 5)</label>
    <input type="file" name="file_persetujuan_laporan"
           accept=".pdf,.doc,.docx" required>
    <small>Format: PDF / Word</small>

    <label>Form Pendaftaran Ujian TA (Form 7)</label>
    <input type="file" name="file_pendaftaran_ujian"
           accept=".pdf,.doc,.docx" required>
    <small>Format: PDF / Word</small>

    <label>Buku Konsultasi TA (Form 4)</label>
    <input type="file" name="file_buku_konsultasi"
           accept=".pdf,.doc,.docx" required>
    <small>Format: PDF / Word</small>

    <button type="submit">Ajukan Seminar Hasil</button>
</form>
<?php endif; ?>

</div>
</div>
</body>
</html>
