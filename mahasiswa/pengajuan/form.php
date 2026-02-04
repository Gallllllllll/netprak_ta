<?php
session_start();
require "../../config/connection.php";
require_once '../../config/base_url.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . base_url('login.php'));
    exit;
}
$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';
$mahasiswa_id = $_SESSION['user']['id'];

$pesan_error  = '';
$boleh_upload = false;

/* ================================
   CEK SUDAH PERNAH PENGAJUAN TA
================================ */
$cek = $pdo->prepare("SELECT id, judul_ta, id_pengajuan, created_at FROM pengajuan_ta WHERE mahasiswa_id = ? LIMIT 1");
$cek->execute([$mahasiswa_id]);
$pengajuan = $cek->fetch(PDO::FETCH_ASSOC);

// Ambil semua template yang terlihat (digunakan sebagai contoh dokumen yang bisa diunduh mahasiswa)
$stmt_tpl = $pdo->prepare("SELECT * FROM template WHERE is_visible = 1");
$stmt_tpl->execute();
$templates_all = $stmt_tpl->fetchAll(PDO::FETCH_ASSOC);

// Helper: cari template berdasarkan kata kunci (mis. 'formulir','transkrip')
function find_template_by_keywords($templates, $keywords){
    foreach ($templates as $t){
        foreach ($keywords as $kw){
            if (stripos($t['nama'], $kw) !== false) return $t;
        }
    }
    return null;
}

if ($pengajuan) {
    $pesan_error = "<b>" . htmlspecialchars($pengajuan['judul_ta']) . "</b>";
} else {
    $boleh_upload = true;
} 
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Form Pengajuan TA</title>
<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
}
/*body*/
body {
    font-family: 'Inter', sans-serif;
    background: #FFF1E5 !important;
    margin: 0;
}

.container {
    background: #FFF1E5 !important;
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
}

/* CARD */
.card {
    background:#fff;
    border-radius:18px;
    padding:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    overflow-x: hidden;
}

.form-card h2{
    text-align: center;
    color: #ff8c42;
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
   FORM UI (VERSI BARU - 2 KOLOM)
============================== */
form{
    padding: 0 15px;
}

.ta-form-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(255, 152, 61, 0.25);
    box-shadow: 0 8px 22px rgba(0,0,0,.10);
    padding: 22px;
    margin-top: 20px;
}

.ta-form-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid rgba(255, 152, 61, 0.25);
    margin-bottom: 18px;
}

.ta-form-title {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: #374151;
}

.ta-form-subtitle {
    margin: 4px 0 0;
    font-size: 13px;
    color: #6b7280;
}

.ta-badge {
    padding: 8px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    background: rgba(255, 152, 61, 0.14);
    color: #FF983D;
    border: 1px solid rgba(255, 152, 61, 0.35);
    white-space: nowrap;
}

/* FIELD COMMON */
.ta-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin: 15px 0;
}

/* make label act as a horizontal container so inline actions align right */
.ta-label {
    font-size: 14px;
    font-weight: 800;
    color: #000000;
    letter-spacing: .3px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
} 

.ta-input {
    width: auto;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    font-size: 14px;
    background: #fff;
    transition: .2s ease;
    font-style: normal;
}

.ta-input::placeholder{
    font-size: 13px;
    font-style: italic;
}

.ta-input:focus {
    outline: none;
    border-color: #FF983D;
    box-shadow: 0 0 0 3px rgba(255,152,61,.20);
}

/* UPLOAD GRID */
.ta-upload-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 10px;
}

.ta-upload-item {
    flex: 1 1 calc(50% - 8px);
    min-width: 240px;
}

/* UPLOAD BOX */
.ta-upload-box {
    position: relative;
    background: #fff7f1;
    border: 1px dashed rgba(255, 152, 61, 0.7);
    border-radius: 16px;
    padding: 16px;
    transition: .2s ease;
}

