<?php
session_start();
require "../../config/connection.php";

if ($_SESSION['user']['role'] !== 'admin') die("Unauthorized");

$stmt = $pdo->query("
    SELECT p.*, m.nama
    FROM pengajuan_ta p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    ORDER BY p.created_at DESC
");

$data = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">
<h3>Daftar Pengajuan TA</h3>
<table border="1">
<tr>
    <th>Mahasiswa</th>
    <th>Judul</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
<?php foreach($data as $row): ?>
<tr>
    <td><?= $row['nama'] ?></td>
    <td><?= $row['judul_ta'] ?></td>
    <td><?= $row['status'] ?></td>
    <td>
        <a href="detail.php?id=<?= $row['id'] ?>">Detail</a>
    </td>
</tr>
<?php endforeach ?>
</table>
</div>