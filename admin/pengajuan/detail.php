<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

/* ===============================
   CEK ROLE ADMIN
================================ */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: " . base_url('login.php'));
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("ID pengajuan tidak diberikan.");

/* ===============================
   AMBIL DATA PENGAJUAN
================================ */
$stmt = $pdo->prepare("
    SELECT p.*, m.nama
    FROM pengajuan_ta p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    WHERE p.id=?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) die("Data pengajuan tidak ditemukan.");

/* ===============================
   LIST FILE
================================ */
$files = [
    'bukti_pembayaran'=>'Bukti Pembayaran',
    'formulir_pendaftaran'=>'Formulir Pendaftaran',
    'transkrip_nilai'=>'Bukti Transkrip Nilai',
    'bukti_magang'=>'Bukti Kelulusan Magang'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pengajuan TA</title>

<style>
:root{
    --primary:#FF8A8A;
    --secondary:#FFB27A;
    --border:#FFB47A;
    --bg:#FFF3E8;
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: #FFF1E5 !important;
    margin: 0;
    padding: 0;
}

.container {
    background: #FFF1E5 !important;
}

.main-content {
    margin-left: 280px;
    padding: 32px;
    min-height: 100vh;
    background: #FFF1E5 !important;
    transition: margin-left 0.3s ease, padding 0.3s ease;
}

/* ================= HEADER ================= */
.dashboard-header{
    background: linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(255, 95, 158, 0.2);
}

.dashboard-header h1{
    margin: 0;
    color: #fff !important;
    -webkit-text-fill-color: initial !important;
    background: none !important;
    -webkit-background-clip: initial !important;
    font-size: 24px;
    font-weight: 700;
}

.dashboard-header p{
    margin: 8px 0 0;
    color: rgba(255, 255, 255, 0.95) !important;
    font-size: 14px;
    font-weight: 500;
}

/* ================= CARD ================= */
.card{
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    border: 2px solid var(--border);
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
    font-weight: 700;
}

/* ================= INFO BOX ================= */
.info-box{
    border-radius: 14px;
    border: 2px solid var(--border);
    padding: 20px;
}

.info-box p{
    margin: 0 0 16px 0;
    font-size: 14px;
    line-height: 1.6;
}

.info-box p:last-child {
    margin-bottom: 0;
}

.info-box p b{
    display: block;
    color: #ff8c42;
    font-size: 13px;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ================= TABLE ================= */
.verification-table {
    width: 100%;
}

.doc-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #FFE5D9;
}

.doc-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

/* ================= STATUS BOX ================= */
.status-box{
    border: 2px solid var(--border);
    border-radius: 14px;
    padding: 16px;
    height: 100%;
}

.status-box b{
    display: block;
    margin-bottom: 10px;
    font-size: 14px;
    color: #333;
}

select, textarea{
    width: 100%;
    border-radius: 10px;
    padding: 10px 14px;
    border: 1px solid #fbc7a1;
    margin-top: 8px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.3s ease;
}

select:focus, textarea:focus {
    outline: none;
    border-color: #ff8c42;
}

textarea{
    resize: vertical;
    min-height: 80px;
}

/* ================= FILE CARD ================= */
.file-card{
    border: 2px solid var(--border);
    border-radius: 14px;
    padding: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    height: 100%;
}

.file-info{
    font-size: 13px;
    flex: 1;
}

.file-info b{
    display: block;
    color: #333;
    font-size: 14px;
    margin-bottom: 4px;
}

.file-info small {
    color: #999;
}

.file-link{
    background: #ffe2cf;
    padding: 8px 20px;
    border-radius: 999px;
    font-size: 12px;
    color: #FF7A00;
    text-decoration: none;
    font-weight: 600;
    white-space: nowrap;
    transition: all 0.3s ease;
}

.file-link:hover{
    background: #ffd3b5;
    transform: translateY(-2px);
}

/* ================= CATATAN ADMIN ================= */
.admin-notes {
    margin-top: 24px;
}

/* ================= BUTTON ================= */
.btn-submit{
    background: linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
    color: #fff;
    border: none;
    padding: 12px 32px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    margin-top: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(255, 95, 158, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(255, 95, 158, 0.4);
}

/* ================= RESPONSIVE - TABLET ================= */
@media (max-width: 1024px) {
    .main-content {
        margin-left: 70px;
        padding: 24px;
    }
}

/* ================= RESPONSIVE - MOBILE ================= */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 20px 16px;
    }

    .dashboard-header {
        padding: 20px;
        border-radius: 14px;
        margin-bottom: 20px;
    }

    .dashboard-header h1 {
        font-size: 20px;
    }

    .dashboard-header p {
        font-size: 13px;
    }

    .card {
        padding: 20px;
        margin-bottom: 20px;
    }

    .card h3 {
        font-size: 16px;
        margin-bottom: 16px;
    }

    /* Stack columns on mobile */
    .doc-row {
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 24px;
    }

    .info-box {
        padding: 16px;
    }

    .info-box p {
        font-size: 13px;
        margin-bottom: 14px;
    }

    .status-box {
        padding: 14px;
    }

    .file-card {
        padding: 14px;
        flex-direction: column;
        align-items: flex-start;
    }

    .file-link {
        align-self: stretch;
        text-align: center;
        padding: 10px;
    }

    .btn-submit {
        width: 100%;
        padding: 14px;
    }
}

