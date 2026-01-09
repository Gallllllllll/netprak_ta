<?php
session_start();
require "../../config/connection.php";

// cek role mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

// ambil semua pengajuan mahasiswa
$stmt = $pdo->prepare("
    SELECT *
    FROM pengajuan_ta
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$pengajuan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Pengajuan TA</title>
<link rel="stylesheet" href="../style.css">
<style>
body { margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container { display:flex; min-height:100vh; }
.content { flex:1; padding:20px; }
.card {
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
.card h3 { margin-top:0; }
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
    display:inline-block; padding:8px 12px;
    margin-top:10px;
    background:#17a2b8; color:#fff;
    border-radius:6px; text-decoration:none;
}
a.button:hover { background:#138496; }
</style>
</head>
<body>

<div class="container">
    <?php include "../sidebar.php"; ?>

    <div class="content">
        <h1>Riwayat Pengajuan TA</h1>

        <?php if ($pengajuan_list): ?>
            <?php foreach($pengajuan_list as $data): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($data['judul_ta']) ?></h3>

                    <?php
                    $status_class = '';
                    switch(strtolower($data['status'])){
                        case 'proses': $status_class='status-proses'; break;
                        case 'disetujui': $status_class='status-disetujui'; break;
                        case 'ditolak': $status_class='status-ditolak'; break;
                        case 'revisi': $status_class='status-revisi'; break;
                    }
                    ?>
                    <p>Status: <span class="status <?= $status_class ?>"><?= strtoupper($data['status']) ?></span></p>

                    <p>Catatan Admin / Dosen: <?= $data['catatan_admin'] ? htmlspecialchars($data['catatan_admin']) : '-' ?></p>

                    <p>
                        <a href="detail.php?id=<?= $data['id'] ?>" class="button">Lihat Detail</a>
                        <?php if(strtolower($data['status'])==='revisi'): ?>
                            <a href="revisi_ta.php?id=<?= $data['id'] ?>" class="button">Upload Revisi</a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <p>Belum ada pengajuan TA. Silakan <a href="upload_ta.php" class="button">Ajukan TA Baru</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
