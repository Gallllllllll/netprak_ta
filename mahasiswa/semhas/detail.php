<?php
session_start();
require "../../config/connection.php";
require_once '../../config/base_url.php';

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
$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';

$id = $_GET['id'] ?? 0;

// ===============================
// AMBIL DATA SEMHAS + TIM DOSEN
// ===============================
$stmt = $pdo->prepare("
    SELECT 
        ps.*,
        pt.judul_ta,
        m.nama AS nama_mahasiswa,

        MAX(d1.nama) AS pembimbing_1,
        MAX(d2.nama) AS pembimbing_2,

        GROUP_CONCAT(DISTINCT dp.nama SEPARATOR ', ') AS tim_penguji

    FROM pengajuan_semhas ps
    
    LEFT JOIN pengajuan_ta pt ON ps.pengajuan_ta_id = pt.id
    LEFT JOIN mahasiswa m ON ps.mahasiswa_id = m.id

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
        'diajukan'  => ['class' => 'badge-proses',    'label' => 'Diajukan'],
        'revisi'    => ['class' => 'badge-revisi',    'label' => 'Perlu Revisi'],
        'disetujui' => ['class' => 'badge-disetujui', 'label' => 'Disetujui'],
        'ditolak'   => ['class' => 'badge-ditolak',   'label' => 'Ditolak'],
        default     => ['class' => 'badge-proses',    'label' => 'Diajukan']
    };
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Seminar Hasil</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<style>
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

/* CARD */
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

/* SCHEDULE BOXES */
.schedule-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    border: 1px solid #fce8e8;
    border-radius: 20px;
    overflow: hidden;
    margin-top: 15px;
    margin-bottom: 25px;
}

