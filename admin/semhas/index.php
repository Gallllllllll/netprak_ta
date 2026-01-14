<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

/* ===============================
   SIMPAN / UPDATE JADWAL
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jadwal_id'])) {
    $stmt = $pdo->prepare("
        UPDATE pengajuan_semhas
        SET tanggal_sidang = ?, jam_sidang = ?, tempat_sidang = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $_POST['tanggal_sidang'],
        $_POST['jam_sidang'],
        $_POST['tempat_sidang'],
        $_POST['jadwal_id']
    ]);

    header("Location: index.php");
    exit;
}

/* ===============================
   AMBIL DATA SEMHAS
================================ */
$stmt = $pdo->query("
    SELECT s.*, 
           m.nama, m.nim, 
           p.judul_ta
    FROM pengajuan_semhas s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    ORDER BY s.created_at DESC
");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin - Seminar Hasil</title>

<style>
.card{
    background:#fff;
    padding:18px;
    border-radius:12px;
    margin-bottom:14px;
    box-shadow:0 2px 6px rgba(0,0,0,.08);
}
.status{font-weight:bold}
.jadwal{
    background:#f8f9fa;
    padding:12px;
    border-radius:8px;
    margin-top:10px;
}
.jadwal label{
    display:block;
    font-size:13px;
    margin-top:6px;
}
.jadwal input{
    width:100%;
    padding:6px;
}
button{
    margin-top:8px;
    padding:6px 14px;
    border:none;
    border-radius:6px;
    background:#28a745;
    color:#fff;
    cursor:pointer;
}
button:hover{opacity:.9}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
<h1>Pengajuan Seminar Hasil</h1>

<?php foreach ($data as $d): ?>
<div class="card">

    <b><?= htmlspecialchars($d['nama']) ?></b> (<?= $d['nim'] ?>)<br>
    <b>Judul TA:</b> <?= htmlspecialchars($d['judul_ta']) ?><br>
    <b>Status:</b>
    <span class="status"><?= strtoupper($d['status']) ?></span><br>

    <a href="detail.php?id=<?= $d['id'] ?>">Lihat Detail</a>

    <?php if ($d['status'] === 'disetujui'): ?>
        <div class="jadwal">
            <b>Jadwal Sidang Seminar Hasil</b>

            <form method="POST">
                <input type="hidden" name="jadwal_id" value="<?= $d['id'] ?>">

                <label>Tanggal</label>
                <input type="date" name="tanggal_sidang"
                       value="<?= $d['tanggal_sidang'] ?>">

                <label>Jam</label>
                <input type="time" name="jam_sidang"
                       value="<?= $d['jam_sidang'] ?>">

                <label>Tempat</label>
                <input type="text" name="tempat_sidang"
                       placeholder="Ruang / Online"
                       value="<?= htmlspecialchars($d['tempat_sidang'] ?? '') ?>">

                <button type="submit">
                    <?= $d['tanggal_sidang'] ? 'Update Jadwal' : 'Simpan Jadwal' ?>
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>
<?php endforeach; ?>

</div>

</body>
</html>
