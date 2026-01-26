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
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
<style>
:root {
    --primary: #ff5f9e;
    --secondary: #ff9f43;
    --bg-color: #fde9d9;
    --card-accent: #ff8c42;
    --border-color: #ffd4b8;
}

body {
    margin: 0;
    padding: 0;
    background: var(--bg-color);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.main-content {
    padding: 25px;
    margin-left: 310px; /* Diperlebar agar lebih mepet ke kanan */
}

/* ================= HEADER ================= */
.dashboard-header {
    background:linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
    padding:20px 24px;
    border-radius:14px;
    margin-bottom:20px;
}

.dashboard-header h1 {
    margin:0;
    color:#fff !important;
    font-size:20px;
}

.dashboard-header p {
    margin: 8px 0 0;
    font-size: 14px;
    opacity: 0.9;
    color:#fff !important;
}

/* ================= CARD ================= */
.card {
    background: #fff;
    border-radius: 20px;
    padding: 30px;
    border-left: 10px solid var(--card-accent);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    margin-bottom: 25px;
}

/* ================= INFO ================= */
.info-row {
    margin-bottom: 15px;
    font-size: 15px;
}

.info-row b {
    color: var(--card-accent);
    display: inline-block;
    width: 120px;
}

.info-row span {
    color: #333;
}

/* ================= FORM ================= */
label {
    display: block;
    margin-top: 20px;
    font-weight: 600;
    color: var(--card-accent);
    margin-bottom: 8px;
    font-size: 14px;
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

input {
    width: 100%;
    padding: 12px 15px;
    padding-right: 40px;
    border-radius: 12px;
    border: 2px solid var(--border-color);
    font-size: 14px;
    outline: none;
    transition: 0.3s;
    background: #fff;
}

input:focus {
    border-color: var(--card-accent);
}

.input-group span {
    position: absolute;
    right: 15px;
    color: var(--card-accent);
    font-size: 20px;
}

/* ================= INFO BOX ================= */
.info-note {
    background: #fff7f0;
    padding: 20px;
    border-radius: 15px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-top: 30px;
}

.info-note span {
    color: var(--card-accent);
    font-size: 24px;
}

.info-note .text b {
    display: block;
    color: var(--card-accent);
    font-size: 14px;
    margin-bottom: 5px;
}

.info-note .text p {
    margin: 0;
    font-size: 13px;
    color: #666;
    line-height: 1.5;
}

/* ================= BUTTON ================= */
.btn-save {
    background: linear-gradient(90deg, #ff8181, #ff9f43);
    color: #fff;
    border: none;
    padding: 14px 28px;
    border-radius: 15px;
    font-weight: 700;
    cursor: pointer;
    font-size: 15px;
    box-shadow: 0 5px 15px rgba(255, 129, 129, 0.3);
    transition: 0.3s;
    margin-top: 20px;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 129, 129, 0.4);
}

.alert {
    background: #ffe0e0;
    color: #d63031;
    padding: 12px 15px;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 20px;
    border-left: 5px solid #d63031;
}
</style>
</head>
<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="dashboard-header">
        <h1>Penjadwalan Seminar Hasil</h1>
        <p>Berisi penentuan jadwal Seminar Hasil</p>
    </div>

    <div class="card">
        <div class="info-row">
            <b>Mahasiswa</b>
            <span>: <?= htmlspecialchars($data['nama']) ?></span>
        </div>
        <div class="info-row">
            <b>Judul TA</b>
            <span>: <?= htmlspecialchars($data['judul_ta']) ?></span>
        </div>
        <div class="info-row">
            <b>Tanggal Sempro</b>
            <span>: <?= $minDate ?></span>
        </div>

        <?php if ($error): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Tanggal Seminar Hasil</label>
            <div class="input-group">
                <input type="date" name="tanggal_semhas" value="<?= $data['tanggal_sidang'] ?? '' ?>" min="<?= $minDate ?>" required>
            </div>

            <label>Jam Seminar Hasil</label>
            <div class="input-group">
                <input type="time" name="jam_semhas" value="<?= $data['jam_sidang'] ?? '' ?>" required>
            </div>

            <label>Ruangan Seminar Hasil</label>
            <div class="input-group">
                <input type="text" name="ruangan_semhas" 
                       value="<?= htmlspecialchars($data['tempat_sidang'] ?? '') ?>" 
                       placeholder="Masukkan Ruangan" required>
            </div>

            <div class="info-note">
                <span class="material-symbols-rounded">info</span>
                <div class="text">
                    <b>Informasi Penting</b>
                    <p>Pastikan seluruh data jadwal telah diverifikasi dengan teliti sebelum memberikan persetujuan</p>
                </div>
            </div>

            <button type="submit" class="btn-save">
                Simpan Penjadwalan
            </button>
        </form>
    </div>
</div>

</body>
</html>
