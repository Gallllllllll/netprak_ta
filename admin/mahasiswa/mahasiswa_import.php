<?php
session_start();
require "../../config/connection.php"; 
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel'])) {
    $file = $_FILES['excel'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Terjadi kesalahan saat upload file.";
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xls','xlsx'])) {
            $error = "Format file harus XLS atau XLSX";
        } else {
            try {
                $spreadsheet = IOFactory::load($file['tmp_name']);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                $count = 0;
                for ($i = 1; $i < count($rows); $i++) {
                    $nim       = trim($rows[$i][0]);
                    $nama      = trim($rows[$i][1]);
                    $username  = trim($rows[$i][2]);
                    $email     = trim($rows[$i][3] ?? '') ?: null;
                    $prodi     = trim($rows[$i][4] ?? '') ?: null;
                    $kelas     = trim($rows[$i][5] ?? '') ?: null;
                    $nomor     = trim($rows[$i][6] ?? '') ?: '0000000000';
                    $password  = trim($rows[$i][7] ?? '');
                    if (!$password) $password = '123456';
                    $password  = password_hash($password, PASSWORD_DEFAULT);

                    if ($nim && $nama && $username) {
                        $cek = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim=? OR username=?");
                        $cek->execute([$nim, $username]);
                        if ($cek->rowCount() == 0) {
                            $stmt = $pdo->prepare("
                                INSERT INTO mahasiswa 
                                (nim, username, password, nama, email, prodi, kelas, nomor_telepon) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([$nim, $username, $password, $nama, $email, $prodi, $kelas, $nomor]);
                            $count++;
                        }
                    }
                }

                // redirect ke index.php setelah import sukses
                header("Location: index.php?success=$count");
                exit;

            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                $error = "Gagal membaca file Excel: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Import Data Mahasiswa</title>
<link rel="stylesheet" href="../../style.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
<style>
body { margin:0; font-family:'Inter', sans-serif; background:#f4f6f8; }
.container { display:flex; min-height:100vh; }

/* Sidebar */
.sidebar { width: 250px; background:#fff; position:fixed; top:0; left:0; bottom:0; padding:20px; overflow-y:auto; z-index:1000; }
.main-content { margin-left:270px; padding:30px; flex:1; }

/* Form */
form label { display:block; margin-bottom:5px; font-weight:bold; }
form input[type="file"] { display:block; margin-bottom:15px; }
form button { background:#3498db; color:#fff; border:none; padding:10px 18px; border-radius:5px; cursor:pointer; }
form button:hover { background:#2980b9; }

/* Error */
.error-message { color:red; margin-bottom:15px; padding:10px; border-radius:5px; background:#fdd; }

/* Excel format list */
ul { margin-top:10px; list-style:disc; padding-left:20px; }

/* Responsive */
@media (max-width:768px){
    .container { flex-direction:column; }
    .sidebar { width:100%; position:relative; }
    .main-content { margin-left:0; }
}
</style>
</head>
<body>
<div class="container">
    <?php include '../sidebar.php'; ?>

    <div class="main-content">
        <h1>Import Data Mahasiswa dari Excel</h1>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Pilih file Excel</label>
            <input type="file" name="excel" accept=".xls,.xlsx" required>
            <button type="submit">Import</button>
        </form>

        <p>Contoh format Excel:</p>
        <ul>
            <li>NIM (wajib)</li>
            <li>Nama (wajib)</li>
            <li>Username (wajib)</li>
            <li>Email (opsional)</li>
            <li>Prodi (opsional)</li>
            <li>Kelas (opsional)</li>
            <li>Nomor Telepon (opsional, default 0000000000)</li>
            <li>Password (opsional, default 123456 jika kosong)</li>
        </ul>
    </div>
</div>
</body>
</html>
