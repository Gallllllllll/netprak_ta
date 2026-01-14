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

<!-- STYLE KHUSUS HALAMAN -->
<style>
.card {
    background: #ffffff;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.card h3 {
    margin: 0 0 10px;
    font-size: 18px;
    color: #1f2937;
}

.status {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    color: #ffffff;
}

.status-diajukan  { background: #f59e0b; }
.status-revisi    { background: #0ea5e9; }
.status-disetujui { background: #22c55e; }
.status-ditolak   { background: #ef4444; }

a.button {
    display: inline-block;
    padding: 8px 14px;
    background: #0ea5e9;
    color: #ffffff;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13px;
    margin-right: 6px;
}

a.button:hover {
    background: #0284c7;
}

ul.files {
    margin: 6px 0 0 20px;
    padding: 0;
}

ul.files li {
    margin-bottom: 4px;
}

ul.files a {
    color: #0ea5e9;
    text-decoration: none;
}

ul.files a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">

    <div class="dashboard-header">
        <h1>Status Seminar Proposal</h1>
        <p>Riwayat pengajuan dan hasil evaluasi Seminar Proposal</p>
    </div>

    <?php if ($sempro_list): ?>
        <?php foreach ($sempro_list as $data): ?>
            <div class="card">
                <h3><?= htmlspecialchars($data['judul_ta']) ?></h3>

                <?php $status_class = 'status-' . strtolower($data['status']); ?>

                <p>
                    Status:
                    <span class="status <?= $status_class ?>">
                        <?= strtoupper($data['status']) ?>
                    </span>
                </p>

                <p>
                    <b>Catatan Admin / Dosen:</b><br>
                    <?= htmlspecialchars($data['catatan_admin'] ?? '-') ?>
                </p>

                <p><b>Dokumen:</b></p>
                <ul class="files">
                    <?php if ($data['file_pendaftaran']): ?>
                        <li>
                            <a href="../../uploads/sempro/<?= htmlspecialchars($data['file_pendaftaran']) ?>" target="_blank">
                                Form Pendaftaran
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($data['file_persetujuan']): ?>
                        <li>
                            <a href="../../uploads/sempro/<?= htmlspecialchars($data['file_persetujuan']) ?>" target="_blank">
                                Persetujuan Proposal
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($data['file_buku_konsultasi']): ?>
                        <li>
                            <a href="../../uploads/sempro/<?= htmlspecialchars($data['file_buku_konsultasi']) ?>" target="_blank">
                                Buku Konsultasi
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <p style="margin-top:12px;">
                    <a href="detail.php?id=<?= $data['id'] ?>" class="button">
                        Lihat Detail
                    </a>

                    <?php if (strtolower($data['status']) === 'revisi'): ?>
                        <a href="revisi_sempro.php?id=<?= $data['id'] ?>" class="button">
                            Upload Revisi
                        </a>
                    <?php endif; ?>
                </p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <p>
                Belum ada pengajuan Seminar Proposal.
            </p>
            <a href="form.php" class="button">Ajukan Seminar Proposal</a>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
