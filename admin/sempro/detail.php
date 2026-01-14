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
<link rel="stylesheet" href="../../style.css">

<style>
.card {
    background: #fff;
    padding: 20px;
    border-radius: 14px;
    margin-bottom: 20px;
    border: 1px solid #f1dcdc;
}

.card h3 { margin-top: 0; }

table { width: 100%; border-collapse: collapse; margin-top: 12px; }
th, td { padding: 12px; border: 1px solid #e5e7eb; vertical-align: top; }
th { background: #f9fafb; width: 260px; }

select, textarea {
    width: 100%;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-family: inherit;
}

textarea { resize: none; min-height: 70px; margin-top: 6px; }

.file-link { color: #2563eb; text-decoration: none; font-weight: 500; }
.file-link:hover { text-decoration: underline; }

.btn { margin-top: 16px; padding: 10px 22px; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; }
.btn-save { background: linear-gradient(135deg, #FF74C7, #FF983D); color: #fff; }

.badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; margin-left: 6px; }
.badge-proses { background: #e5e7eb; color: #374151; }
.badge-revisi { background: #fde68a; color: #92400e; }
.badge-ditolak { background: #fecaca; color: #991b1b; }
.badge-disetujui { background: #bbf7d0; color: #166534; }

@media (max-width: 768px) {
    .main-content { margin-left: 0 !important; padding: 16px; }
    th { width: auto; }
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

    <div class="card">
        <p><b>Nama Mahasiswa:</b> <?= htmlspecialchars($data['nama']) ?></p>
        <p><b>Judul TA:</b> <?= htmlspecialchars($data['judul_ta']) ?></p>
        <p>
            <b>Status Keseluruhan:</b>
            <?php
                $status = $data['status'];
                echo strtoupper($status);
                echo " <span class='badge badge-$status'>$status</span>";
            ?>
        </p>
        <p><b>Catatan Admin:</b> <?= $data['catatan'] ? htmlspecialchars($data['catatan']) : '-' ?></p>
    </div>

    <div class="card">
        <h3>Verifikasi Dokumen Per File</h3>

        <form action="verifikasi_sempro_perfile.php" method="POST">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">

            <table>
                <tr>
                    <th>Status & Catatan</th>
                    <th>Dokumen</th>
                </tr>

                <?php foreach ($files as $key => $label):
                    $file_field     = "file_$key";
                    $status_field   = "status_file_$key";
                    $catatan_field  = "catatan_file_$key";
                    $st = $data[$status_field] ?? 'proses';
                ?>
                <tr>
                    <td>
                        <strong><?= $label ?></strong>
                        <span class="badge badge-<?= $st ?>"><?= $st ?></span>

                        <select name="status[<?= $key ?>]">
                            <option value="proses" <?= $st=='proses'?'selected':'' ?>>Proses</option>
                            <option value="revisi" <?= $st=='revisi'?'selected':'' ?>>Revisi</option>
                            <option value="ditolak" <?= $st=='ditolak'?'selected':'' ?>>Ditolak</option>
                            <option value="disetujui" <?= $st=='disetujui'?'selected':'' ?>>Disetujui</option>
                        </select>

                        <textarea name="catatan[<?= $key ?>]" placeholder="Catatan admin..."><?= htmlspecialchars($data[$catatan_field] ?? '') ?></textarea>
                    </td>
                    <td>
                        <?php if (!empty($data[$file_field])): ?>
                            <a class="file-link" target="_blank"
                               href="../../uploads/sempro/<?= htmlspecialchars($data[$file_field]) ?>">
                               Lihat <?= $label ?>
                            </a>
                        <?php else: ?>
                            <em>File belum diupload</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <button type="submit" class="btn btn-save">Simpan Verifikasi</button>
        </form>
    </div>
</div>

</body>
</html>
