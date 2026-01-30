<?php
session_start();
require "../../config/connection.php";
require_once "../../config/base_url.php";

// cek role mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . base_url('login.php'));
    exit;
}

$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';
$id = $_GET['id'] ?? 0;

// ambil data pengajuan + dosen pembimbing
$stmt = $pdo->prepare("
    SELECT 
        p.*,

        MAX(CASE WHEN db.role = 'dosbing_1' THEN d.nama END) AS dosen1_nama,
        MAX(CASE WHEN db.role = 'dosbing_2' THEN d.nama END) AS dosen2_nama

    FROM pengajuan_ta p
    LEFT JOIN dosbing_ta db ON p.id = db.pengajuan_id
    LEFT JOIN dosen d ON db.dosen_id = d.id

    WHERE p.id = ? AND p.mahasiswa_id = ?
    GROUP BY p.id
");
$stmt->execute([$id, $_SESSION['user']['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Pengajuan tidak ditemukan.");
}

$docs = [
    [
        'label'  => 'Formulir Pendaftaran & Persetujuan Tema',
        'file'   => $data['formulir_pendaftaran'],
        'note'   => $data['catatan_formulir_pendaftaran'],
        'status' => $data['status_formulir_pendaftaran']
    ],
    [
        'label'  => 'Bukti Pembayaran',
        'file'   => $data['bukti_pembayaran'],
        'note'   => $data['catatan_bukti_pembayaran'],
        'status' => $data['status_bukti_pembayaran']
    ],
    [
        'label'  => 'Transkrip Nilai',
        'file'   => $data['transkrip_nilai'],
        'note'   => $data['catatan_transkrip_nilai'],
        'status' => $data['status_transkrip_nilai']
    ],
    [
        'label'  => 'Bukti Kelulusan Mata Kuliah Magang / PI',
        'file'   => $data['bukti_magang'],
        'note'   => $data['catatan_bukti_magang'],
        'status' => $data['status_bukti_magang']
    ]
];


$adaRevisi = false;
foreach ($docs as $doc) {
    if (($doc['status'] ?? '') === 'revisi') {
        $adaRevisi = true;
        break;
    }
}
function getPengajuanBadgeClass($status)
{
    switch (strtolower($status)) {
        case 'disetujui':
            return 'badge-disetujui';
        case 'revisi':
            return 'badge-revisi';
        case 'ditolak':
            return 'badge-ditolak';
        case 'proses':
        case 'diajukan':
        default:
            return 'badge-proses';
    }
}

$badgeClass = getPengajuanBadgeClass($data['status'] ?? 'proses');

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Pengajuan TA</title>
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<link rel="stylesheet" href="<?= base_url('style.css') ?>">

<style>
body{
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
}

.card {
    background:#fff;
    border-radius:18px;
    padding:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    overflow-x: hidden;
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
}

/* tombol */
.actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top: 14px;
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
}

.status-proses {
    color:#2563EB;
    background: rgba(37,99,235,.15);
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
}

.catatan-title {
    font-size:12px;
    font-weight:800;
    color:#FF3A3D;
    margin-bottom:6px;
}

.material-symbols-rounded {
    font-size: 20px;
    vertical-align: middle;
}

.dokumen-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* FIX 2 KOLOM */
    gap: 16px;
    margin-top: 16px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .dokumen-grid {
        grid-template-columns: 1fr; /* HP jadi 1 kolom */
    }
}

.doc-card {
    border-radius:16px;
    padding:14px;
    background:#F8FFF7;
    border:1px solid rgba(22,163,74,.35);
}

.doc-card.revisi {
    background:#FFF5F5;
    border-color: rgba(255,58,61,.35);
}

.doc-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:10px;
}

.doc-title {
    font-size:14px;
    font-weight:700;
    color:#374151;
}

.doc-status {
    font-size:11px;
    font-weight:800;
    padding:4px 10px;
    border-radius:999px;
}

.status-ok {
    color:#16A34A;
    background: rgba(22,163,74,.15);
}

.status-revisi {
    color:#FF3A3D;
    background: rgba(255,58,61,.15);
}

.doc-actions {
    display:flex;
    gap:10px;
}

.btn-doc {
    flex:1;
    border-radius:10px;
    padding:6px 10px;
    font-size:12px;
    font-weight:700;
    border:1px solid #ddd;
    background:#fff;
    text-align:center;
    text-decoration:none;
    color:#555;
}

.btn-doc.primary {
    border-color:#2563EB;
    color:#2563EB;
}

.actions{
    display:flex;
    justify-content:center;
    gap:10px;
    margin-top: 10px;
}

