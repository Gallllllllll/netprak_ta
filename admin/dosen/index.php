<?php
session_start();
require_once "../../config/connection.php";

/* CEK LOGIN */
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$adminName = $_SESSION['user']['nama'] ?? 'Admin';

/* =========================
   HANDLE AJAX SEARCH
   ========================= */
if(isset($_GET['ajax'])){
    $keyword = $_GET['keyword'] ?? '';

    $stmt = $pdo->prepare("
        SELECT * FROM dosen 
        WHERE 
            nama LIKE ? OR 
            nip LIKE ? OR 
            username LIKE ? OR
            email LIKE ?
        ORDER BY id ASC
    ");
    $like = "%$keyword%";
    $stmt->execute([$like,$like,$like,$like]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo renderTable($data);
    exit;
}

/* =========================
   LOAD AWAL
   ========================= */
$stmt = $pdo->query("SELECT * FROM dosen ORDER BY id ASC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   FUNCTION RENDER TABLE
   ========================= */
function renderTable($data){
ob_start(); ?>
<table>
<thead>
<tr>
    <th>ID</th>
    <th>Nama</th>
    <th>NIP</th>
    <th>Username</th>
    <th>Email</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>
<?php if(count($data) > 0): ?>
<?php foreach($data as $d): ?>
<tr>
    <td><?= $d['id'] ?></td>
    <td><?= htmlspecialchars($d['nama']) ?></td>
    <td><?= htmlspecialchars($d['nip']) ?></td>
    <td><?= htmlspecialchars($d['username']) ?></td>
    <td><?= htmlspecialchars($d['email'] ?? '-') ?></td>
    <td>
        <div class="action-btn">
            <a href="edit.php?id=<?= $d['id'] ?>" class="btn">Edit</a>
            <a href="delete.php?id=<?= $d['id'] ?>" 
               onclick="return confirm('Yakin ingin hapus dosen ini?')"
               class="btn delete">Hapus</a>
        </div>
    </td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="6" style="text-align:center">
        Data dosen tidak ditemukan
    </td>
</tr>
<?php endif; ?>
</tbody>
</table>
<?php
return ob_get_clean();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Dosen</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:#fdeee3;}
.main-content{padding:30px}

/* TOP */
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px}
.topbar h1{color:#ff8c42;font-size:28px}

/* PROFILE */
.admin-info{display:flex;align-items:center;gap:12px}
.admin-text span{font-size:13px;color:#555}
.admin-text b{color:#ff8c42;font-size:14px}

.avatar{
    width:42px;height:42px;
    background:#ff8c42;
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
}
.avatar svg{width:22px;fill:#fff}

/* ACTION */
.action-row{
    display:flex;justify-content:space-between;
    align-items:center;margin-bottom:15px;
    flex-wrap:wrap;gap:10px
}

.search-box{
    background:#fff;
    padding:10px 15px;
    border-radius:25px;
    width:300px;
    display:flex;
    box-shadow:0 3px 10px rgba(0,0,0,.15);
}
.search-box input{border:none;outline:none;width:100%}

/* BUTTON */
.btn{
    padding:9px 18px;
    border-radius:20px;
    background:#ff8c42;
    color:#fff;
    text-decoration:none;
    border:none;
}
.btn.delete{background:#ff4d4d}
.btn.blue{background:#4f7cff}

/* CARD */
.card{
    background:#fff;
    border-radius:18px;
    padding:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    overflow-x:auto;
}

/* TABLE */
table{width:100%;border-collapse:collapse;min-width:900px}
thead tr{
    background:linear-gradient(to right,#ff8c42,#ff6aa2);
}
th{
    padding:12px;color:#fff;
    border-right:2px solid rgba(255,255,255,.6);
}
td{
    padding:10px;
    border-bottom:1px solid #eee;
    border-right:2px solid #ffd1dc;
}

/* ACTION BUTTON FIX */
.action-btn{
    display:flex;
    gap:6px;
    flex-wrap:nowrap;
}

/* MOBILE */
@media(max-width:768px){
    .action-row{flex-direction:column;align-items:flex-start}
    .search-box{width:100%}
    table{min-width:700px}
}
</style>
</head>

<body>

<?php require_once __DIR__.'/../sidebar.php'; ?>

<div class="main-content">

<div class="topbar">
    <h1>Daftar Dosen</h1>

    <div class="admin-info">
        <!-- <div class="admin-text">
            <span>Selamat Datang,</span><br>
            <b><?= htmlspecialchars($adminName) ?></b>
        </div> -->
        <div class="avatar">
            <svg viewBox="0 0 24 24">
                <path d="M12 12c2.7 0 4.9-2.2 4.9-4.9S14.7 2.2 12 2.2 7.1 4.4 7.1 7.1 9.3 12 12 12zm0 2.4c-3.3 0-9.8 1.7-9.8 5v2.4h19.6v-2.4c0-3.3-6.5-5-9.8-5z"/>
            </svg>
        </div>
    </div>
</div>

<div class="action-row">
    <div class="search-box">
        <input type="text" id="search" placeholder="Search...">
    </div>

    <div>
        <a href="add.php" class="btn">+ Add Data</a>
        <a href="batch.php" class="btn blue">+ Add Batch</a>
    </div>
</div>

<div id="table-container" class="card">
<?= renderTable($data) ?>
</div>

</div>

<script>
document.getElementById('search').addEventListener('keyup', function() {
    let keyword = this.value;

    fetch('?ajax=1&keyword=' + encodeURIComponent(keyword))
    .then(res => res.text())
    .then(data => {
        document.getElementById('table-container').innerHTML = data;
    });
});
</script>

</body>
</html>
