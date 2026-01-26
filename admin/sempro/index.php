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
        :root {
            --accent-orange: #FF8C61;
            --soft-orange: #FFF5F0;
            --text-dark: #4A4A4A;
            --text-muted: #6C757D;
            --white: #FFFFFF;
            --bg-color: #FDF2E9;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
            color: var(--text-dark);
        }

        .main-content {
            margin-left: 310px;
            padding: 40px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .topbar h1 {
            color: #FF8C42;
            font-size: 28px;
            margin: 0;
            font-weight: 700;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .avatar {
            width: 45px;
            height: 45px;
            background: #ff8c42;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .avatar span {
            color: white;
            font-size: 30px;
        }

        /* ================= SEARCH & ENTRIES ================= */
        .controls-row {
            margin-bottom: 25px;
        }

        .search-box {
            position: relative;
            max-width: 350px;
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            padding-right: 45px;
            border-radius: 25px;
            border: 1px solid #ddd;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            outline: none;
        }

        .search-box span {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            pointer-events: none;
        }

        .entries-control {
            font-size: 14px;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .entries-control select {
            padding: 8px 15px;
            border-radius: 10px;
            border: 1px solid #ddd;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            outline: none;
        }

        /* ================= TABLE ================= */
        .card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            display: block;
            margin: 0;
            padding: 0;
            border: none;
        }

        thead, tbody {
            display: block;
            width: 100%;
        }

        thead tr {
            display: flex;
            width: 100%;
            background: linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
            margin: 0;
            padding: 0;
        }

        th {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            padding: 0 10px;
            border-right: 1px solid rgba(255,255,255,0.2);
            background: transparent !important;
            box-sizing: border-box;
        }

        th:last-child {
            border-right: none;
        }

        /* Column Widths (Synchronized precisely) */
        th:nth-child(1), td:nth-child(1) { flex: 0 0 50px; width: 50px; }
        th:nth-child(2), td:nth-child(2) { flex: 0 0 120px; width: 120px; }
        th:nth-child(3), td:nth-child(3) { flex: 1.5 1 0%; min-width: 0; }
        th:nth-child(4), td:nth-child(4) { flex: 2 1 0%; min-width: 0; }
        th:nth-child(5), td:nth-child(5) { flex: 0 0 120px; width: 120px; }
        th:nth-child(6), td:nth-child(6) { flex: 1.5 1 0%; min-width: 0; }

        tbody tr {
            display: flex;
            width: 100%;
            border-bottom: 1px solid #FFE5D9;
            margin: 0;
            padding: 0;
        }

        td {
            padding: 15px 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 13px;
            color: #444;
            border-right: 1px solid #FFE5D9;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Column Text Styling */
        td:nth-child(2) { font-weight: 600; }
        td:nth-child(3) { justify-content: flex-start; text-align: left; }
        td:nth-child(4) { justify-content: flex-start; text-align: left; line-height: 1.4; }
        td:nth-child(6) { gap: 8px; justify-content: center; }

        td:last-child {
            border-right: none;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            width: 80px; /* Fixed width for uniformity */
            text-align: center;
            color: #f3f7f3ff;
            padding: 6px 0; /* Adjusted padding */
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-diajukan { background-color: #a8b40aff; color: #fcfcfcff; }
        .status-revisi { background-color: #E65100; color: #fcfcfcff; }
        .status-ditolak { background-color: #C62828; color: #fcfcfcff; }
        .status-disetujui { background-color: #3A7C3A; color: #fcfcfcff; }

        /* Action Buttons */
        .btn-action {
            display: inline-block;
            width: 90px; /* Standardized width for "parallel" alignment */
            text-align: center;
            padding: 6px 0;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            color: white;
            transition: opacity 0.2s;
        }

        td:nth-child(6) { 
            gap: 8px; 
            justify-content: center;
        }

        .btn-detail { background-color: #3B82F6; }
        .btn-jadwal { background-color: #E78F00; }

        .btn-action:hover {
            opacity: 0.8;
        }

        .btn-action.disabled {
            background-color: #cbd5e1;
            cursor: not-allowed;
            pointer-events: none;
            opacity: 0.6;
        }

        /* ================= PAGINATION ================= */
        .pagination-container {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 15px;
        }

        .pagination {
            display: flex;
            gap: 5px;
        }

        .pagination a, .pagination span {
            padding: 8px 15px;
            border-radius: 10px;
            background: white;
            border: 1px solid #ddd;
            color: #555;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .pagination a:hover {
            background: #f5f5f5;
        }

        .pagination a.active {
            background: #ff9f43;
            color: white;
            border-color: #ff9f43;
        }

        .pagination span.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="topbar">
        <h1>Daftar Pengajuan Seminar Proposal</h1>
        <div class="admin-info">
        <div class="admin-text">
            <span>Selamat Datang,</span><br>
            <b><?= htmlspecialchars($username) ?></b>
        </div>
        <div class="avatar">
            <span class="material-symbols-rounded" style="color:#fff">person</span>
        </div>
    </div>
    </div>

    <div class="controls-row">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search" value="<?= htmlspecialchars($search) ?>">
            <span class="material-symbols-rounded">search</span>
        </div>

        <div class="entries-control">
            <span>Show</span>
            <select id="entriesSelect">
                
                <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
            </select>
            <span>entries</span>
        </div>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID SEMPRO</th>
                    <th>Nama</th>
                    <th>Judul Seminar Proposal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="table-body">
            <?php if (empty($pengajuan_list)): ?>
                <tr><td colspan="6" style="width: 100%; border: none;">Data tidak ditemukan.</td></tr>
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
                            <a href="jadwal.php?id=<?= $p['id'] ?>" class="btn-action btn-jadwal">Penjadwalan</a>
                        <?php else: ?>
                            <span class="btn-action btn-jadwal disabled" title="Belum disetujui">Penjadwalan</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&limit=<?= $limit ?>">Previous</a>
            <?php else: ?>
                <span class="disabled">Previous</span>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= max(1, $total_page); $i++): ?>
                <a 
                    href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&limit=<?= $limit ?>"
                    class="<?= $i == $page ? 'active' : '' ?>"
                >
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_page): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&limit=<?= $limit ?>">Next</a>
            <?php else: ?>
                <span class="disabled">Next</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const entriesSelect = document.getElementById('entriesSelect');
    let timeout = null;

    searchInput.addEventListener('keyup', function () {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const keyword = this.value;
            window.location.href = "?search=" + encodeURIComponent(keyword) + "&limit=" + entriesSelect.value;
        }, 500);
    });

    entriesSelect.addEventListener('change', function() {
        window.location.href = "?search=" + encodeURIComponent(searchInput.value) + "&limit=" + this.value;
    });
</script>

</body>
</html>
