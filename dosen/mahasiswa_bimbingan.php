<?php
session_start();
require "../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

// cek role dosen
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . base_url('login.php'));
    exit;
}

// ambil data mahasiswa bimbingan
$stmt = $pdo->prepare("
    SELECT 
        d.id AS dosbing_id,
        m.nama AS nama_mahasiswa,
        p.judul_ta,
        d.role,
        d.status_persetujuan,
        d.persetujuan_sempro
    FROM dosbing_ta d
    JOIN pengajuan_ta p ON d.pengajuan_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    WHERE d.dosen_id = ?
    ORDER BY m.nama ASC
");
$stmt->execute([$_SESSION['user']['id']]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Mahasiswa Bimbingan</title>

<link rel="stylesheet" href="<?= base_url('style.css') ?>">

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
}
.container {
    display: flex;
    min-height: 100vh;
}
.content {
    flex: 1;
    padding: 30px;
}
.card {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}
th {
    background: #f1f1f1;
}
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    color: #fff;
    font-size: 13px;
    font-weight: bold;
}
.badge-1 { background: #007bff; }
.badge-2 { background: #28a745; }
.badge-ok { background: #28a745; }
.badge-wait { background: #ffc107; color: #000; }

.btn-upload {
    padding: 6px 12px;
    background: #007bff;
    color: #fff;
    border-radius: 4px;
    text-decoration: none;
    font-size: 13px;
}
.btn-upload:hover {
    background: #0056b3;
}
</style>
</head>
<body>

<div class="container">

    <?php include "sidebar.php"; ?>

    <div class="content">
        <div class="card">
            <h2>Mahasiswa Bimbingan</h2>

            <?php if ($data): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Mahasiswa</th>
                            <th>Judul TA</th>
                            <th>Peran</th>
                            <th>Status Sempro</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($data as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                            <td><?= htmlspecialchars($row['judul_ta']) ?></td>
                            <td>
                                <?php if ($row['role'] === 'dosbing_1'): ?>
                                    <span class="badge badge-1">Pembimbing 1</span>
                                <?php else: ?>
                                    <span class="badge badge-2">Pembimbing 2</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status_persetujuan'] === 'disetujui'): ?>
                                    <span class="badge badge-ok">Disetujui</span>
                                <?php else: ?>
                                    <span class="badge badge-wait">Menunggu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status_persetujuan'] !== 'disetujui'): ?>
                                    <a href="upload_persetujuan_sempro.php?id=<?= $row['dosbing_id'] ?>" class="btn-upload">
                                        Upload Persetujuan
                                    </a>
                                <?php else: ?>
                                    <small>✔ Sudah Upload</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada mahasiswa bimbingan.</p>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
