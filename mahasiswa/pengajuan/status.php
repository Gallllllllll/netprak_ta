<?php
session_start();
require "../../config/connection.php";

// cek role mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

// ambil semua pengajuan mahasiswa + dosen pembimbing
$stmt = $pdo->prepare("
    SELECT 
        p.id,
        p.judul_ta,
        p.status,
        p.catatan_admin,
        p.created_at,

        MAX(CASE WHEN db.role = 'dosbing_1' THEN d.nama END) AS dosen1_nama,
        MAX(CASE WHEN db.role = 'dosbing_2' THEN d.nama END) AS dosen2_nama,

        p.status_bukti_pembayaran,
        p.catatan_bukti_pembayaran,
        p.status_formulir_pendaftaran,
        p.catatan_formulir_pendaftaran,
        p.status_transkrip_nilai,
        p.catatan_transkrip_nilai,
        p.status_bukti_magang,
        p.catatan_bukti_magang

    FROM pengajuan_ta p
    LEFT JOIN dosbing_ta db ON db.pengajuan_id = p.id
    LEFT JOIN dosen d ON db.dosen_id = d.id
    WHERE p.mahasiswa_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$pengajuan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// mapping kolom => label file
$files = [
    'bukti_pembayaran' => ['status'=>'status_bukti_pembayaran', 'catatan'=>'catatan_bukti_pembayaran', 'label'=>'Bukti Pembayaran'],
    'formulir' => ['status'=>'status_formulir_pendaftaran', 'catatan'=>'catatan_formulir_pendaftaran', 'label'=>'Formulir Pendaftaran'],
    'transkrip' => ['status'=>'status_transkrip_nilai', 'catatan'=>'catatan_transkrip_nilai', 'label'=>'Transkrip Nilai'],
    'magang' => ['status'=>'status_bukti_magang', 'catatan'=>'catatan_bukti_magang', 'label'=>'Bukti Kelulusan Magang'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Pengajuan TA</title>

<link rel="stylesheet" href="../style.css">
<style>
body {
    margin:0;
    font-family:Arial,sans-serif;
    background:#f4f6f8;
}
.container {
    display:flex;
    min-height:100vh;
}
.content {
    flex:1;
    padding:20px;
}
.card {
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
.status {
    display:inline-block;
    padding:5px 12px;
    border-radius:20px;
    font-weight:bold;
    color:#fff;
}
.status-proses { background:#ffc107; }
.status-disetujui { background:#28a745; }
.status-ditolak { background:#dc3545; }
.status-revisi { background:#17a2b8; }

a.button {
    display:inline-block;
    padding:8px 12px;
    margin-top:10px;
    background:#17a2b8;
    color:#fff;
    border-radius:6px;
    text-decoration:none;
}
a.button:hover {
    background:#138496;
}
ul.dosen, ul.revisi {
    margin:8px 0 0 20px;
}
ul.revisi li {
    margin-bottom:4px;
}
</style>

</head>
<body>

<div class="container">

    <?php include "../sidebar.php"; ?>

    <div class="main-content">
        <h1>Riwayat Pengajuan TA</h1>

        <?php if ($pengajuan_list): ?>
            <?php foreach($pengajuan_list as $data): ?>

                <div class="card">
                    <h3><?= htmlspecialchars($data['judul_ta']) ?></h3>

                    <?php
                    $status_class = '';
                    switch (strtolower($data['status'])) {
                        case 'proses': $status_class = 'status-proses'; break;
                        case 'disetujui': $status_class = 'status-disetujui'; break;
                        case 'ditolak': $status_class = 'status-ditolak'; break;
                        case 'revisi': $status_class = 'status-revisi'; break;
                        default: $status_class = 'status-proses';
                    }
                    ?>

                    <p>
                        Status:
                        <span class="status <?= $status_class ?>">
                            <?= strtoupper($data['status'] ?? 'PROSES') ?>
                        </span>
                    </p>

                    <p>
                        Catatan Admin / Dosen:
                        <?= !empty($data['catatan_admin'])
                            ? htmlspecialchars($data['catatan_admin'])
                            : '-' ?>
                    </p>

                    <?php if (strtolower($data['status']) === 'disetujui'): ?>
                        <p><b>Dosen Pembimbing:</b></p>
                        <ul class="dosen">
                            <li>Pembimbing 1: <?= $data['dosen1_nama'] ?? '-' ?></li>
                            <li>Pembimbing 2: <?= $data['dosen2_nama'] ?? '-' ?></li>
                        </ul>
                    <?php endif; ?>

                    <?php if (strtolower($data['status']) === 'revisi'): ?>
                        <p><b>File yang perlu direvisi:</b></p>
                        <ul class="revisi">
                            <?php
                            $has_revisi = false;
                            foreach($files as $f){
                                if(($data[$f['status']] ?? '') === 'revisi'){
                                    $has_revisi = true;
                                    echo "<li>{$f['label']}";
                                    if(!empty($data[$f['catatan']])){
                                        echo " - ".htmlspecialchars($data[$f['catatan']]);
                                    }
                                    echo "</li>";
                                }
                            }
                            if(!$has_revisi){
                                echo "<li>- Tidak ada file spesifik, silakan cek catatan admin.</li>";
                            }
                            ?>
                        </ul>
                        <a href="revisi_ta.php?id=<?= $data['id'] ?>" class="button">Upload Revisi</a>
                    <?php endif; ?>

                    <p><a href="detail.php?id=<?= $data['id'] ?>" class="button">Lihat Detail</a></p>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <p>
                    Belum ada pengajuan TA.
                    <a href="form.php" class="button">Ajukan TA Baru</a>
                </p>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
