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
    SELECT ps.*, pta.judul_ta
    FROM pengajuan_semhas ps
    JOIN pengajuan_ta pta ON ps.pengajuan_ta_id = pta.id
    WHERE ps.mahasiswa_id = ?
    ORDER BY ps.created_at DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===============================
// MAPPING FILE SEMHAS
// ===============================
$fileMap = [
    'file_berita_acara' => [
        'label' => 'Lembar Berita Acara',
        'status_field' => 'status_file_berita_acara'
    ],
    'file_persetujuan_laporan' => [
        'label' => 'Persetujuan Laporan Tugas Akhir',
        'status_field' => 'status_file_persetujuan_laporan'
    ],
    'file_pendaftaran_ujian' => [
        'label' => 'Form Pendaftaran Ujian',
        'status_field' => 'status_file_pendaftaran_ujian'
    ],
    'file_buku_konsultasi' => [
        'label' => 'Buku Konsultasi Tugas Akhir',
        'status_field' => 'status_file_buku_konsultasi'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Seminar Hasil</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
    --bg: #FFF1E5;
}

body {
    font-family: 'Outfit', sans-serif;
    background: var(--bg) !important;
    margin: 0;
}

.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 30px 40px;
    min-height: 100vh;
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

/* ENHANCED CARD */
.card-outer-container {
    max-width: 950px;
    margin: 0 auto;
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

.premium-card {
    background: #fff;
    border-radius: 30px;
    padding: 0;
    box-shadow: 0 10px 30px rgba(255, 152, 61, 0.1);
    margin-bottom: 25px;
    border: 1px solid rgba(255, 152, 61, 0.1);
    overflow: hidden;
    
}

.card-sub-header {
    background: #fff;
    padding: 15px;
    text-align: center;
    font-weight: 700;
    color: #666;
    font-size: 15px;
    border-bottom: 1px solid #eba33733;
}

.card-body-enhanced {
    padding: 40px;
}

/* INFO GRID (ID, STATUS, DATE) */
.info-grid-header {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.label-orange-sm {
    color: var(--orange);
    font-size: 13px;
    font-weight: 700;
    display: block;
    margin-bottom: 8px;
}

.capsule-grey {
    background: #f4f4f4;
    color: #555;
    font-size: 12px;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 700;
    display: inline-block;
}

.capsule-status-sm {
    padding: 8px 25px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    border: 1px solid;
    display: inline-block;
}

.st-disetujui { background: rgba(22, 163, 74, 0.05); color: #16A34A; border-color: rgba(22, 163, 74, 0.2); }
.st-proses { background: rgba(37, 99, 235, 0.05); color: #2563EB; border-color: rgba(37, 99, 235, 0.2); }
.st-revisi { background: rgba(255, 152, 61, 0.05); color: #FF983D; border-color: rgba(255, 152, 61, 0.2); }
.st-ditolak { background: rgba(220, 38, 38, 0.05); color: #DC2626; border-color: rgba(220, 38, 38, 0.2); }

.divider-light {
    border: none;
    border-top: 1px dashed rgba(255, 152, 61, 0.2);
    margin-bottom: 30px;
}

/* STUDENT SECTION */
.student-section {
    margin-bottom: 35px;
}

.student-name-lg {
    font-size: 22px;
    font-weight: 800;
    color: #333;
    margin-top: 5px;
}

/* SCHEDULE GRID */
.schedule-container {
    border: 1px solid #ffd1a9;
    border-radius: 20px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    overflow: hidden;
    margin-bottom: 35px;
}

.schedule-item {
    padding: 30px 20px;
    text-align: center;
    border-right: 1px solid #ffd1a9;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.schedule-item:last-child { border-right: none; }

.schedule-item i {
    color: var(--orange);
    font-size: 24px;
}

.schedule-label {
    font-size: 11px;
    color: #a43636;
    font-style: italic;
    font-weight: 600;
}

.schedule-value {
    font-size: 18px;
    font-weight: 800;
    color: #000;
}

/* JUDUL & CATATAN */
.label-catatan {
    color: var(--orange);
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: 8px;
    display: block;
}

.catatan-box-enhanced {
    background: #fff;
    border: 1px solid #ffd1a9;
    border-radius: 20px;
    padding: 20px 25px;
    margin-bottom: 35px;
    min-height: 30px;
    color: #666;
    font-size: 14px;
}

/* DOCUMENT LIST */
.doc-header-sm {
    font-size: 12px;
    font-weight: 800;
    color: #777;
    margin-bottom: 15px;
    text-transform: uppercase;
}

.doc-list-inner {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.doc-item-row {
    background: #fff;
    border: 1px solid #ffd1a9;
    border-radius: 18px;
    padding: 12px 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.doc-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.doc-icon-wrap {
    width: 35px;
    height: 35px;
    border: 1px solid var(--orange);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--orange);
}

.doc-name-text {
    font-size: 14px;
    font-weight: 600;
    color: #555;
}

.btn-view-gradient {
    background: var(--gradient);
    color: white;
    text-decoration: none;
    padding: 8px 35px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 13px;
    box-shadow: 0 4px 12px rgba(255, 116, 199, 0.2);
}

/* BUTTONS */
.action-buttons-flex {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.btn-lih-detail {
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

.btn-lih-detail:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(255, 116, 199, 0.4); }




/* info */
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

@media (max-width: 768px) {
    .main-content { margin-left: 0; padding: 20px; }
    .info-grid-header { grid-template-columns: 1fr; }
    .schedule-container { grid-template-columns: 1fr; }
    .schedule-item { border-right: none; border-bottom: 1px solid #ffd1a9; }
}
</style>
</head>

<body>
<div class="container" style="display: flex; width: 100%;">

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <!-- TOPBAR -->
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

    <div class="card-outer-container">
        <?php if(empty($data)): ?>
            <div class="premium-card" style="text-align: center; color: #999; padding: 50px;">
                <span class="material-symbols-rounded" style="font-size: 48px; margin-bottom: 10px;">assignment_late</span>
                <p>Belum ada pengajuan Seminar Hasil.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($data as $d): ?>
            <?php
                $st = strtolower($d['status']);
                $st_class = "st-".$st;
                $st_lbl = ($st === 'revisi') ? 'PERLU REVISI' : strtoupper($d['status']);
                
                $tanggal = !empty($d['tanggal_sidang']) ? date('Y-m-d', strtotime($d['tanggal_sidang'])) : '-';
                $jam = !empty($d['jam_sidang']) ? substr($d['jam_sidang'], 0, 8) : '-';
                $ruangan = $d['tempat_sidang'] ?? '-';
            ?>

            <div class="premium-card">
                <div class="card-sub-header">Riwayat Pengajuan dan Hasil Evaluasi</div>
                
                <div class="card-body-enhanced">
                    <!-- INFO GRID -->
                    <div class="info-grid-header">
                        <div>
                            <span class="label-orange-sm">ID Semhas</span>
                            <div class="capsule-grey"><?= htmlspecialchars($d['id_semhas']) ?></div>
                        </div>
                        <div>
                            <span class="label-orange-sm">Status Pengajuan</span>
                            <div class="capsule-status-sm <?= $st_class ?>"><?= $st_lbl ?></div>
                        </div>
                        <div>
                            <span class="label-orange-sm">Tanggal Pengajuan</span>
                            <div class="capsule-grey"><?= date('d F Y, H:i', strtotime($d['created_at'])) ?></div>
                        </div>
                    </div>

                    <hr class="divider-light">

                    <!-- STUDENT SECTION -->
                    <div class="student-section">
                        <span class="label-orange-sm">NAMA MAHASISWA</span>
                        <div class="student-name-lg"><?= strtoupper(htmlspecialchars($_SESSION['user']['nama'])) ?></div>
                    </div>

                    <!-- SCHEDULE GRID -->
                    <div class="schedule-container">
                        <div class="schedule-item">
                            <i class="material-symbols-rounded">calendar_today</i>
                            <span class="schedule-label">Tanggal Seminar</span>
                            <span class="schedule-value"><?= $tanggal ?></span>
                        </div>
                        <div class="schedule-item">
                            <i class="material-symbols-rounded">schedule</i>
                            <span class="schedule-label">Waktu Seminar</span>
                            <span class="schedule-value"><?= $jam ?></span>
                        </div>
                        <div class="schedule-item">
                            <i class="material-symbols-rounded">location_on</i>
                            <span class="schedule-label">Ruangan Seminar</span>
                            <span class="schedule-value"><?= $ruangan ?></span>
                        </div>
                    </div>

                    <!-- CATATAN ADMIN -->
                    <div class="catatan-label-wrap">
                        <span class="label-catatan">CATATAN ADMIN:</span>
                        <div class="catatan-box-enhanced">
                            <?= $d['catatan'] ? nl2br(htmlspecialchars($d['catatan'])) : '-' ?>
                        </div>
                    </div>

                    <!-- DOCUMENT LIST -->
                    <div class="doc-list-section">
                        <div class="doc-header-sm">DAFTAR LAMPIRAN BERKAS</div>
                        <div class="doc-list-inner">
                            <?php foreach ($fileMap as $field => $info): ?>
                                <div class="doc-item-row">
                                    <div class="doc-left">
                                        <div class="doc-icon-wrap">
                                            <span class="material-symbols-rounded">description</span>
                                        </div>
                                        <div class="doc-name-text"><?= $info['label'] ?></div>
                                    </div>
                                    <?php if (!empty($d[$field])): ?>
                                        <a href="../../uploads/semhas/<?= htmlspecialchars($d[$field]) ?>" target="_blank" class="btn-view-gradient">Lihat</a>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: #ccc;">Belum upload</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons-flex">
                <a href="detail.php?id=<?= $d['id'] ?>" class="btn-lih-detail">Lihat Detail</a>
                
                <?php if ($d['status'] === 'revisi'): ?>
                    <a href="revisi.php?id=<?= $d['id'] ?>" class="btn-lih-detail" style="background: #FF983D;">
                        Update Berkas Sekarang
                    </a>
                <?php endif; ?>
            </div>

            <div class="info-warning">
                <span class="material-symbols-rounded">info</span>
                Status pengajuan Anda diperbarui secara berkala oleh tim Admin Prodi.
            </div>

        <?php endforeach; ?>
    </div>

</div>
</div>
</body>
</html>
