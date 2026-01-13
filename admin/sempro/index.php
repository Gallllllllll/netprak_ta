<?php
session_start();
require "../../config/connection.php";

// cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// ambil semua pengajuan sempro beserta nama mahasiswa dan judul TA
$stmt = $pdo->query("
    SELECT s.id, m.nama, s.status,
           s.file_pendaftaran, s.file_persetujuan, s.file_buku_konsultasi,
           s.created_at, p.judul_ta
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    ORDER BY s.created_at DESC
");
$pengajuan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Pengajuan Sempro</title>
<style>
.card { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
th { background:#eee; }
a.detail-link { color:#007bff; text-decoration:none; }
a.detail-link:hover { text-decoration:underline; }
.status-badge { padding:5px 10px; border-radius:4px; color:#fff; font-size:12px; display:inline-block; }
.status-diajukan { background:#ffc107; }
.status-revisi { background:#fd7e14; }
.status-ditolak { background:#dc3545; }
.status-disetujui { background:#28a745; }
</style>
</head>
<body>

<div class="container">
    <?php include "../sidebar.php"; ?>

    <div class="main-content">
        <h1>Daftar Pengajuan Seminar Proposal</h1>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Mahasiswa</th>
                        <th>Judul Sempro</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($pengajuan_list)): ?>
                        <tr><td colspan="5">Belum ada pengajuan Sempro.</td></tr>
                    <?php else: 
                        $no = 1;
                        foreach($pengajuan_list as $p):
                            $status_class = 'status-' . ($p['status'] ?? 'diajukan');
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($p['nama']) ?></td>
                        <td><?= htmlspecialchars($p['judul_ta'] ?? '-') ?></td>
                        <td>
                            <span class="status-badge <?= $status_class ?>">
                                <?= $p['status'] ?? 'diajukan' ?>
                            </span>
                        </td>
                        <td>
                            <a class="detail-link" href="detail.php?id=<?= $p['id'] ?>">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
