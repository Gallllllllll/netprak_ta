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
<title>Detail Pengajuan TA</title>

<style>
:root{
    --primary:#FF8A8A;
    --secondary:#FFB27A;
    --border:#FFB47A;
    --bg:#FFF3E8;
}

body{
    margin:0;
    background:var(--bg);
    font-family:system-ui,-apple-system,BlinkMacSystemFont,sans-serif;
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

<!-- SIDEBAR -->
<?php include "../sidebar.php"; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="dashboard-header">
        <h1>Detail Pengajuan TA</h1>
        <p>Verifikasi dokumen dan plot dosen pembimbing</p>
    </div>

    <!-- INFO MAHASISWA -->
    
    <div class="card info-box ">
    <p><b>ID Pengajuan</b><br><?= htmlspecialchars($data['id_pengajuan']) ?></p>
    <p><b>Nama Mahasiswa</b><br><?= htmlspecialchars($data['nama']) ?></p>
    <p><b>Judul TA</b><br><?= htmlspecialchars($data['judul_ta']) ?></p>
   </div>


    <!-- VERIFIKASI -->
    <div class="card">
        <h3>Verifikasi Dokumen</h3>

        <form method="POST" action="verifikasi_perfile.php">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">

            <table>
                <tr>
                    <th>Status dan Catatan</th>
                    <th>Dokumen</th>
                </tr>

                <?php foreach($files as $field=>$label):
                    if(!$data[$field]) continue;
                    $sf="status_$field";
                    $cf="catatan_$field";
                ?>
                <tr>
                    <td>
                        <div class="status-box">
                            <b><?= $label ?></b>
                            <select name="status[<?= $field ?>]">
                                <?php foreach(['diajukan','revisi','ditolak','disetujui'] as $s): ?>
                                    <option value="<?= $s ?>" <?= ($data[$sf]??'')==$s?'selected':'' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="catatan[<?= $field ?>]"><?= htmlspecialchars($data[$cf]??'') ?></textarea>
                        </div>
                    </td>
                    <td>
                        <div class="file-card">
                            <div class="file-info">
                                <b><?= $label ?></b>
                                PDF File
                            </div>
                            <a target="_blank"
                               href="<?= base_url('uploads/ta/'.$data[$field]) ?>"
                               class="file-link">
                               Lihat
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <!-- CATATAN ADMIN KESELURUHAN -->
            <div class="status-box" style="margin-top:20px">
                <b>Catatan Admin (Keseluruhan)</b>
                <textarea name="catatan_admin"
                        placeholder="Tulis catatan umum untuk pengajuan TA ini..."><?= htmlspecialchars($data['catatan_admin'] ?? '') ?></textarea>
            </div>
            <button type="submit">Simpan Verifikasi</button>

        </form>
    </div>

</div>
</body>
</html>
