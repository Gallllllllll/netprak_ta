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

$username = $_SESSION['user']['nama'] ?? 'Admin';

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
$today = date('Y-m-d');
$min_date = $tanggal_value && $tanggal_value > $today 
    ? $today 
    : $today;

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Penjadwalan Seminar Proposal</title>
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
    margin-left: 280px;
    padding: 30px;
    transition: all 0.3s ease;
    width: calc(100vw - 280px);
    max-width: calc(100vw - 280px);
    box-sizing: border-box;
    overflow-x: hidden;
}

.dashboard-header{
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding:20px 24px;
    border-radius:14px;
    margin-bottom:20px;
    background:linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
    gap: 20px;
}

.header-text {
    flex: 1;
}

.dashboard-header h1 {
    margin:0;
    color:#fff !important;
    -webkit-text-fill-color: initial !important;
    background: none !important;
    -webkit-background-clip: initial !important;
    font-size: 20px;
    font-weight: 700;
}

.dashboard-header p {
    margin: 8px 0 0;
    font-size: 14px;
    opacity: 0.9;
    color:#fff !important;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-shrink: 0;
    margin-top: 5px;
}

.admin-profile .text {
    text-align: right;
    max-width: 90px;
    line-height: 1.2;
    color: #fff;
}

.admin-profile small { 
    font-size: 11px;
    display: block;
    opacity: 0.8;
}

.admin-profile b { 
    font-size: 13px; 
    display: block; 
}

.avatar {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
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

/* ================= RESPONSIVE ================= */
@media (max-width:1024px){
    .main-content {
        margin-left: 70px !important;
        padding: 20px !important;
        width: calc(100vw - 70px) !important;
        max-width: calc(100vw - 70px) !important;
    }
}

@media (max-width:768px){
    .main-content {
        margin-left: 60px !important;
        padding: 15px !important;
        width: calc(100vw - 60px) !important;
        max-width: calc(100vw - 60px) !important;
    }

    .dashboard-header {
        padding: 15px;
        gap: 10px;
    }

    .dashboard-header h1 {
        font-size: 18px;
    }

    .admin-profile {
        gap: 10px;
    }

    .admin-profile .text {
        max-width: 80px;
    }

    .avatar {
        width: 36px;
        height: 36px;
    }

    .card {
        padding: 20px;
    }

    .info-row b {
        width: 100%;
        display: block;
        margin-bottom: 5px;
    }
}
</style>
</head>
<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="dashboard-header">
        <div class="header-text">
            <h1>Penjadwalan Seminar Proposal</h1>
            <p>Berisi penentuan jadwal seminar proposal</p>
        </div>
        <div class="admin-profile">
            <div class="text">
                <small>Selamat Datang,</small>
                <b><?= htmlspecialchars($username) ?></b>
            </div>
            <div class="avatar">
                <span class="material-symbols-rounded" style="color:#fff">person</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="info-row">
            <b>Mahasiswa</b>
            <span>: <?= htmlspecialchars($data['nama'] ?? '-') ?></span>
        </div>
        <div class="info-row">
            <b>Judul TA</b>
            <span>: <?= htmlspecialchars($data['judul_ta'] ?? '-') ?></span>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Tanggal Seminar Proposal</label>
            <div class="input-group">
                <input 
                    type="date" 
                    name="tanggal_sempro"
                    value="<?= $tanggal_value ?>"
                    min="<?= $today ?>"
                    required
                >
            </div>

            <label>Jam Seminar</label>
            <div class="input-group">
                <input type="time" name="jam_sempro" value="<?= $jam_value ?>" required>
            </div>

            <label>Ruangan Seminar Proposal</label>
            <div class="input-group">
                <input type="text" name="ruangan_sempro" value="<?= $ruangan_value ?>" placeholder="Ruang Sidang 1" required>
            </div>

            <div class="info-note">
                <span class="material-symbols-rounded">info</span>
                <div class="text">
                    <b>Informasi Penting</b>
                    <p>Pastikan seluruh data jadwal telah diverifikasi dengan teliti sebelum memberikan persetujuan</p>
                </div>
            </div>

            <button type="submit" class="btn-save">Simpan Penjadwalan</button>
        </form>
    </div>
</div>

</body>
</html>

