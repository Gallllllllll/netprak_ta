<?php
session_start();
require "../../config/connection.php";

// ===============================
// CEK ROLE ADMIN
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// ===============================
// AMBIL DATA SEMHAS
// ===============================
$stmt = $pdo->query("
    SELECT 
        s.id,
        s.id_semhas,
        m.nama,
        m.nim,
        s.status,
        p.judul_ta,
        s.tanggal_sidang
    FROM pengajuan_semhas s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    ORDER BY s.created_at DESC
");
$pengajuan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Pengajuan Seminar Hasil</title>

<style>
.card {
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
th { background:#eee; }

a.detail-link {
    color:#007bff;
    text-decoration:none;
    padding:4px 8px;
    border-radius:4px;
    font-size:13px;
    display:inline-block;
}
a.detail-link:hover { text-decoration:underline; }

a.nilai {
    color:#6f42c1;
    border:1px solid #6f42c1;
    margin-left:6px;
}

a.nilai:hover {
    background:#6f42c1;
    color:#fff;
}

a.penguji {
    color:#0d6efd;
    border:1px solid #0d6efd;
    margin-left:6px;
}

a.penguji:hover {
    background:#0d6efd;
    color:#fff;
}

a.penjadwalan {
    color:#28a745;
    margin-left:6px;
    border:1px solid #28a745;
}
a.penjadwalan:hover {
    background:#28a745;
    color:#fff;
}

.status-badge {
    padding:5px 10px;
    border-radius:4px;
    color:#fff;
    font-size:12px;
    display:inline-block;
}
.status-diajukan { background:#ffc107; }
.status-revisi { background:#fd7e14; }
.status-ditolak { background:#dc3545; }
.status-disetujui { background:#28a745; }
</style>
</head>

<body>

<div class="container">
<?php include "../sidebar.php"; ?>

<div class="main-content">
    <h1>Daftar Pengajuan Seminar Hasil</h1>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Semhas</th>
                    <th>Nama Mahasiswa</th>
                    <th>NIM</th>
                    <th>Judul TA</th>
                    <th>Status</th>
                    <th>Tanggal Sidang</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

            <?php if (empty($pengajuan_list)): ?>
                <tr>
                    <td colspan="8">Belum ada pengajuan Seminar Hasil.</td>
                </tr>
            <?php else:
                $no = 1;
                foreach ($pengajuan_list as $p):
                    $status_class = 'status-' . ($p['status'] ?? 'diajukan');
                    $tanggal_sidang = $p['tanggal_sidang']
                        ? date('d M Y', strtotime($p['tanggal_sidang']))
                        : '-';
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><b><?= htmlspecialchars($p['id_semhas']) ?></b></td>
                    <td><?= htmlspecialchars($p['nama']) ?></td>
                    <td><?= htmlspecialchars($p['nim']) ?></td>
                    <td><?= htmlspecialchars($p['judul_ta'] ?? '-') ?></td>
                    <td>
                        <span class="status-badge <?= $status_class ?>">
                            <?= htmlspecialchars($p['status'] ?? 'diajukan') ?>
                        </span>
                    </td>
                    <td><?= $tanggal_sidang ?></td>
                    <td>
                        <a class="detail-link" href="detail.php?id=<?= $p['id'] ?>">
                            Detail
                        </a>

                        <?php if (($p['status'] ?? '') === 'disetujui'): ?>

                            <a class="detail-link penguji"
                            href="detail.php?id=<?= $p['id'] ?>">
                                Plot Penguji
                            </a>

                            <a class="detail-link penjadwalan"
                            href="jadwal.php?id=<?= $p['id'] ?>">
                                Penjadwalan
                            </a>

                            <a class="detail-link nilai"
                            href="input_nilai_semhas.php?id=<?= $p['id'] ?>">
                                Input Nilai
                            </a>

                        <?php endif; ?>

                    </td>

                </tr>
            <?php endforeach; endif; ?>

            </tbody>
        </table>
    </div>
</div>
</div>

</body>
</html>
