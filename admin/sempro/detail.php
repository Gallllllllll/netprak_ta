<?php
session_start();
require "../../config/connection.php";

// cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("ID pengajuan tidak diberikan.");

// ambil data pengajuan sempro + mahasiswa + judul TA
$stmt = $pdo->prepare("
    SELECT s.*, m.nama, p.judul_ta
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Data pengajuan tidak ditemukan.");

// daftar file sempro
$files = [
    'pendaftaran'     => 'Form Pendaftaran',
    'persetujuan'     => 'Persetujuan Proposal',
    'buku_konsultasi' => 'Buku Konsultasi'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Pengajuan Sempro</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root{
    --primary:#FF8A8A;
    --secondary:#FFB27A;
    --border:#FFB47A;
    --bg:#FFF3E8;
}

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

/* ================= HEADER ================= */
.dashboard-header{
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    padding:20px 24px;
    border-radius:14px;
    margin-bottom:20px;
    background:linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
}
.dashboard-header h1{
    margin:0;
    color:#fff !important;
    -webkit-text-fill-color: initial !important;
    background: none !important;
    -webkit-background-clip: initial !important;
    font-size: 20px;
}
.dashboard-header p{
    margin:6px 0 0;
    color:#fff !important;
    font-size:14px;
}

/* ================= CARD ================= */
.card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    border:2px solid var(--border);
    margin-bottom:20px;
}

/* ================= INFO ================= */
.info-box{
    border-radius:14px;
    border:2px solid var(--border);
    padding:16px;
}
.info-box p{
    margin:6px 0;
    font-size:14px;
}

/* ================= TABLE ================= */
table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 16px;
}
th{
    text-align:left;
    padding-bottom:10px;
    border-bottom:2px solid #3b82f6;
    font-size:14px;
}
td{
    vertical-align:top;
}

/* ================= STATUS ================= */
.status-box{
    border:2px solid var(--border);
    border-radius:14px;
    padding:14px;
}
.status-box b{
    display:block;
    margin-bottom:6px;
    font-size:14px;
}
select, textarea{
    width:100%;
    border-radius:10px;
    padding:8px 0px;
    border:1px solid #fbc7a1;
    margin-top:6px;
    font-size:13px;
}
textarea{
    resize:none;
    height:60px;
}

/* ================= FILE CARD ================= */
.file-card{
    border:2px solid var(--border);
    border-radius:14px;
    padding:14px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}
.file-info{
    font-size:13px;
}
.file-info b{
    display:block;
}
.file-link{
    background:#ffe2cf;
    padding:6px 16px;
    border-radius:999px;
    font-size:12px;
    color:#FF7A00;
    text-decoration:none;
    font-weight:600;
}
.file-link:hover{
    background:#ffd3b5;
}

/* ================= BUTTON ================= */
button{
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    color:#fff;
    border:none;
    padding:10px 24px;
    border-radius:999px;
    font-weight:600;
    cursor:pointer;
    margin-top:10px;
    background:linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
}

/* ================= RESPONSIVE ================= */
@media (max-width:768px){
    table, thead, tbody, tr, td, th{
        display:block;
        width:100%;
    }
    th{
        border:none;
        margin-bottom:10px;
    }
    tr{
        margin-bottom:18px;
    }
}
</style>
</head>
<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="dashboard-header">
        <h1>Detail Pengajuan Seminar Proposal</h1>
        <p>Verifikasi dokumen seminar proposal mahasiswa</p>
    </div>

    <!-- INFO MAHASISWA -->
    <div class="card info-box">
        <p><b>ID Seminar Proposal</b><br><?= htmlspecialchars($data['id_sempro'] ?? '-') ?></p>
        <p><b>Nama Mahasiswa</b><br><?= htmlspecialchars($data['nama']) ?></p>
        <p><b>Judul TA</b><br><?= htmlspecialchars($data['judul_ta']) ?></p>
        <p><b>Status Keseluruhan</b><br><?= strtoupper($data['status']) ?></p>
    </div>

    <!-- VERIFIKASI -->
    <div class="card">
        <h3>Verifikasi Dokumen Per File</h3>

        <form action="verifikasi_sempro_perfile.php" method="POST">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">

            <table>
                <tr>
                    <th>Status dan Catatan</th>
                    <th>Dokumen</th>
                </tr>

                <?php foreach ($files as $key => $label):
                    $file_field     = "file_$key";
                    $status_field   = "status_file_$key";
                    $catatan_field  = "catatan_file_$key";
                    $st = $data[$status_field] ?? 'diajukan';
                ?>
                <tr>
                    <td>
                        <div class="status-box">
                            <b><?= $label ?></b>
                            <select name="status[<?= $key ?>]">
                                <?php foreach(['diajukan','revisi','ditolak','disetujui'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $st==$s?'selected':'' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="catatan_file[<?= $key ?>]" placeholder="Catatan admin..."><?= htmlspecialchars($data[$catatan_field] ?? '') ?></textarea>
                        </div>
                    </td>
                    <td>
                        <div class="file-card">
                            <div class="file-info">
                                <b><?= $label ?></b>
                                <?php if (!empty($data[$file_field])): ?>
                                    PDF File
                                <?php else: ?>
                                    <em>File belum diupload</em>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($data[$file_field])): ?>
                                <a target="_blank"
                                   href="../../uploads/sempro/<?= htmlspecialchars($data[$file_field]) ?>"
                                   class="file-link">
                                   Lihat
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <div class="status-box" style="margin-top:20px;">
                <b>Catatan Admin (Keseluruhan)</b>
                <textarea name="catatan" placeholder="Catatan untuk keseluruhan pengajuan..."><?= htmlspecialchars($data['catatan'] ?? '') ?></textarea>
            </div>

            <button type="submit">Simpan Verifikasi</button>
        </form>
    </div>
</div>

</body>
</html>
