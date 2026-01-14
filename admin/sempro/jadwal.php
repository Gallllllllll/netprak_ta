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

// ===============================
// AMBIL DATA PENGAJUAN SEMPRO BERDASARKAN ID
// ===============================
$id = $_GET['id'] ?? null;
if (!$id) die("ID pengajuan tidak diberikan.");

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

// ===============================
// CEK STATUS SEBELUM MENJADWALKAN
// ===============================
if (($data['status'] ?? '') !== 'disetujui') {
    die("Penjadwalan hanya bisa dilakukan jika status sudah DISUTUJUI.");
}

// ===============================
// PROSES SIMPAN JADWAL
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal_sempro'] ?? '';
    $jam     = $_POST['jam_sempro'] ?? '';
    $ruangan = $_POST['ruangan_sempro'] ?? '';

    if (!$tanggal || !$jam || !$ruangan) {
        $error = "Semua field jadwal harus diisi!";
    } else {
        $stmt = $pdo->prepare("
            UPDATE pengajuan_sempro
            SET tanggal_sempro = ?, jam_sempro = ?, ruangan_sempro = ?
            WHERE id = ?
        ");
        $stmt->execute([$tanggal, $jam, $ruangan, $id]);

        header("Location: index.php");
        exit;
    }
}

// fallback aman untuk field form
$ruangan_value = htmlspecialchars($data['ruangan_sempro'] ?? '');
$tanggal_value = $data['tanggal_sempro'] ?? '';
$jam_value     = $data['jam_sempro'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Penjadwalan Seminar Proposal</title>
<link rel="stylesheet" href="../../style.css">
<style>
body { font-family: Arial, sans-serif; background:#f4f6f8; padding:20px; }
.card { background:#fff; padding:20px; border-radius:8px; max-width:600px; margin:auto; box-shadow:0 2px 6px rgba(0,0,0,.1); }
label { display:block; margin-top:12px; font-weight:600; }
input { width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; margin-top:6px; }
button { margin-top:16px; padding:10px 22px; border-radius:8px; background:#28a745; color:#fff; border:none; cursor:pointer; font-weight:600; }
button:hover { background:#218838; }
.alert { background:#fde68a; padding:12px; border-radius:6px; margin-bottom:12px; }
</style>
</head>
<body>

<div class="card">
    <h2>Penjadwalan Seminar Proposal</h2>
    <p><b>Mahasiswa:</b> <?= htmlspecialchars($data['nama'] ?? '-') ?></p>
    <p><b>Judul TA:</b> <?= htmlspecialchars($data['judul_ta'] ?? '-') ?></p>

    <?php if(!empty($error)): ?>
        <div class="alert"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Tanggal Seminar</label>
        <input type="date" name="tanggal_sempro" value="<?= $tanggal_value ?>" required>

        <label>Jam Seminar</label>
        <input type="time" name="jam_sempro" value="<?= $jam_value ?>" required>

        <label>Ruangan Seminar</label>
        <input type="text" name="ruangan_sempro" value="<?= $ruangan_value ?>" placeholder="Ruang Sidang 1" required>

        <button type="submit">Simpan Jadwal</button>
    </form>
</div>

</body>
</html>
