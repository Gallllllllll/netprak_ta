<?php
session_start();
require "../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . base_url('login.php'));
    exit;
}

$dosen_id = $_SESSION['user']['id']; // ⬅️ WAJIB

$where = [];
$params = [$dosen_id];

// filter status
if (!empty($_GET['status'])) {
    $where[] = "d.status_persetujuan = ?";
    $params[] = $_GET['status'];
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

<style>
:root{
    --primary:#ff8c42;
    --soft:#fff3e9;
    --text:#374151;
    --muted:#6b7280;
}

*{box-sizing:border-box}

body{
    margin:0;
    font-family:'Segoe UI', Arial, sans-serif;
    background:#f4f6f8;
    color:var(--text);
}

.main-content{
    padding:32px;
    background:var(--soft);
    min-height:100vh;
}

.dashboard-header h1{
    margin:0;
    font-size:26px;
}
.dashboard-header p{
    margin-top:6px;
    color:var(--muted);
}

/* CARD */
.card{
    background:#fff;
    padding:28px;
    border-radius:20px;
    box-shadow:0 12px 30px rgba(255,140,80,.15);
}

/* SEARCH */
.search-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:18px;
    flex-wrap:wrap;
}
.search-bar input{
    padding:11px 16px;
    border-radius:12px;
    border:1px solid #e5e7eb;
    width:280px;
    outline:none;
    transition:.2s;
}
.search-bar input:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(255,140,80,.15);
}

/* TABLE */
.table-wrapper{
    overflow-x:auto;
}
table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    font-size:14px;
}
th,td{
    padding:14px 14px;
    border-bottom:1px solid #f1f1f1;
    vertical-align:middle;
}
th{
    background:#fafafa;
    font-weight:600;
    color:#111827;
}
th[data-sort]{
    cursor:pointer;
    position:relative;
    user-select:none;
}

th[data-sort]::after{
    content:"▲▼";
    font-size:10px;
    color:#c4c4c4;
    margin-left:6px;
}

tbody tr{
    transition:.15s;
}
tbody tr:hover{
    background:#fff7ed;
}