/* ================= RESPONSIVE - SMALL MOBILE ================= */
@media (max-width: 480px) {
    .main-content {
        padding: 16px 12px;
    }

    .dashboard-header {
        padding: 16px;
    }

    .dashboard-header h1 {
        font-size: 18px;
    }

    .card {
        padding: 16px;
    }

    select, textarea {
        padding: 8px 12px;
        font-size: 12px;
    }
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<?php include "../sidebar.php"; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="dashboard-header">
        <h1>Detail Pengajuan TA</h1>
        <p>Verifikasi dokumen dan plot dosen pembimbing</p>
    </div>

    <!-- INFO MAHASISWA -->
    <div class="card info-box">
        <p><b>ID Pengajuan</b><?= htmlspecialchars($data['id_pengajuan']) ?></p>
        <p><b>Nama Mahasiswa</b><?= htmlspecialchars($data['nama']) ?></p>
        <p><b>Judul TA</b><?= htmlspecialchars($data['judul_ta']) ?></p>
    </div>

    <!-- VERIFIKASI -->
    <div class="card">
        <h3>Verifikasi Dokumen</h3>

        <form method="POST" action="verifikasi_perfile.php">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">

            <div class="verification-table">
                <?php foreach($files as $field=>$label):
                    if(!$data[$field]) continue;
                    $sf="status_$field";
                    $cf="catatan_$field";
                ?>
                <div class="doc-row">
                    <!-- Status Column -->
                    <div>
                        <div class="status-box">
                            <b><?= $label ?></b>
                            <select name="status[<?= $field ?>]">
                                <?php foreach(['diajukan','revisi','ditolak','disetujui'] as $s): ?>
                                    <option value="<?= $s ?>" <?= ($data[$sf]??'')==$s?'selected':'' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="catatan[<?= $field ?>]" placeholder="Tambahkan catatan..."><?= htmlspecialchars($data[$cf]??'') ?></textarea>
                        </div>
                    </div>

                    <!-- Document Column -->
                    <div>
                        <div class="file-card">
                            <div class="file-info">
                                <b><?= $label ?></b>
                                <small>PDF File</small>
                            </div>
                            <a target="_blank"
                               href="<?= base_url('uploads/ta/'.$data[$field]) ?>"
                               class="file-link">
                               Lihat
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- CATATAN ADMIN KESELURUHAN -->
            <div class="admin-notes">
                <div class="status-box">
                    <b>Catatan Admin (Keseluruhan)</b>
                    <textarea name="catatan_admin" placeholder="Tulis catatan umum untuk pengajuan TA ini..."><?= htmlspecialchars($data['catatan_admin'] ?? '') ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn-submit">Simpan Verifikasi</button>
        </form>
    </div>

</div>
</body>
</html>