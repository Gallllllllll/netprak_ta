<?php
session_start();
require "../../config/connection.php";

// ===============================
// CEK ROLE MAHASISWA
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}
$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';

$id = $_GET['id'] ?? 0;
$upload_dir = "../../uploads/semhas/";

// ===============================
// AMBIL DATA PENGAJUAN SEMHAS
// ===============================
$stmt = $pdo->prepare("
    SELECT *
    FROM pengajuan_semhas
    WHERE id=? AND mahasiswa_id=?
");
$stmt->execute([$id, $_SESSION['user']['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Pengajuan Seminar Hasil tidak ditemukan.");
}

// ===============================
// MAPPING FILE SEMHAS
// ===============================
$files = [
    'berita_acara' => [
        'db' => 'file_berita_acara',
        'status' => 'status_file_berita_acara',
        'label' => 'Lembar Berita Acara'
    ],
    'persetujuan_laporan' => [
        'db' => 'file_persetujuan_laporan',
        'status' => 'status_file_persetujuan_laporan',
        'label' => 'Lembar Persetujuan Laporan TA'
    ],
    'pendaftaran_ujian' => [
        'db' => 'file_pendaftaran_ujian',
        'status' => 'status_file_pendaftaran_ujian',
        'label' => 'Form Pendaftaran Ujian TA'
    ],
    'buku_konsultasi' => [
        'db' => 'file_buku_konsultasi',
        'status' => 'status_file_buku_konsultasi',
        'label' => 'Buku Konsultasi TA'
    ],
];

// ===============================
// PROSES UPLOAD REVISI
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $updates = [];

    foreach ($files as $input => $f) {

        // hanya boleh upload file yang status-nya revisi
        if (($data[$f['status']] ?? '') === 'revisi' && !empty($_FILES[$input]['name'])) {

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
        // setelah revisi → status global kembali ke diajukan
        $sql = "
            UPDATE pengajuan_semhas
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
    <title>Revisi Seminar Hasil</title>
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
        .mhs-info{
            display:flex;
            align-items:left;
            gap:20px
        }
        .mhs-text span{
            font-size:13px;
            color:#555
        }
        .mhs-text b{
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
        .card {
            background:#fff;
            border-radius:18px;
            padding:15px;
            box-shadow:0 5px 15px rgba(0,0,0,.2);
            overflow-x: hidden;
        }

        .form-card h2{
            text-align: center;
            color: #ff8c42;
        }

        .card-header {
            padding: 0;
            border-bottom: none;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .icon-upload-wrap {
            width: 28px;
            height: 28px;
            background: #FFF7F1;
            border: 1px solid #FF8C42;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FF8C42;
            flex-shrink: 0;
        }

        .card-header h2 {
            font-size: 16px;
            font-weight: 800;
            color: #FF8C42;
            margin: 0;
        }

        .card-body {
            padding: 0;
        }

        .divider {
            border: none;
            height: 0.5px;
            width: 100% !important;
            background: #FF983D;
            display: block;
            margin: 12px 0;
        }

        /* FILE ITEM */
        .file-item-revisi {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            background: #F9FAFB;
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .file-item-content {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 250px;
        }

        .icon-revisi {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #FFE4E6;
            color: #EF4444;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .file-info {
            flex-grow: 1;
        }

        .file-label {
            font-size: 14px;
            font-weight: 700;
            color: #4b5563;
            margin-bottom: 4px;
        }

        .file-current {
            font-size: 12px;
            color: #9ca3af;
            font-style: italic;
        }

        /* CUSTOM FILE INPUT */
        .btn-pilih-file {
            background: #3B82F6;
            color: white;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            white-space: nowrap;
        }

        .btn-pilih-file:hover {
            background: #2563EB;
        }

        /* HIDE DEFAULT INPUT */
        .hidden-input {
            display: none;
        }

        /* SUBMIT BUTTON */
        .btn-submit-wrap {
            display: flex;
            justify-content: center;
            margin: 25px 0;
        }

        .btn-upload-revisi {
            background: linear-gradient(135deg, #FF74C7, #FF983D);
            color: white;
            padding: 14px 34px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 116, 199, 0.2);
            transition: opacity 0.2s;
        }

        .btn-upload-revisi:hover {
            opacity: 0.9;
        }

        /* INFO BOX */
        .info-warning {
            background: #EAF2FF;
            border: 1px solid #93C5FD;
            border-radius: 14px;
            padding: 14px 16px;
            color: #2563EB;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .material-symbols-rounded {
            font-size: 20px;
            vertical-align: middle;
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
        <div class="topbar">
            <h1>Pengajuan Tugas Akhir</h1>

            <div class="mhs-info">
                <div class="mhs-text">
                    <span>Selamat Datang,</span><br>
                    <b><?= htmlspecialchars($username) ?></b>
                </div>
                <div class="avatar">
                    <span class="material-symbols-rounded" style="color:#fff">person</span>
                </div>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <!-- REVISI CARD -->
            <div class="card">
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
                            <div class="file-item-content">
                                <div class="icon-revisi">
                                    <span class="material-symbols-rounded">close</span>
                                </div>
                                <div class="file-info">
                                    <div class="file-label"><?= htmlspecialchars($f['label']) ?></div>
                                    <div class="file-current">File Saat Ini: <?= htmlspecialchars($data[$f['db']] ?: 'Belum ada file') ?></div>
                                </div>
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

            <div class="info-warning">
                <span class="material-symbols-rounded">info</span>
                Pastikan dokumen yang diunggah ulang sudah dalam format PDF dan memiliki ukuran maksimal 2MB per file.
            </div>
        </form>
        
        <div style="margin-top: 25px; text-align: center;">
            <a href="status.php" style="color: #999; text-decoration: none; font-size: 13px; font-weight: 600;">Kembali ke Status</a>
        </div>
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
