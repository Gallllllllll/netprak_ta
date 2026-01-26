<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

/* ===============================
   CEK LOGIN MAHASISWA
================================ */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ".base_url('login.php'));
    exit;
}

/* ===============================
   FUNGSI NILAI HURUF
================================ */
function nilaiHuruf($nilai) {
    if ($nilai >= 85) return 'A';
    if ($nilai >= 75) return 'B';
    if ($nilai >= 65) return 'C';
    if ($nilai >= 50) return 'D';
    return 'E';
}

$pesan_error  = '';
$boleh_upload = false;

/* ===============================
   CEK TUGAS AKHIR
================================ */
$stmt = $pdo->prepare("
    SELECT id, judul_ta, status 
    FROM pengajuan_ta 
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$_SESSION['user']['id']]);
$ta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ta || $ta['status'] !== 'disetujui') {
    $pesan_error = "Tugas Akhir belum disetujui.";
} else {

    /* ===============================
       CEK SEMINAR PROPOSAL
    ================================ */
    $cek = $pdo->prepare("
        SELECT 
            sp.status,
            ns.nilai
        FROM pengajuan_sempro sp
        LEFT JOIN nilai_sempro ns 
            ON sp.id = ns.pengajuan_id
        WHERE sp.mahasiswa_id = ?
        ORDER BY sp.created_at DESC
        LIMIT 1
    ");
    $cek->execute([$_SESSION['user']['id']]);
    $sempro = $cek->fetch(PDO::FETCH_ASSOC);

    if (!$sempro || $sempro['status'] !== 'disetujui') {

        $pesan_error = "Seminar Proposal belum disetujui.";

    } elseif ($sempro['nilai'] === null) {

        $pesan_error = "Nilai Seminar Proposal belum tersedia.";

    } else {

        $nilai_huruf = nilaiHuruf($sempro['nilai']);

        if (in_array($nilai_huruf, ['D', 'E'])) {

            $pesan_error = "Anda tidak lulus Seminar Proposal, sehingga tidak dapat mengajukan Seminar Hasil.";

        } else {

            /* ===============================
               CEK SUDAH AJUKAN SEMHAS
            ================================ */
            $cek = $pdo->prepare("
                SELECT id 
                FROM pengajuan_semhas
                WHERE mahasiswa_id = ?
                LIMIT 1
            ");
            $cek->execute([$_SESSION['user']['id']]);

            if ($cek->rowCount() > 0) {
                $pesan_error = "Anda sudah mengajukan Seminar Hasil.";
            } else {
                $boleh_upload = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pengajuan Seminar Hasil</title>
<style>
.card{background:#fff;padding:26px;max-width:720px;border-radius:16px}
.alert{background:#fff7ed;color:#9a3412;padding:14px;border-radius:12px}
label{margin-top:14px;display:block;font-weight:600}
input[type=file]{margin-top:6px}
button{
    margin-top:20px;width:100%;padding:12px;border:none;border-radius:14px;
    background:linear-gradient(135deg,#FF74C7,#FF983D);
    color:#fff;font-weight:600
}
small{color:#6b7280}
</style>
</head>
<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
<div class="dashboard-header">
    <h1>Pengajuan Seminar Hasil</h1>
    <p>Unggah dokumen Seminar Hasil</p>
</div>

<div class="card">

<?php if ($pesan_error): ?>
    <div class="alert"><?= htmlspecialchars($pesan_error) ?></div>

<?php elseif ($boleh_upload): ?>
<form action="simpan.php" method="POST" enctype="multipart/form-data">

    <label>Lembar Berita Acara</label>
    <input type="file" name="file_berita_acara" accept=".pdf" required>
    <small>Format: PDF</small>

    <label>Persetujuan Laporan TA</label>
    <input type="file" name="file_persetujuan_laporan" accept=".pdf" required>
    <small>Format: PDF</small>

    <label>Form Pendaftaran Ujian TA</label>
    <input type="file" name="file_pendaftaran_ujian" accept=".pdf" required>
    <small>Format: PDF</small>

    <label>Buku Konsultasi TA</label>
    <input type="file" name="file_buku_konsultasi" accept=".pdf" required>
    <small>Format: PDF</small><br>

    <button type="submit">Ajukan Seminar Hasil</button>
</form>
<?php endif; ?>

</div>
</div>
</body>
</html>
