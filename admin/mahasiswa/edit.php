<?php
session_start();
require "../../config/connection.php";

// cek login admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

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
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if($nama && $nim && $username) {
        // cek NIM unik
        $cek_nim = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ? AND id <> ?");
        $cek_nim->execute([$nim, $id]);
        if($cek_nim->rowCount() > 0){
            $error = "NIM '$nim' sudah dipakai mahasiswa lain!";
        } else {
            if($password) {
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE mahasiswa SET nama=?, nim=?, prodi=?, kelas=?, nomor_telepon=?, email=?, username=?, password=? WHERE id=?");
                $stmt->execute([$nama,$nim,$prodi,$kelas,$nomor_telepon,$email,$username,$password_hashed,$id]);
            } else {
                $stmt = $pdo->prepare("UPDATE mahasiswa SET nama=?, nim=?, prodi=?, kelas=?, nomor_telepon=?, email=?, username=? WHERE id=?");
                $stmt->execute([$nama,$nim,$prodi,$kelas,$nomor_telepon,$email,$username,$id]);
            }
            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Nama, NIM, dan username wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Mahasiswa</title>
<link rel="stylesheet" href="../../style.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
<style>
body { margin:0; font-family:'Inter', sans-serif; background:#f4f6f8; }
.container { display:flex; min-height:100vh; }
.main-content { margin-left:270px; padding:30px; flex:1; background:#f4f6f8; }
form label { display:block; margin-bottom:5px; font-weight:bold; margin-top:10px; }
form input[type="text"], form input[type="email"], form input[type="password"], form select { width:100%; padding:8px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc; }
form button { background:#3498db; color:#fff; border:none; padding:10px 18px; border-radius:5px; cursor:pointer; margin-top:10px; }
form button:hover { background:#2980b9; }
.error-message { color:red; margin-bottom:15px; padding:10px; border-radius:5px; background:#fdd; }
@media (max-width:768px){ .container { flex-direction:column; } .main-content { margin-left:0; } }
</style>
</head>
<body>
<div class="container">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <div class="main-content">
        <h1>Edit Mahasiswa</h1>

        <?php if($error): ?>
            <div class="error-message"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <label>Nama:</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($mahasiswa['nama'] ?? ''); ?>" required>

            <label>NIM:</label>
            <input type="text" name="nim" value="<?= htmlspecialchars($mahasiswa['nim'] ?? ''); ?>" required>

            <label>Prodi:</label>
            <select name="prodi" id="prodi" required>
                <option value="">-- Pilih Prodi --</option>
                <option value="Seni Kuliner" <?= ($mahasiswa['prodi'] ?? '') === 'Seni Kuliner' ? 'selected' : ''; ?>>Seni Kuliner</option>
                <option value="Teknologi Informasi" <?= ($mahasiswa['prodi'] ?? '') === 'Teknologi Informasi' ? 'selected' : ''; ?>>Teknologi Informasi</option>
                <option value="Perhotelan" <?= ($mahasiswa['prodi'] ?? '') === 'Perhotelan' ? 'selected' : ''; ?>>Perhotelan</option>
            </select>

            <label>Kelas:</label>
            <select name="kelas" id="kelas">
                <option value="">-- Pilih Kelas --</option>
            </select>

            <label>Nomor Telepon:</label>
            <input type="text" name="nomor_telepon" value="<?= htmlspecialchars($mahasiswa['nomor_telepon'] ?? ''); ?>">

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($mahasiswa['email'] ?? ''); ?>">

            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($mahasiswa['username'] ?? ''); ?>" required>

            <label>Password (kosongkan kalau tidak diubah):</label>
            <input type="password" name="password">

            <button type="submit" class="btn">Update</button>
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
            const selected = "<?= $mahasiswa['kelas'] ?? '' ?>" === kelas ? "selected" : "";
            kelasSelect.innerHTML += `<option value="${kelas}" ${selected}>${kelas}</option>`;
        });
    }
}

// jalankan saat load & saat prodi berubah
updateKelas();
prodiSelect.addEventListener('change', updateKelas);
</script>
</body>
</html>
