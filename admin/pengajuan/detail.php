<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

// cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: " . base_url('login.php'));
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("ID pengajuan tidak diberikan.");

// ambil data pengajuan
$stmt = $pdo->prepare("
    SELECT p.*, m.nama
    FROM pengajuan_ta p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    WHERE p.id=?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) die("Data pengajuan tidak ditemukan.");

// list file
$files = [
    'bukti_pembayaran'=>'Bukti Pembayaran',
    'formulir_pendaftaran'=>'Formulir Pendaftaran',
    'transkrip_nilai'=>'Transkrip Nilai',
    'bukti_magang'=>'Bukti Kelulusan Magang'
];

// cek semua disetujui
$all_approved = (
    ($data['status_bukti_pembayaran']??'')==='disetujui' &&
    ($data['status_formulir_pendaftaran']??'')==='disetujui' &&
    ($data['status_transkrip_nilai']??'')==='disetujui' &&
    ($data['status_bukti_magang']??'')==='disetujui'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Pengajuan TA</title>

<style>
.card {
    background:#fff;
    padding:24px;
    border-radius:16px;
    border:1px solid #f1dcdc;
    margin-bottom:20px;
}

table {
    width:100%;
    border-collapse:collapse;
}
th, td {
    padding:12px;
    border:1px solid #e5e7eb;
    vertical-align:top;
}
th {
    background:#f9fafb;
    width:240px;
}

select, textarea {
    width:100%;
    padding:8px;
    border-radius:8px;
    border:1px solid #d1d5db;
    margin-top:6px;
}

textarea { resize:none; }

.file-link {
    color:#FF983D;
    font-weight:600;
    text-decoration:none;
}

.file-link:hover { text-decoration:underline; }

button {
    margin-top:16px;
    padding:10px 18px;
    border:none;
    border-radius:12px;
    background:linear-gradient(135deg,#FF74C7,#FF983D);
    color:#fff;
    font-weight:600;
    cursor:pointer;
}

.btn-plot {
    display:inline-block;
    padding:10px 18px;
    border-radius:12px;
    font-weight:600;
    text-decoration:none;
}

.btn-plot.enabled {
    background:#10b981;
    color:#fff;
}

.btn-plot.disabled {
    background:#d1d5db;
    color:#6b7280;
    cursor:not-allowed;
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
    <div class="card">
        <p>
            <b>ID Pengajuan</b><br>
            <span style="background:#f3f4f6;padding:6px 12px;border-radius:8px;font-weight:bold;">
                <?= htmlspecialchars($data['id_pengajuan']) ?>
            </span>
        </p>

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
                    <th>Status & Catatan</th>
                    <th>Dokumen</th>
                </tr>

                <?php foreach($files as $field=>$label):
                    if(!$data[$field]) continue;
                    $sf="status_$field";
                    $cf="catatan_$field";
                ?>
                <tr>
                    <td>
                        <b><?= $label ?></b>
                        <select name="status[<?= $field ?>]">
                            <?php foreach(['proses','revisi','ditolak','disetujui'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($data[$sf]??'')==$s?'selected':'' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <textarea name="catatan[<?= $field ?>]"><?= htmlspecialchars($data[$cf]??'') ?></textarea>
                    </td>
                    <td>
                        <a class="file-link" target="_blank"
                           href="<?= base_url('uploads/ta/'.$data[$field]) ?>">
                            Lihat <?= $label ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <button type="submit">Simpan Verifikasi</button>
        </form>
    </div>

    <!-- PLOT DOSEN -->
    <div class="card">
        <h3>Plot Dosen Pembimbing</h3>
        <?php if($all_approved): ?>
            <a class="btn-plot enabled"
               href="plot_dosbing.php?id=<?= $data['id'] ?>">
                Plot Dosen
            </a>
        <?php else: ?>
            <span class="btn-plot disabled">
                Semua dokumen harus disetujui
            </span>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
