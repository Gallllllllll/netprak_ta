<?php
session_start();
require "../../config/connection.php";

// cek role admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if(!$id) die("ID pengajuan tidak diberikan.");

// ambil data pengajuan sempro + mahasiswa
$stmt = $pdo->prepare("
    SELECT s.*, m.nama, p.judul_ta
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    WHERE s.id=?
");
$stmt->execute([$id]);
$data = $stmt->fetch();
if(!$data) die("Data pengajuan tidak ditemukan.");

// array file untuk loop
$files = [
    'pendaftaran' => 'Form Pendaftaran',
    'persetujuan' => 'Persetujuan Proposal',
    'buku_konsultasi' => 'Buku Konsultasi'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Pengajuan Sempro</title>
<link rel="stylesheet" href="../../style.css">
<style>
body { font-family:Arial,sans-serif; background:#f4f6f8; margin:0; }
.container { display:flex; min-height:100vh; }
.content { flex:1; padding:20px; }
.card { background:#fff; padding:20px; border-radius:8px; margin-bottom:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
th { background:#eee; width:200px; }
select, textarea { width:100%; padding:8px; margin-top:5px; border-radius:4px; border:1px solid #ccc; }
textarea { min-height:60px; resize:none; }
button { padding:10px 20px; background:#28a745; color:#fff; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#218838; }
a.file-link { color:#007bff; text-decoration:none; }
a.file-link:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="container">
    <?php include "../sidebar.php"; ?>

    <div class="content">
        <h1>Detail Pengajuan Seminar Proposal</h1>

        <div class="card">
            <p><b>Nama Mahasiswa:</b> <?= htmlspecialchars($data['nama']) ?></p>
            <p><b>Judul TA:</b> <?= htmlspecialchars($data['judul_ta']) ?></p>
            <p><b>Status Keseluruhan:</b> <?= strtoupper($data['status']) ?></p>
            <p><b>Catatan Admin:</b> <?= $data['catatan'] ? htmlspecialchars($data['catatan']) : '-' ?></p>
        </div>

        <div class="card">
            <h3>Verifikasi Dokumen Per File</h3>
            <form action="verifikasi_sempro_perfile.php" method="POST">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">
                <table>
                    <tr>
                        <th>Status & Catatan</th>
                        <th>File</th>
                    </tr>
                    <?php foreach($files as $key => $label): 
                        $file_field = "file_{$key}";
                        $status_field = "status_file_{$key}";
                        $catatan_field = "catatan_file_{$key}";
                    ?>
                    <tr>
                        <td>
                            <label><?= $label ?></label>
                            <select name="status[<?= $key ?>]">
                                <option value="proses" <?= ($data[$status_field]??'')=='proses'?'selected':'' ?>>Proses</option>
                                <option value="revisi" <?= ($data[$status_field]??'')=='revisi'?'selected':'' ?>>Revisi</option>
                                <option value="ditolak" <?= ($data[$status_field]??'')=='ditolak'?'selected':'' ?>>Ditolak</option>
                                <option value="disetujui" <?= ($data[$status_field]??'')=='disetujui'?'selected':'' ?>>Disetujui</option>
                            </select>
                            <textarea name="catatan[<?= $key ?>]"><?= htmlspecialchars($data[$catatan_field]??'') ?></textarea>
                        </td>
                        <td>
                            <a class="file-link" href="../../uploads/sempro/<?= htmlspecialchars($data[$file_field]) ?>" target="_blank"><?= $label ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <button type="submit">Simpan Status</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
