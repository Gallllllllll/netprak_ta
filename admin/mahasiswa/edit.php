<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

/* CEK LOGIN */
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$username = $_SESSION['user']['nama'] ?? 'Admin';

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = ?");
$stmt->execute([$id]);
$mahasiswa = $stmt->fetch();

if(!$mahasiswa) {
    header("Location: index.php");
    exit;
}

$error = '';

/* ======================
   HANDLE UPDATE
   ====================== */
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if($nama && $nim && $username) {

        $cek = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ? AND id <> ?");
        $cek->execute([$nim, $id]);

        if($cek->rowCount() > 0) {
            $error = "NIM sudah digunakan mahasiswa lain!";
        } else {
            if($password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE mahasiswa SET 
                    nama=?, nim=?, email=?, prodi=?, kelas=?, nomor_telepon=?, username=?, password=?
                    WHERE id=?
                ");
                $stmt->execute([
                    $nama,$nim,$email,$prodi,$kelas,$nomor_telepon,$username,$hashed,$id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE mahasiswa SET 
                    nama=?, nim=?, email=?, prodi=?, kelas=?, nomor_telepon=?, username=?
                    WHERE id=?
                ");
                $stmt->execute([
                    $nama,$nim,$email,$prodi,$kelas,$nomor_telepon,$username,$id
                ]);
            }

            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Nama, NIM, dan Username wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Edit Mahasiswa</title>

<style>

/* TOP */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px
}
.topbar h1{
    color:#ff8c42;
    font-size:28px
}

/* PROFILE */
.admin-info{
    display:flex;
    align-items:left;
    gap:20px
}
.admin-text span{
    font-size:13px;
    color:#555
}
.admin-text b{
    color:#ff8c42;
    font-size:14px
}

.avatar{
    width:42px;
    height:42px;
    background:#ff8c42;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}

/* CARD */
.form-card {
    background: #fff;
    padding: 24px;
    border-radius: 16px;
    border: 1px solid #f1dcdc;
}

/* FORM ROW */
.form-group {
    display: grid;
    grid-template-columns: 160px 1fr;
    align-items: center;
    gap: 16px;
    margin-bottom: 14px;
    padding-right: 30px;
}

label {
    font-weight: 700;
    font-size: 14px;
}

input, select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #ddd;
    font-size: 14px;
}

input:focus, select:focus {
    outline: none;
    border-color: #FF983D;
}

/* BUTTON */
.form-actions {
    display: flex;
    gap: 12px;
    margin-left: 176px;
}

.btn {
    background: linear-gradient(135deg, #FF74C7, #FF983D);
    color: #fff;
    border: none;
    padding: 12px 22px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
}

.btn.secondary {
    background: #e5e7eb;
    color: #374151;
}

.btn:hover {
    opacity: 0.9;
}

/* ERROR */
.error {
    background: #ffe5e5;
    color: #c0392b;
    padding: 10px 14px;
    border-radius: 10px;
    margin-bottom: 14px;
}

/* RESPONSIVE */
@media (max-width:600px){
    .form-group {
        grid-template-columns: 1fr;
    }
    .form-actions {
        margin-left: 0;
        flex-direction: column;
    }
}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">

    <div class="topbar">
        <h1>Edit Data Mahasiswa</h1>

        <div class="admin-info">
            <div class="admin-text">
                <span>Selamat Datang,</span><br>
                <b><?= htmlspecialchars($username) ?></b>
            </div>
            <div class="avatar">
                <span class="material-symbols-rounded" style="color:#fff">person</span>
            </div>
        </div>
    </div>

    <div class="form-card">

        <?php if($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($mahasiswa['nama']) ?>" required>
            </div>

            <div class="form-group">
                <label>NIM</label>
                <input type="text" name="nim" value="<?= htmlspecialchars($mahasiswa['nim']) ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($mahasiswa['email']) ?>">
            </div>

            <div class="form-group">
                <label>Program Studi</label>
                <select name="prodi">
                    <option value="">-- Pilih Prodi --</option>
                    <option value="Seni Kuliner" <?= $mahasiswa['prodi']=='Seni Kuliner'?'selected':'' ?>>Seni Kuliner</option>
                    <option value="Teknologi Informasi" <?= $mahasiswa['prodi']=='Teknologi Informasi'?'selected':'' ?>>Teknologi Informasi</option>
                    <option value="Perhotelan" <?= $mahasiswa['prodi']=='Perhotelan'?'selected':'' ?>>Perhotelan</option>
                </select>
            </div>

            <div class="form-group">
                <label>Kelas</label>
                <input type="text" name="kelas" value="<?= htmlspecialchars($mahasiswa['kelas']) ?>">
            </div>

            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" name="nomor_telepon" value="<?= htmlspecialchars($mahasiswa['nomor_telepon']) ?>">
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($mahasiswa['username']) ?>" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak diubah">
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn secondary">Kembali</a>
                <button type="submit" class="btn">Update</button>
            </div>

        </form>
    </div>
</div>

</body>
</html>
