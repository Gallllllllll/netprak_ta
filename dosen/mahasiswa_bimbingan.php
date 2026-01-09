<?php
session_start();
require "../config/connection.php";

$stmt = $pdo->prepare("
    SELECT m.nama, p.judul_ta
    FROM dosbing_ta d
    JOIN pengajuan_ta p ON d.pengajuan_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    WHERE d.dosen_id = ?
");

$stmt->execute([$_SESSION['user']['id']]);
$data = $stmt->fetchAll();
?>

<h3>Mahasiswa Bimbingan</h3>
<ul>
<?php foreach($data as $row): ?>
<li><?= $row['nama'] ?> - <?= $row['judul_ta'] ?></li>
<?php endforeach ?>
</ul>
