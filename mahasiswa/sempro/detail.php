<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

// ===============================
// CEK LOGIN
// ===============================
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','mahasiswa'])) {
    header("Location: ".base_url('login.php'));
    exit;
}

// ===============================
// AMBIL ID PENGAJUAN
// ===============================
$id = $_GET['id'] ?? null;
if (!$id) die("ID pengajuan tidak diberikan.");

// ===============================
// AMBIL DATA PENGAJUAN SEMPRO
// ===============================
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        m.nama AS mahasiswa_nama
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Data pengajuan tidak ditemukan.");

// ===============================
// DAFTAR DOKUMEN
// ===============================
$files = [
    'file_pendaftaran'     => 'Form Pendaftaran',
    'file_persetujuan'     => 'Persetujuan Proposal',
    'file_buku_konsultasi' => 'Buku Konsultasi'
];

// ===============================
// STATUS CLASS
// ===============================
$status_class_map = [
    'proses'    => 'status-proses',
    'diajukan'  => 'status-proses',
    'disetujui' => 'status-disetujui',
    'ditolak'   => 'status-ditolak',
    'revisi'    => 'status-revisi'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pengajuan Seminar Proposal</title>
<link rel="stylesheet" href="../../style.css">
<style>
body { margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container { display:flex; min-height:100vh; }
.content { flex:1; padding:20px; }
.card { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
.card h3 { margin-top:0; }
table { width:100%; border-collapse:collapse; margin-bottom:15px; }
th, td { padding:10px; border:1px solid #ccc; vertical-align:top; }
th { background:#eee; text-align:left; width:200px; }
.status { display:inline-block; padding:5px 12px; border-radius:20px; font-weight:bold; color:#fff; }
.status-proses { background:#ffc107; }
.status-disetujui { background:#28a745; }
.status-ditolak { background:#dc3545; }
.status-revisi { background:#17a2b8; }
a.file-link { color:#007bff; text-decoration:none; }
a.file-link:hover { text-decoration:underline; }
.id-badge {
    display:inline-block;
    padding:6px 14px;
    border-radius:20px;
    background:#1f2937;
    color:#fff;
    font-weight:bold;
    font-size:13px;
}
</style>
</head>
<body>

<div class="container">
    <?php include "../sidebar.php"; ?>

    <div class="main-content">
        <h1>Detail Pengajuan Seminar Proposal</h1>

        <!-- =============================== -->
        <!-- INFORMASI MAHASISWA -->
        <!-- =============================== -->
        <div class="card">
            <h3>Informasi Pengajuan</h3>

            <p>
                <b>ID Seminar Proposal:</b><br>
                <span class="id-badge">
                    <?= htmlspecialchars($data['id_sempro'] ?? '-') ?>
                </span>
            </p>

            <p><b>Nama Mahasiswa:</b> <?= htmlspecialchars($data['mahasiswa_nama'] ?? '-') ?></p>
            <p><b>Tanggal Pengajuan:</b> <?= htmlspecialchars($data['created_at'] ?? '-') ?></p>

            <!-- TAMBAHKAN TANGGAL, RUANGAN, JAM SEMPRO -->
            <p><b>Tanggal Seminar Proposal:</b> <?= htmlspecialchars($data['tanggal_sempro'] ?? '-') ?></p>
            <p><b>Jam Seminar Proposal:</b> <?= htmlspecialchars($data['jam_sempro'] ?? '-') ?></p>
            <p><b>Ruangan Seminar Proposal:</b> <?= htmlspecialchars($data['ruangan_sempro'] ?? '-') ?></p>

            <p>
                <b>Status:</b>
                <span class="status <?= $status_class_map[strtolower($data['status'] ?? 'proses')] ?>">
                    <?= strtoupper($data['status'] ?? 'DIAJUKAN') ?>
                </span>
            </p>

            <p><b>Catatan Admin / Dosen:</b><br>
                <?= htmlspecialchars($data['catatan_admin'] ?? '-') ?>
            </p>
        </div>

        <!-- =============================== -->
        <!-- DOKUMEN -->
        <!-- =============================== -->
        <div class="card">
            <h3>Dokumen Persyaratan</h3>
            <table>
                <tr>
                    <th>Nama Dokumen</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Catatan</th>
                </tr>

                <?php foreach($files as $field => $label): ?>
                <tr>
                    <td><?= $label ?></td>
                    <td>
                        <?php if (!empty($data[$field])): ?>
                            <a href="../../uploads/sempro/<?= htmlspecialchars($data[$field]) ?>" target="_blank" class="file-link">
                                Lihat File
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status <?= $status_class_map[strtolower($data['status_'.$field] ?? 'proses')] ?>">
                            <?= strtoupper($data['status_'.$field] ?? '-') ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($data['catatan_'.$field] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>
</div>

</body>
</html>
