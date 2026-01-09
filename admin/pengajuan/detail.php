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

// ambil data pengajuan
$stmt = $pdo->prepare("
    SELECT p.*, m.nama
    FROM pengajuan_ta p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    WHERE p.id=?
");
$stmt->execute([$id]);
$data = $stmt->fetch();
if (!$data) die("Data pengajuan tidak ditemukan.");

// ambil semua dosen
$dosen = $pdo->query("SELECT * FROM dosen")->fetchAll(PDO::FETCH_ASSOC);

// buat array file untuk loop
$files = [
    'bukti_pembayaran'=>'Bukti Pembayaran',
    'formulir_pendaftaran'=>'Formulir Pendaftaran',
    'transkrip_nilai'=>'Transkrip Nilai',
    'bukti_magang'=>'Bukti Kelulusan Magang'
];

// cek apakah semua file sudah disetujui
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pengajuan TA</title>
<link rel="stylesheet" href="../../style.css">
<style>
body { margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container { display:flex; min-height:100vh; }
.content { flex:1; padding:20px; }
.card { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
.card h3 { margin-top:0; }
table { width:100%; border-collapse:collapse; margin-bottom:15px; }
th, td { padding:10px; border:1px solid #ccc; vertical-align:top; }
th { background:#eee; text-align:left; width:200px; }
a.file-link { color:#007bff; text-decoration:none; }
a.file-link:hover { text-decoration:underline; }
select, textarea { width:100%; padding:8px; margin-top:5px; border:1px solid #ccc; border-radius:4px; }
textarea { min-height:60px; resize:none; }
button { padding:10px 20px; background:#28a745; color:#fff; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#218838; }
.btn-plot { display:inline-block; padding:10px 15px; border-radius:6px; color:#fff; text-decoration:none; margin-top:10px; }
.btn-plot.disabled { background:#ccc; pointer-events:none; cursor:not-allowed; }
.btn-plot.enabled { background:#007bff; }
</style>
</head>
<body>

<div class="container">
    <?php include "../sidebar.php"; ?>

    <div class="content">
        <h1>Detail Pengajuan TA</h1>

        <div class="card">
            <h3>Informasi Mahasiswa</h3>
            <p><b>Nama:</b> <?= htmlspecialchars($data['nama']) ?></p>
            <p><b>Judul TA:</b> <?= htmlspecialchars($data['judul_ta']) ?></p>
        </div>

        <div class="card">
            <h3>Verifikasi Semua Dokumen</h3>
            <form action="verifikasi_perfile.php" method="POST">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">
                <table>
                    <tr>
                        <th>Status & Catatan</th>
                        <th>Dokumen</th>
                    </tr>
                    <?php foreach($files as $field=>$label): 
                        if(!$data[$field]) continue;
                        $status_field = "status_$field";
                        $catatan_field = "catatan_$field";
                    ?>
                    <tr>
                        <td>
                            <label><?= $label ?></label>
                            <select name="status[<?= $field ?>]">
                                <option value="proses" <?= ($data[$status_field]??'')=='proses'?'selected':'' ?>>Proses</option>
                                <option value="revisi" <?= ($data[$status_field]??'')=='revisi'?'selected':'' ?>>Revisi</option>
                                <option value="ditolak" <?= ($data[$status_field]??'')=='ditolak'?'selected':'' ?>>Ditolak</option>
                                <option value="disetujui" <?= ($data[$status_field]??'')=='disetujui'?'selected':'' ?>>Disetujui</option>
                            </select>
                            <textarea name="catatan[<?= $field ?>]"><?= htmlspecialchars($data[$catatan_field]??'') ?></textarea>
                        </td>
                        <td>
                            <a class="file-link" href="../../uploads/ta/<?= htmlspecialchars($data[$field]) ?>" target="_blank"><?= $label ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <button type="submit">Simpan Semua Status</button>
            </form>
        </div>

        <div class="card">
            <h3>Plot Dosen Pembimbing</h3>
            <?php if($all_approved): ?>
                <a href="plot_dosbing.php?id=<?= $data['id'] ?>" class="btn-plot enabled">Plot Dosen</a>
            <?php else: ?>
                <span class="btn-plot disabled">Plot Dosen (aktifkan jika semua file disetujui)</span>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>
