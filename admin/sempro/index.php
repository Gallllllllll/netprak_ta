<?php
session_start();
require "../../config/connection.php";

// ===============================
// CEK ROLE ADMIN
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$username = $_SESSION['user']['nama'] ?? 'Admin';

/* ===============================
   PAGINATION
================================ */
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* ===============================
   SEARCH
================================ */
$search = $_GET['search'] ?? '';
$params = [];
$where = '';

if ($search !== '') {
    $where = "WHERE m.nama LIKE ? OR s.id_sempro LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

/* ===============================
   TOTAL DATA
================================ */
$countStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    $where
");
$countStmt->execute($params);
$total_data = $countStmt->fetchColumn();
$total_page = ceil($total_data / $limit);

/* ===============================
   DATA SEMPRO
================================ */
$stmt = $pdo->prepare("
    SELECT 
        s.id AS pengajuan_id,
        s.id_sempro,
        s.status,
        s.created_at,
        m.nama,
        p.judul_ta
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    $where
    ORDER BY s.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$pengajuan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Pengajuan Sempro</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
/* ==== CSS DIPERSINGKAT, INTI SAJA ==== */
body { font-family: Outfit, sans-serif; background:#FDF2E9; margin:0 }
.main-content { margin-left:310px; padding:40px }
.status-badge { padding:6px 12px; border-radius:20px; color:#fff; font-size:11px }
.status-disetujui { background:#2e7d32 }
.status-ditolak { background:#c62828 }
.status-revisi { background:#ef6c00 }
.status-diajukan { background:#9e9d24 }

.btn-action {
    display:inline-block;
    padding:6px 12px;
    border-radius:10px;
    font-size:11px;
    font-weight:600;
    color:#fff;
    text-decoration:none;
    margin:2px;
}
.btn-detail { background:#3b82f6 }
.btn-jadwal { background:#f59e0b }
.btn-nilai { background:#10b981 }
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
<h2>Daftar Pengajuan Seminar Proposal</h2>

<table width="100%" cellpadding="10" cellspacing="0" bgcolor="#fff">
<thead>
<tr bgcolor="#ff9f43" style="color:white">
    <th>No</th>
    <th>ID Sempro</th>
    <th>Nama Mahasiswa</th>
    <th>Judul</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>

<?php if (!$pengajuan_list): ?>
<tr>
    <td colspan="6" align="center">Data tidak ditemukan</td>
</tr>
<?php else: $no = $offset + 1; ?>
<?php foreach ($pengajuan_list as $p): ?>
<tr>
    <td><?= $no++ ?></td>
    <td><b><?= htmlspecialchars($p['id_sempro']) ?></b></td>
    <td><?= htmlspecialchars($p['nama']) ?></td>
    <td><?= htmlspecialchars($p['judul_ta']) ?></td>
    <td>
        <span class="status-badge status-<?= htmlspecialchars($p['status']) ?>">
            <?= htmlspecialchars($p['status']) ?>
        </span>
    </td>
    <td>
        <a href="detail.php?id=<?= $p['pengajuan_id'] ?>" class="btn-action btn-detail">
            Detail
        </a>

        <?php if ($p['status'] === 'disetujui'): ?>
            <a href="input_nilai_sempro.php?pengajuan_id=<?= $p['pengajuan_id'] ?>" 
               class="btn-action btn-nilai">
                Input Nilai
            </a>

            <a href="jadwal.php?id=<?= $p['pengajuan_id'] ?>" 
               class="btn-action btn-jadwal">
                Jadwal
            </a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

</tbody>
</table>

</div>
</body>
</html>
