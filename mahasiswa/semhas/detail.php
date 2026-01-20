<?php
session_start();
require "../../config/connection.php";

// ===============================
// CEK LOGIN MAHASISWA
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// ===============================
// AMBIL DATA SEMHAS
// ===============================
$stmt = $pdo->prepare("
    SELECT *
    FROM pengajuan_semhas
    WHERE id = ? AND mahasiswa_id = ?
");
$stmt->execute([$id, $_SESSION['user']['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data Seminar Hasil tidak ditemukan.");
}

// helper badge status
function badgeStatus($status) {
    return match($status) {
        'diajukan'  => 'badge-diajukan',
        'revisi'    => 'badge-revisi',
        'disetujui' => 'badge-disetujui',
        'ditolak'   => 'badge-ditolak',
        default     => 'badge-diajukan'
    };
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Seminar Hasil</title>
<link rel="stylesheet" href="../../style.css">

<style>
.card {
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 2px 6px rgba(0,0,0,.08);
}

.file-item {
    margin-bottom:16px;
    padding-bottom:12px;
    border-bottom:1px dashed #ddd;
}

.file-item a {
    color:#007bff;
    text-decoration:none;
    font-weight:600;
}
.file-item a:hover {
    text-decoration:underline;
}

.badge {
    padding:5px 12px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
}
.badge-diajukan { background:#e3f2fd; color:#1565c0; }
.badge-revisi   { background:#fff3cd; color:#856404; }
.badge-disetujui{ background:#d4edda; color:#155724; }
.badge-ditolak  { background:#f8d7da; color:#721c24; }

.note {
    margin-top:6px;
    font-size:14px;
    color:#444;
    background:#f8f9fa;
    padding:8px 12px;
    border-radius:8px;
}

.jadwal {
    margin-top:15px;
    padding:14px;
    border-radius:10px;
    background:#e9f7ef;
    font-size:14px;
}
.jadwal b { color:#155724; }

.actions {
    margin-top:20px;
}
.actions a {
    display:inline-block;
    padding:9px 16px;
    border-radius:8px;
    font-size:14px;
    text-decoration:none;
    margin-right:8px;
}
.btn-back {
    background:#6c757d;
    color:#fff;
}
.btn-revisi {
    background:#17a2b8;
    color:#fff;
}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">

<h1>Detail Seminar Hasil</h1>

<div class="card">

    <!-- ID SEMHAS -->
    <p>
        <b>ID Seminar Hasil:</b><br>
        <span style="font-size:16px;font-weight:600;">
            <?= htmlspecialchars($data['id_semhas']) ?>
        </span>
    </p>

    <hr>

    <!-- ===============================
         DOKUMEN & CATATAN PER FILE
    =============================== -->
    <h3>Dokumen Seminar Hasil</h3>

    <?php if ($data['file_berita_acara']): ?>
    <div class="file-item">
        <a href="../../uploads/semhas/<?= htmlspecialchars($data['file_berita_acara']) ?>" target="_blank">
            📄 Berita Acara Seminar Hasil
        </a><br>
        <div class="note">
            <b>Catatan:</b>
            <?= $data['catatan_file_berita_acara'] ? htmlspecialchars($data['catatan_file_berita_acara']) : '-' ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($data['file_persetujuan_laporan']): ?>
    <div class="file-item">
        <a href="../../uploads/semhas/<?= htmlspecialchars($data['file_persetujuan_laporan']) ?>" target="_blank">
            📄 Persetujuan Laporan TA (Form 5)
        </a><br>
        <div class="note">
            <b>Catatan:</b>
            <?= $data['catatan_file_persetujuan_laporan'] ? htmlspecialchars($data['catatan_file_persetujuan_laporan']) : '-' ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($data['file_pendaftaran_ujian']): ?>
    <div class="file-item">
        <a href="../../uploads/semhas/<?= htmlspecialchars($data['file_pendaftaran_ujian']) ?>" target="_blank">
            📄 Form Pendaftaran Ujian TA (Form 7)
        </a><br>
        <div class="note">
            <b>Catatan:</b>
            <?= $data['catatan_file_pendaftaran_ujian'] ? htmlspecialchars($data['catatan_file_pendaftaran_ujian']) : '-' ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($data['file_buku_konsultasi']): ?>
    <div class="file-item">
        <a href="../../uploads/semhas/<?= htmlspecialchars($data['file_buku_konsultasi']) ?>" target="_blank">
            📄 Buku Konsultasi TA (Form 4)
        </a><br>
        <div class="note">
            <b>Catatan:</b>
            <?= $data['catatan_file_buku_konsultasi'] ? htmlspecialchars($data['catatan_file_buku_konsultasi']) : '-' ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- STATUS GLOBAL -->
    <p>
        <b>Status Pengajuan:</b>
        <span class="badge <?= badgeStatus($data['status']) ?>">
            <?= strtoupper($data['status']) ?>
        </span>
    </p>

    <!-- CATATAN GLOBAL -->
    <p>
        <b>Catatan Admin (Umum):</b><br>
        <?= $data['catatan'] ? htmlspecialchars($data['catatan']) : '-' ?>
    </p>

    <!-- JADWAL -->
    <?php if ($data['tanggal_sidang']): ?>
        <div class="jadwal">
            <b>📅 Jadwal Seminar Hasil</b><br><br>
            <b>Tanggal:</b> <?= date('d M Y', strtotime($data['tanggal_sidang'])) ?><br>
            <b>Jam:</b> <?= substr($data['jam_sidang'], 0, 5) ?> WIB<br>
            <b>Tempat:</b> <?= htmlspecialchars($data['tempat_sidang']) ?>
        </div>
    <?php endif; ?>

    <!-- ACTION -->
    <div class="actions">
        <a class="btn-back" href="status.php">Kembali</a>

        <?php if ($data['status'] === 'revisi'): ?>
            <a class="btn-revisi" href="revisi.php?id=<?= $data['id'] ?>">
                Revisi Dokumen
            </a>
        <?php endif; ?>
    </div>

</div>
</div>

</body>
</html>
