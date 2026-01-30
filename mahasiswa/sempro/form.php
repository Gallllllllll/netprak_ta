<?php
session_start();
require "../../config/connection.php";
require_once '../../config/base_url.php';

/* ===============================
   CEK LOGIN MAHASISWA
================================ */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . base_url('login.php'));
    exit;
}
$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';
$pesan_error  = '';
$boleh_upload = false;
$can_proceed = false;

// ===============================
// DEFAULT SEMPRO (BELUM AJUAN)
// ===============================
$pengajuan = [
    'id_sempro'   => '-',
    'created_at'  => null,
    'status'      => '-'
];

$has_sempro = false;

/* ===============================
   CEK PENGAJUAN TA TERAKHIR
================================ */
$stmt = $pdo->prepare("
    SELECT id, id_pengajuan, judul_ta, status
    FROM pengajuan_ta
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$_SESSION['user']['id']]);
$ta = $stmt->fetch(PDO::FETCH_ASSOC);
$dosen = null;

if ($ta) {
    $stmt = $pdo->prepare("
        SELECT
            MAX(CASE WHEN db.role = 'dosbing_1' THEN d.nama END) AS dosen1_nama,
            MAX(CASE WHEN db.role = 'dosbing_2' THEN d.nama END) AS dosen2_nama,
            MAX(CASE WHEN db.role = 'dosbing_1' THEN db.status_persetujuan END) AS dosen1_status,
            MAX(CASE WHEN db.role = 'dosbing_2' THEN db.status_persetujuan END) AS dosen2_status,

            MAX(CASE WHEN db.role = 'dosbing_1' THEN db.persetujuan_sempro END) AS dosen1_file,
            MAX(CASE WHEN db.role = 'dosbing_2' THEN db.persetujuan_sempro END) AS dosen2_file

        FROM dosbing_ta db
        LEFT JOIN dosen d ON d.id = db.dosen_id
        WHERE db.pengajuan_id = ?
    ");
    $stmt->execute([$ta['id']]);
    $dosen = $stmt->fetch(PDO::FETCH_ASSOC);
}
function badgeStatus($status) {
    return match ($status) {
        'disetujui' => '<span class="status-ok">DISETUJUI</span>',
        'pending'  => '<span class="status-wait">MENUNGGU</span>',
        default     => '<span class="status-wait">MENUNGGU</span>',
    };
}


/* ===============================
   VALIDASI ALUR TA
================================ */
if (!$ta) {

    $pesan_error = "Anda belum mengajukan Tugas Akhir.\nSilakan ajukan Tugas Akhir terlebih dahulu.";
    // Set default values when no TA submission exists
    $ta = [
        'id' => '-',
        'status' => '-',
        'judul_ta' => '-'
    ];

} elseif ($ta['status'] !== 'disetujui') {

    $pesan_error = "Pengajuan Tugas Akhir belum selesai.\nStatus saat ini: {$ta['status']}";

} else {

    /* ===============================
       CEK PERSETUJUAN DOSEN PEMBIMBING
    ================================ */
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM dosbing_ta
        WHERE pengajuan_id = ?
        AND status_persetujuan = 'disetujui'
    ");
    $stmt->execute([$ta['id']]);

    if ($stmt->fetchColumn() < 2) {

        $pesan_error = "Pengajuan Seminar Proposal belum dapat dilakukan. Anda memerlukan persetujuan dari kedua Dosen Pembimbing untuk melanjutkan.";

    } else {

        /* ===============================
           CEK SUDAH AJUKAN SEMPRO
        ================================ */

        $cek = $pdo->prepare("
            SELECT id, id_sempro, created_at
            FROM pengajuan_sempro
            WHERE pengajuan_ta_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $cek->execute([$ta['id']]);

        if ($cek->rowCount() > 0) {

            $pengajuan = $cek->fetch(PDO::FETCH_ASSOC);
            $has_sempro = true;
            $pesan_error = "Anda sudah pernah mengajukan Seminar Proposal.";
        } else {
            $boleh_upload = true;
        }
        $can_proceed = $boleh_upload;

        }
}

// Enable button based on submission status
$can_proceed = $boleh_upload;

// Ambil template contoh (dipakai sebagai 'Contoh Template' untuk mahasiswa)
$stmt_tpl = $pdo->prepare("SELECT * FROM template WHERE is_visible = 1");
$stmt_tpl->execute();
$templates_all = $stmt_tpl->fetchAll(PDO::FETCH_ASSOC);

function find_template_by_keywords($templates, $keywords){
    foreach ($templates as $t){
        foreach ($keywords as $kw){
            if (stripos($t['nama'], $kw) !== false) return $t;
        }
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Pengajuan Seminar Proposal</title>

<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
}
body{
    background:#FFF1E5 !important;
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
    color:#ff8c42;
}
.divider {
    border: none;
    height: 0.5px;
    width: 100% !important;
    background: #FF983D;
    display: block;
    margin: 12px 0;
}
/* INFO BOX */
.info-box {
    background: #5F5F5F;
    color: #ffffff;
    border: 1px solid rgba(255, 152, 61, 0.35);
    border-radius: 14px;
    padding: 16px 18px;
    margin-top: 20px;
    font-size: 14px;
}
.info-box strong {
    display:flex;
    align-items:center;
    gap:6px;
    margin-bottom:8px;
    justify-content: center;
    font-size: 18px;
}

.info-box li {
    margin-bottom:4px;
    color:#ffffff;
}

.info-box p{
    border: solid 1px #ff8c42;
    padding:10px;
    border-radius:8px;
    background:#ffffff;
    color:#555;
}

.divider {
    border: none;
    height: 0.5px;
    width: 100% !important;
    background: #FF983D;
    display: block;
    margin: 12px 0;
}

.pretty-ol {
    list-style: none;        /* hilangkan nomor default */
    padding-left: 0;
    margin: 0;
    counter-reset: step;     /* reset counter */
}

.pretty-ol li {
    counter-increment: step;
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 10px;
    color: #fff;             /* teks kalau background gelap */
    font-size: 14px;
    line-height: 1.5;
}

/* BOX angka */
.pretty-ol li::before {
    content: counter(step);
    min-width: 28px;
    height: 28px;
    border: 1px solid #fff;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}
.ta-info-header {
    display:flex;
    align-items:center;
    gap:10px;
    font-weight:700;
    color:#ff8c42;
    margin-bottom:12px;
}

.ta-info-header .material-symbols-rounded {
    font-size:20px;
}

.ta-badge {
    margin-left:auto;
    padding:6px 14px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    border:1px solid;
}

/* STATUS COLORS */
.badge-green {
    color:#16A34A;
    background:rgba(22,163,74,.15);
    border-color:rgba(22,163,74,.4);
}
.badge-blue {
    color:#2563EB;
    background:rgba(37,99,235,.15);
    border-color:rgba(37,99,235,.4);
}
.badge-orange {
    color:#FF983D;
    background:rgba(255,152,61,.18);
    border-color:rgba(255,152,61,.4);
}
.badge-red {
    color:#DC2626;
    background:rgba(220,38,38,.15);
    border-color:rgba(220,38,38,.4);
}
.badge-gray {
    color:#6B7280;
    background:#f3f4f6;
    border-color:#d1d5db;
}
.ta-judul {
    font-size:14px;
    font-weight:700;
    margin-bottom:6px;
    color:#ff8c42;
}
.ta-judul-box {
    background:#f9fafb;
    border-radius:12px;
    padding:14px 16px;
    font-size:14px;
    font-weight:600;
    color:#444;
    line-height:1.5;
}

.material-symbols-rounded {
    font-size: 20px;
    vertical-align: middle;
}
.upload-list {
    display:flex;
    flex-direction:column;
    gap:14px;
    margin-top:20px;
}

.upload-item {
    display:flex;
    justify-content:space-between;
    align-items:center;
    border:1px dashed rgba(255,152,61,.5);
    padding:14px 16px;
    border-radius:14px;
    background:#fffaf6;
}

.upload-left {
    display:flex;
    align-items:center;
    gap:12px;
}

.upload-icon {
    width:38px;
    height:38px;
    border-radius:10px;
    background:rgba(255,152,61,.15);
    color:#FF983D;
    display:flex;
    align-items:center;
    justify-content:center;
}

.upload-status {
    font-size:12px;
    color:#999;
    font-style:italic;
}

.btn-upload {
    background: var(--gradient);
    color:#fff;
    padding:8px 18px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    white-space:nowrap;
}
/* ACTION */
.ta-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 18px;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 152, 61, 0.25);
}

.ta-btn {
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:10px 16px;
    border-radius:10px;
    font-size:13px;
    font-weight:600;
    text-decoration:none;
    letter-spacing: .3px;
    background: linear-gradient(135deg, #FF74C7, #FF983D);
    color: #ffffff;
    border:1px solid #FF983D;
    cursor: pointer;
    width: fit-content;
    margin: auto;
}
.ta-btn-primary:hover {
    opacity: .9;
}

/* small variant used for inline sample template links */
.template-sample{font-size:12px;padding:4px 6px;border-radius:8px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;background:#e5e7eb;color:#374151}
.template-sample .material-symbols-rounded{font-size:16px;line-height:1}
@media (max-width:768px){.template-sample{font-size:12px;padding:4px 6px;display:inline-flex;white-space:nowrap}}
/* message BOX */
.message-box {
    background: #FFDFE0;
    color: #FF3A3D;
    border: 1px solid rgba(255, 152, 61, 0.35);
    border-radius: 14px;
    padding: 16px 18px;
    margin-top: 20px;
    font-size: 14px;
}
.message-box strong {
    display:flex;
    align-items:center;
    gap:6px;
    font-size: 13px;
}

/* ==============================
   ERROR CARD (SUDAH PERNAH AJUAN)
============================== */
.ta-error-card{
    background: #FFDFE0;
    border-radius: 18px;
    padding: 15px 15px;
    border: #FF3A3D 1px solid;
    box-shadow: 0 3px 15px rgba(0,0,0,.10);
}

.ta-error-head{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom: auto;
}

.ta-error-icon{
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display:flex;
    align-items:center;
    justify-content:center;
    background: #fff;
    border: 1px solid #FF3A3D;
    flex-shrink: 0;
}

.ta-error-icon span{
    color:#FF3A3D;
    font-size: 22px;
}

.ta-error-title{
    margin:0;
    font-size: 16px;
    font-weight: 800;
    color:#FF3A3D;
}

.ta-error-desc{
    margin: 2px 0 0;
    font-size: 14px;
    color:#6b7280;
}

/* isi message */
.ta-error-body{
    background: #f5f5f5;
    border: 1px solid #9f9f9f;
    border-radius: 14px;
    padding: 14px 16px;
    color: #555;
    font-size: 14px;
    margin-top: 8px;
}

.ta-error-label{
    margin-top: 20px;
    font-size: 14px;
    font-weight: 700;
    color: #ff8c42;
}

.ta-textbox { 
    background:#f5f5f5; 
    color:#555;
    font-size:14px;
    font-weight:700; 
    padding:8px; 
    border-radius:6px;
    margin-top:8px;
    margin-bottom:15px; 
    width: fit-content; }

/* ===============================
   DOSEN VALIDATION
================================ */
.dosen-section {
    margin-top:20px;
}

.dosen-item {
    border:1px dashed #E5E7EB;
    border-radius:14px;
    padding:14px 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:12px;
}

.dosen-left {
    display:flex;
    gap:12px;
    align-items:center;
}

.dosen-avatar {
    width:38px;
    height:38px;
    border-radius:50%;
    background:#E5E7EB;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#6B7280;
}

.dosen-name {
    font-weight:700;
    font-size:13px;
    color:#374151;
}

.dosen-role {
    font-size:11px;
    color:#9CA3AF;
}

/* STATUS BADGE */
.status-wait {
    padding:6px 14px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    color:#FB923C;
    border:1px solid #FDBA74;
    background:#FFF7ED;
}

.status-ok {
    padding:6px 14px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    color:#22C55E;
    border:1px solid #86EFAC;
    background:#ECFDF5;
}

/* INFO FOOTER */
.info-footer {
    margin-top:18px;
    background:#EFF6FF;
    border:1px solid #BFDBFE;
    border-radius:16px;
    padding:16px;
    color:#2563EB;
    font-size:13px;
    display:flex;
    gap:10px;
    align-items:center;
}



.alert {
    background:#fff7ed;
    color:#9a3412;
    padding:14px;
    border-radius:12px;
    margin-bottom:16px;
    white-space:pre-line;
}
label {
    display:block;
    margin-top:14px;
    font-weight:600;
}
button {
    margin-top:20px;
    width:100%;
    padding:12px;
    background:linear-gradient(135deg,#FF74C7,#FF983D);
    color:#fff;
    border:none;
    border-radius:14px;
    font-weight:600;
    cursor:pointer;
}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="topbar">
        <h1>Pengajuan Seminar Proposal</h1>

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

    <?php if ($boleh_upload): ?>
    <!-- INFO UMUM (DI LUAR CARD) -->
    <div class="info-box" style="margin-top:20px;">
        <strong>
            <span class="material-symbols-rounded">info</span>
            Informasi Penting
        </strong>
        <hr class="divider">
        <ol class="pretty-ol">
            <li>Maksimal ukuran dokumen 2 MB</li>
            <li>Upload wajib dengan format PDF</li>
            <li>Format penamaan dokumen: NIM_Nama File_Nama</li>
            <li>Dokumen terlihat jelas (HD)</li>
            <li>Pastikan dokumen yang diunggah sudah benar</li>
        </ol>
    </div>
    <?php endif; ?>

    <div class="card" style="margin-top:20px;">
        <h2>Pengajuan Tugas Akhir</h2>
        <hr class="divider">

        <?php if ($pesan_error): ?>
        <div class="ta-error-card">
            <div class="ta-error-head">
                <div class="ta-error-icon">
                    <span class="material-symbols-rounded">error_outline</span>
                </div>
                <div>
                    <h3 class="ta-error-title">INFORMASI SISTEM!</h3>
                    <p class="ta-error-desc"><?= nl2br(htmlspecialchars($pesan_error)) ?></p>    
                </div>
            </div>
        </div>
        <div class="ta-info-header" style="margin-top:20px;">
                    <span class="material-symbols-rounded">description</span>
                    <span>INFORMASI TUGAS AKHIR</span>

                    <?php
                    $statusClass = match ($ta['status']) {
                        'disetujui' => 'badge-green',
                        'diajukan'  => 'badge-blue',
                        'revisi'    => 'badge-orange',
                        'ditolak'   => 'badge-red',
                        default     => 'badge-gray',
                    };
                    ?>
                    <span class="ta-badge <?= $statusClass ?>">
                        <?= strtoupper($ta['status']) ?>
                    </span>
                </div>
                <div>
                    <div class="ta-judul">Judul Tugas Akhir</div>
                    <div class="ta-judul-box">
                        <?= htmlspecialchars($ta['judul_ta']) ?>
                    </div>
                    <hr class="divider">
                </div>
                <div class="dosen-section">
                <div class="ta-info-header">
                    <span class="material-symbols-rounded">assignment_turned_in</span>
                    VALIDASI DOSEN PEMBIMBING
                </div>

                    <?php if ($dosen): ?>
                    <!-- DOSEN 1 -->
                    <div class="dosen-item">
                        <div class="dosen-left">
                            <div class="dosen-avatar">
                                <span class="material-symbols-rounded">person</span>
                            </div>
                            <div>
                                <div class="dosen-role">DOSEN PEMBIMBING 1</div>
                                <?= htmlspecialchars($dosen['dosen1_nama'] ?? '-') ?>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;align-items:center;">
                        <?= badgeStatus($dosen['dosen1_status'] ?? 'menunggu') ?>
                    </div>
                    </div>

                    <!-- DOSEN 2 -->
                    <div class="dosen-item">
                        <div class="dosen-left">
                            <div class="dosen-avatar">
                                <span class="material-symbols-rounded">person</span>
                            </div>
                            <div>
                                <div class="dosen-role">DOSEN PEMBIMBING 2</div>
                                <?= htmlspecialchars($dosen['dosen2_nama'] ?? '-') ?>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;align-items:center;">
                        <?= badgeStatus($dosen['dosen2_status'] ?? 'menunggu') ?>
                    </div>
                    </div>
                    <hr class="divider">
                    <!--tampilkan id sempro dan tanggal pengajuan -->
                        <p class="ta-error-label">ID Seminar Proposal</p>
                        <div class="ta-textbox">
                            <?= htmlspecialchars($pengajuan['id_sempro']) ?>
                        </div>
                        <p class="ta-error-label">Tanggal Pengajuan</p>
                        <div class="ta-textbox">
                            <?= $pengajuan['created_at']
                                ? date('d F Y, H:i:s', strtotime($pengajuan['created_at']))
                                : '-' ?>
                        </div>

                        <div class="ta-actions">
                        <?php if ($has_sempro): ?>
                            <a href="<?= base_url('mahasiswa/sempro/status.php') ?>" 
                            class="ta-btn ta-btn-primary">
                                <span class="material-symbols-rounded">history</span>
                                Lihat Riwayat Ajuan
                            </a>
                        <?php else: ?>
                            <button class="ta-btn" disabled style="opacity:.5;cursor:not-allowed;">
                                <span class="material-symbols-rounded">history</span>
                                Lihat Riwayat Ajuan
                            </button>
                        <?php endif; ?>
                        </div>

                    <?php endif; ?>
                </div>

            <div class="info-footer">
                <span class="material-symbols-rounded">info</span>
                <div>
                    Silakan lakukan bimbingan draf proposal Anda. Tombol pendaftaran akan terbuka otomatis setelah kedua dosen melakukan validasi di sistem.
                </div>
            </div>

            </div>
        <?php endif; ?>


        <?php if ($boleh_upload): ?>
            <div>
                <div class="ta-info-header">
                    <span class="material-symbols-rounded">description</span>
                    <span>INFORMASI TUGAS AKHIR</span>

                    <?php
                    $statusClass = match ($ta['status']) {
                        'disetujui' => 'badge-green',
                        'diajukan'  => 'badge-blue',
                        'revisi'    => 'badge-orange',
                        'ditolak'   => 'badge-red',
                        default     => 'badge-gray',
                    };
                    ?>
                    <span class="ta-badge <?= $statusClass ?>">
                        <?= strtoupper($ta['status']) ?>
                    </span>
                </div>
                <div>
                    <div class="ta-judul">Judul Tugas Akhir</div>
                    <div class="ta-judul-box">
                        <?= htmlspecialchars($ta['judul_ta']) ?>
                    </div>
                    <hr class="divider">
                </div>
            </div>

            <div class="ta-info-header">
                <span class="material-symbols-rounded">upload</span>
                Unggah Dokumen Pendaftaran Seminar Proposal
            </div>

            <form action="simpan.php" method="POST" enctype="multipart/form-data">

                <!-- HANYA KIRIM ID TA -->
                <input type="hidden" name="pengajuan_ta_id" value="<?= $ta['id'] ?>">

                <div class="upload-list">
                    <!-- ITEM 1 -->
                    <div class="upload-item">
                        <div class="upload-left">
                            <div class="upload-icon">
                                <span class="material-symbols-rounded">description</span>
                            </div>
                            <?php $tpl = find_template_by_keywords($templates_all, ['pendaftaran seminar proposal']); ?>
                            <div style="display:flex;flex-direction:column;gap:4px;min-width:0;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <strong style="flex:0 0 auto">Form Pendaftaran Seminar Proposal</strong>
                                    <?php if ($tpl && $tpl['file']): ?>
                                        <a class="template-sample"
                                        href="<?= base_url('mahasiswa/pengajuan/download_template.php?file=' . urlencode($tpl['file'])) ?>">
                                            <span class="material-symbols-rounded">download</span>
                                            Contoh Template
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <small class="upload-status">Belum ada file</small>
                            </div>
                        </div>

                        <label class="btn-upload">
                            Pilih File
                            <input type="file" name="file_pendaftaran" accept="application/pdf" hidden required>
                        </label>
                    </div>

                    <!-- ITEM 2 -->
                    <div class="upload-item">
                        <div class="upload-left">
                            <div class="upload-icon">
                                <span class="material-symbols-rounded">description</span>
                            </div>
                            <?php $tpl = find_template_by_keywords($templates_all, ['persetujuan proposal tugas akhir']); ?>
                            <div style="display:flex;flex-direction:column;gap:4px;min-width:0;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <strong style="flex:0 0 auto">Lembar Persetujuan Proposal TA</strong>
                                    <?php if ($tpl && $tpl['file']): ?>
                                        <a class="template-sample"
                                        href="<?= base_url('mahasiswa/pengajuan/download_template.php?file=' . urlencode($tpl['file'])) ?>">
                                            <span class="material-symbols-rounded">download</span>
                                            Contoh Template
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <small class="upload-status">Belum ada file</small>
                            </div>
                        </div>

                        <label class="btn-upload">
                            Pilih File
                            <input type="file" name="file_persetujuan" accept="application/pdf" hidden required>
                        </label>
                    </div>

                    <!-- ITEM 3 -->
                    <div class="upload-item">
                        <div class="upload-left">
                            <div class="upload-icon">
                                <span class="material-symbols-rounded">description</span>
                            </div>
                            <?php $tpl = find_template_by_keywords($templates_all, ['konsultasi','buku konsultasi','bimbingan','logbook']); ?>
                            <div style="display:flex;flex-direction:column;gap:4px;min-width:0;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <strong style="flex:0 0 auto">Buku Konsultasi Tugas Akhir</strong>
                                    <?php if ($tpl && $tpl['file']): ?>
                                        <a class="template-sample"
                                        href="<?= base_url('mahasiswa/pengajuan/download_template.php?file=' . urlencode($tpl['file'])) ?>">
                                            <span class="material-symbols-rounded">download</span>
                                            Contoh Template
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <small class="upload-status">Belum ada file</small>
                            </div>
                        </div>

                        <label class="btn-upload">
                            Pilih File
                            <input type="file" name="file_konsultasi" accept="application/pdf" hidden required>
                        </label>
                    </div>
                </div>
            <div class="message-box">
                <strong>
                    <span class="material-symbols-rounded">info</span> 
                    Pastikan semua dokumen telah ditandatangani dan distempel jika diperlukan. Kesalahan dokumen dapat memperlambat proses pendaftaran.
                </strong>
            </div>
            <div class="ta-actions">
                <button type="submit" class="ta-btn ta-btn-primary">
                    <span class="material-symbols-rounded">send</span>
                    Ajukan Seminar Proposal
                </button>
            </div>
            </form>

        <?php endif; ?>



    </div>
</div>
<script>
document.querySelectorAll('.upload-item input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        if (!this.files.length) return;

        const item = this.closest('.upload-item');
        const status = item.querySelector('.upload-status');

        status.textContent = 'File dipilih: ' + this.files[0].name;
        status.style.color = '#16A34A';
        status.style.fontStyle = 'normal';
        status.style.fontWeight = '600';
    });
});
</script>
<script>
const btn = document.getElementById('btnLanjut');
const form = document.getElementById('formSempro');

if (btn && !btn.disabled) {
    btn.addEventListener('click', function () {
        form.style.display = 'block';
        this.style.display = 'none';
        form.scrollIntoView({ behavior: 'smooth' });
    });
}
</script>

</body>
</html>