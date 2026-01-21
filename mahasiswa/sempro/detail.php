<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

// ===============================
// CEK LOGIN
// ===============================
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','mahasiswa'])) {
    header("Location: ".base_url('login.php'));
    exit;
}

// ===============================
// AMBIL ID PENGAJUAN
// ===============================
$id = $_GET['id'] ?? null;
if (!$id) die("ID pengajuan tidak diberikan.");

// ===============================
// AMBIL DATA PENGAJUAN SEMPRO
// ===============================
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        m.nama AS mahasiswa_nama
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Data pengajuan tidak ditemukan.");

// ===============================
// DAFTAR DOKUMEN (FIELD => LABEL)
// ===============================
$files = [
    'file_pendaftaran'     => 'Form Pendaftaran',
    'file_persetujuan'     => 'Persetujuan Proposal',
    'file_buku_konsultasi' => 'Buku Konsultasi'
];

// ===============================
// STATUS CLASS
// ===============================
$status_class_map = [
    'proses'    => 'status-proses',
    'diajukan'  => 'status-proses',
    'disetujui' => 'status-disetujui',
    'ditolak'   => 'status-ditolak',
    'revisi'    => 'status-revisi'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Pengajuan Seminar Proposal</title>
<link rel="stylesheet" href="../../style.css">

<style>
body { margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container { display:flex; min-height:100vh; }
.main-content { flex:1; padding:20px; }
.card { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; border:1px solid #ccc; vertical-align:top; }
th { background:#eee; width:180px; }
.status { display:inline-block; padding:5px 12px; border-radius:20px; font-weight:bold; color:#fff; font-size:13px; }
.status-proses { background:#ffc107; color:#000; }
.status-disetujui { background:#28a745; }
.status-ditolak { background:#dc3545; }
.status-revisi { background:#17a2b8; }
.file-link { color:#007bff; text-decoration:none; }
.file-link:hover { text-decoration:underline; }
.id-badge {
    display:inline-block;
    padding:6px 14px;
    border-radius:20px;
    background:#1f2937;
    color:#fff;
    font-weight:bold;
    font-size:13px;
}

/* ====== BUTTON REVISI ====== */
.btn-revisi {
    display:inline-block;
    padding:10px 16px;
    background:#dc3545;
    color:#fff;
    text-decoration:none;
    border-radius:4px;
    font-weight:bold;
    margin-top: 14px;
}
.btn-revisi:hover {
    background:#c82333;
}
</style>
</head>

<body>
<div class="container">

<?php include "../sidebar.php"; ?>

<div class="main-content">

<h1>Detail Pengajuan Seminar Proposal</h1>

<!-- =============================== -->
<!-- INFORMASI PENGAJUAN -->
<!-- =============================== -->
<div class="card">
    <p>
        <b>ID Seminar Proposal:</b><br>
        <span class="id-badge"><?= htmlspecialchars($data['id_sempro']) ?></span>
    </p>

    <p><b>Nama Mahasiswa:</b> <?= htmlspecialchars($data['mahasiswa_nama']) ?></p>
    <p><b>Tanggal Pengajuan:</b> <?= htmlspecialchars($data['created_at']) ?></p>

    <p><b>Tanggal Seminar:</b> <?= htmlspecialchars($data['tanggal_sempro'] ?? '-') ?></p>
    <p><b>Jam Seminar:</b> <?= htmlspecialchars($data['jam_sempro'] ?? '-') ?></p>
    <p><b>Ruangan:</b> <?= htmlspecialchars($data['ruangan_sempro'] ?? '-') ?></p>

    <p>
        <b>Status Pengajuan:</b><br>
        <span class="status <?= $status_class_map[$data['status']] ?>">
            <?= strtoupper($data['status']) ?>
        </span>
    </p>

    <p><b>Catatan Admin:</b><br>
        <?= $data['catatan'] ? htmlspecialchars($data['catatan']) : '-' ?>
    </p>

    <!-- ====== BUTTON UPLOAD REVISI ====== -->
    <?php if (strtolower($data['status']) === 'revisi' && $_SESSION['user']['role'] === 'mahasiswa'): ?>
        <a class="btn-revisi" href="<?= base_url('mahasiswa/sempro/revisi.php?id=' . $data['id']) ?>">
            Upload Revisi
        </a>
    <?php endif; ?>
</div>

<!-- =============================== -->
<!-- DOKUMEN -->
<!-- =============================== -->
<div class="card">
<h3>Dokumen Persyaratan</h3>

<table>
<tr>
    <th>Dokumen</th>
    <th>File</th>
    <th>Status</th>
    <th>Catatan</th>
</tr>

<?php foreach ($files as $field => $label): ?>
<tr>
    <td><?= $label ?></td>

    <td>
        <?php if (!empty($data[$field])): ?>
            <a href="../../uploads/sempro/<?= htmlspecialchars($data[$field]) ?>"
               target="_blank" class="file-link">
               Lihat File
            </a>
        <?php else: ?>
            -
        <?php endif; ?>
    </td>

    <td>
        <?php
        $status_file = $data['status_'.$field] ?? 'proses';
        ?>
        <span class="status <?= $status_class_map[$status_file] ?>">
            <?= strtoupper($status_file) ?>
        </span>
    </td>

    <td>
        <?= !empty($data['catatan_'.$field])
            ? htmlspecialchars($data['catatan_'.$field])
            : '-' ?>
    </td>
</tr>
<?php endforeach; ?>

</table>
</div>

</div>
</div>
</body>
</html>
