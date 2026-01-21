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
// AMBIL DATA SEMPRO MAHASISWA
// ===============================
$stmt = $pdo->prepare("
    SELECT s.*, p.judul_ta
    FROM pengajuan_sempro s
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    WHERE s.mahasiswa_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$sempro_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===============================
// MAPPING FILE SEMPRO
// ===============================
$file_map = [
    'file_pendaftaran' => [
        'label'  => 'Form Pendaftaran',
        'status' => 'status_file_pendaftaran'
    ],
    'file_persetujuan' => [
        'label'  => 'Persetujuan Proposal',
        'status' => 'status_file_persetujuan'
    ],
    'file_buku_konsultasi' => [
        'label'  => 'Buku Konsultasi',
        'status' => 'status_file_buku_konsultasi'
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Seminar Proposal</title>

<style>
.card {
    background:#fff;
    padding:20px;
    border-radius:14px;
    box-shadow:0 4px 10px rgba(0,0,0,.08);
    margin-bottom:20px;
}

.card h3 {
    margin:0 0 10px;
    font-size:18px;
    color:#1f2937;
}

.id-sempro {
    background:#eef2ff;
    color:#3730a3;
    padding:6px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
    display:inline-block;
    margin-bottom:8px;
}

.status {
    display:inline-block;
    padding:6px 14px;
    border-radius:999px;
    font-size:13px;
    font-weight:600;
    color:#fff;
}

.status-diajukan  { background:#f59e0b; }
.status-revisi    { background:#0ea5e9; }
.status-disetujui { background:#22c55e; }
.status-ditolak   { background:#ef4444; }

.badge-revisi {
    background:#fee2e2;
    color:#b91c1c;
    padding:2px 8px;
    border-radius:999px;
    font-size:12px;
    margin-left:6px;
}

a.button {
    display:inline-block;
    padding:8px 14px;
    background:#0ea5e9;
    color:#fff;
    border-radius:10px;
    text-decoration:none;
    font-size:13px;
    margin-right:6px;
}

a.button:hover {
    background:#0284c7;
}

ul.files {
    margin:6px 0 0 20px;
    padding:0;
}

ul.files li {
    margin-bottom:6px;
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

            <?php $status_class = 'status-' . strtolower($data['status']); ?>

            <div class="card">

                <!-- ID SEMPRO -->
                <div class="id-sempro">
                    ID SEMPRO: <?= htmlspecialchars($data['id_sempro'] ?? '-') ?>
                </div>

                <h3><?= htmlspecialchars($data['judul_ta']) ?></h3>

                <p>
                    Status:
                    <span class="status <?= $status_class ?>">
                        <?= strtoupper($data['status']) ?>
                    </span>
                </p>

                <p>
                    <b>Catatan Admin:</b><br>
                    <?= htmlspecialchars($data['catatan'] ?? '-') ?>
                </p>

                <p><b>Dokumen:</b></p>
                <ul class="files">
                    <?php foreach ($file_map as $field => $info): ?>
                        <?php if (!empty($data[$field])): ?>
                            <li>
                                <a href="../../uploads/sempro/<?= htmlspecialchars($data[$field]) ?>" target="_blank">
                                    <?= $info['label'] ?>
                                </a>

                                <?php if (($data[$info['status']] ?? '') === 'revisi'): ?>
                                    <span class="badge-revisi">
                                        Revisi
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <p style="margin-top:14px;">
                    <a href="detail.php?id=<?= $data['id'] ?>" class="button">
                        Lihat Detail
                    </a>

                    <?php if (strtolower($data['status']) === 'revisi'): ?>
                        <a href="revisi.php?id=<?= $data['id'] ?>" class="button">
                            Upload Revisi
                        </a>
                    <?php endif; ?>
                </p>
            </div>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <p>Belum ada pengajuan Seminar Proposal.</p>
            <a href="form.php" class="button">Ajukan Seminar Proposal</a>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
