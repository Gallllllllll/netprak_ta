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

// ===============================
// NAMA MAHASISWA LOGIN
// ===============================
$namaMahasiswa = $_SESSION['user']['nama'] ?? 'Mahasiswa';

$id = $_GET['id'] ?? 0;

// ===============================
// AMBIL DATA SEMHAS + TIM DOSEN
// ===============================
$stmt = $pdo->prepare("
    SELECT 
        ps.*,

        MAX(d1.nama) AS pembimbing_1,
        MAX(d2.nama) AS pembimbing_2,

        GROUP_CONCAT(DISTINCT dp.nama SEPARATOR ', ') AS tim_penguji

    FROM pengajuan_semhas ps

    LEFT JOIN dosbing_ta db1
        ON db1.pengajuan_id = ps.pengajuan_ta_id
        AND db1.role = 'dosbing_1'
    LEFT JOIN dosen d1 ON db1.dosen_id = d1.id

    LEFT JOIN dosbing_ta db2
        ON db2.pengajuan_id = ps.pengajuan_ta_id
        AND db2.role = 'dosbing_2'
    LEFT JOIN dosen d2 ON db2.dosen_id = d2.id

    LEFT JOIN tim_semhas ts
        ON ts.pengajuan_id = ps.id
    LEFT JOIN dosen dp
        ON ts.dosen_id = dp.id

    WHERE ps.id = ?
      AND ps.mahasiswa_id = ?

    GROUP BY ps.id
");
$stmt->execute([$id, $_SESSION['user']['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data Seminar Hasil tidak ditemukan.");
}

// ===============================
// FILE
// ===============================
$files = [
    'file_berita_acara' => [
        'label' => 'Berita Acara Seminar Hasil',
        'catatan' => 'catatan_file_berita_acara'
    ],
    'file_persetujuan_laporan' => [
        'label' => 'Persetujuan Laporan TA',
        'catatan' => 'catatan_file_persetujuan_laporan'
    ],
    'file_pendaftaran_ujian' => [
        'label' => 'Form Pendaftaran Ujian TA',
        'catatan' => 'catatan_file_pendaftaran_ujian'
    ],
    'file_buku_konsultasi' => [
        'label' => 'Buku Konsultasi TA',
        'catatan' => 'catatan_file_buku_konsultasi'
    ]
];

function badgeFileStatus($status) {
    return match($status) {
        'diajukan'  => ['class' => 'status-diajukan',  'label' => 'Diajukan'],
        'revisi'    => ['class' => 'status-revisi',    'label' => 'Revisi'],
        'disetujui' => ['class' => 'status-disetujui', 'label' => 'Disetujui'],
        'ditolak'   => ['class' => 'status-ditolak',   'label' => 'Ditolak'],
        default     => ['class' => 'status-diajukan',  'label' => 'Diajukan']
    };
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Seminar Hasil</title>
<link rel="stylesheet" href="../../style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body {
    margin:0;
    font-family:'Segoe UI', Arial, sans-serif;
    background:linear-gradient(180deg,#f9b4b4,#ff9f80);
}

.container {
    display:flex;
    min-height:100vh;
}

.main-content {
    flex:1;
    padding:30px;
}

.page-card {
    max-width:900px;
    margin:auto;
    background:#fff7f3;
    border-radius:18px;
    padding:24px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.page-title {
    font-size:22px;
    font-weight:700;
    color:#ff6b35;
}

.subtitle {
    font-size:14px;
    color:#777;
    margin-bottom:20px;
}

.header-box {
    background:#fff;
    border-radius:14px;
    padding:18px;
    border:1px solid #ffe1d6;
}

.status-badge {
    display:inline-block;
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:700;
}

.status-diajukan { background:#ffeeba; color:#856404; }
.status-revisi { background:#bee5eb; color:#0c5460; }
.status-disetujui { background:#c3e6cb; color:#155724; }
.status-ditolak { background:#f5c6cb; color:#721c24; }

.id-chip {
    background:#f1f1f1;
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
    color:#555;
}

.info-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
    margin-top:16px;
}

.info-item {
    background:#fffaf7;
    border:1px solid #ffd9c9;
    border-radius:12px;
    padding:12px;
    text-align:center;
}

.info-item b {
    display:block;
    font-size:13px;
    color:#ff6b35;
    margin-bottom:6px;
}

.section {
    margin-top:24px;
}

.section h3 {
    font-size:16px;
    color:#444;
    margin-bottom:12px;
}

.doc-item {
    background:#fff;
    border:1px solid #ffd6c4;
    border-radius:14px;
    padding:14px;
    margin-bottom:12px;
}

.doc-row {
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.doc-title {
    font-weight:600;
}

.btn-view {
    padding:6px 14px;
    background:linear-gradient(90deg,#ff6b35,#ff9f1c);
    color:#fff;
    text-decoration:none;
    border-radius:20px;
    font-size:12px;
}

.note-box {
    margin-top:8px;
    font-size:13px;
    color:#777;
    background:#fff4ee;
    padding:8px 12px;
    border-radius:10px;
}

.footer-note {
    margin-top:24px;
    background:#ffe1e1;
    border-radius:14px;
    padding:14px;
    font-size:13px;
    color:#a94442;
}
.actions {
    margin-top:20px;
}
.btn-back {
    padding:10px 18px;
    background:#6b7280; /* slate-500 */
    color:#fff;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:all .2s ease;
}

.btn-back:hover {
    background:#4b5563; /* slate-700 */
    transform:translateY(-1px);
}


.btn-update {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    padding:14px 28px;
    min-width:260px;

    background: linear-gradient(90deg, #ff6fb1, #ffa600);
    color:#fff;
    font-weight:700;
    font-size:14px;

    border:none;
    border-radius:999px;
    text-decoration:none;

    box-shadow:0 6px 18px rgba(255,111,177,.35);
    transition:all .25s ease;
}

.btn-update:hover {
    transform:translateY(-2px);
    box-shadow:0 10px 24px rgba(255,111,177,.45);
}

.btn-update i {
    font-size:16px;
}

</style>
</head>

<body>
<div class="container">

<?php include "../sidebar.php"; ?>

<div class="main-content">

<div class="page-card">

    <!-- ===============================
         HEADER + NAMA MAHASISWA
    =============================== -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div>
            <div class="page-title">Detail Status Seminar Hasil</div>
            <div class="subtitle">Informasi lengkap pelaksanaan seminar hasil</div>
        </div>
        <div style="text-align:right;">
            <small>Nama Mahasiswa</small><br>
            <strong style="color:#ff6b35;">
                <?= htmlspecialchars($namaMahasiswa) ?>
            </strong>
        </div>
    </div>

    <!-- HEADER BOX -->
    <div class="header-box">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="status-badge status-<?= $data['status'] ?>">
                <?= strtoupper($data['status']) ?>
            </span>
            <span class="id-chip"><?= htmlspecialchars($data['id_semhas']) ?></span>
        </div>

        <p style="margin-top:12px">
            <b>Catatan Admin:</b><br>
            <?= $data['catatan'] ?: '-' ?>
        </p>

        <div class="info-grid">
            <div class="info-item">
                <b>Tanggal Seminar</b>
                <?= $data['tanggal_sidang']
                    ? date('Y-m-d', strtotime($data['tanggal_sidang']))
                    : '-' ?>
            </div>
            <div class="info-item">
                <b>Waktu Seminar</b>
                <?= $data['jam_sidang'] ? substr($data['jam_sidang'],0,5) : '-' ?>
            </div>
            <div class="info-item">
                <b>Ruangan Seminar</b>
                <?= $data['tempat_sidang'] ?: '-' ?>
            </div>
        </div>
    </div>

    <!-- TIM DOSEN -->
    <div class="section">
        <h3>Tim Seminar Hasil</h3>
        <p><b>Dosen Pembimbing 1:</b> <?= $data['pembimbing_1'] ?: '-' ?></p>
        <p><b>Dosen Pembimbing 2:</b> <?= $data['pembimbing_2'] ?: '-' ?></p>
        <p><b>Tim Penguji:</b> <?= $data['tim_penguji'] ?: '-' ?></p>
    </div>

    <!-- DOKUMEN -->
    <div class="section">
        <h3>Daftar Lampiran Berkas</h3>

        <?php foreach ($files as $field => $info): ?>
        <?php
            // ambil nama status field
            $statusField = 'status_' . $field; // contoh: status_file_berita_acara
            $statusValue = $data[$statusField] ?? 'diajukan';
            $badge = badgeFileStatus($statusValue);
        ?>
        <div class="doc-item">
            <div class="doc-row">
                <div class="doc-title"><?= $info['label'] ?></div>

                <div style="display:flex;align-items:center;gap:8px;">
                    <!-- BADGE STATUS FILE -->
                    <span class="status-badge <?= $badge['class'] ?>">
                        <?= $badge['label'] ?>
                    </span>

                    <!-- TOMBOL LIHAT -->
                    <?php if (!empty($data[$field])): ?>
                        <a class="btn-view"
                        href="../../uploads/semhas/<?= htmlspecialchars($data[$field]) ?>"
                        target="_blank">
                            Lihat
                        </a>
                    <?php else: ?>
                        <span style="font-size:12px;color:#aaa">Belum upload</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="note-box">
                Catatan:
                <?= !empty($data[$info['catatan']])
                    ? htmlspecialchars($data[$info['catatan']])
                    : '-' ?>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <?php if (
        empty($data['tanggal_sidang']) ||
        empty($data['jam_sidang']) ||
        empty($data['tempat_sidang'])
    ): ?>
        <div class="footer-note">
            Jadwal seminar akan muncul setelah pengajuan divalidasi dan dikonfirmasi oleh Admin Program Studi.
        </div>
    <?php endif; ?>

    <div class="actions">

        <?php if ($data['status'] !== 'revisi'): ?>
            <a class="btn-back" href="status.php">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
        <?php endif; ?>

        <?php if ($data['status'] === 'revisi'): ?>
            <div style="margin-top:24px;text-align:center;">
                <a class="btn-update" href="revisi.php?id=<?= $data['id'] ?>">
                    <i class="fa-solid fa-rotate"></i> Update Berkas Sekarang
                </a>
            </div>
        <?php endif; ?>

    </div>


</div>
</div>
</div>
</body>
</html>