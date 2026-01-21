<?php
session_start();
require "../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

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
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Alur dan Panduan</title>

<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
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
.card{
    background:#fff;
    border-radius:18px;
    padding:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    overflow-x: hidden;
}

.card h2{
    text-align:center;
    color:#ff8c42;
}

.card img{
    display:block;
    margin: 15px auto;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    max-width: 90%;
}

.divider {
    border: none;
    height: 0.5px;
    width: 100% !important;
    background: #FF983D;
    display: block;
    margin: 12px 0;
}

.download-guide {
    display:flex;
    flex-direction: column;
    justify-content:space-between;
    align-items: center;
    flex-wrap:wrap;
    padding: 0;
}
.download-guide p {
    margin-top:10px;
    margin-bottom: 15px;
    font-size:13px;
    color:#555;
}

.btn-download {
    margin-top:15px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    padding:10px 16px;
    border-radius:10px;
    font-size:13px;
    font-weight:600;
    text-decoration:none;
    background:#FF983D26;
    color:#FF983D;
    border:1px solid #FF983D;
}
.btn-download:hover {
    background: var(--gradient);
    color:#fff;
}

/* INFO BOX */
.info-box {
    background: #FFDFE0;
    color: #FF3A3D;
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
}
.info-box ul {
    margin:0;
    padding-left:20px;
}
.info-box li {
    margin-bottom:4px;
    color:#555;
}

.info-box p{
    border: solid 1px #ff8c42;
    padding:10px;
    border-radius:8px;
    background:#ffffff;
    color:#555;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/coba/mahasiswa/sidebar.php'; ?>

<!-- CONTENT -->
<div class="main-content">
    <div class="topbar">
        <h1>Alur dan Panduan</h1>

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
        <h2>Diagram Alur Pengajuan</h2>
        <hr class="divider">
        <img src="<?= base_url('assets/img/alurpanduan.png') ?>" alt="Alur Pengajuan">
    </div>

    <div class="card" style="margin-top:20px;">
        <h2>Dokumen Panduan Lengkap</h2>
        <hr class="divider">
        <div class="download-guide">
        <a class="btn-download"
                    download>                
            <span class="material-symbols-rounded">download</span>
            Download Panduan Penggunaan Sistem
        </a> <!-- HREF FILE YG AKAN DI DOWNLOAD href="<?= base_url('assets/template/Template Impor Data Batch Dosen.xlsx') ?>"-->
        <p>Klik untuk mengunduh panduan lengkap</p>
        </div>
    </div>

    <div class="info-box">
            <strong>
                <span class="material-symbols-rounded">info</span> 
                Informasi Penting
            </strong>
            <p>Pastikan Anda membaca panduan dengan seksama sebelum melakukan pengajuan.</p>
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
