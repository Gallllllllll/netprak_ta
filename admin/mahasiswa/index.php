<?php
session_start();
require_once "../../config/connection.php";

/* CEK LOGIN */
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$adminName = $_SESSION['user']['nama'] ?? 'Admin';

/* VALIDASI SORT */
$validColumns = ['id','nama','nim','prodi','kelas','nomor_telepon','email','username'];
$sort = $_GET['sort'] ?? 'id';
$order = strtolower($_GET['order'] ?? 'asc');
if(!in_array($sort, $validColumns)) $sort = 'id';
if(!in_array($order, ['asc','desc'])) $order = 'asc';

/* PAGINATION SETTINGS */
$perPage = 5; // data per halaman
$page = intval($_GET['page'] ?? 1);
if($page < 1) $page = 1;

/* HANDLE AJAX */
if(isset($_GET['ajax'])){
    $keyword = $_GET['keyword'] ?? '';
    $sort = $_GET['sort'] ?? 'id';
    $order = $_GET['order'] ?? 'asc';
    $page = intval($_GET['page'] ?? 1);
    if(!in_array($sort, $validColumns)) $sort='id';
    if(!in_array($order,['asc','desc'])) $order='asc';
    if($page<1) $page=1;

    $offset = ($page-1)*$perPage;

    $stmt = $pdo->prepare("
        SELECT * FROM mahasiswa 
        WHERE 
            nama LIKE ? OR 
            nim LIKE ? OR 
            prodi LIKE ? OR 
            kelas LIKE ?
        ORDER BY $sort $order
        LIMIT $perPage OFFSET $offset
    ");
    $like = "%$keyword%";
    $stmt->execute([$like,$like,$like,$like]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung total page
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM mahasiswa WHERE nama LIKE ? OR nim LIKE ? OR prodi LIKE ? OR kelas LIKE ?");
    $stmtTotal->execute([$like,$like,$like,$like]);
    $totalData = $stmtTotal->fetchColumn();
    $totalPage = ceil($totalData/$perPage);

    echo renderTable($data,$sort,$order,$page,$totalPage);
    exit;
}

/* LOAD AWAL */
$offset = ($page-1)*$perPage;
$stmt = $pdo->query("SELECT * FROM mahasiswa ORDER BY $sort $order LIMIT $perPage OFFSET $offset");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmtTotal = $pdo->query("SELECT COUNT(*) FROM mahasiswa");
$totalData = $stmtTotal->fetchColumn();
$totalPage = ceil($totalData/$perPage);

/* FUNCTION RENDER TABLE */
function renderTable($data, $sort='id', $order='asc', $currentPage=1, $totalPage=1){
    ob_start();
    $nextOrder = $order === 'asc' ? 'desc' : 'asc';
    function sortArrow($column,$sort,$order){
        return $column === $sort ? ($order==='asc' ? ' ↑' : ' ↓') : '';
    }
?>
<table>
<thead>
<tr>
<?php
$columns = ['id'=>'ID','nama'=>'Nama','nim'=>'NIM','prodi'=>'Prodi','kelas'=>'Kelas','nomor_telepon'=>'No. Telepon','email'=>'Email','username'=>'Username'];
foreach($columns as $col=>$label):
?>
<th data-column="<?= $col ?>" data-order="<?= $sort === $col && $order==='asc' ? 'desc' : 'asc' ?>">
    <?= $label . sortArrow($col,$sort,$order) ?>
</th>
<?php endforeach; ?>
<th>Aksi</th>
</tr>
</thead>
<tbody>
<?php foreach($data as $m): ?>
<tr>
    <td><?= $m['id'] ?></td>
    <td><?= htmlspecialchars($m['nama']) ?></td>
    <td><?= htmlspecialchars($m['nim']) ?></td>
    <td><?= htmlspecialchars($m['prodi']) ?></td>
    <td><?= htmlspecialchars($m['kelas']) ?></td>
    <td><?= htmlspecialchars($m['nomor_telepon']) ?></td>
    <td><?= htmlspecialchars($m['email']) ?></td>
    <td><?= htmlspecialchars($m['username']) ?></td>
    <td>
        <div class="action-btn">
            <a href="edit.php?id=<?= $m['id'] ?>" class="btn">Edit</a>
            <a href="delete.php?id=<?= $m['id'] ?>" onclick="return confirm('Yakin?')" class="btn delete">Hapus</a>
        </div>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- PAGINATION -->
<div class="pagination">
<?php for($i=1;$i<=$totalPage;$i++): ?>
    <button class="page-btn <?= $i==$currentPage?'active':'' ?>" data-page="<?= $i ?>"><?= $i ?></button>
<?php endfor; ?>
</div>

<style>
.pagination{margin-top:15px;text-align:center;}
.page-btn{margin:2px;padding:6px 12px;border:none;border-radius:5px;background:#ff8c42;color:#fff;cursor:pointer;}
.page-btn.active{background:#ff4d4d;}
.page-btn:hover{opacity:0.9;}
</style>

<?php
return ob_get_clean();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Mahasiswa</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:#fdeee3;}
.main-content{padding:30px;}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;}
.topbar h1{color:#ff8c42;font-size:28px;}
.admin-info{display:flex;align-items:center;gap:12px;}
.admin-text span{font-size:13px;color:#555;}
.admin-text b{color:#ff8c42;font-size:14px;}
.avatar{width:42px;height:42px;background:#ff8c42;border-radius:50%;display:flex;align-items:center;justify-content:center;}
.avatar svg{width:22px;fill:#fff;}
.action-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;}
.search-box{background:#fff;padding:10px 15px;border-radius:25px;width:300px;display:flex;box-shadow:0 3px 10px rgba(0,0,0,.15);}
.search-box input{border:none;outline:none;width:100%;}
.btn{padding:9px 18px;border-radius:20px;background:#ff8c42;color:#fff;text-decoration:none;border:none;}
.btn.delete{background:#ff4d4d;}
.btn.blue{background:#4f7cff;}
.card{background:#fff;border-radius:18px;padding:15px;box-shadow:0 5px 15px rgba(0,0,0,.2);overflow-x:auto;}
table{width:100%;border-collapse:collapse;min-width:900px;}
thead tr{background:linear-gradient(to right,#ff8c42,#ff6aa2);}
th{padding:12px;color:#fff;border-right:2px solid rgba(255,255,255,.6);cursor:pointer;}
td{padding:10px;border-bottom:1px solid #eee;border-right:2px solid #ffd1dc;}
.action-btn{display:flex;gap:6px;flex-wrap:nowrap;}
@media(max-width:768px){.action-row{flex-direction:column;align-items:flex-start}.search-box{width:100%}table{min-width:700px}}
</style>
</head>
<body>
<?php require_once __DIR__.'/../sidebar.php'; ?>
<div class="main-content">
<div class="topbar">
    <h1>Daftar Mahasiswa</h1>
    <div class="admin-info">
        <div class="admin-text">
            <span>Selamat Datang,</span><br>
            <b><?= htmlspecialchars($adminName) ?></b>
        </div>
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
        <a href="mahasiswa_import.php" class="btn blue">+ Add Batch</a>
    </div>
</div>

<div id="table-container" class="card"><?= renderTable($data,$sort,$order,$page,$totalPage) ?></div>
</div>

<script>
let currentSort = '<?= $sort ?>';
let currentOrder = '<?= $order ?>';
let currentPage = <?= $page ?>;
const tableContainer = document.getElementById('table-container');
const searchInput = document.getElementById('search');

function loadTable() {
    const keyword = searchInput.value;
    fetch(`?ajax=1&keyword=${encodeURIComponent(keyword)}&sort=${currentSort}&order=${currentOrder}&page=${currentPage}`)
    .then(res => res.text())
    .then(data => {
        tableContainer.innerHTML = data;
        attachSortEvents();
        attachPageEvents();
    });
}

function attachSortEvents() {
    document.querySelectorAll('th[data-column]').forEach(th=>{
        th.addEventListener('click', ()=>{
            const col = th.getAttribute('data-column');
            const ord = th.getAttribute('data-order');
            currentSort = col;
            currentOrder = ord;
            currentPage = 1;
            loadTable();
        });
    });
}

function attachPageEvents() {
    document.querySelectorAll('.page-btn').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            currentPage = btn.getAttribute('data-page');
            loadTable();
        });
    });
}

searchInput.addEventListener('keyup', ()=>{
    currentPage = 1;
    loadTable();
});

attachSortEvents();
attachPageEvents();
</script>
</body>
</html>
