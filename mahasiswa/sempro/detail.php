<?php
session_start();
require "../../config/connection.php";
require_once '../../config/base_url.php';

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
// DAFTAR DOKUMEN (FIELD => LABEL)
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
    --bg: #FFF1E5;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    overflow-x: hidden;
    max-width: 100%;
}

body{
    background:#FFF1E5 !important;
}

.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 30px 40px;
    min-height: 100vh;
    width: calc(100vw - 280px);
    max-width: calc(100vw - 280px);
    overflow-x: hidden;
    box-sizing: border-box;
}

/* TOPBAR */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.topbar h1 {
    color: var(--orange);
    font-size: 28px;
    font-weight: 800;
    margin: 0;
}

.mhs-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.mhs-text {
    text-align: right;
    font-size: 13px;
    color: #555;
}

.mhs-text b {
    color: var(--orange);
    display: block;
    font-size: 14px;
}

.avatar {
    width: 45px;
    height: 45px;
    background: var(--orange);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

/* MAIN CARD */
.premium-card {
    background: #fff;
    border-radius: 25px;
    padding: 35px;
    box-shadow: 0 10px 30px rgba(255, 152, 61, 0.1);
    margin-bottom: 25px;
    border: 1px solid rgba(255, 152, 61, 0.1);
    max-width: 100%;
    overflow-wrap: break-word;
    word-wrap: break-word;
}

.card-title-main {
    text-align: center;
    color: #666;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 25px;
    border-bottom: 1px solid #dc9532ff;
    padding-bottom: 15px;
}

/* HEADER ROW (SNIPPET STYLE) */
.info-grid-header {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.label-orange-bold {
    color: var(--orange);
    font-size: 15px;
    font-weight: 700;
    display: block;
    margin-bottom: 10px;
}

.capsule-value {
    background: #f3f4f6;
    color: #555;
    font-size: 13px;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 700;
    display: inline-block;
}

.dashed-divider {
    border: none;
    border-top: 1px dashed rgba(255, 152, 61, 0.3);
    margin: 25px 0;
}

.capsule-status {
    padding: 10px 25px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    border: 1px solid;
    display: inline-block;
}

.st-disetujui { background: rgba(22, 163, 74, 0.1); color: #16A34A; border-color: rgba(22, 163, 74, 0.3); }
.st-proses { background: rgba(37, 99, 235, 0.1); color: #2563EB; border-color: rgba(37, 99, 235, 0.3); }
.st-diajukan { background: rgba(37, 99, 235, 0.1); color: #2563EB; border-color: rgba(37, 99, 235, 0.3); }
.st-revisi { background: rgba(255, 152, 61, 0.1); color: #FF983D; border-color: rgba(255, 152, 61, 0.3); }
.st-ditolak { background: rgba(220, 38, 38, 0.1); color: #DC2626; border-color: rgba(220, 38, 38, 0.3); }

.capsule-id {
    background: #f1f3f5;
    color: #999;
    padding: 8px 20px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
}

.date-top {
    margin-left: auto;
    color: #aaa;
    font-size: 13px;
    font-weight: 600;
}

/* STUDENT INFO */
.label-small {
    color: var(--orange);
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: 5px;
    display: block;
}

.student-name {
    font-size: 22px;
    font-weight: 800;
    color: #444;
    margin-bottom: 25px;
}

/* SCHEDULE BOXES */
.schedule-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    border: 1px solid #eba337ff;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 30px;
}

.schedule-box {
    padding: 25px 15px;
    text-align: center;
    border-right: 1px solid #eba337ff;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.schedule-box:last-child { border-right: none; }

.schedule-box .icon {
    color: var(--orange);
    margin-bottom: 5px;
}

.schedule-box .label {
    font-size: 11px;
    color: #a43636ff;
    font-style: italic;
    font-weight: 600;
}

.schedule-box .value {
    font-size: 16px;
    font-weight: 800;
    color: #080808ff;
    letter-spacing: 0.5px;
}

/* CATATAN AREA */
.catatan-section {
    margin-top: 10px;
}

.catatan-box {
    background: #f9f9fb;
    border: 1px solid #eba337ff;
    border-radius: 15px;
    padding: 15px 20px;
    min-height: 30px;
    color: #777;
    font-size: 13px;
    margin-top: 8px;
}

/* DOCUMENT LIST */
.doc-section {
    margin-top: 40px;
}

.doc-section-title {
    font-size: 14px;
    font-weight: 800;
    color: #666;
    margin-bottom: 20px;
}

.doc-card {
    background: #fff;
    border: 1px solid #eba337ff;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 20px;
}

.doc-main {
    display: flex;
    align-items: center;
    gap: 20px;
}

.doc-icon {
    width: 45px;
    height: 45px;
    background: #fff5f0;
    border: 1px solid #eba337ff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--orange);
}

.doc-name {
    flex: 1;
    font-size: 16px;
    font-weight: 700;
    color: #555;
}

.btn-lihat {
    background: var(--gradient);
    color: white;
    text-decoration: none;
    padding: 10px 40px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 4px 15px rgba(255, 116, 199, 0.2);
    transition: all 0.3s;
}

.btn-lihat:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 116, 199, 0.3); }

.doc-catatan-label {
    margin-top: 15px;
    font-size: 11px;
    font-weight: 800;
    color: var(--orange);
    display: block;
}

.doc-catatan-box {
    background: #fff;
    border: 1px solid #eba337ff;
    border-radius: 15px;
    padding: 12px 20px;
    margin-top: 5px;
    font-size: 12px;
    color: #999;
}

/* FOOTER BAR */
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
.material-symbols-rounded {
    font-size: 20px;
    vertical-align: middle;
}

/* TABLET */
@media (max-width: 1024px) {
    .main-content {
        margin-left: 70px;
        padding: 25px 30px;
        width: calc(100vw - 70px);
        max-width: calc(100vw - 70px);
    }
    
    .premium-card {
        padding: 30px;
    }
    
    .schedule-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .schedule-box {
        border-right: none;
        border-bottom: 1px solid #eba337ff;
    }
    
    .schedule-box:last-child {
        border-bottom: none;
    }
    
    .doc-main {
        flex-wrap: wrap;
        gap: 15px;
    }
}

/* MOBILE */
@media (max-width: 768px) {
    .container {
        display: block !important;
        width: 100% !important;
        overflow-x: hidden !important;
    }
    
    .main-content { 
        margin-left: 60px !important;
        padding: 15px !important;
        width: calc(100vw - 60px) !important;
        max-width: calc(100vw - 60px) !important;
    }

    
    .topbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .topbar h1 {
        font-size: 22px;
    }
    
    .mhs-info {
        align-self: flex-end;
    }
    
    .premium-card {
        padding: 20px;
        border-radius: 20px;
    }
    
    .card-title-main {
        font-size: 14px;
    }
    
    .info-grid-header {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .label-orange-bold {
        font-size: 13px;
    }
    
    .capsule-value {
        font-size: 12px;
        padding: 8px 14px;
        width: 100%;
        text-align: center;
    }
    
    .capsule-status {
        padding: 8px 20px;
        font-size: 11px;
        width: 100%;
        text-align: center;
    }
    
    .student-name {
        font-size: 18px;
    }
    
    .schedule-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .schedule-box {
        padding: 20px 15px;
        border-right: none;
        border-bottom: 1px solid #eba337ff;
    }
    
    .schedule-box:last-child {
        border-bottom: none;
    }
    
    .schedule-box .value {
        font-size: 14px;
    }
    
    .catatan-box {
        font-size: 12px;
        padding: 12px 15px;
    }
    
    .doc-section {
        margin-top: 30px;
    }
    
    .doc-section-title {
        font-size: 13px;
    }
    
    .doc-card {
        padding: 15px;
    }
    
    .doc-main {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .doc-name {
        font-size: 14px;
    }
    
    .btn-lihat {
        width: 100%;
        text-align: center;
        padding: 12px 20px;
    }
    
    .info-warning {
        font-size: 12px;
        padding: 12px 15px;
    }
}

/* SMALL MOBILE */
@media (max-width: 480px) {
    .main-content {
        padding: 10px;
    }
    
    .topbar h1 {
        font-size: 18px;
    }
    
    .premium-card {
        padding: 15px;
        border-radius: 15px;
    }
    
    .schedule-box .value {
        font-size: 13px;
    }
    
    .doc-icon {
        width: 40px;
        height: 40px;
    }
}
</style>
</head>

<body>
<div class="container" style="display: flex; width: 100%;">

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <!-- TOPBAR -->
    <div class="topbar">
        <h1>Detail Status Seminar Proposal</h1>
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

    <!-- MAIN CARD -->
    <div class="premium-card">
        <div class="card-title-main">Informasi Lengkap Pendaftaran Seminar Proposal</div>

        <?php 
            $st = strtolower($data['status']);
            $st_class = "st-".$st;
            $st_lbl = ($st === 'revisi') ? 'PERLU REVISI' : strtoupper($data['status']);
        ?>
        
        <div class="info-grid-header">
            <div class="info-item">
                <span class="label-orange-bold">ID Sempro</span>
                <div class="capsule-value"><?= htmlspecialchars($data['id_sempro']) ?></div>
            </div>
            <div class="info-item">
                <span class="label-orange-bold">Status Pengajuan</span>
                <div class="capsule-status <?= $st_class ?>"><?= $st_lbl ?></div>
            </div>
            <div class="info-item">
                <span class="label-orange-bold">Tanggal Pengajuan</span>
                <div class="capsule-value"><?= date('d F Y, H:i', strtotime($data['created_at'])) ?></div>
            </div>
        </div>

        <hr class="dashed-divider">

        <span class="label-small">Nama Mahasiswa</span>
        <div class="student-name"><?= strtoupper(htmlspecialchars($data['mahasiswa_nama'])) ?></div>

        <div class="schedule-grid">
            <div class="schedule-box">
                <div class="icon"><span class="material-symbols-rounded">calendar_today</span></div>
                <div class="label">Tanggal Seminar</div>
                <div class="value"><?= $data['tanggal_sempro'] ? date('Y-m-d', strtotime($data['tanggal_sempro'])) : '-' ?></div>
            </div>
            <div class="schedule-box">
                <div class="icon"><span class="material-symbols-rounded">schedule</span></div>
                <div class="label">Waktu Seminar</div>
                <div class="value"><?= $data['jam_sempro'] ?? '-' ?></div>
            </div>
            <div class="schedule-box">
                <div class="icon"><span class="material-symbols-rounded">location_on</span></div>
                <div class="label">Ruangan Seminar</div>
                <div class="value"><?= $data['ruangan_sempro'] ?? '-' ?></div>
            </div>
        </div>

        <div class="catatan-section">
            <span class="label-small">Catatan Admin:</span>
            <div class="catatan-box">
                <?= $data['catatan'] ? nl2br(htmlspecialchars($data['catatan'])) : '-' ?>
            </div>
        </div>
        
        <!-- DOCUMENTS -->
        <div class="doc-section">
            <div class="doc-section-title">DAFTAR LAMPIRAN BERKAS</div>
            
            <?php foreach ($files as $field => $label): ?>
                <?php 
                    $st_file = strtolower($data['status_'.$field] ?? 'proses');
                    $st_f_class = "st-".$st_file;
                    $st_f_lbl = ($st_file === 'revisi') ? 'PERLU REVISI' : strtoupper($st_file);
                ?>
                <div class="doc-card">
                    <div class="doc-main">
                        <div class="doc-icon">
                            <span class="material-symbols-rounded">description</span>
                        </div>
                        <div class="doc-name"><?= $label ?> Tugas Akhir</div>
                        
                        <div class="capsule-status <?= $st_f_class ?>" style="padding: 5px 15px; margin-right: 15px;"><?= $st_f_lbl ?></div>

                        <?php if (!empty($data[$field])): ?>
                            <a href="../../uploads/sempro/<?= htmlspecialchars($data[$field]) ?>" target="_blank" class="btn-lihat">Lihat</a>
                        <?php else: ?>
                            <span style="font-size: 12px; color: #ccc;">Belum upload</span>
                        <?php endif; ?>
                    </div>
                    <span class="doc-catatan-label">Catatan:</span>
                    <div class="doc-catatan-box">
                        <?= !empty($data['catatan_'.$field]) ? htmlspecialchars($data['catatan_'.$field]) : '-' ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- FOOTER INFO BAR -->

    <div class="info-warning">
                <span class="material-symbols-rounded">info</span>
                Jadwal seminar akan muncul secara otomatis setelah pengajuan divalidasi dan dikonfirmasi oleh Admin Program Studi. Pastikan Anda rutin memantau halaman ini.
            </div>

    <!-- ACTION (UPLOAD REVISI) -->
    <?php if (strtolower($data['status']) === 'revisi' && $_SESSION['user']['role'] === 'mahasiswa'): ?>
        <div style="display: flex; justify-content: center; margin-top: 30px; margin-bottom: 50px;">
            <a class="btn-lihat" style="padding: 15px 50px; border-radius: 18px; font-size: 16px;" href="<?= base_url('mahasiswa/sempro/revisi.php?id=' . $data['id']) ?>">
                Upload Revisi Sekarang
            </a>
        </div>
    <?php endif; ?>

</div>
</div>
</body>
</html>
