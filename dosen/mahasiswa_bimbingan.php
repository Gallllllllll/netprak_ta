<?php
session_start();
require "../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

// ===============================
// CEK ROLE DOSEN
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . base_url('login.php'));
    exit;
}

// ===============================
// AMBIL DATA MAHASISWA BIMBINGAN + TANGGAL SIDANG
// ===============================
$stmt = $pdo->prepare("
    SELECT 
        d.id AS dosbing_id,
        m.nama AS nama_mahasiswa,
        p.judul_ta,
        d.role,
        d.status_persetujuan,
        d.persetujuan_sempro,
        s.tanggal_sidang
    FROM dosbing_ta d
    JOIN pengajuan_ta p ON d.pengajuan_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    LEFT JOIN pengajuan_semhas s ON s.mahasiswa_id = m.id
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
.card {
    background: #fff;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 10px rgba(0,0,0,.08);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    text-align: left;
}

th {
    background: #f9fafb;
    font-weight: 600;
}

/* ===============================
   BADGE
=============================== */
.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

/* peran */
.badge-1 { background: #0d6efd; color:#fff; }
.badge-2 { background: #198754; color:#fff; }

/* status */
.badge-ok   { background: #198754; color:#fff; }
.badge-wait { background: #ffc107; color:#000; }

/* tanggal sidang */
.badge-belum { background:#e5e7eb; color:#374151; }
.badge-hijau { background:#dcfce7; color:#166534; }
.badge-kuning{ background:#fef3c7; color:#92400e; }
.badge-merah { background:#fee2e2; color:#991b1b; }

.btn-upload {
    padding: 7px 14px;
    background: #0d6efd;
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
}

.btn-upload:hover {
    background: #0b5ed7;
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

        <?php if ($data): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Mahasiswa</th>
                    <th>Judul TA</th>
                    <th>Peran</th>
                    <th>Status Sempro</th>
                    <th>Tanggal Sidang</th>
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
                        <?= $row['role'] === 'dosbing_1'
                            ? '<span class="badge badge-1">Pembimbing 1</span>'
                            : '<span class="badge badge-2">Pembimbing 2</span>' ?>
                    </td>

                    <!-- STATUS SEMPRO -->
                    <td>
                        <?= $row['status_persetujuan'] === 'disetujui'
                            ? '<span class="badge badge-ok">Disetujui</span>'
                            : '<span class="badge badge-wait">Menunggu</span>' ?>
                    </td>

                    <!-- TANGGAL SIDANG -->
                    <td>
                        <?php
                        if (!$row['tanggal_sidang']) {
                            echo '<span class="badge badge-belum">Belum Dijadwalkan</span>';
                        } else {
                            $today  = new DateTime();
                            $sidang = new DateTime($row['tanggal_sidang']);
                            $selisih = $today->diff($sidang)->days;
                            $isLewat = $sidang < $today;

                            if ($isLewat) {
                                $badge = 'badge-merah';
                                $label = 'Terlewat';
                            } elseif ($selisih <= 3) {
                                $badge = 'badge-merah';
                                $label = 'H-' . $selisih;
                            } elseif ($selisih <= 7) {
                                $badge = 'badge-kuning';
                                $label = 'H-' . $selisih;
                            } else {
                                $badge = 'badge-hijau';
                                $label = date('d M Y', strtotime($row['tanggal_sidang']));
                            }

                            echo "<span class='badge $badge'>$label</span>";
                        }
                        ?>
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

</body>
</html>
