<?php
session_start();
require "../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/ta_netprak/config/base_url.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . base_url('login.php'));
    exit;
}

$dosen_id = $_SESSION['user']['id'];

// Ambil nama dosen dari database
$stmt = $pdo->prepare("SELECT nama FROM dosen WHERE id = ?");
$stmt->execute([$dosen_id]);
$dosen = $stmt->fetch(PDO::FETCH_ASSOC);
$nama_dosen = $dosen['nama'] ?? 'Dosen';

$where = [];
$params = [$dosen_id];

// filter status - DIPERBAIKI
if (!empty($_GET['status'])) {
    if ($_GET['status'] === 'disetujui_sempro') {
        $where[] = "d.status_persetujuan = 'disetujui'";
    } elseif ($_GET['status'] === 'menunggu_sempro') {
        $where[] = "d.status_persetujuan = 'menunggu'";
    } elseif ($_GET['status'] === 'disetujui_semhas') {
        $where[] = "d.status_persetujuan_semhas = 'disetujui'";
    } elseif ($_GET['status'] === 'menunggu_semhas') {
        $where[] = "(d.status_persetujuan_semhas = 'menunggu' OR d.status_persetujuan_semhas IS NULL)";
    }
}

// filter jenis sidang
if (!empty($_GET['jenis'])) {
    if ($_GET['jenis'] === 'sempro') {
        $where[] = "sempro.id IS NOT NULL";
    } elseif ($_GET['jenis'] === 'semhas') {
        $where[] = "s.id IS NOT NULL";
    } elseif ($_GET['jenis'] === 'belum') {
        $where[] = "sempro.id IS NULL AND s.id IS NULL";
    }
}

// filter nama mahasiswa
if (!empty($_GET['nama'])) {
    $where[] = "m.nama LIKE ?";
    $params[] = "%" . $_GET['nama'] . "%";
}

$whereSQL = $where ? " AND " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("
    SELECT 
        d.id AS dosbing_id,
        m.nama AS nama_mahasiswa,
        p.judul_ta,
        d.role,
        d.status_persetujuan,
        d.status_persetujuan_semhas,
        s.tanggal_sidang,
        sempro.tanggal_sempro
    FROM dosbing_ta d
    JOIN pengajuan_ta p ON d.pengajuan_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    LEFT JOIN pengajuan_semhas s ON s.mahasiswa_id = m.id
    LEFT JOIN pengajuan_sempro sempro ON sempro.mahasiswa_id = m.id
    WHERE d.dosen_id = ?
    $whereSQL
    ORDER BY p.id DESC
");

$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Mahasiswa Bimbingan</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --gradient: linear-gradient(135deg, #FF6B9D 0%, #FF8E3C 100%);
    --bg: #FFF1E5;
    --text: #1F2937;
    --muted: #6B7280;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    color: var(--text);
}

.main-content {
    margin-left: 280px;
    padding: 40px;
    min-height: 100vh;
    background: var(--bg);
}

/* HEADER - SAMA SEPERTI ADMIN */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.greeting h1 {
    font-size: 28px;
    margin: 0;
    color: #FF8E3C;
    font-weight: 700;
}

.greeting p {
    margin: 5px 0 0;
    color: var(--muted);
    font-size: 15px;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-profile .text {
    text-align: right;
    line-height: 1.3;
}

.admin-profile small {
    color: var(--muted);
    font-size: 12px;
    display: block;
}

.admin-profile b {
    color: #FF8E3C;
    font-size: 14px;
    display: block;
}

.avatar {
    width: 48px;
    height: 48px;
    background: #FF8E3C;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(255, 142, 60, 0.3);
}

/* CARD HEADER - STANDALONE */
.card-header {
    background: var(--gradient);
    padding: 32px;
    border-radius: 24px;
    color: white;
    margin-bottom: 24px;
}

.card-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}

.card-header p {
    margin: 8px 0 0;
    opacity: 0.95;
    font-size: 15px;
}

/* CARD BODY - INDEPENDENT WHITE CARD (TABLE ONLY) */
.card-body {
    background: white;
    padding: 0;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(255, 140, 80, 0.12);
    overflow: hidden;
}

/* CONTROLS */
.controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 16px;
    flex-wrap: wrap;
}

