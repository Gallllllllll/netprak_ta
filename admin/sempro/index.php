<?php
session_start();
require "../../config/connection.php";


// cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}
$username = $_SESSION['user']['nama'] ?? 'Admin';

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
        p.judul_ta,

        MAX(CASE WHEN ns.peran = 'dosbing_1' THEN ns.nilai END) AS nilai_dosbing_1,
        MAX(CASE WHEN ns.peran = 'dosbing_2' THEN ns.nilai END) AS nilai_dosbing_2

    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    LEFT JOIN nilai_sempro ns ON ns.pengajuan_id = s.id

    $search_sql
    GROUP BY s.id
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
/* ================= PAGE TITLE ================= */
/* h1 {
    color: #ff8c42;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 20px;
    margin-left: -10px;

} */
/* TOP */
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px}
.topbar h1{color:#ff8c42;font-size:28px}

/* PROFILE */
.admin-info{display:flex;align-items:left;gap:20px}
.admin-text span{font-size:13px;color:#555}
.admin-text b{color:#ff8c42;font-size:14px}

.avatar{
    width:42px;height:42px;
    background:#ff8c42;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
}
/* ================= CARD ================= */
.card {
    background:#fff;border-radius:18px;
    padding:24px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    overflow-x:auto;  
}

/* ================= SEARCH & ENTRIES ================= */
.table-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
    margin-bottom: 20px;
}

.search-box {
    position: relative;
    max-width: 300px;
    margin-top: 50px;
    margin-bottom: 40px;
    margin-left: 0px; 
}

.search-box input {
    padding: 10px 40px 10px 14px;
    width: 100%;
    border-radius: 20px;
    border: 1px solid #ddd;
    font-size: 14px;
    box-sizing: border-box;
}

.search-box::after {
    
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    opacity: 0.5;
}

.entries-control {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    margin-bottom: 10px;
    margin-left: 0px;   
}

.entries-control select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ddd;
}

/* ================= TABLE ================= */
table {
    width:100%;
    border-collapse: collapse;
    border: 1px solid #ff9f9f;
    border-radius: 0px;
    overflow: hidden;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #ffe0e0;
    border-right: 1px solid #ffe0e0;
    text-align: center;
    font-size: 14px;
}

table th:nth-child(1),
table td:nth-child(1){
    width: 20px;
    text-align: center;
}

table th:nth-child(2),
table td:nth-child(2){
    width: 90px;
    text-align: center;
}

table th:nth-child(3),
table td:nth-child(3){
    width: 170px;
    text-align: left;
}

table th:nth-child(4),
table td:nth-child(4){
    width: 280px;
    text-align: center;
}

table th:nth-child(5),
table td:nth-child(5){
    width: 100px;
    text-align: center;
}
table th:nth-child(6),
table td:nth-child(6){
    width: 220px;
    text-align: center;
}

th:last-child, td:last-child {
    border-right: none;
}

tr:last-child td {
    border-bottom: none;
}

thead tr {
    background: linear-gradient(90deg, #ff5f9e, #ff9f43);
}

th {
    background: transparent !important;
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    height: 10px;   
}

tbody tr:hover {
    background: #fff9f5;
}

/* ================= STATUS BADGE ================= */
.status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    color:#fff;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
}
.status-diajukan { background:#facc15; color:#000; height: 17px; }
.status-revisi { background:#fb923c; height: 17px; }
.status-ditolak { background:#ef4444; height: 17px; }
.status-disetujui { background:#3A7C3A; height: 17px; }

/* ================= ACTION BUTTONS ================= */
.btn-action {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    margin: 2px;
}

.btn-nilai {
    background: #10b981; /* hijau */
    color: #fff;
    border-radius:12px;
    height: 17px;
}

.btn-nilai:hover {
    background: #059669;
}


.btn-detail {
    background: #3b82f6;
    color: #fff;
    border-radius:12px;
    height: 17px;  
}

.btn-detail:hover {
    background: #2563eb;
}

.btn-jadwal {
    background: #E78F00;
    color: #fff;
    border-radius:12px;
    height: 17px;  
}

.btn-jadwal:hover {
    opacity: 0.9;
}

/* ================= PAGINATION ================= */
.pagination {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

.pagination a, .pagination span {
    padding: 8px 14px;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
    font-size: 13px;
    background: #fff;
}

.pagination a:hover {
    background: #f5f5f5;
}

.pagination a.active {
    background: #ff9f43;
    color: #fff;
    border-color: #ff9f43;
}
</style>
</head>

<body>

<div class="container">
<?php include "../sidebar.php"; ?>

<div class="main-content">

<div class="topbar">
    <h1>Daftar Pengajuan Seminar Proposal</h1>

    <div class="admin-info">
        <div class="admin-text">
            <span>Selamat Datang,</span><br>
            <b><?php echo htmlspecialchars($username); ?></b>
        </div>
        <div class="avatar">
            <span class="material-symbols-rounded" style="color:#fff">person</span>
        </div>
    </div>
</div>

<!-- SEARCH -->
<div class="search-box">
    <input 
        type="text" 
        id="search"
        placeholder="Search"
        value="<?= htmlspecialchars($search) ?>"
    >
</div>

<!-- ENTRIES -->
    <div class="entries-control">
        <span>Show</span>
        <select id="entries">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span>entries</span>
    </div>

<div class="card">


    <table id="table th">
        <thead>
            <tr>
                <th>No</th>
                <th>ID SEMPRO</th>
                <th>Nama Mahasiswa</th>
                <th>Judul Seminar Proposal</th>
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
                <a href="detail.php?id=<?= $p['id'] ?>" class="btn-action btn-detail">Detail</a>

                <?php if ($p['status'] === 'disetujui'): ?>
                    <a href="input_nilai_sempro.php?pengajuan_id=<?= $p['id'] ?>"
                    class="btn-action btn-nilai">
                        Input nilai
                    </a>

                    <a href="jadwal.php?id=<?= $p['id'] ?>"
                    class="btn-action btn-jadwal">
                        Penjadwalan
                    </a>
                <?php endif; ?>

            </td>

            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>



</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
    <?php else: ?>
        <span style="opacity:0.5; padding: 8px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; background: #fff; cursor: not-allowed;">Previous</span>
    <?php endif; ?>
    
    <?php for ($i = 1; $i <= max(1, $total_page); $i++): ?>
        <a 
            href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
            class="<?= $i == $page ? 'active' : '' ?>"
        >
            <?= $i ?>
        </a>
    <?php endfor; ?>
    
    <?php if ($page < $total_page): ?>
        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
    <?php else: ?>
        <span style="opacity:0.5; padding: 8px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; background: #fff; cursor: not-allowed;">Next</span>
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
