<?php
session_start();
require_once "../config/connection.php";

// Cek Login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

// Ambil Data Mahasiswa
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE username = ? LIMIT 1");
$stmt->execute([$_SESSION['user']['username']]);
$mhs = $stmt->fetch(PDO::FETCH_ASSOC);

$nama = $mhs['nama'] ?? $_SESSION['user']['nama'];
$nim = $mhs['nim'] ?? '-';
$prodi = $mhs['prodi'] ?? '-';
$kelas = $mhs['kelas'] ?? '-';
$status_pengajuan = 'Sedang berjalan'; // Placeholder, bisa diambil dari logic database

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <style>
        :root {
            --pink: #FF74C7;
            --orange: #FF983D;
            --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
            --bg-soft: #FFF9F5;
            --text-dark: #1F2937;
            --text-grey: #6B7280;
        }

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
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* HEADER */
        .dash-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            gap: 20px;
            width: 100%;
        }
        
        .dash-title h1 {
            color: var(--orange);
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }

        /* User welcome responsive adjustment */
        .user-welcome {
            text-align: right;
            max-width: 90px; /* Force wrapping like mockup */
            line-height: 1.2;
        }
        
        .user-welcome span { font-size: 12px; color: #888; display: block; }
        .user-welcome b { color: var(--orange); font-size: 14px; display: block; }
        
        .header-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 5px; /* Visual alignment with H1 top */
        }
        .header-avatar {
            width: 40px; height: 40px;
            background: var(--orange);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        /* HERO CARD */
        .hero-card {
            background: var(--gradient);
            border-radius: 20px;
            padding: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(255, 116, 199, 0.3);
            margin-bottom: 30px;
        }

        .hero-content {
            z-index: 2;
            flex: 1;
            min-width: 0; /* Prevent flex overflow */
        }

        .hero-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .mhs-details table {
            color: white;
            font-size: 14px;
            font-weight: 500;
        }
        .mhs-details td {
            padding-bottom: 6px;
            padding-right: 10px;
            vertical-align: top;
        }
        .mhs-details td:first-child {
            font-weight: 600;
            width: 100px;
        }

        .status-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.3);
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 12px;
            backdrop-filter: blur(5px);
            white-space: nowrap;
        }

        .hero-actions {
            margin-top: 25px;
            display: flex;
            gap: 15px;
        }

        .btn-hero {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-hero:hover {
            background: white;
            color: var(--pink);
        }

        .hero-illustration {
            position: relative;
            z-index: 1;
            /* illustration handling */
            width: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .hero-illustration img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
            /* drop shadow for pop */
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15));
        }

        /* UPLOAD SECTION */
        .section-card {
            background: white;
            border-radius: 16px;
            padding: 20px 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin-bottom: 25px;
        }
        
        .section-header {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .section-header h2 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            color: #444;
        }
        .section-icon { color: var(--orange); }

        .upload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Responsive Grid */
            gap: 20px;
        }

        .upload-item {
            border: 2px dashed #eee;
            border-radius: 16px;
            padding: 20px;
            transition: all 0.2s;
            text-decoration: none;
            display: block;
        }
        .upload-item:hover {
            border-color: var(--orange);
            background: #FFF9F5;
        }

        .up-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .up-ta { color: var(--orange); background: #FFF4E6; }
        .up-sempro { color: var(--pink); background: #FFEAF6; }
        .up-semhas { color: #A855F7; background: #F3E8FF; }

        .up-title {
            font-size: 15px;
            font-weight: 700;
            color: #333;
            margin-bottom: 4px;
            display: block;
        }
        .up-desc {
            font-size: 12px;
            color: #888;
            font-style: italic;
        }

        /* DOCUMENT LIST */
        .doc-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .doc-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .doc-left { display: flex; align-items: center; gap: 12px; flex: 1; }
        .doc-icon { color: #9CA3AF; flex-shrink: 0; }
        .doc-name { font-size: 14px; font-weight: 500; color: #555; line-height: 1.4; }
        .doc-dl {
            color: var(--orange);
            text-decoration: none;
            cursor: pointer;
            padding: 8px;
        }
        .btn-more {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 15px;
            float: right;
            font-size: 13px;
            color: var(--orange);
            font-weight: 600;
            text-decoration: none;
            background: #FFF4E6;
            padding: 6px 14px;
            border-radius: 99px;
            transition: all 0.2s;
        }
        .btn-more:hover { background-color: #ffe0b2; }

        /* SUPPORT BOX */
        .support-box {
            background: #E0F2FE;
            border: 1px solid #BAE6FD;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
        }
        .support-title {
            color: #0369A1;
            font-weight: 800;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .btn-contact {
            background: #60A5FA;
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            box-shadow: 0 4px 10px rgba(96, 165, 250, 0.4);
            transition: all 0.2s;
        }
        .btn-contact:hover { background: #3B82F6; }

        /* ALERT FOOTER */
        .alert-footer {
            margin-top: 25px;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            gap: 15px;
            align-items: flex-start;
            color: #DC2626;
        }
        .alert-icon { font-size: 24px; margin-top: -2px; flex-shrink: 0; }
        .alert-text { font-size: 13px; line-height: 1.5; font-weight: 500; }

        /* ============================
           MOBILE RESPONSIVE
           ============================ */
        @media screen and (max-width: 900px) {
            .hero-card {
                padding: 24px;
            }
            .hero-illustration {
                width: 300px;
            }
        }

        @media screen and (max-width: 768px) {
            /* Header */
            .dash-header {
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
                gap: 10px;
            }
            .dash-title {
                flex: 1;
                min-width: 0;
            }
            .dash-title h1 { 
                font-size: 20px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .user-welcome {
                display: block !important; /* Ensure container is visible */
            }
            .user-welcome span {
                display: block; /* Restore 'Selamat Datang' text visibility */
                font-size: 11px;
                line-height: 1.2;
            }
            .user-welcome b {
                font-size: 13px;
                display: block;
            }
            .header-profile { 
                gap: 8px;
            }
            .header-avatar {
                width: 35px;
                height: 35px;
            }

            /* HERO CARD STACKING */
            .hero-card {
                flex-direction: column-reverse;
                text-align: center;
                gap: 25px;
                overflow: visible; /* Allow illustration to pop if needed, or contain it */
            }
            
            .hero-content {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .mhs-details table {
                margin: 0 auto;
                text-align: left;
            }

            .hero-actions {
                justify-content: center;
                flex-wrap: wrap;
                width: 100%;
            }
            
            .btn-hero {
                flex: 1;
                justify-content: center;
                min-width: 120px;
            }

            .hero-illustration {
                width: 100%;
                margin-top: -10px;
                margin-bottom: 10px;
            }
            
            .hero-illustration img {
                max-width: 250px;
                max-height: 200px;
            }

            /* Upload Grid */
            .upload-grid {
                grid-template-columns: 1fr; /* Full width on mobile */
            }

            /* Doc List */
            .doc-item {
                flex-wrap: nowrap; /* Keep row but allowing scrolling or compacting */
            }
            .doc-name {
                font-size: 13px;
            }
            
            /* Alert Footer */
            .alert-footer {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .alert-icon { margin-bottom: 8px; }
        }

        @media screen and (max-width: 480px) {
             /* Ultra compact adjustments */
             .dash-header {
                 margin-bottom: 20px;
             }
             .section-card {
                 padding: 15px;
             }
        }
    </style>
</head>
<body>

<div class="container">
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="dashboard-container">
            <!-- Header -->
            <div class="dash-header">
                <div class="dash-title">
                    <h1>Dashboard Mahasiswa</h1>
                </div>
                <div class="header-profile">
                    <div class="user-welcome">
                        <span>Selamat Datang,</span>
                        <b><?= htmlspecialchars($nama) ?></b>
                    </div>
                    <div class="header-avatar">
                        <span class="material-symbols-rounded">person</span>
                    </div>
                </div>
            </div>

            <!-- Profile Hero -->
            <div class="hero-card">
                <div class="hero-content">
                    <div class="hero-title">Profil Mahasiswa</div>
                    <table class="mhs-details">
                        <tr><td>Nama</td> <td>: <?= htmlspecialchars($nama) ?></td></tr>
                        <tr><td>NIM</td> <td>: <?= htmlspecialchars($nim) ?></td></tr>
                        <tr><td>Program Studi</td> <td>: <?= htmlspecialchars($prodi) ?></td></tr>
                        <tr><td>Kelas</td> <td>: <?= htmlspecialchars($kelas) ?></td></tr>
                        <tr>
                            <td>Status Pengajuan</td>
                            <td>: <span class="status-badge"><?= $status_pengajuan ?></span></td>
                        </tr>
                    </table>

                    <div class="hero-actions">
                        <a href="alurpanduan.php" class="btn-hero">
                            <span class="material-symbols-rounded">info</span>
                            Alur & Panduan
                        </a>
                        <a href="semhas/cek_nilai.php" class="btn-hero">
                            <span class="material-symbols-rounded">military_tech</span>
                            Hasil Nilai
                        </a>
                    </div>
                </div>
                <!-- Illustration from Assets or Generated -->
                <div class="hero-illustration">
                    <!-- Assuming generic illustration or one from assets -->
                    <img src="../assets/img/dbmhs.png" alt="Student Illustration">
                </div>
            </div>

            <!-- Upload Section -->
            <div class="section-card">
                <div class="section-header">
                    <span class="material-symbols-rounded section-icon">upload_file</span>
                    <h2>Upload Berkas dan Pendaftaran</h2>
                </div>
                
                <div class="upload-grid">
                    <!-- Tugas Akhir -->
                    <a href="pengajuan/form.php" class="upload-item" style="border-color: #FED7AA;">
                        <div class="up-icon up-ta">
                            <span class="material-symbols-rounded">menu_book</span>
                        </div>
                        <span class="up-title">Tugas Akhir</span>
                        <span class="up-desc">Unggah Berkas Pendaftaran</span>
                    </a>

                    <!-- Sempro -->
                    <a href="sempro/form.php" class="upload-item" style="border-color: #FBCFE8;">
                        <div class="up-icon up-sempro">
                            <span class="material-symbols-rounded">bookmark</span>
                        </div>
                        <span class="up-title">Seminar Proposal</span>
                        <span class="up-desc">Unggah Berkas Pendaftaran</span>
                    </a>

                    <!-- Semhas -->
                    <a href="semhas/form.php" class="upload-item" style="border-color: #E9D5FF;">
                        <div class="up-icon up-semhas">
                            <span class="material-symbols-rounded">school</span>
                        </div>
                        <span class="up-title">Seminar Hasil</span>
                        <span class="up-desc">Unggah Berkas Pendaftaran</span>
                    </a>
                </div>
            </div>

            <!-- Documents -->
            <div class="section-card">
                <div class="section-header">
                    <h2>Dokumen & Blangko</h2>
                </div>
                <div class="doc-list">
                    <div class="doc-item">
                        <div class="doc-left">
                            <span class="material-symbols-rounded doc-icon">description</span>
                            <span class="doc-name">Form Pendaftaran dan Persetujuan Tema</span>
                        </div>
                        <a href="../assets/template/Persetujuan%20Tema%20dan%20Form%20Pendaftaran.docx" class="doc-dl" download><span class="material-symbols-rounded">download</span></a>
                    </div>
                    <div class="doc-item">
                        <div class="doc-left">
                            <span class="material-symbols-rounded doc-icon">description</span>
                            <span class="doc-name">Form Pendaftaran Seminar Proposal</span>
                        </div>
                        <a href="../assets/template/Form%20Pendaftaran%20Seminar%20Proposal%20dan%20Berita%20Acara.docx" class="doc-dl" download><span class="material-symbols-rounded">download</span></a>
                    </div>
                    <div class="doc-item">
                        <div class="doc-left">
                            <span class="material-symbols-rounded doc-icon">description</span>
                            <span class="doc-name">Lembar Persetujuan Proposal Tugas Akhir</span>
                        </div>
                        <a href="../assets/template/Lembar%20Persetujuan%20Proposal%20Tugas%20Akhir.docx" class="doc-dl" download><span class="material-symbols-rounded">download</span></a>
                    </div>
                </div>
                <div style="overflow: hidden;">
                    <a href="template.php" class="btn-more">
                        Selengkapnya <span class="material-symbols-rounded" style="font-size:16px;">arrow_forward</span>
                    </a>
                </div>
            </div>

            <!-- Help & Warning -->
            <div class="support-box">
                <div class="support-title">BUTUH BANTUAN?</div>
                
                <a href="https://wa.me/628112951003" target="_blank" class="btn-contact">
                            Hubungi Admin
                        </a>
            </div>

            <div class="alert-footer">
                <span class="material-symbols-rounded alert-icon">info</span>
                <span class="alert-text">
                    Pastikan Status Pengajuan sudah "Disetujui" sebelum lanjut ke tahap berikutnya.Jangan lupa untuk memeriksa Hasil Nilai secara berkala setelah seminar dilaksanakan.
                </span>
            </div>

        </div>
    </div>
</div>

</body>
</html>