.search-wrapper {
    position: relative;
    flex: 1;
    min-width: 280px;
}

.search-wrapper input {
    width: 100%;
    padding: 12px 16px 12px 44px;
    border-radius: 12px;
    border: 1px solid #E5E7EB;
    outline: none;
    font-size: 14px;
    transition: all 0.2s;
}

.search-wrapper input:focus {
    border-color: #FF8E3C;
    box-shadow: 0 0 0 3px rgba(255, 142, 60, 0.1);
}

.search-wrapper .material-symbols-rounded {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    font-size: 20px;
}

.entries-select {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: var(--muted);
}

.entries-select select {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #E5E7EB;
    outline: none;
    font-size: 14px;
}

/* FILTER BAR */
.filter-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-bar select,
.filter-bar input {
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #E5E7EB;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
    background: white;
}

.filter-bar select:focus,
.filter-bar input:focus {
    border-color: #FF8E3C;
    box-shadow: 0 0 0 3px rgba(255, 142, 60, 0.1);
}

/* TABLE */
.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

thead {
    background: var(--gradient);
}

th {
    padding: 16px 14px;
    text-align: left;
    font-weight: 600;
    color: white;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

th[data-sort] {
    cursor: pointer;
    user-select: none;
}

td {
    padding: 16px 14px;
    border-bottom: 1px solid #F3F4F6;
    vertical-align: middle;
}

tbody tr {
    transition: background 0.15s;
}

tbody tr:hover {
    background: #FFF7ED;
}

tbody tr:last-child td {
    border-bottom: none;
}

/* BADGE */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.badge-pembimbing-1 {
    background: #FFE4E8;
    color: #E91E63;
}

.badge-pembimbing-2 {
    background: #FFE9D5;
    color: #FF8E3C;
}

.badge-disetujui {
    background: #D1FAE5;
    color: #065F46;
}

.badge-menunggu {
    background: #FEF3C7;
    color: #92400E;
}

.badge-belum {
    background: #F3F4F6;
    color: #6B7280;
}

.badge-tanggal {
    background: #E0F2FE;
    color: #0369A1;
}

/* BUTTON */
.btn-upload {
    padding: 8px 16px;
    background: var(--gradient);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(255, 107, 157, 0.2);
}

.btn-upload:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3);
}

.btn-done {
    color: #10B981;
    font-weight: 600;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 4px;
    justify-content: center;
}

/* PAGINATION */
.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.pagination-info {
    color: var(--muted);
    font-size: 14px;
}

.pagination {
    display: flex;
    gap: 6px;
}

.page-btn {
    padding: 8px 14px;
    border-radius: 8px;
    background: white;
    border: 1px solid #E5E7EB;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    min-width: 40px;
    text-align: center;
}

.page-btn:hover:not(:disabled):not(.active) {
    background: linear-gradient(135deg, #FFE4E8 0%, #FFE9D5 100%);
    border-color: #FF8E3C;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 157, 0.2);
}

.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.page-btn.active {
    background: var(--gradient);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3);
}

/* EMPTY STATE */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--muted);
}

.empty-state .material-symbols-rounded {
    font-size: 80px;
    color: #E5E7EB;
    margin-bottom: 16px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 20px;
    }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .admin-profile {
        width: 100%;
        justify-content: flex-end;
    }

    .controls {
        flex-direction: column;
        align-items: stretch;
    }

    .search-wrapper {
        min-width: 100%;
    }

    th, td {
        padding: 12px 10px;
        font-size: 13px;
    }
}
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="main-content">

<!-- HEADER -->
<div class="topbar">
    <div class="greeting">
        
    </div>
    <div class="admin-profile">
        <div class="text">
            <small>Selamat Datang,</small>
            <b><?= htmlspecialchars($nama_dosen) ?></b>
        </div>
        <div class="avatar">
            <span class="material-symbols-rounded" style="color:white">person</span>
        </div>
    </div>