.schedule-box {
    padding: 20px 15px;
    text-align: center;
    border-right: 1px solid #fce8e8;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.schedule-box:last-child { border-right: none; }

.schedule-box .icon {
    font-size: 24px;
    color: #FF983D;
    margin-bottom: 5px;
}

.schedule-box .label {
    font-size: 12px;
    color: #a43636ff;
    font-style: italic;
    font-weight: 600;
}

.schedule-box .value {
    font-size: 14px;
    font-weight: 800;
    color: #080808ff;
    letter-spacing: 0.5px;
}

/* DOSEN BOX */
.dosen-box {
    background:#fff7f1;
    border:1px solid rgba(255,152,61,.35);
    border-radius:16px;
    padding:16px;
    margin-top:16px;
}

.dosen-title {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    font-size:13px;
    font-weight:800;
    color: #ff8c42;
    margin-bottom:10px;
}

.dosen-list {
    list-style:none;
    padding:0;
    margin:0;
}

.dosen-list li {
    display:flex;
    gap:10px;
    align-items:flex-start;
    margin-bottom:12px;
    font-size:13px;
}

.dosen-num {
    width:26px;
    height:26px;
    border-radius:8px;
    border:1px solid #FF983D;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    color:#FF983D;
    flex-shrink:0;
}
.dosen-info{
    display: flex;
    flex-direction: column;
    justify-content: start;
    font-size:14px;
    font-weight: 600;
    color: #5F5F5F;
}
.dosen-info p{
    margin: 0;
    font-size: 11px;
    font-weight: 800;
    color: #FF3A3D;
}

/* CATATAN ADMIN */
.catatan-box {
    background:#FFEDEE;
    border:1px solid rgba(255,58,61,.25);
    border-radius:16px;
    padding:14px 16px;
    margin-top:18px;
    font-size: 12px;
    color: #555;
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

/* DOCUMENT LIST */
.doc-section {
    margin-top: 30px;
}

.doc-section-title {
    font-size: 13px;
    font-weight: 800;
    color: #666;
    margin-bottom: 20px;
}

.doc-card {
    background: #fff;
    border: 1px solid #FFD9BB;
    border-radius: 18px;
    padding: 18px;
    margin-bottom: 18px;
}

.doc-main {
    display: flex;
    align-items: center;
    gap: 16px;
}

.doc-icon {
    width: 40px;
    height: 40px;
    background: #fff5f0;
    border: 1px solid #FFD9BB;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FF983D;
    flex-shrink: 0;
}

.doc-name {
    flex: 1;
    font-size: 14px;
    font-weight: 700;
    color: #555;
}

.capsule-status {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    border: 1px solid;
    display: inline-block;
    margin-right: 10px;
}

.btn-lihat {
    background: linear-gradient(135deg, #FF74C7, #FF983D);
    color: white;
    text-decoration: none;
    padding: 8px 18px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 12px;
    box-shadow: 0 4px 15px rgba(255, 116, 199, 0.2);
    transition: all 0.3s;
}

.btn-lihat:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 6px 20px rgba(255, 116, 199, 0.3); 
}

.doc-catatan-label {
    margin-top: 12px;
    font-size: 10px;
    font-weight: 800;
    color: #FF983D;
    display: block;
}

.doc-catatan-box {
    background: #f9f9fb;
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 10px 14px;
    margin-top: 5px;
    font-size: 12px;
    color: #777;
}

/* FILE STATUS BADGES */
.badge-proses {
    color: #2563EB;
    background: rgba(37, 99, 235, 0.12);
    border-color: rgba(37, 99, 235, 0.4);
}

.badge-revisi {
    color: #FF983D;
    background: rgba(255, 152, 61, 0.15);
    border-color: rgba(255, 152, 61, 0.5);
}

.badge-disetujui {
    color: #16A34A;
    background: rgba(22, 163, 74, 0.15);
    border-color: rgba(22, 163, 74, 0.5);
}

.badge-ditolak {
    color: #DC2626;
    background: rgba(220, 38, 38, 0.15);
    border-color: rgba(220, 38, 38, 0.5);
}

/* ACTIONS */
.actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
    margin-bottom: 30px;
}

.btn-admin {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 999px;
    background: #FF983D26;
    color: #FF983D;
    border: 1px solid #FF983D;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-admin:hover {
    background: #FF983D;
    color: white;
}

/* INFO WARNING */
.info-warning {
    background: #FFE4E5;
    border: 1px solid rgba(255, 58, 61, 0.35);
    border-radius: 18px;
    padding: 16px 18px;
    color: #FF3A3D;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    margin-bottom: 30px;
}
</style>
</head>

<body>
<div class="container">

<?php include "../sidebar.php"; ?>

<div class="main-content">
        <div class="topbar">
            <h1>Detail Status Seminar Hasil</h1>

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
            <h2>Informasi Lengkap Pendaftaran Seminar Hasil</h2>
            <hr class="divider">

            <!-- META INFO -->
            <div class="ta-grid-meta">
                <div class="ta-item">
                    <span class="ta-meta-label">ID Semhas</span>
                    <span class="badge-id">
                        <?= htmlspecialchars($data['id_semhas'] ?? '-') ?>
                    </span>
                </div>

                <div class="ta-item">
                    <span class="ta-meta-label">Status Pengajuan</span>
                    <span class="badge-status <?php 
                        $status = strtolower($data['status'] ?? '');
                        echo match($status) {
                            'disetujui' => 'badge-disetujui',
                            'revisi' => 'badge-revisi',
                            'ditolak' => 'badge-ditolak',
                            default => 'badge-proses'
                        };
                    ?>">
                        <?= strtoupper($data['status'] ?? 'PROSES') ?>
                    </span>
                </div>

                <div class="ta-item">
                    <span class="ta-meta-label">Tanggal Pengajuan</span>
                    <span class="badge-id">
                        <?= $data['created_at'] ? date('d F Y, H:i', strtotime($data['created_at'])) : '-' ?>
                    </span>
                </div>
            </div>

            <!-- NAMA MAHASISWA -->
            <div class="ta-judul-wrap">
                <span class="ta-meta-label">Nama Mahasiswa</span>
                <div class="ta-body">
                    <?= htmlspecialchars($data['nama'] ?? $username ?? '-') ?>
                </div>
            </div>

            <!-- JUDUL -->
            <div class="ta-judul-wrap">
                <span class="ta-meta-label">Judul Tugas Akhir</span>
                <div class="ta-body">
                    <?= htmlspecialchars($data['judul_ta'] ?? '-') ?>
                </div>
            </div>

            <!-- SCHEDULE GRID -->
            <?php if ($data['tanggal_sidang'] || $data['jam_sidang'] || $data['tempat_sidang']): ?>
            <div class="schedule-grid">
                <div class="schedule-box">
                    <span class="material-symbols-rounded icon">calendar_today</span>
                    <span class="label">Tanggal Seminar</span>
                    <span class="value"><?= $data['tanggal_sidang'] ? date('d F Y', strtotime($data['tanggal_sidang'])) : '-' ?></span>
                </div>
                <div class="schedule-box">
                    <span class="material-symbols-rounded icon">schedule</span>
                    <span class="label">Waktu Seminar</span>
                    <span class="value"><?= $data['jam_sidang'] ? substr($data['jam_sidang'],0,5) : '-' ?></span>
                </div>
                <div class="schedule-box">
                    <span class="material-symbols-rounded icon">location_on</span>
                    <span class="label">Ruangan Seminar</span>
                    <span class="value"><?= $data['tempat_sidang'] ?: '-' ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- CATATAN ADMIN -->
            <div class="catatan-box">
                <div class="catatan-title">
                    <span class="material-symbols-rounded" style="font-size: 18px;">info</span>
                    CATATAN ADMIN
                </div>
                <hr class="divider" style="margin:8px 0;">
                <?= !empty($data['catatan']) ? htmlspecialchars($data['catatan']) : '-' ?>
            </div>

            <!-- TIM SEMINAR HASIL -->
            <div class="dosen-box">
                <div class="dosen-title">
                    <span class="material-symbols-rounded">school</span>
                    TIM SEMINAR HASIL
                </div>
                <hr class="divider">
                <ul class="dosen-list">
                    <li>
                        <div class="dosen-num">1</div>
                        <div class="dosen-info">
                            <p>DOSEN PEMBIMBING 1</p>
                            <?= htmlspecialchars($data['pembimbing_1'] ?: '-') ?>
                        </div>
                    </li>
                    <li>
                        <div class="dosen-num">2</div>
                        <div class="dosen-info">
                            <p>DOSEN PEMBIMBING 2</p>
                            <?= htmlspecialchars($data['pembimbing_2'] ?: '-') ?>
                        </div>
                    </li>
                    <li>
                        <div class="dosen-num">3</div>
                        <div class="dosen-info">
                            <p>DOSEN PENGUJI</p>
                            <?= htmlspecialchars($data['tim_penguji'] ?: '-') ?>
                        </div>
                    </li>
                </ul>
            </div>
            <hr class="divider">

    <!-- DOKUMEN -->
    <div class="doc-section">
        <div class="doc-section-title">DAFTAR LAMPIRAN BERKAS</div>
        
        <?php foreach ($files as $field => $info): ?>
        <?php
            $statusField = 'status_' . $field;
            $statusValue = $data[$statusField] ?? 'diajukan';
            $badge = badgeFileStatus($statusValue);
        ?>
            <div class="doc-card">
                <div class="doc-main">
                    <div class="doc-icon">
                        <span class="material-symbols-rounded">description</span>
                    </div>
                    <div class="doc-name"><?= htmlspecialchars($info['label']) ?></div>
                    
                    <span class="capsule-status <?= $badge['class'] ?>" style="padding: 5px 15px; margin-right: 15px;"><?= $badge['label'] ?></span>

                    <?php if (!empty($data[$field])): ?>
                        <a href="../../uploads/semhas/<?= htmlspecialchars($data[$field]) ?>" target="_blank" class="btn-lihat">Lihat</a>
                    <?php else: ?>
                        <span style="font-size: 12px; color: #ccc;">Belum upload</span>
                    <?php endif; ?>
                </div>
                <span class="doc-catatan-label">Catatan:</span>
                <div class="doc-catatan-box">
                    <?= !empty($data[$info['catatan']]) ? htmlspecialchars($data[$info['catatan']]) : '-' ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (
        empty($data['tanggal_sidang']) ||
        empty($data['jam_sidang']) ||
        empty($data['tempat_sidang'])
    ): ?>
        <div class="info-warning">
            <span class="material-symbols-rounded">info</span>
            Jadwal seminar akan muncul setelah pengajuan divalidasi dan dikonfirmasi oleh Admin Program Studi.
        </div>
    <?php endif; ?>

    <div class="actions">
        <a href="https://wa.me/628112951003" target="_blank" class="btn-admin">
            <span class="material-symbols-rounded">call</span>
            Hubungi Admin
        </a>
    </div>

</div>

    <!-- INFO BOX -->
    <div class="info-warning">
        <span class="material-symbols-rounded">info</span>
        Jadwal seminar akan muncul secara otomatis setelah pengajuan divalidasi dan dikonfirmasi oleh admin. Pastikan Anda rutin memantau halaman ini.
    </div>

</div>
</div>
</body>
</html>