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
                    $nip       = trim($rows[$i][0]);
                    $username  = trim($rows[$i][1]);
                    $password  = trim($rows[$i][2]);
                    $nama      = trim($rows[$i][3]);
                    $email     = trim($rows[$i][4] ?? '') ?: null;
                    $created   = trim($rows[$i][5] ?? '') ?: null;

                    // validasi wajib
                    if (!$nip || !$username || !$nama) {
                        continue; // skip baris yang tidak lengkap
                    }

                    if (!$password) $password = '123456';
                    $password = password_hash($password, PASSWORD_DEFAULT);

                    // cek duplicate nip / username
                    $cek = $pdo->prepare("SELECT id FROM dosen WHERE nip=? OR username=?");
                    $cek->execute([$nip, $username]);

                    if ($cek->rowCount() == 0) {

                        if ($created) {
                            $stmt = $pdo->prepare("
                                INSERT INTO dosen
                                (nip, username, password, nama, email, created_at)
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([$nip, $username, $password, $nama, $email, $created]);
                        } else {
                            $stmt = $pdo->prepare("
                                INSERT INTO dosen
                                (nip, username, password, nama, email)
                                VALUES (?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([$nip, $username, $password, $nama, $email]);
                        }

                        $count++;
                    }
                }

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
<title>Import Data Dosen</title>
<link rel="stylesheet" href="../../style.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
<style>
body { margin:0; font-family:'Inter', sans-serif; background:#f4f6f8; }
.container { display:flex; min-height:100vh; }

.sidebar { width: 250px; background:#fff; position:fixed; top:0; left:0; bottom:0; padding:20px; overflow-y:auto; z-index:1000; }
.main-content { margin-left:270px; padding:30px; flex:1; }

form label { display:block; margin-bottom:5px; font-weight:bold; }
form input[type="file"] { display:block; margin-bottom:15px; }
form button { background:#3498db; color:#fff; border:none; padding:10px 18px; border-radius:5px; cursor:pointer; }
form button:hover { background:#2980b9; }

.error-message { color:red; margin-bottom:15px; padding:10px; border-radius:5px; background:#fdd; }

ul { margin-top:10px; list-style:disc; padding-left:20px; }

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
        <h1>Import Data Dosen dari Excel</h1>

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
            <li>nip (wajib)</li>
            <li>username (wajib)</li>
            <li>password (wajib, default 123456 jika kosong)</li>
            <li>nama (wajib)</li>
            <li>email (opsional)</li>
        </ul>
    </div>
</div>
</body>
</html>
