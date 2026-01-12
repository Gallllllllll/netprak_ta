<?php
session_start();
require "../../config/connection.php";

// cek role mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

// ambil semua pengajuan sempro mahasiswa
$stmt = $pdo->prepare("
    SELECT s.*, p.judul_ta
    FROM pengajuan_sempro s
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    WHERE s.mahasiswa_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$sempro_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Pengajuan Seminar Proposal</title>
<link rel="stylesheet" href="../style.css">
<style>
body {
    margin:0;
    font-family:Arial,sans-serif;
    background:#f4f6f8;
}
.container {
    display:flex;
    min-height:100vh;
}
.content {
    flex:1;
    padding:20px;
}
.card {
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
.card h3 { margin-top:0; }
.status {
    display:inline-block;
    padding:5px 12px;
    border-radius:20px;
    font-weight:bold;
    color:#fff;
}
.status-diajukan { background:#ffc107; }
.status-revisi { background:#17a2b8; }
.status-disetujui { background:#28a745; }
.status-ditolak { background:#dc3545; }

a.button {
    display:inline-block;
    padding:8px 12px;
    margin-top:10px;
    background:#17a2b8;
    color:#fff;
    border-radius:6px;
    text-decoration:none;
}
a.button:hover { background:#138496; }

ul.files { margin:8px 0 0 20px; }
ul.files li { margin-bottom:4px; }
</style>
</head>
<body>

<div class="container">

    <?php include "../sidebar.php"; ?>

    <div class="content">
        <h1>Riwayat Pengajuan Seminar Proposal</h1>

        <?php if ($sempro_list): ?>
            <?php foreach($sempro_list as $data): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($data['judul_ta']) ?></h3>

                    <?php
                    $status_class = 'status-' . strtolower($data['status']);
                    ?>

                    <p>
                        Status: <span class="status <?= $status_class ?>"><?= strtoupper($data['status']) ?></span>
                    </p>

                    <p>
                        Catatan Admin / Dosen:
                        <?= htmlspecialchars($data['catatan_admin'] ?? '-') ?>
                    </p>


                    <p><b>Dokumen:</b></p>
                    <ul class="files">
                        <?php if($data['file_pendaftaran']): ?>
                        <li><a href="../../uploads/sempro/<?= htmlspecialchars($data['file_pendaftaran']) ?>" target="_blank">Form Pendaftaran</a></li>
                        <?php endif; ?>
                        <?php if($data['file_persetujuan']): ?>
                        <li><a href="../../uploads/sempro/<?= htmlspecialchars($data['file_persetujuan']) ?>" target="_blank">Persetujuan Proposal</a></li>
                        <?php endif; ?>
                        <?php if($data['file_buku_konsultasi']): ?>
                        <li><a href="../../uploads/sempro/<?= htmlspecialchars($data['file_buku_konsultasi']) ?>" target="_blank">Buku Konsultasi</a></li>
                        <?php endif; ?>
                    </ul>

                    <p>
                        <a href="detail.php?id=<?= $data['id'] ?>" class="button">Lihat Detail</a>

                        <?php if (strtolower($data['status']) === 'revisi'): ?>
                            <a href="revisi_sempro.php?id=<?= $data['id'] ?>" class="button">Upload Revisi</a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <p>
                    Belum ada pengajuan Seminar Proposal.
                    <a href="form.php" class="button">Ajukan Seminar Proposal</a>
                </p>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
