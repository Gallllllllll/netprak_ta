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
$username = $_SESSION['user']['username'];
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once __DIR__ . '/../sidebar.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Pengajuan TA</title>

<style>
body{
    background:#fde9d9;
    font-family:'Segoe UI',sans-serif;
}
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
/* CARD */
.main-content{
    background:#fff3e6;
    padding:25px;
    border-radius:18px;
}

/* SEARCH */
.tools{
    margin:15px 0;
}

.search-box{
    background:#fff;padding:10px 15px;
    border-radius:25px;width:300px;
    display:flex;box-shadow:0 3px 10px rgba(0,0,0,.15)
}
.search-box input{border:none;outline:none;width:100%}

/* TABLE CARD */
.table-wrapper{
    background:white;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}
/* CARD */
.card{
    background:#fff;border-radius:18px;
    padding:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    overflow-x:auto;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

/* GARIS TABLE */
table th,
table td{
    border:1px solid #f0c4a5; /* warna garis */
}
table th:nth-child(1),
table td:nth-child(1){
    width: 20px;
    text-align: center;
}

table th:nth-child(2),
table td:nth-child(2){
    width: 170px;
    text-align: left;
}

table th:nth-child(3),
table td:nth-child(3){
    width: 300px;
    text-align: left;
}

table th:nth-child(4),
table td:nth-child(4){
    width: 100px;
    text-align: center;
}

table th:nth-child(5),
table td:nth-child(5){
    width: 180px;
    text-align: center;
}
/* HEADER */
thead tr{
    background:linear-gradient(90deg,#ff9a4d,#ff5fcf);
}

th{
    color:white;
    padding:12px;
    font-size:14px;
    text-align:center;
    position:relative;
}

/* ICON SORT */
th::after{
    font-size:9px;
    opacity:.6;
    position:absolute;
    right:8px;
    top:50%;
    transform:translateY(-50%);
}

/* BODY */
td{
    padding:12px;
    font-size:14px;
    background:white;
}


/* STATUS */
.status-btn{
    width:120px;
    text-align:center;
    padding:5px 0px;
    border-radius:20px;
    border:1px solid #ff8c42;
    color:#ff8c42;
    font-size:14px;
    background:white;
    display:inline-block;
    justify-content:center;
}

.status-disetujui{
    border-color:#22c55e;
    color:white;
    background:#22c55e;
    justify-content:center;
}

.status-revisi{
    border-color:#f59e0b;
    color:white;
    background:#f59e0b;
    justify-content:center;
}

.status-ditolak{
    border-color:#ef4444;
    color:white;
    background:#ef4444;
    justify-content:center;
}

/* ACTION */
.actions{
    display:flex;
    justify-content:center;
    gap:5px;
}

.btn-detail{
    background:#3b82f6;
    color:white;
    padding:5px 15px;
    border-radius:12px;
    font-size:14px;
    text-decoration:none;
}

.btn-plot{
    background:#FFAE00;
    color:white;
    padding:5px 10px;
    border-radius:12px;
    font-size:14px;
    text-decoration:none;
}

/* PAGINATION (dummy) */
.pagination{
    margin-top:15px;
    display:flex;
    justify-content:flex-end;
    gap:5px;
}
.pagination a{
    padding:5px 10px;
    border:1px solid #ddd;
    border-radius:5px;
    text-decoration:none;
    font-size:12px;
    color:#555;
}
.pagination .active{
    background:#ff8c42;
    color:white;
}

/* RESPONSIVE */
@media(max-width:600px){
.search-box input{width:100%;}
.table-wrapper{overflow-x:auto;}
table{min-width:650px;}
.actions{flex-direction:column;}
}
</style>
</head>

<body>

<div class="main-content">

<div class="topbar">
    <h1>Daftar Pengajuan TA</h1>

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
<div class="tools">
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search...">
    </div>
</div>

<!-- TABLE -->
<div class="table-wrapper card">
<table id="taTable">
<thead>
<tr>
    <th>ID</th>
    <th>Nama</th>
    <th>Judul TA</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>
<?php foreach($data as $i=>$row): ?>

<?php
$status = strtolower($row['status']);

$classStatus = "";
if($status=="disetujui") $classStatus="status-disetujui";
elseif($status=="revisi") $classStatus="status-revisi";
elseif($status=="ditolak") $classStatus="status-ditolak";
?>

<tr>
    <td><?= $i+1 ?></td>
    <td><?= htmlspecialchars($row['nama']) ?></td>
    <td><?= htmlspecialchars($row['judul_ta']) ?></td>

    <!-- STATUS DISPLAY ONLY -->
    <td>
        <span class="status-btn <?= $classStatus ?>">
            <?= ucfirst($row['status']) ?>
        </span>
    </td>

    <!-- AKSI -->
    <td class="actions">
        <a class="btn-detail" 
           href="detail.php?id=<?= $row['id'] ?>">
           Detail
        </a>

        <a class="btn-plot" 
           href="plot_dosbing.php?id=<?= $row['id'] ?>">
           Plot Dosen
        </a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>

</table>
</div>

<!-- PAGINATION DUMMY -->
<div class="pagination">
    <a>Previous</a>
    <a class="active">1</a>
    <a>2</a>
    <a>3</a>
    <a>4</a>
    <a>Next</a>
</div>

</div>

<!-- LIVE SEARCH -->
<script>
const searchInput = document.getElementById("searchInput");
const table = document.getElementById("taTable");
const rows = table.getElementsByTagName("tr");

searchInput.addEventListener("keyup", function() {
    let filter = searchInput.value.toLowerCase();

    for (let i = 1; i < rows.length; i++) {
        let rowText = rows[i].textContent.toLowerCase();

        if (rowText.indexOf(filter) > -1) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
});
</script>

</body>
</html>
