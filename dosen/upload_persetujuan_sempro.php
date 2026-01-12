<?php
session_start();
require "../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

// validasi role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . base_url('login.php'));
    exit;
}

if (!isset($_GET['id'])) {
    die("ID tidak valid");
}

$id = (int) $_GET['id'];
$dosen_id = $_SESSION['user']['id'];

/* ===============================
   VALIDASI DATA DOSBING
================================ */
$stmt = $pdo->prepare("
    SELECT d.id, m.nama, p.judul_ta
    FROM dosbing_ta d
    JOIN pengajuan_ta p ON d.pengajuan_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    WHERE d.id = ? AND d.dosen_id = ?
");
$stmt->execute([$id, $dosen_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data tidak ditemukan atau bukan hak akses Anda");
}

$error = '';

/* ===============================
   PROSES UPLOAD
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
        $error = "File wajib diupload";
    } else {

        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            $error = "File harus PDF";
        } else {

            $filename = 'persetujuan_sempro_' . $id . '_' . time() . '.pdf';

            // gunakan absolute path dan buat folder otomatis
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/coba/uploads/persetujuan_sempro/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {

                $stmt = $pdo->prepare("
                    UPDATE dosbing_ta
                    SET persetujuan_sempro = ?, status_persetujuan = 'disetujui'
                    WHERE id = ? AND dosen_id = ?
                ");
                $stmt->execute([$filename, $id, $dosen_id]);

                header("Location: mahasiswa_bimbingan.php");
                exit;

            } else {
                $error = "Gagal menyimpan file. Periksa folder uploads dan permissionnya.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Upload Persetujuan Sempro</title>
<link rel="stylesheet" href="<?= base_url('style.css') ?>">

<style>
.container {
    max-width: 500px;
    margin: 60px auto;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
h2 { margin-bottom: 10px; }
label { font-weight: bold; }
input[type=file] {
    width: 100%;
    margin: 10px 0;
}
button {
    padding: 10px 20px;
    background: #007bff;
    border: none;
    color: #fff;
    border-radius: 4px;
    cursor: pointer;
}
button:hover {
    background: #0056b3;
}
.error {
    color: red;
    margin-bottom: 10px;
}
</style>
</head>
<body>

<div class="container">
    <h2>Upload Persetujuan Sempro</h2>

    <p>
        <b>Mahasiswa:</b> <?= htmlspecialchars($data['nama']) ?><br>
        <b>Judul TA:</b> <?= htmlspecialchars($data['judul_ta']) ?>
    </p>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>File Persetujuan (PDF)</label>
        <input type="file" name="file" accept="application/pdf" required>
        <br><br>
        <button type="submit">Upload & Setujui</button>
    </form>
</div>

</body>
</html>