</div>

<!-- CARD HEADER ONLY -->
<div class="card-header">
    <h2>Mahasiswa Bimbingan</h2>
    <p>Daftar mahasiswa yang Anda bimbing</p>
</div>

<?php if ($data): ?>

<!-- CONTROLS (OUTSIDE CARD) -->
<div class="controls">
    <div class="search-wrapper">
        <span class="material-symbols-rounded">search</span>
        <input type="text" id="searchInput" placeholder="Cari nama atau judul...">
    </div>
    <div class="entries-select">
        <span>Show</span>
        <select id="entriesSelect">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span>entries</span>
    </div>
</div>

<!-- FILTER BAR (OUTSIDE CARD) -->
<div class="filter-bar">
    <select id="filterStatus">
        <option value="">Semua Status</option>
        <option value="disetujui">Disetujui (SEMPRO)</option>
        <option value="menunggu">Menunggu (SEMPRO)</option>
        <option value="disetujui_semhas">Disetujui (SEMHAS)</option>
        <option value="menunggu_semhas">Menunggu (SEMHAS)</option>
    </select>

    <select id="filterJenis">
        <option value="">Semua Jenis Sidang</option>
        <option value="sudah_sempro">Sudah SEMPRO</option>
        <option value="sudah_semhas">Sudah SEMHAS</option>
        <option value="belum_sidang">Belum Sidang</option>
    </select>
</div>

