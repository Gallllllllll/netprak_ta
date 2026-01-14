<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized");
}

$stmt = $pdo->query("
    SELECT p.*, m.nama
    FROM pengajuan_ta p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    ORDER BY p.created_at DESC
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once __DIR__ . '/../sidebar.php'; ?>

<style>
table {
    width:100%;
    border-collapse:collapse;
    background:#fff;
}
th, td {
    padding:12px;
    border:1px solid #e5e7eb;
    text-align:left;
}
th {
    background:#f9fafb;
}

.badge {
    padding:4px 10px;
    border-radius:12px;
    font-size:13px;
    font-weight:600;
}
.badge-proses { background:#fff3cd; color:#856404; }
.badge-disetujui { background:#d4edda; color:#155724; }
.badge-revisi { background:#d1ecf1; color:#0c5460; }
.badge-ditolak { background:#f8d7da; color:#721c24; }

.actions a {
    display:inline-block;
    padding:6px 12px;
    border-radius:8px;
    font-size:13px;
    text-decoration:none;
    margin-right:6px;
}

.btn-detail {
    background:#3b82f6;
    color:#fff;
}

.btn-plot {
    background:#10b981;
    color:#fff;
}

.btn-lock {
    background:#e5e7eb;
    color:#6b7280;
    cursor:not-allowed;
}
</style>

<div class="main-content">
<h3>Daftar Pengajuan TA</h3>

<table>
<tr>
    <th>Mahasiswa</th>
    <th>Judul</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php foreach ($data as $row): ?>
<tr>
    <td><?= htmlspecialchars($row['nama']) ?></td>
    <td><?= htmlspecialchars($row['judul_ta']) ?></td>
    <td>
        <span class="badge badge-<?= strtolower($row['status']) ?>">
            <?= strtoupper($row['status']) ?>
        </span>
    </td>
    <td class="actions">

        <!-- DETAIL -->
        <a class="btn-detail" href="detail.php?id=<?= $row['id'] ?>">
            Detail
        </a>

        <!-- PLOT DOSEN -->
        <?php if ($row['status'] === 'disetujui'): ?>
            <a class="btn-plot"
               href="plot_dosbing.php?id=<?= $row['id'] ?>">
                Plot Dosen
            </a>
        <?php else: ?>
            <span class="btn-lock">
                🔒 Plot Dosen
            </span>
        <?php endif; ?>

    </td>
</tr>
<?php endforeach; ?>

</table>
</div>
