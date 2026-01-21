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

<!-- Include Sidebar CSS -->
<link rel="stylesheet" href="<?= base_url('style.css') ?>">

<style>
/* ==============================
   MAIN CONTENT STYLE
============================== */
.main-content {
    margin-left: 270px;
    padding: 28px 32px;
    margin-bottom: 60px;
}

.card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 14px rgba(0,0,0,0.08);
    border: 1px solid #f1dcdc;
}

.card h2 {
    margin: 0;
    font-size: 20px;
    color: #2f3e55;
}

.card p {
    margin-top: 10px;
    font-size: 14px;
    color: #6b7280;
}

.form-group {
    margin-top: 18px;
}

label {
    font-weight: 600;
    color: #2f3e55;
}

input[type=file] {
    width: 100%;
    margin-top: 10px;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    background: #fafafa;
}

button {
    padding: 12px 20px;
    background: var(--gradient);
    border: none;
    color: #fff;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    margin-top: 14px;
}

button:hover {
    opacity: 0.9;
}

.error {
    color: #ff3b3b;
    margin-top: 12px;
    font-weight: 600;
}
</style>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/coba/dosen/sidebar.php'; ?>

<div class="main-content">
    <div class="card">
        <h2>Upload Persetujuan Sempro</h2>

        <p>
            <b>Mahasiswa:</b> <?= htmlspecialchars($data['nama']) ?><br>
            <b>Judul TA:</b> <?= htmlspecialchars($data['judul_ta']) ?>
        </p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>File Persetujuan</label>
                <input type="file" name="file" accept="application/pdf" required>
                <small>Format: PDF</small><br>
            </div>
            <button type="submit">Upload & Setujui</button>
        </form>
    </div>
</div>

</body>
</html>
