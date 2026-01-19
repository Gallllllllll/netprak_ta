<?php
session_start();
require "../../config/connection.php";

// cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

/* ===============================
   PAGINATION SETUP
================================ */
$limit = 10;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* ===============================
   SEARCH
================================ */
$search = $_GET['search'] ?? '';
$search_sql = '';
$params = [];

if ($search !== '') {
    $search_sql = "WHERE m.nama LIKE ? OR s.id_sempro LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

/* ===============================
   HITUNG TOTAL DATA
================================ */
$countStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    $search_sql
");
$countStmt->execute($params);
$total_data = $countStmt->fetchColumn();
$total_page = ceil($total_data / $limit);

/* ===============================
   AMBIL DATA
================================ */
$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.id_sempro,
        s.status,
        s.created_at,
        m.nama,
        p.judul_ta
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    $search_sql
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Pengajuan Sempro</title>

<style>
.card {
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,.08);
    margin-bottom:20px;
}
table {
    width:100%;
    border-collapse:collapse;
}
th, td {
    padding:10px;
    border:1px solid #ddd;
}
th {
    background:#f3f4f6;
}
.search-box {
    margin-bottom:15px;
}
.search-box input {
    padding:8px 12px;
    width:260px;
    border-radius:8px;
    border:1px solid #ccc;
}
.status-badge {
    padding:4px 10px;
    border-radius:6px;
    color:#fff;
    font-size:12px;
}
.status-diajukan { background:#facc15; color:#000; }
.status-revisi { background:#fb923c; }
.status-ditolak { background:#ef4444; }
.status-disetujui { background:#22c55e; }
.pagination {
    margin-top:15px;
}
.pagination a {
    padding:6px 12px;
    border:1px solid #ccc;
    margin-right:4px;
    border-radius:6px;
    text-decoration:none;
    color:#333;
}
.pagination a.active {
    background:#2563eb;
    color:#fff;
    border-color:#2563eb;
}
</style>
</head>

<body>

<div class="container">
<?php include "../sidebar.php"; ?>

<div class="main-content">

<h1>Daftar Pengajuan Seminar Proposal</h1>

<div class="card">

    <!-- SEARCH -->
    <div class="search-box">
        <input 
            type="text" 
            id="search"
            placeholder="Cari ID Sempro / Nama Mahasiswa..."
            value="<?= htmlspecialchars($search) ?>"
        >
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Sempro</th>
                <th>Nama Mahasiswa</th>
                <th>Judul TA</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="table-body">
        <?php if (empty($pengajuan_list)): ?>
            <tr><td colspan="6">Data tidak ditemukan.</td></tr>
        <?php else: 
            $no = $offset + 1;
            foreach ($pengajuan_list as $p):
                $status_class = 'status-' . ($p['status'] ?? 'diajukan');
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><b><?= htmlspecialchars($p['id_sempro']) ?></b></td>
                <td><?= htmlspecialchars($p['nama']) ?></td>
                <td><?= htmlspecialchars($p['judul_ta']) ?></td>
                <td>
                    <span class="status-badge <?= $status_class ?>">
                        <?= $p['status'] ?? 'diajukan' ?>
                    </span>
                </td>
                <td>
                    <a href="detail.php?id=<?= $p['id'] ?>">Detail</a>
                    <?php if ($p['status'] === 'disetujui'): ?>
                        | <a href="jadwal.php?id=<?= $p['id'] ?>" style="color:#22c55e">Penjadwalan</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>

    <!-- PAGINATION -->
    <?php if ($total_page > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_page; $i++): ?>
            <a 
                href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                class="<?= $i == $page ? 'active' : '' ?>"
            >
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>
</div>
</div>

<script>
const searchInput = document.getElementById('search');
let timeout = null;

searchInput.addEventListener('keyup', function () {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        const keyword = this.value;
        window.location.href = "?search=" + encodeURIComponent(keyword);
    }, 400);
});
</script>

</body>
</html>
