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

$id = $_GET['id'] ?? null;
if (!$id) die("ID pengajuan tidak diberikan.");

// ===============================
// AMBIL DATA SEMHAS + MAHASISWA + TA
// ===============================
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        m.nama,
        m.nim,
        p.judul_ta
    FROM pengajuan_semhas s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die('Data pengajuan Seminar Hasil tidak ditemukan.');
}

// ===============================
// CEK PENGUJI
// ===============================
$stmt = $pdo->prepare("
    SELECT t.*, d.nama
    FROM tim_semhas t
    JOIN dosen d ON t.dosen_id = d.id
    WHERE t.pengajuan_id = ? AND t.peran = 'penguji'
");
$stmt->execute([$id]);
$penguji = $stmt->fetch(PDO::FETCH_ASSOC);

// ===============================
// DAFTAR DOSEN
// ===============================
$dosenList = $pdo->query("
    SELECT id, nama
    FROM dosen
    ORDER BY nama
")->fetchAll(PDO::FETCH_ASSOC);

// ===============================
// DAFTAR FILE SEMHAS
// ===============================
$files = [
    'berita_acara'        => 'Berita Acara Seminar Hasil',
    'persetujuan_laporan' => 'Persetujuan Laporan TA',
    'pendaftaran_ujian'   => 'Form Pendaftaran Ujian TA',
    'buku_konsultasi'     => 'Buku Konsultasi TA'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Pengajuan Seminar Hasil</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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
    font-size:20px;
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
    box-shadow:0 2px 6px rgba(0,0,0,0.08);
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

/* ================= BADGE (for Plot Dosen) ================= */
.badge {
    display:inline-block;
    padding:4px 10px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
}
.badge-disetujui{ background:#dcfce7; color:#166534; }

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
        <h1>Detail Pengajuan Seminar Hasil</h1>
        <p>Verifikasi dokumen Seminar Hasil mahasiswa</p>
    </div>

<!-- ===============================
     INFORMASI UTAMA
=============================== -->
<div class="card">
    <p><b>ID Pengajuan Semhas:</b><br><?= htmlspecialchars($data['id_semhas']) ?></p>
    <p><b>Nama Mahasiswa:</b><br><?= htmlspecialchars($data['nama']) ?> (<?= htmlspecialchars($data['nim']) ?>)</p>
    <p><b>Judul Tugas Akhir:</b><br><?= htmlspecialchars($data['judul_ta']) ?></p>
    <p>
        <b>Status Keseluruhan:</b><br>
        <span class="badge badge-<?= $data['status'] ?>">
            <?= strtoupper($data['status']) ?>
        </span>


    </p>
    <form action="simpan_catatan_semhas.php" method="POST">
    <input type="hidden" name="id" value="<?= $data['id'] ?>">
</form>

</div>

<?php if ($data['status'] === 'disetujui'): ?>
<div class="card">
    <h3>Plot Dosen Penguji</h3>

    <?php if ($penguji): ?>
        <p>
            <b>Dosen Penguji:</b><br>
            <?= htmlspecialchars($penguji['nama']) ?>
        </p>
        <span class="badge badge-disetujui">Sudah Diplot</span>
    <?php else: ?>
        <form action="simpan_penguji.php" method="POST">
            <input type="hidden" name="pengajuan_id" value="<?= $id ?>">

                <label><b>Pilih Dosen Penguji</b></label>
                <select name="dosen_id" required>
                    <option value="">-- Pilih Dosen --</option>
                    <?php foreach ($dosenList as $d): ?>
                        <option value="<?= $d['id'] ?>">
                            <?= htmlspecialchars($d['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Simpan Penguji</button>
            </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>


<!-- ===============================
     VERIFIKASI FILE
=============================== -->
<div class="card">
<h3>Verifikasi Dokumen Per File</h3>

        <form action="verifikasi_semhas_perfile.php" method="POST">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">

<table>
<tr>
    <th>Status & Catatan</th>
    <th>Dokumen</th>
</tr>

<?php foreach ($files as $key => $label):
    $file_field    = "file_$key";
    $status_field  = "status_file_$key";
    $catatan_field = "catatan_file_$key";
?>
<tr>
<td>
    <b><?= $label ?></b>

    <select name="status[<?= $key ?>]">
        <option value="diajukan" <?= $data[$status_field]==='diajukan'?'selected':'' ?>>Diajukan</option>
        <option value="revisi" <?= $data[$status_field]==='revisi'?'selected':'' ?>>Revisi</option>
        <option value="disetujui" <?= $data[$status_field]==='disetujui'?'selected':'' ?>>Disetujui</option>
        <option value="ditolak" <?= $data[$status_field]==='ditolak'?'selected':'' ?>>Ditolak</option>
    </select>


    <textarea name="catatan_file[<?= $key ?>]"
        placeholder="Catatan untuk mahasiswa..."><?= htmlspecialchars($data[$catatan_field] ?? '') ?>
    </textarea>
</td>
<td>
<?php if (!empty($data[$file_field])): ?>
    <a class="file-link" href="../../uploads/semhas/<?= htmlspecialchars($data[$file_field]) ?>" target="_blank">
        Lihat Dokumen
    </a>
<?php else: ?>
    <em>File belum diunggah</em>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>

</table>
<div class="status-box" style="margin-top:20px;">
    <b>Catatan Admin (Keseluruhan)</b>
    <textarea name="catatan" placeholder="Catatan untuk keseluruhan pengajuan..."><?= htmlspecialchars($data['catatan'] ?? '') ?></textarea>
</div>

<button type="submit">Simpan Status Verifikasi</button>
</form>
</div>

</div>

</body>
</html>

