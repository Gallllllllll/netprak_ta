<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized");
}
$username = $_SESSION['user']['nama'] ?? 'Admin';

/* ===============================
   PAGINATION SETUP
================================ */
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* ===============================
   SEARCH
================================ */
$search = $_GET['search'] ?? '';
$search_sql = '';
$params = [];

if ($search !== '') {
    $search_sql = "WHERE m.nama LIKE ? OR p.judul_ta LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

/* ===============================
   HITUNG TOTAL DATA
================================ */
$countStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM pengajuan_ta p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    $search_sql
");
$countStmt->execute($params);
$total_data = $countStmt->fetchColumn();
$total_page = ceil($total_data / $limit);

/* ===============================
   AMBIL DATA
================================ */
$stmt = $pdo->prepare("
    SELECT p.*, m.nama
    FROM pengajuan_ta p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    $search_sql
    ORDER BY p.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengajuan TA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
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
            margin-left: 280px;
            padding: 30px;
            transition: all 0.3s ease;
            width: calc(100vw - 280px);
            max-width: calc(100vw - 280px);
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            gap: 20px;
            width: 100%;
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

        /* PROFILE */
.admin-text {
    text-align: right;
}
.admin-text span{
    font-size:12px;
    color:#777;
    display: block;
    line-height: 1.2;
}
.admin-text b{
    color:#ff8c42;
    font-size:14px;
    display: block;
    line-height: 1.2;
}

.avatar{
    width:42px;
    height:42px;
    background:#ff8c42;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink: 0;
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

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 20px;
        }

        table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }

        thead tr {
            display: flex;
            width: 100%;
            background: linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
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
        }

        th:last-child {
            border-right: none;
        }

        /* Column Widths (Synchronized precisely) */
        th:nth-child(1), td:nth-child(1) { flex: 0 0 50px; width: 50px; }
        th:nth-child(2), td:nth-child(2) { flex: 1.5 1 0%; min-width: 0; }
        th:nth-child(3), td:nth-child(3) { flex: 2 1 0%; min-width: 0; }
        th:nth-child(4), td:nth-child(4) { flex: 0 0 120px; width: 120px; }
        th:nth-child(5), td:nth-child(5) { flex: 1.5 1 0%; min-width: 0; }

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
        td:nth-child(2) { justify-content: flex-start; text-align: left; }
        td:nth-child(3) { justify-content: flex-start; text-align: left; line-height: 1.4; }
        td:nth-child(5) { gap: 8px; justify-content: center; }

        td:last-child {
            border-right: none;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            width: 80px;
            text-align: center;
            padding: 6px 0;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
            color: #fcfcfcff;
        }

        .status-diajukan { background-color: #a8b40aff; }
        .status-revisi { background-color: #E65100; }
        .status-ditolak { background-color: #C62828; }
        .status-disetujui { background-color: #3A7C3A; }

        /* Action Buttons */
        .btn-action {
            display: inline-block;
            width: 85px; /* Standardized width for alignment */
            text-align: center;
            padding: 6px 0;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            color: white;
            transition: opacity 0.2s;
        }

        .btn-detail { background-color: #3B82F6; }
        .btn-plot { background-color: #E78F00; }

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

        /* ================= RESPONSIVE ================= */
        @media screen and (max-width: 1024px) {
            .main-content {
                margin-left: 70px !important;
                padding: 20px !important;
                width: calc(100vw - 70px) !important;
                max-width: calc(100vw - 70px) !important;
            }
        }

        @media screen and (max-width: 768px) {
            .main-content {
                margin-left: 60px !important;
                padding: 15px !important;
                width: calc(100vw - 60px) !important;
                max-width: calc(100vw - 60px) !important;
            }

            .topbar {
                gap: 15px;
            }

            .topbar h1 {
                font-size: 20px;
            }

            .admin-text {
                max-width: 85px;
            }

            .admin-text span {
                font-size: 11px;
            }

            .admin-text b {
                font-size: 13px;
            }

            .avatar {
                width: 38px;
                height: 38px;
            }

            .controls-row {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            .search-box {
                max-width: 100%;
                margin-bottom: 0;
            }

            .entries-control {
                justify-content: flex-start;
            }

            .pagination-container {
                align-items: center;
            }
        }
    </style>
</head>
<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="topbar">
        <h1>Daftar Pengajuan Tugas Akhir</h1>
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
            <input type="text" id="searchInput" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
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

    <div class="card table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Judul TA</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr><td colspan="5" style="width: 100%; border: none;">Data tidak ditemukan.</td></tr>
                <?php else: 
                    foreach ($data as $i => $row):
                        $status = strtolower($row['status']);
                        $status_class = "status-" . $status;
                        $isApproved = ($status == "disetujui");
                ?>
                    <tr>
                        <td><?= $i + 1 + $offset ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['judul_ta']) ?></td>
                        <td>
                            <span class="status-badge <?= $status_class ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="detail.php?id=<?= $row['id'] ?>" class="btn-action btn-detail">Detail</a>
                            <?php if ($isApproved): ?>
                                <a href="plot_dosbing.php?id=<?= $row['id'] ?>" class="btn-action btn-plot">Plot Dosen</a>
                            <?php else: ?>
                                <span class="btn-action btn-plot disabled" title="Belum disetujui">Plot Dosen</span>
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
    const searchInput = document.getElementById("searchInput");
    const entriesSelect = document.getElementById("entriesSelect");
    let timeout = null;

    searchInput.addEventListener("keyup", function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const val = searchInput.value;
            window.location.href = `?search=${encodeURIComponent(val)}&limit=${entriesSelect.value}`;
        }, 800);
    });

    entriesSelect.addEventListener("change", function() {
        window.location.href = `?search=${encodeURIComponent(searchInput.value)}&limit=${this.value}`;
    });
</script>

</body>
</html>
