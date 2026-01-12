<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

// cek role mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . base_url('login.php'));
    exit;
}

$id = $_GET['id'] ?? 0;

// ambil data pengajuan + dosen pembimbing
$stmt = $pdo->prepare("
    SELECT 
        p.*,

        MAX(CASE WHEN db.role = 'dosbing_1' THEN d.nama END) AS dosen1_nama,
        MAX(CASE WHEN db.role = 'dosbing_2' THEN d.nama END) AS dosen2_nama

    FROM pengajuan_ta p
    LEFT JOIN dosbing_ta db ON p.id = db.pengajuan_id
    LEFT JOIN dosen d ON db.dosen_id = d.id

    WHERE p.id = ? AND p.mahasiswa_id = ?
    GROUP BY p.id
");
$stmt->execute([$id, $_SESSION['user']['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$data) {
    die("Pengajuan tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Pengajuan TA</title>

<link rel="stylesheet" href="<?= base_url('style.css') ?>">

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    display: flex;
}
.content {
    flex: 1;
    padding: 30px;
    background: #f4f6f9;
    min-height: 100vh;
}
.card {
    background: #fff;
    padding: 25px;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,.1);
    max-width: 800px;
}
.card h1 {
    margin-top: 0;
    margin-bottom: 20px;
}
.status {
    padding: 6px 12px;
    border-radius: 4px;
    font-weight: bold;
    display: inline-block;
}
.status.proses { background:#ffeeba; color:#856404; }
.status.disetujui { background:#d4edda; color:#155724; }
.status.ditolak { background:#f8d7da; color:#721c24; }
.status.revisi { background:#d1ecf1; color:#0c5460; }

.dokumen a {
    display: block;
    margin: 8px 0;
    color: #007bff;
    text-decoration: none;
}
.dokumen a:hover {
    text-decoration: underline;
}

.btn-revisi {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 16px;
    background: #dc3545;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
}
.btn-revisi:hover {
    background: #c82333;
}

ul.dosen {
    margin: 8px 0 0 20px;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/coba/mahasiswa/sidebar.php'; ?>

<!-- CONTENT -->
<div class="content">
    <div class="card">

        <h1>Detail Pengajuan TA</h1>

        <p><b>Judul TA:</b><br>
            <?= htmlspecialchars($data['judul_ta']) ?>
        </p>

        <p><b>Status:</b><br>
            <span class="status <?= strtolower($data['status']) ?>">
                <?= strtoupper($data['status']) ?>
            </span>
        </p>

        <p><b>Catatan Admin / Dosen:</b><br>
            <?= $data['catatan_admin']
                ? htmlspecialchars($data['catatan_admin'])
                : '-' ?>
        </p>

        <?php if (strtolower($data['status']) === 'disetujui'): ?>
            <p><b>Dosen Pembimbing:</b></p>
            <ul class="dosen">
                <li>
                    Pembimbing 1:
                    <?= $data['dosen1_nama']
                        ? htmlspecialchars($data['dosen1_nama'])
                        : '-' ?>
                </li>
                <li>
                    Pembimbing 2:
                    <?= $data['dosen2_nama']
                        ? htmlspecialchars($data['dosen2_nama'])
                        : '-' ?>
                </li>
            </ul>
        <?php endif; ?>

        <h3>Dokumen</h3>
        <div class="dokumen">
            <?php if ($data['bukti_pembayaran']): ?>
                <a href="<?= base_url('uploads/ta/' . $data['bukti_pembayaran']) ?>" target="_blank">
                    📄 Bukti Pembayaran
                </a>
            <?php endif; ?>

            <?php if ($data['formulir_pendaftaran']): ?>
                <a href="<?= base_url('uploads/ta/' . $data['formulir_pendaftaran']) ?>" target="_blank">
                    📄 Formulir Pendaftaran
                </a>
            <?php endif; ?>

            <?php if ($data['transkrip_nilai']): ?>
                <a href="<?= base_url('uploads/ta/' . $data['transkrip_nilai']) ?>" target="_blank">
                    📄 Transkrip Nilai
                </a>
            <?php endif; ?>

            <?php if ($data['bukti_magang']): ?>
                <a href="<?= base_url('uploads/ta/' . $data['bukti_magang']) ?>" target="_blank">
                    📄 Bukti Kelulusan Magang
                </a>
            <?php endif; ?>
        </div>

        <?php if (strtolower($data['status']) === 'revisi'): ?>
            <a class="btn-revisi"
               href="<?= base_url('mahasiswa/pengajuan/revisi_ta.php?id=' . $data['id']) ?>">
                Upload Revisi
            </a>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
