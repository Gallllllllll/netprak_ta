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

// ambil data pengajuan semhas + mahasiswa
$stmt = $pdo->prepare("
    SELECT s.*, m.nama, p.judul_ta
    FROM pengajuan_semhas s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) die("Data pengajuan tidak ditemukan.");

// daftar file SEMHAS
$files = [
    'berita_acara'        => 'Berita Acara Seminar Hasil',
    'persetujuan_laporan' => 'Persetujuan Laporan TA (Form 5)',
    'pendaftaran_ujian'   => 'Form Pendaftaran Ujian TA (Form 7)',
    'buku_konsultasi'     => 'Buku Konsultasi TA (Form 4)'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Pengajuan Seminar Hasil</title>
<link rel="stylesheet" href="../../style.css">

<style>
.card {
    background:#fff;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 2px 6px rgba(0,0,0,0.08);
}

table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

th, td {
    padding:12px;
    border:1px solid #ddd;
    vertical-align:top;
}

th {
    background:#f1f5f9;
    width:240px;
}

select, textarea {
    width:100%;
    padding:8px;
    margin-top:6px;
    border-radius:6px;
    border:1px solid #ccc;
}

textarea {
    min-height:70px;
    resize:none;
}

button {
    margin-top:15px;
    padding:10px 22px;
    background:#16a34a;
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:600;
}

button:hover {
    background:#15803d;
}

a.file-link {
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
}

a.file-link:hover {
    text-decoration:underline;
}
</style>
</head>
<body>

<?php include "../sidebar.php"; ?>

<!-- =========================
     MAIN CONTENT (ANTI TABRAK)
========================= -->
<div class="main-content">

    <div class="dashboard-header">
        <h1>Detail Pengajuan Seminar Hasil</h1>
        <p>Verifikasi dokumen seminar hasil mahasiswa</p>
    </div>

    <div class="card">
        <p><b>Nama Mahasiswa:</b><br><?= htmlspecialchars($data['nama']) ?></p>
        <p><b>Judul Tugas Akhir:</b><br><?= htmlspecialchars($data['judul_ta']) ?></p>
        <p><b>Status Keseluruhan:</b>
            <span style="font-weight:600;">
                <?= strtoupper($data['status']) ?>
            </span>
        </p>
        <p><b>Catatan Admin:</b><br>
            <?= $data['catatan'] ? htmlspecialchars($data['catatan']) : '-' ?>
        </p>
    </div>

    <div class="card">
        <h3>Verifikasi Dokumen Per File</h3>

        <form action="verifikasi_semhas_perfile.php" method="POST">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">

            <table>
                <tr>
                    <th>Status & Catatan</th>
                    <th>File</th>
                </tr>

                <?php foreach ($files as $key => $label):
                    $file_field     = "file_$key";
                    $status_field   = "status_file_$key";
                    $catatan_field = "catatan_file_$key";
                ?>
                <tr>
                    <td>
                        <b><?= $label ?></b>

                        <select name="status[<?= $key ?>]">
                            <option value="pending" <?= ($data[$status_field] ?? '')=='pending'?'selected':'' ?>>Pending</option>
                            <option value="revisi" <?= ($data[$status_field] ?? '')=='revisi'?'selected':'' ?>>Revisi</option>
                            <option value="valid" <?= ($data[$status_field] ?? '')=='valid'?'selected':'' ?>>Valid</option>
                        </select>


                        <textarea name="catatan[<?= $key ?>]"><?= htmlspecialchars($data[$catatan_field] ?? '') ?></textarea>
                    </td>
                    <td>
                        <?php if (!empty($data[$file_field])): ?>
                            <a class="file-link"
                               href="../../uploads/semhas/<?= htmlspecialchars($data[$file_field]) ?>"
                               target="_blank">
                               Lihat Dokumen
                            </a>
                        <?php else: ?>
                            <em>File belum diunggah</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <button type="submit">Simpan Status Verifikasi</button>
        </form>
    </div>

</div>

</body>
</html>
