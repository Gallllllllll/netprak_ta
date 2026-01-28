<?php
session_start();
require "../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

/* ===============================
   VALIDASI ROLE DOSEN
================================ */
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
   VALIDASI DOSBING
================================ */
$stmt = $pdo->prepare("
    SELECT 
        d.id,
        d.pengajuan_id,
        d.status_persetujuan,
        m.nama,
        p.judul_ta
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

/* ===============================
   CEK SEMPRO SUDAH DISETUJUI
================================ */
if ($data['status_persetujuan'] !== 'disetujui') {
    die("Persetujuan SEMPRO belum disetujui. Tidak dapat upload persetujuan SEMHAS.");
}

/* ===============================
   CEK NILAI SEMPRO
================================ */
$stmt = $pdo->prepare("
    SELECT AVG(ns.nilai) AS rata_nilai
    FROM nilai_sempro ns
    JOIN pengajuan_sempro ps ON ns.pengajuan_id = ps.id
    WHERE ps.pengajuan_ta_id = ?
");
$stmt->execute([$data['pengajuan_id']]);
$nilaiSempro = $stmt->fetch(PDO::FETCH_ASSOC);

$blokir_upload = false;

if (!$nilaiSempro || $nilaiSempro['rata_nilai'] === null || $nilaiSempro['rata_nilai'] <= 0) {
    $blokir_upload = true;
}

$error = '';

/* ===============================
   PROSES UPLOAD
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$blokir_upload) {

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
        $error = "File wajib diupload";
    } else {

        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            $error = "File harus PDF";
        } else {

            $filename = 'persetujuan_semhas_' . $id . '_' . time() . '.pdf';

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/coba/uploads/persetujuan_semhas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {

                $stmt = $pdo->prepare("
                    UPDATE dosbing_ta
                    SET 
                        persetujuan_semhas = ?,
                        status_persetujuan_semhas = 'disetujui'
                    WHERE id = ? AND dosen_id = ?
                ");
                $stmt->execute([$filename, $id, $dosen_id]);

                header("Location: mahasiswa_bimbingan.php");
                exit;

            } else {
                $error = "Gagal menyimpan file. Periksa folder uploads dan permission.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Upload Persetujuan Semhas</title>

<link rel="stylesheet" href="<?= base_url('style.css') ?>">

<style>
.main-content {
    margin-left: 270px;
    padding: 28px 32px;
}

.card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 14px rgba(0,0,0,0.08);
    border: 1px solid #f1dcdc;
}

.card h2 {
    font-size: 20px;
    color: #2f3e55;
}

.card p {
    margin-top: 10px;
    font-size: 14px;
    color: #6b7280;
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
    margin-top: 14px;
    padding: 12px 20px;
    background: var(--gradient);
    border: none;
    color: #fff;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
}

.error {
    margin-top: 12px;
    color: red;
    font-weight: 600;
}
</style>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/coba/dosen/sidebar.php'; ?>

<div class="main-content">
    <div class="card">
        <h2>Upload Persetujuan Semhas</h2>

        <p>
            <b>Mahasiswa:</b> <?= htmlspecialchars($data['nama']) ?><br>
            <b>Judul TA:</b> <?= htmlspecialchars($data['judul_ta']) ?>
        </p>

        <?php if ($blokir_upload): ?>
            <div class="error">
                Nilai Seminar Proposal belum diinput atau masih 0.<br>
                Upload persetujuan SEMHAS tidak dapat dilakukan.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!$blokir_upload): ?>
        <form method="post" enctype="multipart/form-data">
            <label>File Persetujuan SEMHAS (PDF)</label>
            <input type="file" name="file" accept="application/pdf" required>
            <button type="submit">Upload & Setujui</button>
        </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
