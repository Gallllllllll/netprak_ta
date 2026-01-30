<?php
session_start();
require "../../config/connection.php";
require_once '../../config/base_url.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

/* ===============================
   AMBIL DATA SEMHAS
=============================== */
$stmt = $pdo->prepare("
    SELECT sh.*, ta.judul_ta
    FROM pengajuan_semhas sh
    JOIN pengajuan_ta ta ON sh.pengajuan_ta_id = ta.id
    WHERE sh.mahasiswa_id = ?
    ORDER BY sh.created_at DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$semhas_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   MAPPING FILE SEMHAS
=============================== */
$file_map = [
    'file_berita_acara' => [
        'label'  => 'Lembar Berita Acara',
        'status' => 'status_file_berita_acara'
    ],
    'file_persetujuan_laporan' => [
        'label'  => 'Persetujuan Laporan Tugas Akhir',
        'status' => 'status_file_persetujuan_laporan'
    ],
    'file_pendaftaran_ujian' => [
        'label'  => 'Form Pendaftaran Ujian',
        'status' => 'status_file_pendaftaran_ujian'
    ],
    'file_buku_konsultasi' => [
        'label'  => 'Buku Konsultasi Tugas Akhir',
        'status' => 'status_file_buku_konsultasi'
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Status Seminar Hasil</title>
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
</head>
<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
}

body {
    font-family: 'Inter', sans-serif;
    background: #FFF1E5 !important;
    margin: 0;
}

.main-content {
    margin-left: 280px;
    padding: 32px;
    min-height: 100vh;
    background: #FFF1E5 !important;
}

/* TOP */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px
}
.topbar h1{
    color:#ff8c42;
    font-size:28px
}

/* PROFILE */
.mhs-info{
    display:flex;
    align-items:left;
    gap:20px
}
.mhs-text span{
    font-size:13px;
    color:#555
}
.mhs-text b{
    color:#ff8c42;
    font-size:14px
}

.avatar{
    width:42px;
    height:42px;
    background:#ff8c42;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    color: #fff;
}

/* ensure avatar icon is white */
.avatar .material-symbols-rounded{ color: #fff !important; }

/* CARD */
.card {
    background:#fff;
    border-radius:18px;
    padding:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    overflow-x: hidden;
    margin-bottom: 25px;
    
}

.card h2{
    text-align: center;
    color: #ff8c42;
}

.divider {
    border: none;
    height: 0.5px;
    width: 100% !important;
    background: #FF983D;
    display: block;
    margin: 12px 0;
}

/* JUDUL */
.ta-judul-wrap{
    margin-top:18px;
}
.ta-body{
    margin-top:6px;
    background:#f3f4f6;
    border-radius:12px;
    padding:8px 10px;
    font-size:14px;
    font-weight:700;
    color:#555;
}

/* TANGGAL */
.ta-textbox{
    background:#f3f4f6;
    padding:6px 10px;
    border-radius:8px;
    font-size:12px;
    font-weight:700;
    color:#555;
    width:fit-content;
}

.ta-meta-label{
    margin-top: 10px;
    font-size: 14px;
    font-weight: 700;
    color: #ff8c42;
    display: block;
}

/* badge status */
.badge-status {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    border: 1px solid;
    display: inline-block;
    width: fit-content;
}

/* DIAJUKAN / PROSES */
.badge-proses {
    color:#2563EB;
    background: rgba(37,99,235,.12);
    border-color: rgba(37,99,235,.4);
}

/* PERLU REVISI */
.badge-revisi {
    color:#FF983D;
    background: rgba(255,152,61,.15);
    border-color: rgba(255,152,61,.5);
}

/* DISETUJUI */
.badge-disetujui {
    color:#16A34A;
    background: rgba(22,163,74,.15);
    border-color: rgba(22,163,74,.5);
}

/* DITOLAK */
.badge-ditolak {
    color:#DC2626;
    background: rgba(220,38,38,.15);
    border-color: rgba(220,38,38,.5);
}


/* id capsule */
.badge-id {
    background:#f3f4f6;
    color:#555;
    font-size:12px;
    padding:6px 14px;
    border-radius:10px;
    font-weight:700;
    width: fit-content;
    display: inline-block;
}

/* GRID META */
.ta-grid-meta{
    display:grid;
    grid-template-columns: repeat(3, minmax(160px, auto));
    gap:20px;
    padding-bottom:12px;
    border-bottom:1px dashed rgba(255,152,61,.3);
}

/* ITEM */
.ta-item{
    display:flex;
    flex-direction:column;
    gap:6px;
}

/* CATATAN ADMIN */
.catatan-box {
    background:#FFEDEE;
    border:1px solid rgba(255,58,61,.25);
    border-radius:16px;
    padding:14px 16px;
    margin-top:18px;
    font-size: 12px;
}

.catatan-title {
    font-size: 12px;
    font-weight:800;
    color:#FF3A3D;
    margin-bottom:6px;
    display:flex;
    align-items:center;
    gap:6px;
}

/* info bawah */
.info-warning {
    background:#FFE4E5;
    border:1px solid rgba(255,58,61,.35);
    border-radius:18px;
    padding:16px 18px;
    color:#FF3A3D;
    font-size:13px;
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:20px;
}

/* FILE LIST STYLES (Adapted for classic) */
.file-item {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    background: #fff;
    border: 1px solid #ff9f43;
    border-radius: 12px;
    margin-bottom: 10px;
}
.file-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    border: 1px solid #ff9f43;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    color: #ff9f43;
}
.file-label {
    font-size: 14px;
    font-weight: 600;
    color: #555;
    flex-grow: 1;
}
.file-status-badge {
    color: #ff9f43;
    border: 1px solid #ff9f43;
    padding: 3px 12px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    margin-right: 10px;
}
.btn-lihat {
    background: var(--gradient);
    color: white;
    text-decoration: none;
    padding: 6px 20px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    box-shadow: 0 4px 8px rgba(255, 95, 158, 0.2);
}

.btn-action {
    background: var(--gradient);
    color: white;
    text-decoration: none;
    padding: 12px 25px;
    border-radius: 15px;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 6px 15px rgba(255, 95, 158, 0.25);
    display: inline-block;
    flex: 1;
}

.btn-hubungi {
    background:#FF983D;
    color: white;
    text-decoration: none;
    padding: 12px 25px;
    border-radius: 15px;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 6px 15px rgba(255, 95, 158, 0.25);
    display: inline-block;
    flex: 1;
}

.action-row {
    display: flex;
    gap: 15px;
    margin-top: 20px;
    justify-content: center;
}

@media (max-width: 768px) {
    .main-content { margin-left: 0; padding: 20px; }
    .ta-grid-meta { grid-template-columns: repeat(2, 1fr); }
    .action-row { flex-direction: column; }
}
</style>
<body>
<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="topbar">
        <h1>Status Seminar Hasil</h1>
        <div class="mhs-info">
            <div class="mhs-text">
                <span>Selamat Datang,</span><br>
                <b><?= htmlspecialchars($_SESSION['user']['nama']) ?></b>
            </div>
            <div class="avatar">
                <span class="material-symbols-rounded">person</span>
            </div>
        </div>
    </div>

<?php if ($semhas_list): ?>
<?php foreach ($semhas_list as $data): 
    $status = strtolower(trim($data['status']));
    $display_status = $status === 'revisi' ? 'PERLU REVISI' : strtoupper($data['status']);

    $badgeClass = match(true) {
        str_contains($status,'revisi') => 'badge-revisi',
        $status === 'disetujui'        => 'badge-disetujui',
        $status === 'ditolak'          => 'badge-ditolak',
        default                        => 'badge-proses',
    };

    $tanggal = date('d F Y, H:i', strtotime($data['created_at']));
?>

<div class="card">
    <h2>Status Pengajuan</h2>
    <hr class="divider">

    <div class="ta-grid-meta">
        <div class="ta-item">
            <span class="ta-meta-label">ID Semhas</span>
            <span class="badge-id"><?= $data['id_semhas'] ?></span>
        </div>

        <div class="ta-item">
            <span class="ta-meta-label">Status Pengajuan</span>
            <span class="badge-status <?= $badgeClass ?>">
                <?= $display_status ?>
            </span>
        </div>

        <div class="ta-item">
            <span class="ta-meta-label">Tanggal Pengajuan</span>
            <span class="badge-id"><?= $tanggal ?></span>
        </div>
    </div>

    <div class="ta-judul-wrap">
        <span class="ta-meta-label">Judul Tugas Akhir</span>
        <div class="ta-body"><?= htmlspecialchars($data['judul_ta']) ?></div>
    </div>

    <div style="margin-top:20px;">
        <span class="ta-meta-label" style="margin-bottom: 10px;">Lampiran Berkas Seminar Hasil</span>

        <?php foreach ($file_map as $field => $info): ?>
        <div class="file-item">
            <div class="file-icon">
                <span class="material-symbols-rounded">description</span>
            </div>

            <div class="file-label"><?= $info['label'] ?></div>

            <?php if (str_contains(strtolower($data[$info['status']] ?? ''),'revisi')): ?>
                <div class="file-status-badge">PERLU REVISI</div>
            <?php endif; ?>

            <?php if (!empty($data[$field])): ?>
                <a href="../../uploads/semhas/<?= $data[$field] ?>" target="_blank" class="btn-lihat">Lihat</a>
            <?php else: ?>
                <span class="text-muted">Belum upload</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="catatan-box">
        <div class="catatan-title">
            <span class="material-symbols-rounded">info</span>
            CATATAN ADMIN
        </div>
        <?= $data['catatan'] ? nl2br(htmlspecialchars($data['catatan'])) : '-' ?>
    </div>

    <div class="action-row">
        <a href="detail.php?id=<?= $data['id'] ?>" class="btn-action">Lihat Detail</a>

        <?php if (str_contains($status,'revisi')): ?>
            <a href="revisi.php?id=<?= $data['id'] ?>" class="btn-action">
                Update Berkas Sekarang
            </a>
        <?php else: ?>
            <a href="https://wa.me/628112951003" target="_blank" class="btn-hubungi">
                Hubungi Admin
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="info-warning">
    <span class="material-symbols-rounded">info</span>
    Status pengajuan Anda diperbarui secara berkala oleh Admin Prodi.
</div>

<?php endforeach; ?>
<?php else: ?>
<div class="card" style="text-align:center;color:#999">
    Belum ada pengajuan Seminar Hasil.
</div>
<?php endif; ?>

</div>
</body>
</html>