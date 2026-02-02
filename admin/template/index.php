<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

/* CEK LOGIN */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$username = $_SESSION['user']['username'];

/* LOAD DATA */
$no = 1;
$stmt = $pdo->query("SELECT * FROM template ORDER BY created_at DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Manajemen Template</title>

<!-- MATERIAL ICON -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

<!-- DATATABLES CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

<style>
body {
    font-family: 'Inter', sans-serif;
    background: #FFF1E5 !important;
    margin: 0;
}

.container {
    background: #FFF1E5 !important;
}

.main-content {
    margin-left: 280px;
    padding: 32px;
    min-height: 100vh;
    background: #FFF1E5 !important;
}
/* ================= TOP ================= */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px
}
.topbar h1{
    color:#ff8c42;
    font-size:28px
}

/* ================= PROFILE ================= */
.admin-info{
    display:flex;
    align-items:center;
    gap:20px
}
.admin-text span{
    font-size:13px;
    color:#555
}
.admin-text b{
    color:#ff8c42;
    font-size:14px
}
.avatar{
    width:42px;height:42px;
    background:#ff8c42;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}

/* ================= ACTION ROW ================= */
.action-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
    flex-wrap:wrap;
    gap:10px
}

.search-box{
    background:#fff;
    padding:10px 15px;
    border-radius:25px;
    width:300px;
    display:flex;
    box-shadow:0 3px 10px rgba(0,0,0,.15)
}
.search-box input{
    border:none;
    outline:none;
    width:100%
}

.btn{
    padding:10px 18px;
    border-radius:20px;
    background:#ff8c42;
    color:#fff;
    text-decoration:none;
    border:none;
    font-size:14px;
    display:inline-flex;
    align-items:center;
    gap:6px
}

.btn .material-symbols-rounded {
    font-size: 20px;
    line-height: 1;       
    align-content: center;
    vertical-align: middle;
}

.btn.delete{background:#ff4d4d;padding: 5px 15px !important;}
.btn.edit{background:#ff8c42;padding: 5px 15px !important;}


/* ================= CARD ================= */
.card{
    background:#fff;
    border-radius:18px;
    padding:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    overflow-x:auto;
}

/* ================= TABLE ================= */
table{
    width:100%;
    border-collapse:collapse;
    min-width:800px
}

/* THEAD 1 BAR GRADIENT */
thead tr{
    background:linear-gradient(to right,#ff8c42,#ff6aa2);
}
th{
    padding:12px;
    color:#fff;
    font-size:14px;
    text-align:center
}
td{
    padding:10px;
    font-size:14px;
    text-align:left
}

/* ================= DATATABLES CUSTOM ================= */
.dataTables_filter{display:none}

.dataTables_info{
    font-size:14px;
    margin:20px 2px;
    color:#555
}

.dataTables_paginate .paginate_button{
    padding:6px 12px;
    margin:20px 2px;
    border-radius:10px;
    font-size:14px !important
}
.dataTables_paginate .paginate_button.current{
    background:#ff8c42 !important;
    color:#fff !important;
}

/* ================= GARIS ANTAR KOLOM ================= */
table.dataTable th,
table.dataTable td{
    border-right:1px solid #e5e7eb
}
table.dataTable th:last-child,
table.dataTable td:last-child{
    border-right:none
}
table.dataTable tbody tr td{
    border-bottom:1px solid #e5e7eb
}

/* ================= FIX WIDTH KOLOM ================= */
table.dataTable th:nth-child(1),
table.dataTable td:nth-child(1){
    width:40px;
    text-align:center
}

table.dataTable th:nth-child(2),
table.dataTable td:nth-child(2){
    width:250px
}

table.dataTable th:nth-child(3),
table.dataTable td:nth-child(3){
    width:300px
}

table.dataTable th:last-child,
table.dataTable td:last-child{
    width:140px;
    text-align:center
}

/* ================= ACTION ================= */
.action-btn{
    display:flex;
    gap:6px;
    justify-content:center
}
.action-btn a{
    font-size:13px;
    font-weight:600;
    text-decoration:none
}

</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">

    <!-- TOPBAR -->
    <div class="topbar">
        <h1>Manajemen Template</h1>

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

    <!-- ACTION -->
    <div class="action-row">
        <div class="search-box">
            <input type="text" id="search" placeholder="Search...">
        </div>

        <a href="create.php" class="btn">
            <span class="material-symbols-rounded">add</span>
            Tambah Template
        </a>
    </div>

    <!-- TABLE -->
    <div class="card">
        <table id="datatable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Template</th>
                    <th>File</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$data): ?>
                <tr>
                    <td colspan="4" style="text-align:center;color:#9ca3af">
                        Belum ada template
                    </td>
                </tr>
            <?php else: foreach($data as $t): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($t['nama']) ?></td>
                    <td>
                        <?php if ($t['file']): ?>
                            <a href="../../uploads/templates/<?= htmlspecialchars($t['file']) ?>" target="_blank">
                                <?= htmlspecialchars($t['file']) ?>
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btn">
                            <a href="edit.php?id=<?= $t['id'] ?>" class="btn edit">Edit</a>

                            <a href="toggle.php?id=<?= $t['id'] ?>"
                            class="btn"
                            style="background:<?= $t['is_visible'] ? '#6b7280' : '#22c55e' ?>">
                                <?= $t['is_visible'] ? 'Sembunyikan' : 'Tampilkan' ?>
                            </a>

                            <a href="delete.php?id=<?= $t['id'] ?>"
                            onclick="return confirm('Yakin ingin menghapus template ini?')"
                            class="btn delete">Hapus</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DATATABLES -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    const table = $('#datatable').DataTable({
        pageLength:20,
        lengthChange:false,
        ordering:true,
        info:true,
        language:{
            emptyTable:"Data tidak ditemukan",
            zeroRecords:"Data tidak ditemukan"
        }
    });

    $('#search').on('keyup', function () {
        table.search(this.value).draw();
    });
});
</script>

</body>
</html>