<!-- TABLE CARD -->
<div class="card-body">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th data-sort="number">No</th>
                    <th data-sort="text">Nama</th>
                    <th data-sort="text">JUDUL TA</th>
                    <th data-sort="text">Peran</th>
                    <th data-sort="text">Status SEMPRO</th>
                    <th data-sort="date">Tanggal SEMPRO</th>
                    <th data-sort="text">Status SEMHAS</th>
                    <th data-sort="date">Tanggal SEMHAS</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach($data as $row): ?>
                <tr 
                    data-status-sempro="<?= htmlspecialchars($row['status_persetujuan']) ?>"
                    data-status-semhas="<?= htmlspecialchars($row['status_persetujuan_semhas'] ?? 'menunggu') ?>"
                    data-tanggal-sempro="<?= $row['tanggal_sempro'] ? 'ada' : 'belum' ?>"
                    data-tanggal-semhas="<?= $row['tanggal_sidang'] ? 'ada' : 'belum' ?>"
                >
                    <td><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($row['nama_mahasiswa']) ?></strong></td>
                    <td><?= htmlspecialchars($row['judul_ta']) ?></td>
                    <td>
                        <?= $row['role'] == 'dosbing_1' 
                            ? '<span class="badge badge-pembimbing-1">Pembimbing 1</span>' 
                            : '<span class="badge badge-pembimbing-2">Pembimbing 2</span>' ?>
                    </td>
                    <td>
                        <?= $row['status_persetujuan'] == 'disetujui' 
                            ? '<span class="badge badge-disetujui">disetujui</span>' 
                            : '<span class="badge badge-menunggu">menunggu</span>' ?>
                    </td>
                    <td>
                        <?= $row['tanggal_sempro'] 
                            ? '<span class="badge badge-tanggal">'.date('d M Y', strtotime($row['tanggal_sempro'])).'</span>' 
                            : '<span class="badge badge-belum">belum</span>' ?>
                    </td>
                    <td>
                        <?php
                        if ($row['status_persetujuan_semhas'] === 'disetujui') {
                            echo '<span class="badge badge-disetujui">disetujui</span>';
                        } else {
                            echo '<span class="badge badge-menunggu">menunggu</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (!$row['tanggal_sidang']) {
                            echo '<span class="badge badge-belum">belum</span>';
                        } else {
                            echo '<span class="badge badge-tanggal">' . date('d M Y', strtotime($row['tanggal_sidang'])) . '</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($row['status_persetujuan'] !== 'disetujui') {
                            // Belum upload persetujuan SEMPRO
                            echo '<a class="btn-upload" href="upload_persetujuan_sempro.php?id=' . $row['dosbing_id'] . '">Upload Persetujuan SEMPRO</a>';
                        } elseif ($row['status_persetujuan'] === 'disetujui' && $row['status_persetujuan_semhas'] !== 'disetujui') {
                            // Sudah upload SEMPRO, belum upload SEMHAS
                            echo '<a class="btn-upload" href="upload_persetujuan_semhas.php?id=' . $row['dosbing_id'] . '">Upload Persetujuan SEMHAS</a>';
                        } else {
                            // Sudah upload semua
                            echo '<span class="btn-done"><span class="material-symbols-rounded" style="font-size:16px">check_circle</span> sudah upload</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- PAGINATION (OUTSIDE CARD) -->
<div class="pagination-wrapper">
    <div class="pagination-info" id="paginationInfo"></div>
    <div class="pagination" id="pagination"></div>
</div>

<?php else: ?>
<div class="card-body">
    <div class="empty-state">
        <span class="material-symbols-rounded">inbox</span>
        <p>Belum ada mahasiswa bimbingan</p>
    </div>
</div>
<?php endif; ?>

</div>

<script>
let rowsPerPage = 25;
const allRows = [...document.querySelectorAll("tbody tr")];
const tableBody = document.querySelector("tbody");
const pagination = document.getElementById("pagination");
const paginationInfo = document.getElementById("paginationInfo");
const searchInput = document.getElementById("searchInput");
const entriesSelect = document.getElementById("entriesSelect");

const filterStatus = document.getElementById("filterStatus");
const filterJenis = document.getElementById("filterJenis");

let filteredRows = [...allRows];
let currentPage = 1;
let sortDirection = {};

// RENDER TABLE
function render() {
    tableBody.innerHTML = "";
    let start = (currentPage - 1) * rowsPerPage;
    let pageRows = filteredRows.slice(start, start + rowsPerPage);
    
    pageRows.forEach((r, index) => {
        r.children[0].textContent = start + index + 1;
        tableBody.appendChild(r);
    });
    
    updatePaginationInfo();
}

// UPDATE PAGINATION INFO
function updatePaginationInfo() {
    let start = (currentPage - 1) * rowsPerPage + 1;
    let end = Math.min(start + rowsPerPage - 1, filteredRows.length);
    
    if (filteredRows.length === 0) {
        paginationInfo.textContent = 'Showing 0 to 0 of 0 entries';
    } else {
        paginationInfo.textContent = `Showing ${start} to ${end} of ${filteredRows.length} entries`;
    }
}

// PAGINATE
function paginate() {
    pagination.innerHTML = "";
    let totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    
    if (totalPages === 0) totalPages = 1;
    
    // Previous button
    let prevBtn = document.createElement("button");
    prevBtn.textContent = "Previous";
    prevBtn.className = "page-btn";
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => {
        if (currentPage > 1) {
            currentPage--;
            render();
            paginate();
        }
    };
    pagination.appendChild(prevBtn);
    
    // Calculate page range to show
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);
    
    // Adjust if we're near the start or end
    if (currentPage <= 3) {
        endPage = Math.min(5, totalPages);
    }
    if (currentPage >= totalPages - 2) {
        startPage = Math.max(1, totalPages - 4);
    }
    
    // First page + ellipsis
    if (startPage > 1) {
        let firstBtn = document.createElement("button");
        firstBtn.textContent = "1";
        firstBtn.className = "page-btn";
        firstBtn.onclick = () => {
            currentPage = 1;
            render();
            paginate();
        };
        pagination.appendChild(firstBtn);
        
        if (startPage > 2) {
            let dots = document.createElement("span");
            dots.textContent = "...";
            dots.style.padding = "8px";
            dots.style.color = "#6B7280";
            pagination.appendChild(dots);
        }
    }
    
    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        let btn = document.createElement("button");
        btn.textContent = i;
        btn.className = "page-btn" + (i === currentPage ? " active" : "");
        btn.onclick = () => {
            currentPage = i;
            render();
            paginate();
        };
        pagination.appendChild(btn);
    }
    
    // Ellipsis + last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            let dots = document.createElement("span");
            dots.textContent = "...";
            dots.style.padding = "8px";
            dots.style.color = "#6B7280";
            pagination.appendChild(dots);
        }
        
        let lastBtn = document.createElement("button");
        lastBtn.textContent = totalPages;
        lastBtn.className = "page-btn";
        lastBtn.onclick = () => {
            currentPage = totalPages;
            render();
            paginate();
        };
        pagination.appendChild(lastBtn);
    }
    
    // Next button
    let nextBtn = document.createElement("button");
    nextBtn.textContent = "Next";
    nextBtn.className = "page-btn";
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => {
        if (currentPage < totalPages) {
            currentPage++;
            render();
            paginate();
        }
    };
    pagination.appendChild(nextBtn);
}

