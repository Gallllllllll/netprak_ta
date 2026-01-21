<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$username = $_SESSION['user']['username'] ?? 'Admin';

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $nim = trim($_POST['nim']);
    $email = trim($_POST['email']);
    $prodi = trim($_POST['prodi']);
    $kelas = trim($_POST['kelas']);
    $nomor_telepon = trim($_POST['nomor_telepon']);
    $username = trim($_POST['username']);
    $password_raw = trim($_POST['password']);

    if($nama && $nim && $username && $password_raw) {
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO mahasiswa 
            (nama, nim, email, prodi, kelas, nomor_telepon, username, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nama, $nim, $email, $prodi,
            $kelas, $nomor_telepon,
            $username, $password
        ]);

        header("Location: index.php");
        exit;
    } else {
        $error = "Nama, NIM, Username, dan Password wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/img/Logo.webp')?>">
<title>Tambah Mahasiswa</title>

<style>
/* TOP */
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px}
.topbar h1{color:#ff8c42;font-size:28px}

/* PROFILE */
.admin-info{display:flex;align-items:left;gap:20px}
.admin-text span{font-size:13px;color:#555}
.admin-text b{color:#ff8c42;font-size:14px}

.avatar{
    width:42px;height:42px;
    background:#ff8c42;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
}

/* CARD */
.form-card {
    background: #fff;
    padding: 24px;
    border-radius: 16px;
    max-width: auto;
    border: 1px solid #f1dcdc;
}

/* FORM ROW (HORIZONTAL) */
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
    color: #000000;
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

.form-actions {
    display: flex;
    gap: 12px;
    margin-left: 176px;
}

/* ERROR */
.error {
    background: #ffe5e5;
    color: #c0392b;
    padding: 10px 14px;
    border-radius: 10px;
    margin-bottom: 14px;
    font-size: 14px;
}

/* RESPONSIVE */
@media (max-width: 600px) {
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
        <h1>Tambah Data Mahasiswa</h1>

        <div class="admin-info">
            <div class="admin-text">
                <span>Selamat Datang,</span><br>
                <b><?php echo htmlspecialchars($username); ?></b>
            </div>
            <div class="avatar">
                <span class="material-symbols-rounded" style="color:#fff">person</span>
            </div>
        </div>
    </div>

    <div class="form-card">

        <?php if($error): ?>
            <div class="error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" required>
            </div>

            <div class="form-group">
                <label>NIM</label>
                <input type="text" name="nim" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>

            <div class="form-group">
                <label>Program Studi</label>
                <select name="prodi">
                    <option value="">-- Pilih Prodi --</option>
                    <option value="Seni Kuliner">Seni Kuliner</option>
                    <option value="Teknologi Informasi">Teknologi Informasi</option>
                    <option value="Perhotelan">Perhotelan</option>
                </select>
            </div>

            <div class="form-group">
                <label>Kelas</label>
                <input type="text" name="kelas">
            </div>

            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" name="nomor_telepon">
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn secondary">Kembali</a>
                <button type="submit" class="btn">Simpan</button>
            </div>

        </form>

    </div>
</div>

</body>
</html>