/* BADGE */
.badge{
    padding:6px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
    display:inline-block;
}
.badge-1{background:#6366f1;color:#fff}
.badge-2{background:#10b981;color:#fff}
.badge-ok{background:#dcfce7;color:#166534}
.badge-wait{background:#fef3c7;color:#92400e}
.badge-belum{background:#e5e7eb;color:#374151}
.badge-hijau{background:#dcfce7;color:#166534}
.badge-kuning{background:#fef3c7;color:#92400e}
.badge-merah{background:#fee2e2;color:#991b1b}

/* BUTTON */
.btn-upload{
    padding:8px 18px;
    background:var(--primary);
    color:#fff;
    border-radius:12px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    transition:.2s;
}
.btn-upload:hover{
    background:#ff7a26;
    transform:translateY(-1px);
}

/* PAGINATION */
.pagination{
    margin-top:22px;
    display:flex;
    justify-content:center;
    gap:8px;
    flex-wrap:wrap;
}
.page-btn{
    padding:7px 14px;
    border-radius:10px;
    background:#fff;
    border:1px solid #e5e7eb;
    cursor:pointer;
    font-weight:600;
}
.page-btn.active{
    background:var(--primary);
    color:#fff;
    border-color:var(--primary);
}
.page-btn:hover{
    background:#ffe4d5;
}

/* FILTER BAR */
.filter-bar{
    display:flex;
    gap:12px;
    margin-bottom:20px;
    flex-wrap:wrap;
}
.filter-bar select,
.filter-bar input{
    padding:10px 14px;
    border-radius:12px;
    border:1px solid #e5e7eb;
    font-size:14px;
    outline:none;
    transition:.2s;
}
.filter-bar select:focus,
.filter-bar input:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(255,140,80,.15);
}


/* RESPONSIVE */
@media(max-width:768px){
    .main-content{padding:20px}
    th,td{padding:12px 10px}
}
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="main-content">

<div class="dashboard-header">
    <h1>Mahasiswa Bimbingan</h1>
    <p>Daftar mahasiswa yang Anda bimbing</p>
</div>

<div class="card">

<div class="search-bar">
    <input type="text" id="searchInput" placeholder="Cari nama / judul TA...">
    <small id="infoData"></small>
</div>

<?php if ($data): ?>
<div class="table-wrapper">
    <div class="filter-bar">
        <select id="filterStatus">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="disetujui">Disetujui</option>
        </select>

        <select id="filterJenis">
            <option value="">Semua Jenis</option>
            <option value="sempro">Sempro</option>
            <option value="semhas">Semhas</option>
            <option value="belum">Belum Sidang</option>
        </select>

        <input type="text" id="filterNama" placeholder="Cari mahasiswa...">
    </div>


<table>
<thead>
<tr>
    <th data-sort="number">No</th>
    <th data-sort="text">Nama</th>
    <th data-sort="text">Judul TA</th>
    <th data-sort="text">Peran</th>
    <th data-sort="text">Status Sempro</th>
    <th data-sort="date">Tanggal Sempro</th>
    <th data-sort="date">Tanggal Sidang</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>
<?php $no=1; foreach($data as $row): ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
    <td><?= htmlspecialchars($row['judul_ta']) ?></td>
    <td><?= $row['role']=='dosbing_1'
        ? '<span class="badge badge-1">Pembimbing 1</span>'
        : '<span class="badge badge-2">Pembimbing 2</span>' ?></td>
    <td><?= $row['status_persetujuan']=='disetujui'
        ? '<span class="badge badge-ok">Disetujui</span>'
        : '<span class="badge badge-wait">Menunggu</span>' ?></td>
    <td><?= $row['tanggal_sempro']
        ? '<span class="badge badge-hijau">'.date('d M Y',strtotime($row['tanggal_sempro'])).'</span>'
        : '<span class="badge badge-belum">Belum</span>' ?></td>
    <td>
    <?php
    if (!$row['tanggal_sidang']) {
        echo '<span class="badge badge-belum">Belum</span>';
    } else {
        echo '<span class="badge badge-hijau">' . date('d M Y', strtotime($row['tanggal_sidang'])) . '</span>';
    }
    ?>
</td>

    <td>
        <?php if($row['status_persetujuan']!=='disetujui'): ?>
            <a class="btn-upload" href="upload_persetujuan_sempro.php?id=<?= $row['dosbing_id'] ?>">Upload</a>
        <?php else: ?>
            <small>✔ Sudah Upload</small>
        <?php endif ?>
    </td>
</tr>
<?php endforeach ?>
</tbody>
</table>
</div>

<div class="pagination" id="pagination"></div>
<?php else: ?>
<p>Belum ada mahasiswa bimbingan.</p>
<?php endif ?>

</div>
</div>

<script>
const rowsPerPage=5;
const rows=[...document.querySelectorAll("tbody tr")];
const tableBody=document.querySelector("tbody");
const pagination=document.getElementById("pagination");
const searchInput=document.getElementById("searchInput");
const infoData=document.getElementById("infoData");

let filteredRows=[...rows],currentPage=1;

function render(){
    tableBody.innerHTML="";
    let start=(currentPage-1)*rowsPerPage;
    let pageRows=filteredRows.slice(start,start+rowsPerPage);
    pageRows.forEach(r=>tableBody.appendChild(r));
    infoData.textContent=`Menampilkan ${pageRows.length} dari ${filteredRows.length} data`;
}
function paginate(){
    pagination.innerHTML="";
    let pages=Math.ceil(filteredRows.length/rowsPerPage);
    for(let i=1;i<=pages;i++){
        let b=document.createElement("button");
        b.textContent=i;
        b.className="page-btn"+(i===currentPage?" active":"");
        b.onclick=()=>{currentPage=i;render();paginate();}
        pagination.appendChild(b);
    }
}
searchInput.addEventListener("keyup",()=>{
    let k=searchInput.value.toLowerCase();
    filteredRows=rows.filter(r=>r.innerText.toLowerCase().includes(k));
    currentPage=1;render();paginate();
});
render();paginate();

let sortDirection = {};

document.querySelectorAll("th[data-sort]").forEach((th, index) => {
    sortDirection[index] = 1;

    th.addEventListener("click", () => {
        let type = th.dataset.sort;
        sortDirection[index] *= -1;

        filteredRows.sort((a, b) => {
            let A = a.children[index].innerText.trim();
            let B = b.children[index].innerText.trim();

            if(type === "number"){
                return sortDirection[index] * (parseInt(A) - parseInt(B));
            }

            if(type === "date"){
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

</script>
<script>
const filterStatus = document.getElementById("filterStatus");
const filterJenis  = document.getElementById("filterJenis");
const filterNama   = document.getElementById("filterNama");

function applyLiveFilter(){
    const statusVal = filterStatus.value;
    const jenisVal  = filterJenis.value;
    const namaVal   = filterNama.value.toLowerCase();

    filteredRows = rows.filter(row => {
        const nama   = row.children[1].innerText.toLowerCase();
        const status = row.children[4].innerText.toLowerCase();
        const sempro = row.children[5].innerText.toLowerCase();
        const sidang = row.children[6].innerText.toLowerCase();

        // filter nama
        if (namaVal && !nama.includes(namaVal)) return false;

        // filter status
        if (statusVal === "pending" && !status.includes("menunggu")) return false;
        if (statusVal === "disetujui" && !status.includes("disetujui")) return false;

        // filter jenis
        if (jenisVal === "sempro" && sempro.includes("belum")) return false;
        if (jenisVal === "semhas" && sidang.includes("belum")) return false;
        if (jenisVal === "belum" && (!sempro.includes("belum") || !sidang.includes("belum"))) return false;

        return true;
    });

    currentPage = 1;
    render();
    paginate();
}

filterStatus.addEventListener("change", applyLiveFilter);
filterJenis.addEventListener("change", applyLiveFilter);
filterNama.addEventListener("keyup", applyLiveFilter);
</script>

</body>
</html>
