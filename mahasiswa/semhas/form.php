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

$mahasiswa_id = $_SESSION['user']['id'];

$pesan_error  = '';
$boleh_upload = false;

/* ===============================
   CEK SUDAH AJUKAN SEMHAS
================================ */
$cek = $pdo->prepare("
    SELECT id, status, created_at
    FROM pengajuan_semhas
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$cek->execute([$mahasiswa_id]);
$semhas = $cek->fetch(PDO::FETCH_ASSOC);

if ($semhas) {
    $pesan_error = "Anda sudah melakukan pengajuan Seminar Hasil.";
}

/* ===============================
   LANJUT CEK SYARAT (JIKA BELUM AJUKAN)
================================ */
if (!$pesan_error) {

    /* ===============================
       CEK TUGAS AKHIR
    ================================ */
    $stmt = $pdo->prepare("
        SELECT id, status 
        FROM pengajuan_ta
        WHERE mahasiswa_id = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$mahasiswa_id]);
    $ta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ta || $ta['status'] !== 'disetujui') {
        $pesan_error = "Tugas Akhir belum disetujui.";

    } else {

        /* ===============================
           CEK SEMPRO
        ================================ */
        $cek = $pdo->prepare("
            SELECT sp.status, ns.nilai
            FROM pengajuan_sempro sp
            LEFT JOIN nilai_sempro ns ON sp.id = ns.pengajuan_id
            WHERE sp.mahasiswa_id = ?
            ORDER BY sp.created_at DESC
            LIMIT 1
        ");
        $cek->execute([$mahasiswa_id]);
        $sempro = $cek->fetch(PDO::FETCH_ASSOC);

        if (!$sempro || $sempro['status'] !== 'disetujui') {
            $pesan_error = "Seminar Proposal belum disetujui.";

        } elseif ($sempro['nilai'] === null) {
            $pesan_error = "Nilai Seminar Proposal belum tersedia.";

        } elseif ($sempro['nilai'] < 65) {
            $pesan_error = "Anda tidak lulus Seminar Proposal.";

        } else {

            /* ===============================
               CEK DOSEN PEMBIMBING
            ================================ */
            $cek = $pdo->prepare("
                SELECT COUNT(*) 
                FROM dosbing_ta
                WHERE pengajuan_id = ?
                  AND status_persetujuan_semhas = 'disetujui'
            ");
            $cek->execute([$ta['id']]);
            $jumlah_setuju = $cek->fetchColumn();

            if ($jumlah_setuju < 2) {
                $pesan_error = "Persetujuan Seminar Hasil dari kedua dosen pembimbing belum lengkap.";
            } else {
                $boleh_upload = true;
            }
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
.card{
    background:#fff;
    padding:26px;
    max-width:720px;
    border-radius:16px
}
.alert{
    background:#fff7ed;
    color:#9a3412;
    padding:14px;
    border-radius:12px
}
label{
    margin-top:14px;
    display:block;
    font-weight:600
}
input[type=file]{
    margin-top:6px
}
button{
    margin-top:20px;
    width:100%;
    padding:12px;
    border:none;
    border-radius:14px;
    background:linear-gradient(135deg,#FF74C7,#FF983D);
    color:#fff;
    font-weight:600
}
small{
    color:#6b7280
}
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
    <input type="file" name="file_berita_acara" accept=".pdf" required>

    <label>Persetujuan Laporan TA</label>
    <input type="file" name="file_persetujuan_laporan" accept=".pdf" required>

    <label>Form Pendaftaran Ujian TA</label>
    <input type="file" name="file_pendaftaran_ujian" accept=".pdf" required>

    <label>Buku Konsultasi TA</label>
    <input type="file" name="file_buku_konsultasi" accept=".pdf" required>

    <button type="submit">Ajukan Seminar Hasil</button>
</form>
<?php endif; ?>

</div>
</div>
</body>
</html>