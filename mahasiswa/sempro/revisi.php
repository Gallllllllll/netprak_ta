<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

// cek role mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ".base_url('login.php'));
    exit;
}

$id = $_GET['id'] ?? 0;
$upload_dir = "../../uploads/sempro/";

// ambil data pengajuan
$stmt = $pdo->prepare("SELECT * FROM pengajuan_sempro WHERE id=? AND mahasiswa_id=?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Pengajuan Seminar Proposal tidak ditemukan.");

// mapping file
$files = [
    'file_pendaftaran' => [
        'db' => 'file_pendaftaran',
        'status' => 'status_file_pendaftaran',
        'label' => 'File Pendaftaran'
    ],
    'file_persetujuan' => [
        'db' => 'file_persetujuan',
        'status' => 'status_file_persetujuan',
        'label' => 'Lembar Persetujuan'
    ],
    'file_buku_konsultasi' => [
        'db' => 'file_buku_konsultasi',
        'status' => 'status_file_buku_konsultasi',
        'label' => 'Buku Konsultasi'
    ],
];

// proses upload revisi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $updates = [];

    foreach ($files as $input => $f) {

        // hanya boleh upload jika status file = revisi
        $current_status = strtolower($data[$f['status']] ?? '');
        if (str_contains($current_status, 'revisi') && !empty($_FILES[$input]['name'])) {

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = time() . '_' . basename($_FILES[$input]['name']);
            $target = $upload_dir . $filename;

            if (move_uploaded_file($_FILES[$input]['tmp_name'], $target)) {
                $updates[] = "{$f['db']} = " . $pdo->quote($filename);
                $updates[] = "{$f['status']} = 'diajukan'";
            }
        }
    }

    if ($updates) {
        $sql = "
            UPDATE pengajuan_sempro
            SET " . implode(', ', $updates) . ",
                status = 'diajukan'
            WHERE id=?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    header("Location: status.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Revisi Seminar Proposal</title>
<link rel="stylesheet" href="../../style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
}

body {
    font-family: 'Outfit', sans-serif;
    background: #FFF1E5 !important;
    margin: 0;
}

.main-content {
    margin-left: 280px;
    padding: 32px;
    min-height: 100vh;
    background: #FFF1E5 !important;
}

/* TOPBAR */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.topbar h1 {
    color: var(--orange);
    font-size: 28px;
    font-weight: 800;
    margin: 0;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-text {
    text-align: right;
    font-size: 13px;
    color: #555;
}

.user-text b {
    color: var(--orange);
    display: block;
}

.avatar {
    width: 45px;
    height: 45px;
    background: var(--orange);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

/* REVISI CARD */
.revisi-card {
    background: white;
    border-radius: 25px;
    box-shadow: 0 10px 30px rgba(255, 152, 61, 0.1);
    overflow: hidden;
    margin-bottom: 30px;
    border: 1px solid #fce8e8;
}

.card-header {
    padding: 20px 30px;
    border-bottom: 1px solid #fce8e8;
    display: flex;
    align-items: center;
    gap: 15px;
}

.icon-upload-wrap {
    width: 40px;
    height: 40px;
    background: #eef2ff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6366f1;
}

.card-header h2 {
    font-size: 18px;
    font-weight: 700;
    color: #4b5563;
    margin: 0;
}

.card-body {
    padding: 40px 30px;
}

/* FILE ITEM */
.file-item-revisi {
    background: #fcfcfc;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.icon-revisi {
    width: 45px;
    height: 45px;
    border: 1px solid #fee2e2;
    background: #fff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ef4444;
}

.file-info {
    flex-grow: 1;
}

.file-label {
    font-size: 15px;
    font-weight: 700;
    color: #6b7280;
    margin-bottom: 4px;
}

.file-current {
    font-size: 12px;
    color: #9ca3af;
    font-style: italic;
}

/* CUSTOM FILE INPUT */
.btn-pilih-file {
    background: #60a5fa;
    color: white;
    padding: 10px 25px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    white-space: nowrap;
}

.btn-pilih-file:hover {
    background: #3b82f6;
}

/* HIDE DEFAULT INPUT */
.hidden-input {
    display: none;
}

/* SUBMIT BUTTON */
.btn-submit-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
}

.btn-upload-revisi {
    background: var(--gradient);
    color: white;
    padding: 10px 25px;
    border-radius: 20px;
    font-size: 15px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    box-shadow: 0 10px 20px rgba(255, 116, 199, 0.3);
    transition: transform 0.2s, opacity 0.2s;
}

.btn-upload-revisi:hover {
    transform: translateY(-3px);
    opacity: 0.95;
}

/* INFO BAR */
.info-bar {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 20px;
    padding: 20px 30px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(96, 165, 250, 0.1);
}

.icon-info {
    width: 35px;
    height: 35px;
    background: white;
    border-radius: 50%;
    border: 2px solid #60a5fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #60a5fa;
}

.info-text {
    font-size: 14px;
    font-weight: 700;
    color: #3b82f6;
    font-style: italic;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .main-content { margin-left: 0; padding: 20px; }
    .topbar { flex-direction: column; gap: 15px; text-align: center; }
    .user-profile { justify-content: center; }
}
</style>
</head>
<body>

<div class="container" style="display: flex;">

    <?php include "../sidebar.php"; ?>

    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <h1>Form Revisi Pengajuan Seminar Proposal</h1>
            <div class="user-profile">
                <div class="user-text">
                    Selamat Datang,<br>
                    <b><?= htmlspecialchars($_SESSION['user']['nama']) ?></b>
                </div>
                <div class="avatar">
                    <span class="material-symbols-rounded">person</span>
                </div>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <!-- REVISI CARD -->
            <div class="revisi-card">
                <div class="card-header">
                    <div class="icon-upload-wrap">
                        <span class="material-symbols-rounded">upload</span>
                    </div>
                    <h2>Unggah Ulang Dokumen</h2>
                </div>

                <div class="card-body">
                    <?php
                    $ada_revisi = false;
                    foreach ($files as $input => $f):
                        $status_file = strtolower($data[$f['status']] ?? '');
                        if (str_contains($status_file, 'revisi')):
                            $ada_revisi = true;
                    ?>
                        <div class="file-item-revisi">
                            <div class="icon-revisi">
                                <span class="material-symbols-rounded">close</span>
                            </div>
                            <div class="file-info">
                                <div class="file-label"><?= htmlspecialchars($f['label']) ?> Seminar Proposal</div>
                                <div class="file-current">File Saat Ini: <?= htmlspecialchars($data[$f['db']] ?: 'Belum ada file') ?></div>
                            </div>
                            <!-- Custom Label as Button -->
                            <label for="input-<?= $input ?>" class="btn-pilih-file" id="label-<?= $input ?>">
                                Pilih File Baru
                            </label>
                            <input type="file" name="<?= $input ?>" id="input-<?= $input ?>" class="hidden-input" accept=".pdf" required onchange="handleFileSelect('<?= $input ?>')">
                        </div>
                    <?php
                        endif;
                    endforeach;
                    ?>

                    <?php if (!$ada_revisi): ?>
                        <p style="text-align: center; color: #9ca3af; font-style: italic;">
                            Tidak ada dokumen yang perlu direvisi.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($ada_revisi): ?>
                <div class="btn-submit-wrap">
                    <button type="submit" class="btn-upload-revisi">Upload Revisi</button>
                </div>
            <?php endif; ?>

            <!-- INFO FOOTER -->
            <div class="info-bar">
                <div class="icon-info">
                    <span class="material-symbols-rounded">info</span>
                </div>
                <div class="info-text">
                    Pastikan dokumen yang diunggah ulang sudah dalam format PDF dan memiliki ukuran maksimal 2MB per file.
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function handleFileSelect(inputId) {
    const input = document.getElementById('input-' + inputId);
    const label = document.getElementById('label-' + inputId);
    if (input.files.length > 0) {
        label.innerText = 'Sudah Memilih File';
        label.style.background = '#10b981'; // Green to indicate selection
    } else {
        label.innerText = 'Pilih File Baru';
        label.style.background = '#60a5fa';
    }
}
</script>

</body>
</html>
