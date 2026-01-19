<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/* CEK LOGIN */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$username = $_SESSION['user']['username'];
$error = '';
$success = 0;

/* HANDLE IMPORT */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel'])) {

    $file = $_FILES['excel'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Terjadi kesalahan saat upload file.";
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xls', 'xlsx'])) {
            $error = "Format file harus XLS atau XLSX.";
        } else {
            try {
                $spreadsheet = IOFactory::load($file['tmp_name']);
                $rows = $spreadsheet->getActiveSheet()->toArray();

                $pdo->beginTransaction();
                $count = 0;

                for ($i = 1; $i < count($rows); $i++) {

                    $nim      = trim($rows[$i][0] ?? '');
                    $nama     = trim($rows[$i][1] ?? '');
                    $username = trim($rows[$i][2] ?? '');
                    $email    = trim($rows[$i][3] ?? '') ?: null;
                    $prodi    = trim($rows[$i][4] ?? '') ?: null;
                    $kelas    = trim($rows[$i][5] ?? '') ?: null;
                    $nomor    = trim($rows[$i][6] ?? '') ?: '0000000000';
                    $rawPass  = trim($rows[$i][7] ?? '') ?: '123456';
                    $password = password_hash($rawPass, PASSWORD_DEFAULT);

                    if (!$nim || !$nama || !$username) {
                        continue;
                    }

                    /* CEK DUPLIKAT */
                    $cek = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ? OR username = ?");
                    $cek->execute([$nim, $username]);

                    if ($cek->rowCount() === 0) {
                        $stmt = $pdo->prepare("
                            INSERT INTO mahasiswa
                            (nim, username, password, nama, email, prodi, kelas, nomor_telepon)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $nim, $username, $password,
                            $nama, $email, $prodi, $kelas, $nomor
                        ]);
                        $count++;
                    }
                }

                $pdo->commit();
                header("Location: index.php?imported=$count");
                exit;

            } catch (Throwable $e) {
                $pdo->rollBack();
                $error = "Gagal membaca file Excel.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Import Mahasiswa</title>

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

/* FORM */
.form-group {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 16px;
    align-items: center;
    margin-bottom: 16px;
}

label {
    font-weight: 700;
    font-size: 14px;
}

input[type="file"] {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
}

/* BUTTON */
.form-actions {
    display: flex;
    gap: 12px;
    margin-left: 196px;
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

.btn:hover { opacity: .9; }

/* ERROR */
.error {
    background: #ffe5e5;
    color: #c0392b;
    padding: 10px 14px;
    border-radius: 10px;
    margin-bottom: 16px;
}

/* INFO / WARNING BOX */
.info-box {
    background: rgba(255, 152, 61, 0.15);
    color: #FF983D;
    border: 1px solid rgba(255, 152, 61, 0.35);
    border-radius: 14px;
    padding: 16px 18px;
    margin-top: 20px;
    font-size: 14px;
}

.info-box strong {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
    margin-bottom: 8px;
}

.info-box ul {
    margin: 0;
    padding-left: 20px;
}

.info-box li {
    margin-bottom: 4px;
    color: #555;
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

<?php include '../sidebar.php'; ?>

<div class="main-content">

    <div class="topbar">
        <h1>Import Data Mahasiswa</h1>

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

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>File Excel</label>
                <input type="file" name="excel" accept=".xls,.xlsx" required>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn secondary">Kembali</a>
                <button type="submit" class="btn">Import</button>
            </div>

        </form>

    <div class="info-box">
        <strong>
            <span class="material-symbols-rounded">info</span>
            Format File Excel
        </strong>
        <ul>
            <li>NIM (wajib)</li>
            <li>Nama (wajib)</li>
            <li>Username (wajib)</li>
            <li>Email (opsional)</li>
            <li>Prodi (opsional)</li>
            <li>Kelas (opsional)</li>
            <li>No Telepon (default: 0000000000)</li>
            <li>Password (default: 123456)</li>
        </ul>
    </div>


    </div>
</div>

</body>
</html>
