<?php
session_start();
require "../../config/connection.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

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
<title>Tambah Mahasiswa</title>
<style>
/* FORM CARD */
.form-card {
    background: #fff;
    padding: 24px;
    border-radius: 16px;
    max-width: 700px;
    border: 1px solid #f1dcdc;
}
.form-group { margin-bottom: 14px; }
label { font-weight: 500; font-size: 14px; }
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
.btn {
    background: linear-gradient(135deg, #FF74C7, #FF983D);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
}
.btn:hover { opacity: 0.9; }
.error {
    background: #ffe5e5;
    color: #c0392b;
    padding: 10px 14px;
    border-radius: 10px;
    margin-bottom: 14px;
    font-size: 14px;
}
</style>
</head>
<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="dashboard-header">
        <h1>Tambah Mahasiswa</h1>
        <p>Form pendaftaran akun mahasiswa baru</p>
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
                <select name="prodi" id="prodi">
                    <option value="">-- Pilih Prodi --</option>
                    <option value="Seni Kuliner">Seni Kuliner</option>
                    <option value="Teknologi Informasi">Teknologi Informasi</option>
                    <option value="Perhotelan">Perhotelan</option>
                </select>
            </div>

            <div class="form-group">
                <label>Kelas</label>
                <select name="kelas" id="kelas">
                    <option value="">-- Pilih Kelas --</option>
                </select>
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

            <button type="submit" class="btn">Simpan</button>
        </form>
    </div>
</div>

<script>
// mapping prodi ke kelas
const kelasByProdi = {
    "Teknologi Informasi": ["TI-1","TI-2","TI-3"],
    "Perhotelan": ["PH-1","PH-2","PH-3"],
    "Seni Kuliner": ["SK-1","SK-2","SK-3"]
};

const prodiSelect = document.getElementById('prodi');
const kelasSelect = document.getElementById('kelas');

function updateKelas() {
    const prodi = prodiSelect.value;
    kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
    if(kelasByProdi[prodi]) {
        kelasByProdi[prodi].forEach(kelas => {
            kelasSelect.innerHTML += `<option value="${kelas}">${kelas}</option>`;
        });
    }
}

// jalankan saat load & saat prodi berubah
updateKelas();
prodiSelect.addEventListener('change', updateKelas);
</script>

</body>
</html>