.btn-revisi {
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-top:20px;
    padding:10px 15px;
    border-radius:999px;
    background: linear-gradient(135deg,#FF74C7,#FF983D);
    color:#fff;
    font-weight:600;
    text-decoration:none;
    font-size: 14px;
}

.btn-admin {
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-top:20px;
    padding:10px 15px;
    border-radius:999px;
    text-decoration:none;
    font-size: 14px;
    font-weight:600;
    text-decoration:none;
    background:#FF983D26;
    color:#FF983D;
    border:1px solid #FF983D;
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

</style>
</head>
<body>

<!-- SIDEBAR -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/coba/mahasiswa/sidebar.php'; ?>

<!-- CONTENT -->
<div class="main-content">
        <div class="topbar">
            <h1>Detail Pengajuan Tugas Akhir</h1>

            <div class="mhs-info">
                <div class="mhs-text">
                    <span>Selamat Datang,</span><br>
                    <b><?= htmlspecialchars($username) ?></b>
                </div>
                <div class="avatar">
                    <span class="material-symbols-rounded" style="color:#fff">person</span>
                </div>
            </div>
        </div>

        <div class="card">

                <!-- HEADER -->
                <h2>Status Pengajuan</h2>
                <hr class="divider">

                <!-- META INFO -->
                <div class="ta-grid-meta">
                    <div class="ta-item">
                        <span class="ta-meta-label">ID TA</span>
                        <span class="badge-id">
                            <?= htmlspecialchars($data['id_pengajuan'] ?? '-') ?>
                        </span>
                    </div>

                    <div class="ta-item">
                        <span class="ta-meta-label">Status Pengajuan</span>
                        <span class="badge-status <?= $badgeClass ?>">
                            <?= strtoupper($data['status'] ?? 'PROSES') ?>
                        </span>
                    </div>

                    <div class="ta-item">
                        <span class="ta-meta-label">Tanggal Pengajuan</span>
                        <span class="badge-id">
                            <?= date('d F Y, H:i', strtotime($data['created_at'])) ?>
                        </span>
                    </div>
                </div>

                <!-- JUDUL -->
                <div class="ta-judul-wrap">
                    <span class="ta-meta-label">Judul Tugas Akhir</span>
                    <div class="ta-body">
                        <?= htmlspecialchars($data['judul_ta']) ?>
                    </div>
                </div>

                <!-- CATATAN -->
                <div class="catatan-box">
                    <div class="catatan-title">
                        <span class="material-symbols-rounded">info</span>
                        CATATAN ADMIN
                    </div>
                    <?= !empty($data['catatan_admin'])
                        ? htmlspecialchars($data['catatan_admin'])
                        : '-' ?>
                </div>
                <hr class="divider">

        <h3>Daftar Lampiran Berkas</h3>
        <div class="dokumen-grid">
        <?php foreach ($docs as $doc): ?>
            <?php if (!$doc['file']) continue; ?>

            <?php
                $docStatus = strtolower($doc['status'] ?? 'proses');

                switch ($docStatus) {
                    case 'disetujui':
                        $statusText  = 'Disetujui';
                        $statusClass = 'badge-disetujui';
                        $cardClass   = 'doc-card';
                        break;

                    case 'revisi':
                        $statusText  = 'Perlu Revisi';
                        $statusClass = 'badge-revisi';
                        $cardClass   = 'doc-card revisi';
                        break;

                    default:
                        $statusText  = 'Belum Dicek';
                        $statusClass = 'badge-proses';
                        $cardClass   = 'doc-card';
                }

            ?>

            <div class="<?= $cardClass ?>">
                <div class="doc-header">
                    <div class="doc-title"><?= $doc['label'] ?></div>
                    <span class="badge-status <?= $statusClass ?>">
                        <?= $statusText ?>
                    </span>
                </div>

                <div style="font-size:12px; margin-bottom:8px;
                    color: <?= $docStatus === 'revisi' ? '#FF3A3D' : '#9ca3af' ?>;">Catatan:
                    
                    <?= !empty($doc['note'])
                        ? htmlspecialchars($doc['note'])
                        : '-' ?>

                </div>



                <div class="doc-actions">
                    <a class="btn-doc" target="_blank"
                    href="<?= base_url('uploads/ta/'.$doc['file']) ?>">
                    Lihat
                    </a>

                    <a class="btn-doc primary"
                    href="<?= base_url('uploads/ta/'.$doc['file']) ?>" download>
                    Unduh
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
                        
        <div class="actions">
        <?php if ($adaRevisi): ?>
            <a href="<?= base_url('mahasiswa/pengajuan/revisi_ta.php?id='.$data['id']) ?>"
            class="btn-revisi">
                <span class="material-symbols-rounded">sync</span>
                Update Judul & Berkas Sekarang
            </a>
            <a href="https://wa.me/628112951003" target="_blank"
            class="btn-admin">
                <span class="material-symbols-rounded">call</span>
                Hubungi Admin
            </a>
        <?php endif; ?>
        </div>
    </div>
    <!-- INFO GLOBAL -->
            <div class="info-warning">
                <span class="material-symbols-rounded">info</span>
                Status pengajuan Anda diperbarui secara berkala oleh tim Admin Prodi.
            </div>
</div>

</body>
</html>
