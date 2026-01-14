<?php
session_start();
require "../../config/connection.php";

// ===============================
// CEK ROLE MAHASISWA
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

// ===============================
// AMBIL DATA SEMHAS MAHASISWA
// ===============================
$stmt = $pdo->prepare("
    SELECT *
    FROM pengajuan_semhas
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===============================
// MAPPING FILE SEMHAS
// ===============================
$fileMap = [
    'file_berita_acara' => [
        'label' => 'Berita Acara Seminar Hasil',
        'status' => 'status_file_berita_acara'
    ],
    'file_persetujuan_laporan' => [
        'label' => 'Persetujuan Laporan TA (Form 5)',
        'status' => 'status_file_persetujuan_laporan'
    ],
    'file_pendaftaran_ujian' => [
        'label' => 'Form Pendaftaran Ujian TA (Form 7)',
        'status' => 'status_file_pendaftaran_ujian'
    ],
    'file_buku_konsultasi' => [
        'label' => 'Buku Konsultasi TA (Form 4)',
        'status' => 'status_file_buku_konsultasi'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Seminar Hasil</title>
<link rel="stylesheet" href="../../style.css">

<style>
.card {
    background:#fff;
    padding:18px;
    border-radius:10px;
    margin-bottom:15px;
    box-shadow:0 2px 6px rgba(0,0,0,.08);
}

.badge {
    display:inline-block;
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}
.badge-diajukan { background:#e3f2fd; color:#1565c0; }
.badge-revisi   { background:#fff3cd; color:#856404; }
.badge-disetujui{ background:#d4edda; color:#155724; }
.badge-ditolak  { background:#f8d7da; color:#721c24; }

.revisi-files {
    margin-top:8px;
    padding-left:18px;
    font-size:13px;
    color:#856404;
}
.revisi-files li { margin-bottom:4px; }

.jadwal {
    margin-top:10px;
    background:#e9f7ef;
    padding:10px 14px;
    border-radius:8px;
    font-size:14px;
}
.jadwal b { color:#155724; }

.actions {
    margin-top:12px;
}
.actions a {
    display:inline-block;
    padding:8px 14px;
    border-radius:6px;
    text-decoration:none;
    font-size:14px;
    margin-right:6px;
}
.btn-detail { background:#007bff; color:#fff; }
.btn-revisi { background:#17a2b8; color:#fff; }
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">

<h1>Status Pengajuan Seminar Hasil</h1>

<?php if(empty($data)): ?>
    <p>Belum ada pengajuan Seminar Hasil.</p>
<?php endif; ?>

<?php foreach ($data as $d): ?>

<?php
    // ===============================
    // FILE YANG REVISI
    // ===============================
    $revisiFiles = [];
    foreach ($fileMap as $f) {
        if (($d[$f['status']] ?? '') === 'revisi') {
            $revisiFiles[] = $f['label'];
        }
    }
?>

<div class="card">

    <p>
        <b>Status:</b>
        <span class="badge badge-<?= $d['status'] ?>">
            <?= strtoupper($d['status']) ?>
        </span>
    </p>

    <p>
        <b>Tanggal Pengajuan:</b>
        <?= date('d M Y', strtotime($d['created_at'])) ?>
    </p>

    <!-- ===============================
         JADWAL SIDANG (JIKA ADA)
    =============================== -->
    <?php if (!empty($d['tanggal_sidang'])): ?>
        <div class="jadwal">
            <b>📅 Jadwal Sidang Seminar Hasil</b><br>
            Tanggal : <?= date('d M Y', strtotime($d['tanggal_sidang'])) ?><br>
            Jam     : <?= substr($d['jam_sidang'], 0, 5) ?> WIB<br>
            Tempat : <?= htmlspecialchars($d['tempat_sidang']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($revisiFiles)): ?>
        <p><b>Dokumen yang perlu direvisi:</b></p>
        <ul class="revisi-files">
            <?php foreach ($revisiFiles as $rf): ?>
                <li>• <?= $rf ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="actions">
        <a class="btn-detail" href="detail.php?id=<?= $d['id'] ?>">Detail</a>

        <?php if ($d['status'] === 'revisi'): ?>
            <a class="btn-revisi" href="revisi.php?id=<?= $d['id'] ?>">
                Revisi Dokumen
            </a>
        <?php endif; ?>
    </div>

</div>
<?php endforeach; ?>

</div>

</body>
</html>
