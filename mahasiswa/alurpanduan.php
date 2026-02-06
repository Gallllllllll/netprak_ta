<?php
session_start();
require "../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/ta_netprak/config/base_url.php';

// cek role mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . base_url('login.php'));
    exit;
}
$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Alur dan Panduan</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
    --bg: #FFF1E5;
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    margin: 0;
    color: #1F2937;
}

.main-content {
    margin-left: 280px;
    padding: 40px;
    min-height: 100vh;
    background: var(--bg);
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
    font-weight: 700;
    margin: 0;
}

/* PROFILE */
.mhs-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.mhs-text {
    text-align: right;
}

.mhs-text span {
    font-size: 12px;
    color: #6B7280;
    display: block;
}

.mhs-text b {
    color: var(--orange);
    font-size: 14px;
    display: block;
}

.avatar {
    width: 48px;
    height: 48px;
    background: var(--orange);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(255, 152, 61, 0.3);
}

/* CARD */
.card {
    background: #fff;
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 10px 30px rgba(255, 140, 80, 0.12);
    margin-bottom: 24px;
}

.card h2 {
    text-align: center;
    color: var(--orange);
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 20px;
}

.divider {
    border: none;
    height: 2px;
    width: 100%;
    background: linear-gradient(90deg, transparent, var(--orange), transparent);
    margin: 20px 0;
}

/* IMAGE CONTAINER */
.image-container {
    text-align: center;
    margin: 24px 0;
}

.card img {
    max-width: 100%;
    height: auto;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.card img:hover {
    transform: scale(1.02);
}

/* DOWNLOAD SECTION */
.download-guide {
    text-align: center;
    padding: 24px;
}

.download-guide p {
    margin: 0 0 20px;
    font-size: 14px;
    color: #6B7280;
}

.btn-download {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 28px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    background: white;
    color: var(--orange);
    border: 2px solid var(--orange);
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(255, 152, 61, 0.2);
}

.btn-download:hover {
    background: var(--gradient);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 152, 61, 0.4);
}

.btn-download .material-symbols-rounded {
    font-size: 22px;
}

/* INFO BOX */
.info-box {
    background: linear-gradient(135deg, #FFF5F5 0%, #FFEDE0 100%);
    border-left: 4px solid var(--orange);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(255, 152, 61, 0.15);
}

.info-box-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    color: var(--orange);
    font-weight: 700;
    font-size: 16px;
}

.info-box-header .material-symbols-rounded {
    font-size: 24px;
}

.info-box-content {
    background: white;
    border: 1px solid rgba(255, 152, 61, 0.3);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
}

.info-box-content p {
    margin: 0;
    color: #374151;
    line-height: 1.6;
}

.info-box ol {
    margin: 0;
    padding-left: 24px;
}

.info-box li {
    margin-bottom: 8px;
    color: #4B5563;
    line-height: 1.6;
}

.info-box li:last-child {
    margin-bottom: 0;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 20px;
    }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .mhs-info {
        width: 100%;
        justify-content: flex-end;
    }

    .card {
        padding: 20px;
    }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/ta_netprak/mahasiswa/sidebar.php'; ?>

<!-- CONTENT -->
<div class="main-content">
    
    <!-- TOPBAR -->
    <div class="topbar">
        <h1>Alur dan Panduan</h1>
        <div class="mhs-info">
            <div class="mhs-text">
                <span>Selamat Datang,</span>
                <b><?= htmlspecialchars($username) ?></b>
            </div>
            <div class="avatar">
                <span class="material-symbols-rounded" style="color:#fff">person</span>
            </div>
        </div>
    </div>

    <!-- CARD DIAGRAM ALUR -->
    <div class="card">
        <h2>Diagram Alur Pengajuan</h2>
        <hr class="divider">
        <div class="image-container">
            <img src="<?= base_url('assets/img/alurpanduan.png') ?>" alt="Alur Pengajuan">
        </div>
    </div>

    <!-- CARD DOWNLOAD PANDUAN -->
    <div class="card">
        <h2>Dokumen Panduan Lengkap</h2>
        <hr class="divider">
        <div class="download-guide">
            <p>Unduh panduan lengkap penggunaan sistem untuk membantu proses pengajuan Anda</p>
            <a href="https://drive.google.com/file/d/1YsnxFdWSpHMwoD6rBde10dKkX-AZjdn8/view" 
               class="btn-download" 
               target="_blank"
               rel="noopener noreferrer">
                <span class="material-symbols-rounded">download</span>
                Download Panduan Penggunaan Sistem
            </a>
        </div>
    </div>

    <!-- INFO BOX -->
    <div class="info-box">
        <div class="info-box-header">
            <span class="material-symbols-rounded">info</span> 
            Informasi Penting
        </div>
        <div class="info-box-content">
            <p>Pastikan Anda membaca panduan dengan seksama sebelum melakukan pengajuan.</p>
        </div>
        <ol>
            <li>Persiapkan semua dokumen yang diperlukan</li>
            <li>Lengkapi formulir dengan data yang valid</li>
            <li>Perhatikan jadwal dan tenggat waktu pengajuan</li>
            <li>Konsultasikan dengan dosen pembimbing</li>
        </ol>
    </div>

</div>

</body>
</html>