// ENTRIES SELECT
entriesSelect.addEventListener("change", () => {
    rowsPerPage = parseInt(entriesSelect.value);
    currentPage = 1;
    render();
    paginate();
});

// SEARCH
searchInput.addEventListener("keyup", () => {
    applyFilters();
});

// FILTERS - DIPERBAIKI
function applyFilters() {
    const searchVal = searchInput.value.toLowerCase().trim();
    const statusVal = filterStatus.value;
    const jenisVal = filterJenis.value;

    filteredRows = allRows.filter(row => {
        // Ambil data dari row
        const nama = row.children[1].innerText.toLowerCase();
        const judul = row.children[2].innerText.toLowerCase();
        
        // Ambil dari data attributes
        const statusSempro = row.dataset.statusSempro;
        const statusSemhas = row.dataset.statusSemhas;
        const tanggalSempro = row.dataset.tanggalSempro;
        const tanggalSemhas = row.dataset.tanggalSemhas;

        // 1. Search filter (nama atau judul)
        if (searchVal && !nama.includes(searchVal) && !judul.includes(searchVal)) {
            return false;
        }

        // 2. Status filter
        if (statusVal) {
            if (statusVal === 'disetujui' && statusSempro !== 'disetujui') {
                return false;
            }
            if (statusVal === 'menunggu' && statusSempro !== 'menunggu') {
                return false;
            }
            if (statusVal === 'disetujui_semhas' && statusSemhas !== 'disetujui') {
                return false;
            }
            if (statusVal === 'menunggu_semhas' && statusSemhas === 'disetujui') {
                return false;
            }
        }

        // 3. Jenis sidang filter
        if (jenisVal) {
            if (jenisVal === 'sudah_sempro' && tanggalSempro === 'belum') {
                return false;
            }
            if (jenisVal === 'sudah_semhas' && tanggalSemhas === 'belum') {
                return false;
            }
            if (jenisVal === 'belum_sidang' && (tanggalSempro === 'ada' || tanggalSemhas === 'ada')) {
                return false;
            }
        }

        return true;
    });

    currentPage = 1;
    render();
    paginate();
}

filterStatus.addEventListener("change", applyFilters);
filterJenis.addEventListener("change", applyFilters);

// SORTING
document.querySelectorAll("th[data-sort]").forEach((th, index) => {
    sortDirection[index] = 1;

    th.addEventListener("click", () => {
        let type = th.dataset.sort;
        sortDirection[index] *= -1;

        filteredRows.sort((a, b) => {
            let A = a.children[index].innerText.trim();
            let B = b.children[index].innerText.trim();

            if (type === "number") {
                return sortDirection[index] * (parseInt(A) - parseInt(B));
            }

            if (type === "date") {
                let dA = Date.parse(A) || 0;
                let dB = Date.parse(B) || 0;
                return sortDirection[index] * (dA - dB);
            }

            return sortDirection[index] * A.localeCompare(B);
        });

        currentPage = 1;
        render();
        paginate();
    });
});

// INIT
render();
paginate();
</script>

</body>
</html>