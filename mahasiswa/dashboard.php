<?php
session_start();
require_once "../config/connection.php";

// cek login mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

// ambil data mahasiswa berdasarkan username
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE username = ? LIMIT 1");
$stmt->execute([$_SESSION['user']['username']]);
$mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Mahasiswa</title>
<link rel="stylesheet" href="../style.css">
<style>
.container { display:flex; min-height:100vh; }
.content { flex:1; padding:20px; background:#f4f6f8; }
.card {
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
.card h3 { margin-top:0; }
.grid {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap:15px;
}
.menu-card {
    background:#007bff;
    color:#fff;
    padding:20px;
    border-radius:8px;
    text-decoration:none;
}
.menu-card:hover { background:#0056b3; }
</style>
</head>
<body>

<div class="container">
    <?php include "sidebar.php"; ?>

    <div class="content">
        <h1>Dashboard Mahasiswa</h1>

        <div class="card">
            <h3>Profil Mahasiswa</h3>
            <p><b>Nama:</b> <?= htmlspecialchars($mahasiswa['nama']); ?></p>
            <p><b>NIM:</b> <?= htmlspecialchars($mahasiswa['nim']); ?></p>
            <p><b>Prodi:</b> <?= htmlspecialchars($mahasiswa['prodi']); ?></p>
            <p><b>Kelas:</b> <?= htmlspecialchars($mahasiswa['kelas']); ?></p>
        </div>

        <div class="grid">
            <a href="upload_ta.php" class="menu-card">
                <h3>Upload Berkas TA</h3>
                <p>Proposal, Seminar, Sidang</p>
            </a>

            <a href="status_pengajuan.php" class="menu-card">
                <h3>Status Pengajuan</h3>
                <p>Lihat status persetujuan dosen</p>
            </a>

            <a href="nilai.php" class="menu-card">
                <h3>Nilai</h3>
                <p>Lihat nilai seminar & sidang</p>
            </a>

            <a href="dokumen.php" class="menu-card">
                <h3>Dokumen</h3>
                <p>Unduh surat & blangko</p>
            </a>
        </div>
    </div>
</div>

</body>
</html>
