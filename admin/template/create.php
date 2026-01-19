<?php
session_start();
require "../../config/connection.php";

// cek admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $file = $_FILES['file'] ?? null;

    if (!$nama) {
        $error = "Nama template wajib diisi.";
    } elseif (!$file || !$file['tmp_name']) {
        $error = "File template wajib diunggah.";
    } else {
        $filename = basename($file['name']); // pakai nama asli file
        move_uploaded_file($file['tmp_name'], "../../uploads/templates/$filename");

        $stmt = $pdo->prepare("INSERT INTO template (nama, file) VALUES (?, ?)");
        $stmt->execute([$nama, $filename]);
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
<title>Tambah Template</title>
<link rel="stylesheet" href="../../style.css">
<style>
/* ==========================
   STYLE KHUSUS HALAMAN
   ========================== */
.main-content {
    padding: 20px;
}

.card {
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 2px 6px rgba(0,0,0,0.08);
    margin-bottom:20px;
}

.form-group {
    margin-bottom: 15px;
}

label {
    display:block;
    font-weight:600;
    margin-bottom:6px;
}

input[type="text"],
input[type="file"] {
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    outline:none;
}

input[type="text"]:focus,
input[type="file"]:focus {
    border-color:#2563eb;
}

button {
    padding:10px 20px;
    background:#16a34a;
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:600;
}

button:hover {
    background:#15803d;
}

.error {
    background:#fee2e2;
    color:#991b1b;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
    border:1px solid #fca5a5;
}
</style>
</head>
<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="card">
        <h2>Tambah Template</h2>
        <p>Unggah file template yang akan digunakan.</p>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Template</label>
                <input type="text" name="nama" placeholder="Masukkan nama template" required>
            </div>

            <div class="form-group">
                <label>File Template</label>
                <input type="file" name="file" accept=".doc,.docx,.pdf,.xlsx,.pptx" required>
            </div>

            <button type="submit">Simpan Template</button>
        </form>
    </div>
</div>

</body>
</html>