.ta-upload-box:hover {
    border-color: #FF74C7;
    background: rgba(255, 116, 199, 0.07);
}

.ta-upload-inner {
    display: flex;
    gap: 12px;
    align-items: center;
}

.ta-upload-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(255,116,199,.25), rgba(255,152,61,.22));
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid rgba(255, 152, 61, 0.35);
}

.ta-upload-icon span {
    color: #FF983D;
    font-size: 24px;
}

.ta-upload-text {
    flex: 1;
}

.ta-upload-text strong {
    display: block;
    font-size: 13px;
    color: #374151;
    margin-bottom: 2px;
}

.ta-upload-text small {
    font-size: 12px;
    color: #6b7280;
}

/* INPUT FILE HIDDEN FULL COVER */
.ta-upload-box input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

/* ===== STATUS FILE ===== */
.file-status {
    margin-top: 6px;
    font-size: 12px;
    color: #9ca3af;
}

.file-status.uploaded {
    color: #16a34a;
    font-weight: 700;
}

/* optional: kalau sudah upload, box jadi solid */
.ta-upload-box.is-uploaded {
    border-style: solid;
    border-color: rgba(22, 163, 74, 0.6);
    background: rgba(22, 163, 74, 0.06);
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
    width: fit-content !important;
}
.ta-btn-primary:hover {
    opacity: .9;
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

/* tombol */
.ta-error-actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top: 14px;
}
.ta-btn-secondary{
    background:#e5e7eb;
    color:#374151;
    border:none;
    padding:10px 16px;
    border-radius:12px;
    font-weight:700;
    cursor:pointer;
    text-decoration:none;
    font-size:13px;
}
.ta-btn-secondary:hover{
    opacity:.9;
}

/* small variant used for inline sample template links */
.template-sample{font-size:12px;padding:3px 5px;border-radius:5px;display:inline-flex;align-items:center;gap:6px;text-decoration:none}
@media (max-width:768px){.template-sample{display:inline-flex;flex-wrap:nowrap;margin-top:8px;white-space:nowrap;}}
.template-sample span{font-size:16px;}

