<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/ta_netprak/config/base_url.php';

/* CEK LOGIN */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$username = $_SESSION['user']['username'] ?? 'Admin';

/* AMBIL ID */
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

/* LOAD DATA TEMPLATE */
$stmt = $pdo->prepare("SELECT * FROM template WHERE id = ?");
$stmt->execute([$id]);
$template = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$template) {
    header("Location: index.php");
    exit;
}

$error = '';

/* HANDLE SUBMIT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $file = $_FILES['file'] ?? null;

    $filename = $template['file']; // default file lama

    if ($file && $file['tmp_name']) {
        $filename = time().'_'.basename($file['name']);
        move_uploaded_file(
            $file['tmp_name'],
            $_SERVER['DOCUMENT_ROOT']."/coba/uploads/templates/$filename"
        );
    }

    if (!$nama) {
        $error = "Nama template wajib diisi.";
    } else {
        $stmt = $pdo->prepare("UPDATE template SET nama = ?, file = ? WHERE id = ?");
        $stmt->execute([$nama, $filename, $id]);

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
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Edit Template</title>

<style>
body {
    font-family: 'Inter', sans-serif;
    background: #FFF1E5 !important;
    margin: 0;
}

.container {
    background: #FFF1E5 !important;
}

.main-content {
    margin-left: 280px;
    padding: 32px;
    min-height: 100vh;
    background: #FFF1E5 !important;
}
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
    border: 1px solid #f1dcdc;
    max-width: auto;
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

input[type="text"],
input[type="file"] {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #ddd;
    font-size: 14px;
}

input:focus {
    outline: none;
    border-color: #FF983D;
}

/* FILE INFO */
.file-info {
    font-size: 13px;
    color: #555;
    padding-top: 10px;
}

/* BUTTON */
.form-actions {
    display: flex;
    gap: 12px;
    margin-left: 176px;
    margin-top: 20px;
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
    margin-bottom: 16px;
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
    <h1>Edit Template Dokumen</h1>

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

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Nama Template</label>
                <input type="text" name="nama"
                       value="<?= htmlspecialchars($template['nama']) ?>" required>
            </div>

            <div class="form-group">
                <label>File Template</label>
                <div>
                    <input type="file" name="file">
                    <?php if ($template['file']): ?>
                        <div class="file-info">
                            File saat ini:
                            <a href="<?= base_url('uploads/templates/'.$template['file']) ?>"
                               target="_blank">
                                <?= htmlspecialchars($template['file']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
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
