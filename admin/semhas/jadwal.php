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
// AMBIL DATA PENGAJUAN SEMHAS BERDASARKAN ID
// ===============================
$id = $_GET['id'] ?? null;
if (!$id) die("ID pengajuan tidak diberikan.");

// Ambil data semhas beserta mahasiswa dan judul TA
$stmt = $pdo->prepare("
    SELECT sh.*, m.nama, p.judul_ta, m.id AS mahasiswa_id
    FROM pengajuan_semhas sh
    JOIN mahasiswa m ON sh.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON sh.pengajuan_ta_id = p.id
    WHERE sh.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Data pengajuan tidak ditemukan.");

// ===============================
// AMBIL TANGGAL SEMPRO MAHASISWA
// ===============================
$stmt2 = $pdo->prepare("
    SELECT tanggal_sempro
    FROM pengajuan_sempro
    WHERE mahasiswa_id = ?
    ORDER BY tanggal_sempro DESC
    LIMIT 1
");
$stmt2->execute([$data['mahasiswa_id']]);
$sempro = $stmt2->fetch(PDO::FETCH_ASSOC);

$minDate = $sempro['tanggal_sempro'] ?? date('Y-m-d'); // fallback kalau belum ada sempro

// ===============================
// CEK STATUS SEBELUM MENJADWALKAN
// ===============================
if ($data['status'] !== 'disetujui') {
    die("Penjadwalan hanya bisa dilakukan jika status sudah DISUTUJUI.");
}

// ===============================
// PROSES SIMPAN / UPDATE JADWAL
// ===============================
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal_semhas'] ?? null;
    $jam     = $_POST['jam_semhas'] ?? null;
    $ruangan = $_POST['ruangan_semhas'] ?? null;

    if (!$tanggal || !$jam || !$ruangan) {
        $error = "Semua field jadwal harus diisi!";
    } elseif ($tanggal < $minDate) {
        $error = "Tanggal Semhas harus setelah tanggal Sempro: $minDate";
    } else {
        $stmt = $pdo->prepare("
            UPDATE pengajuan_semhas
            SET tanggal_sidang = ?, jam_sidang = ?, tempat_sidang = ?
            WHERE id = ?
        ");
        $stmt->execute([$tanggal, $jam, $ruangan, $id]);

        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Penjadwalan Seminar Hasil</title>
<link rel="stylesheet" href="../../style.css">
<style>
body { font-family: Arial, sans-serif; background:#f4f6f8; padding:20px; }
.card { 
    background:#fff; 
    padding:20px; 
    border-radius:8px; 
    max-width:600px; 
    margin:auto; 
    box-shadow:0 2px 6px rgba(0,0,0,.1); 
}
label { display:block; margin-top:12px; font-weight:600; }
input { width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; margin-top:6px; }
button { margin-top:16px; padding:10px 22px; border-radius:8px; background:#28a745; color:#fff; border:none; cursor:pointer; font-weight:600; }
button:hover { background:#218838; }
.alert { background:#fde68a; padding:12px; border-radius:6px; margin-bottom:12px; }
</style>
</head>
<body>

<div class="card">
    <h2>Penjadwalan Seminar Hasil</h2>
    <p><b>Mahasiswa:</b> <?= htmlspecialchars($data['nama']) ?></p>
    <p><b>Judul TA:</b> <?= htmlspecialchars($data['judul_ta']) ?></p>
    <p><b>Tanggal Sempro:</b> <?= $minDate ?></p>

    <?php if ($error): ?>
        <div class="alert"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Tanggal Semhas</label>
        <input type="date" name="tanggal_semhas" value="<?= $data['tanggal_sidang'] ?? '' ?>" min="<?= $minDate ?>" required>

        <label>Jam Semhas</label>
        <input type="time" name="jam_semhas" value="<?= $data['jam_sidang'] ?? '' ?>" required>

        <label>Ruangan Semhas</label>
        <input type="text" name="ruangan_semhas" 
               value="<?= htmlspecialchars($data['tempat_sidang'] ?? '') ?>" 
               placeholder="Ruang / Online" required>

        <button type="submit">
            <?= $data['tanggal_sidang'] ? 'Update Jadwal' : 'Simpan Jadwal' ?>
        </button>
    </form>
</div>

</body>
</html>