/* TABLET */
@media (max-width: 1024px) {
    /* Adjust content area to sidebar collapsed width from sidebar styles */
    .main-content {
        margin-left: 70px;
        padding: 24px 20px;
    }

    .ta-form-head{
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .ta-upload-item {
        flex: 1 1 100%;
        min-width: 100%;
    }

    .ta-upload-box { padding: 12px; }
    .ta-upload-icon { width: 38px; height: 38px; }
    .ta-upload-icon span { font-size: 20px; }
    .ta-upload-text strong { font-size: 14px; }
    .ta-actions { justify-content: flex-end; gap: 8px; }
}

/* MOBILE */
@media (max-width: 768px) {
    .main-content {
        margin-left: 60px;
        padding: 20px 16px;
    }

    .ta-form-head{
        align-items:flex-start;
        gap:8px;
    }

    .ta-upload-item {
        flex: 1 1 100%;
        min-width: 100%;
    }

    /* Center icon + text inside upload box on mobile */
    .ta-upload-inner{
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 8px;
    }

    .ta-upload-box { padding: 12px; }
    .ta-upload-icon { width: 40px; height: 40px; }
    .ta-upload-icon span { font-size: 22px; }
    .ta-upload-text small { display:block; }

    /* Keep template sample small */
    .template-sample{ margin-top:8px; flex-wrap:nowrap; white-space:nowrap; align-self:center; }

    .ta-actions{
        flex-direction: column;
        gap: 12px;
    }

    .ta-btn{ width:100%; justify-content:center; padding:12px; font-size:15px }

    .ta-form-title{ font-size:16px }
    .info-box{ font-size:13px }
    .pretty-ol li{ font-size:13px }
}

/* EXTRA SMALL */
@media (max-width: 480px) {
    .ta-upload-icon { width:36px; height:36px }
    .ta-upload-icon span{ font-size:18px }
    .ta-input{ padding:10px; font-size:13px }
    .ta-upload-box{ padding:10px }
    .ta-error-icon{ width:42px; height:42px }
    .ta-btn{ font-size:14px; padding:10px }
}
</style>
</head>
<body>

<div class="container">
    <?php include '../../mahasiswa/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <h1>Pengajuan Tugas Akhir</h1>

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
                <li>Upload Transkrip Nilai dengan IPK ≥ 2.50, SKS minimal 100, nilai C tidak lebih dari 5 mata kuliah dan tidak ada nilai D atau E</li>
                <li>Upload Surat dengan format PDF</li>
                <li>Format penamaan dokumen: NIM_Nama File_Nama</li>
                <li>Maksimal ukuran dokumen 2 MB</li>
                <li>Pastikan dokumen yang diunggah sudah benar</li>
            </ol>
        </div>
        <?php endif; ?>


        <div class="card" style="margin-top:20px;">
        <?php if ($pesan_error): ?>
            <div class="form-card">
                <h2>Pengajuan Tugas Akhir</h2>
                <hr class="divider">

                <div class="ta-error-card">
                    <div class="ta-error-head">
                        <div class="ta-error-icon">
                            <span class="material-symbols-rounded">error_outline</span>
                        </div>
                        <div>
                            <h3 class="ta-error-title">INFORMASI SISTEM!</h3>
                            <p class="ta-error-desc">Anda sudah pernah mengajukan Tugas Akhir. Saat ini sistem membatasi pengajuan hanya satu kali per periode hingga pengajuan selesai diproses.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="ta-error-label">Judul Tugas Akhir</p>
                    <div class="ta-error-body">
                        <?= $pesan_error ?>
                    </div>
                    <p class="ta-error-label">ID Tugas Akhir</p>
                    <div class="ta-textbox">
                        <?= htmlspecialchars($pengajuan['id_pengajuan']) ?>
                    </div>
                    <p class="ta-error-label">Tanggal Pengajuan</p>
                    <div class="ta-textbox">
                        <?= date('d F Y, H:i:s', strtotime($pengajuan['created_at'])) ?>
                    </div>
                    <div class="ta-actions">
                        <a href="<?= base_url('mahasiswa/pengajuan/status.php') ?>" 
                        class="ta-btn ta-btn-primary">
                            <span class="material-symbols-rounded">history</span>
                            Lihat Riwayat Ajuan
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <?php elseif ($boleh_upload): ?>
        <div class="form-card">
            <h2>Pengajuan Tugas Akhir</h2>
            <hr class="divider">
        </div>

        <form action="simpan.php" method="POST" enctype="multipart/form-data">

            <div class="ta-field">
                <label class="ta-label" for="judul">Judul Tugas Akhir</label>
                <input
                    type="text"
                    id="judul"
                    name="judul"
                    class="ta-input"
                    placeholder="Masukkan judul tugas akhir Anda disini ... (Gunakan HURUF KAPITAL)"
                    required
                >
            </div>

            <div class="ta-upload-grid">

                <div class="ta-upload-item">
                    <div class="ta-field">
                    <?php $tpl = find_template_by_keywords($templates_all, ['formulir','pendaftaran','persetujuan']); ?>
                        <label class="ta-label">Formulir Pendaftaran & Persetujuan Tema
                            <?php if ($tpl && $tpl['file']): ?>
                                <a class="ta-btn ta-btn-secondary template-sample"
                                href="<?= base_url('mahasiswa/pengajuan/download_template.php?file=' . urlencode($tpl['file'])) ?>">
                                    <span class="material-symbols-rounded">download</span>
                                    Contoh Template
                                </a>
                            <?php endif; ?>
                        </label>
                        <label class="ta-upload-box">
                            <div class="ta-upload-inner">
                                <div class="ta-upload-icon">
                                    <span class="material-symbols-rounded">upload</span>
                                </div>
                                <div class="ta-upload-text">
                                    <strong>Klik untuk pilih file</strong>
                                    <small>File yang boleh diunggah format <b>.pdf</b> dan maksimal ukuran 2MB</small>
                                    <div class="file-status" id="status-formulir">Tidak ada file yang dipilih</div>
                                </div>
                            </div>
                            <input type="file" id="file-formulir" name="formulir" required accept=".pdf">
                        </label>
                    </div>
                </div>

                <div class="ta-upload-item">
                    <div class="ta-field">
                    <?php $tpl = find_template_by_keywords($templates_all, ['pembayaran','bukti pembayaran','transfer']); ?>
                        <label class="ta-label">Bukti Pembayaran
                            <?php if ($tpl && $tpl['file']): ?>
                            <a class="ta-btn ta-btn-secondary template-sample"
                            href="<?= base_url('mahasiswa/pengajuan/download_template.php?file=' . urlencode($tpl['file'])) ?>">
                                <span class="material-symbols-rounded">download</span>
                                Contoh Template
                            </a>
                            <?php endif; ?>
                        </label>
                        <label class="ta-upload-box">
                            <div class="ta-upload-inner">
                                <div class="ta-upload-icon">
                                    <span class="material-symbols-rounded">upload</span>
                                </div>
                                <div class="ta-upload-text">
                                    <strong>Klik untuk pilih file</strong>
                                    <small>File yang boleh diunggah format <b>.pdf</b> dan maksimal ukuran 2MB</small>
                                    <div class="file-status" id="status-bayar">Tidak ada file yang dipilih</div>
                                </div>
                            </div>
                            <input type="file" id="file-bayar" name="bukti_pembayaran" required accept=".pdf">
                        </label>
                    </div>
                </div>

                <div class="ta-upload-item">
                    <div class="ta-field">
                    <?php $tpl = find_template_by_keywords($templates_all, ['transkrip']); ?>
                        <label class="ta-label">Transkrip Nilai
                            <?php if ($tpl && $tpl['file']): ?>
                            <a class="ta-btn ta-btn-secondary template-sample"
                            href="<?= base_url('mahasiswa/pengajuan/download_template.php?file=' . urlencode($tpl['file'])) ?>">
                                <span class="material-symbols-rounded">download</span>
                                Contoh Template
                            </a>
                            <?php endif; ?>
                        </label>
                        <label class="ta-upload-box">
                            <div class="ta-upload-inner">
                                <div class="ta-upload-icon">
                                    <span class="material-symbols-rounded">upload</span>
                                </div>
                                <div class="ta-upload-text">
                                    <strong>Klik untuk pilih file</strong>
                                    <small>File yang boleh diunggah format <b>.pdf</b> dan maksimal ukuran 2MB</small>
                                    <div class="file-status" id="status-transkrip">Tidak ada file yang dipilih</div>
                                </div>
                            </div>
                            <input type="file" id="file-transkrip" name="transkrip" required accept=".pdf">
                        </label>
                    </div>
                </div>

                <div class="ta-upload-item">
                    <div class="ta-field">
                    <?php $tpl = find_template_by_keywords($templates_all, ['magang','pi','praktik','kelulusan']); ?>
                        <label class="ta-label">Bukti Kelulusan Mata Kuliah Magang / PI
                            <?php if ($tpl && $tpl['file']): ?>
                            <a class="ta-btn ta-btn-secondary template-sample"
                            href="<?= base_url('mahasiswa/pengajuan/download_template.php?file=' . urlencode($tpl['file'])) ?>">
                                <span class="material-symbols-rounded">download</span>
                                Contoh Template
                            </a>
                            <?php endif; ?>
                        </label>
                        <label class="ta-upload-box">
                            <div class="ta-upload-inner">
                                <div class="ta-upload-icon">
                                    <span class="material-symbols-rounded">upload</span>
                                </div>
                                <div class="ta-upload-text">
                                    <strong>Klik untuk pilih file</strong>
                                    <small>File yang boleh diunggah format <b>.pdf</b> dan maksimal ukuran 2MB</small>
                                    <div class="file-status" id="status-magang">Tidak ada file yang dipilih</div>
                                </div>
                            </div>
                            <input type="file" id="file-magang" name="magang" required accept=".pdf">
                        </label>
                    </div>
                </div>

            </div>
            <div class="message-box">
                <strong>
                    <span class="material-symbols-rounded">info</span> 
                    Kesalahan data atau dokumen yang diupload dapat menyebabkan penolakan pendaftaran. Pastikan semua berkas adalah dokumen asli yang telah discan.
                </strong>
            </div>
            <div class="ta-actions">
                <button type="submit" id="btn-submit-ta" class="ta-btn ta-btn-primary">
                    <span class="material-symbols-rounded">send</span>
                    Kirim Pengajuan Tugas Akhir
                </button>
            </div>

        </form>
        <?php endif; ?>
    </div>
</div>
<script>
function setFileStatus(inputId, statusId) {
    const input = document.getElementById(inputId);
    const status = document.getElementById(statusId);

    if (!input || !status) return;

    input.addEventListener("change", function () {
        const wrapper = input.closest(".ta-upload-box");

        if (this.files && this.files.length > 0) {
            const fileName = this.files[0].name;

            status.textContent = "File dipilih: " + fileName;
            status.classList.add("uploaded");

            if(wrapper) wrapper.classList.add("is-uploaded");
        } else {
            status.textContent = "Tidak ada file yang dipilih";
            status.classList.remove("uploaded");

            if(wrapper) wrapper.classList.remove("is-uploaded");
        }
    });
}

setFileStatus("file-formulir", "status-formulir");
setFileStatus("file-bayar", "status-bayar");
setFileStatus("file-transkrip", "status-transkrip");
setFileStatus("file-magang", "status-magang");
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('btn-submit-ta').addEventListener('click', function (e) {
    e.preventDefault(); // tahan submit dulu

    const requiredFiles = [
        { id: 'file-formulir', label: 'Formulir Pendaftaran & Persetujuan Tema' },
        { id: 'file-bayar', label: 'Bukti Pembayaran' },
        { id: 'file-transkrip', label: 'Transkrip Nilai' },
        { id: 'file-magang', label: 'Bukti Kelulusan Magang / PI' }
    ];

    let missing = [];

    requiredFiles.forEach(f => {
        const input = document.getElementById(f.id);
        if (!input || input.files.length === 0) {
            missing.push(f.label);
        }
    });

    if (missing.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Dokumen Belum Lengkap',
            html: `
                <p style="margin-bottom:8px;">Silakan lengkapi dokumen berikut:</p>
                <ul style="text-align:left;">
                    ${missing.map(m => `<li>${m}</li>`).join('')}
                </ul>
            `,
            confirmButtonText: 'Baik, Saya Lengkapi',
            confirmButtonColor: '#FF983D',
            background: '#FFF1E5'
        });
        return;
    }

    // Konfirmasi final
    Swal.fire({
        icon: 'question',
        title: 'Konfirmasi Pengajuan Tugas Akhir',
        html: `
            <p style="margin-bottom:10px;">
                Apakah Anda <b>yakin</b> seluruh data dan dokumen yang diunggah sudah benar?
            </p>
            <small style="color:#6b7280;">
                Kesalahan dokumen dapat menyebabkan pengajuan ditolak dan tidak dapat diubah.
            </small>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim Pengajuan',
        cancelButtonText: 'Periksa Kembali',
        confirmButtonColor: '#FF74C7',
        cancelButtonColor: '#e5e7eb',
        background: '#FFF1E5',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // submit form manual
            e.target.closest('form').submit();
        }
    });
});
</script>

</body>
</html>
