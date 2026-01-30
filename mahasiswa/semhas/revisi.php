<?php
session_start();
require "../../config/connection.php";
require_once '../../config/base_url.php';

// ===============================
// CEK ROLE MAHASISWA
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ".base_url('login.php'));
    exit;
}
$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';

$id = $_GET['id'] ?? 0;
$upload_dir = "../../uploads/semhas/";

// ===============================
// AMBIL DATA PENGAJUAN SEMHAS
// ===============================
$stmt = $pdo->prepare("SELECT * FROM pengajuan_semhas WHERE id=? AND mahasiswa_id=?");
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
// PROSES UPLOAD REVISI (BACKEND TIDAK DIUBAH)
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $updates = [];

    foreach ($files as $input => $f) {
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
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Revisi Seminar Hasil</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

<style>
body{
    background:#FFF1E5 !important;
}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px}
.topbar h1{color:#ff8c42;font-size:28px}

.mhs-info{display:flex;gap:20px}
.mhs-text span{font-size:13px;color:#555}
.mhs-text b{color:#ff8c42;font-size:14px}

.avatar{width:42px;height:42px;background:#ff8c42;border-radius:50%;display:flex;align-items:center;justify-content:center}

.card{background:#fff;border-radius:18px;padding:15px;box-shadow:0 5px 15px rgba(0,0,0,.2)}

.divider{border:none;height:.5px;background:#FF983D;margin:12px 0}

.doc-revisi-item{display:flex;justify-content:space-between;align-items:center;background:#F9FAFB;border-radius:14px;padding:14px;margin-bottom:12px;flex-wrap:wrap}

.doc-left{display:flex;gap:12px;align-items:center;min-width:250px}

.doc-icon{width:36px;height:36px;border-radius:10px;background:#FFE4E6;color:#EF4444;display:flex;align-items:center;justify-content:center;font-weight:800}

.btn-file{background:#3B82F6;color:#fff;padding:8px 16px;border-radius:999px;font-size:13px;font-weight:700;cursor:pointer}

.center-action{display:flex;justify-content:center;margin:25px 0}

.btn-gradient{background:linear-gradient(135deg,#FF74C7,#FF983D);color:#fff;padding:14px 34px;border-radius:999px;border:none;font-weight:600;cursor:pointer}

/* === BUTTON DISABLED STATE === */
.btn-gradient:disabled {
    background: #E5E7EB !important;
    color: #9CA3AF !important;
    cursor: not-allowed;
    box-shadow: none;
    opacity: 0.9;
}

.judul-head{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom: auto;
}

.judul-icon{
    width: 25px;
    height: 25px;
    border-radius: 8px;
    display:flex;
    align-items:center;
    justify-content:center;
    background: #FFF7F1;
    border: 1px solid #FF8C42;
    flex-shrink: 0;
    padding:6px;
}

.judul-icon span{
    color:#FF8C42;
}

.judul-title{
    margin:0;
    font-size: 16px;
    font-weight: 800;
    color:#FF8C42;
}

.info-box {
    background:#EAF2FF;
    border:1px solid #93C5FD;
    border-radius:16px;
    padding:16px;
    color:#2563EB;
    font-size:13px;
    display:flex;
}

.info-box span{
    font-size:18px;
    margin-right:8px;
}
.file-updated{color:#16A34A;font-weight:700}

.doc-icon.success{background:#DCFCE7;color:#16A34A}
.btn-file.success{background:#16A34A}
</style>
</head>
<body>

<div class="container">
<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="topbar">
        <h1>Form Revisi Seminar Hasil</h1>
        <div class="mhs-info">
            <div class="mhs-text">
                <span>Selamat Datang,</span><br>
                <b><?= htmlspecialchars($username) ?></b>
            </div>
            <div class="avatar"><span class="material-symbols-rounded" style="color:#fff">person</span></div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="card">
        <div class="judul-head">
            <div class="judul-icon">
                <span class="material-symbols-rounded">docs</span>
            </div>
            <h4 class="judul-title">Unggah Ulang Dokumen</h4>
        </div>
            <hr class="divider">

            <?php $has_revisi=false; foreach($files as $input=>$f): if(($data[$f['status']]??'')==='revisi'): $has_revisi=true; ?>
            <div class="doc-revisi-item">
                <div class="doc-left">
                    <div class="doc-icon"><span class="material-symbols-rounded">close</span></div>
                    <div>
                        <strong><?= htmlspecialchars($f['label']) ?></strong><br>
                        <small class="file-current">File saat ini: <?= htmlspecialchars($data[$f['db']] ?: '-') ?></small>
                    </div>
                </div>
                <label class="btn-file">
                    Pilih File Baru
                    <input type="file" name="<?= $input ?>" accept=".pdf" hidden>
                </label>
            </div>
            <?php endif; endforeach; ?>

            <?php if(!$has_revisi): ?>
                <div class="info-box">Tidak ada dokumen yang perlu direvisi.</div>
            <?php endif; ?>
        </div>

        <?php if($has_revisi): ?>
        <div class="center-action">
            <button type="submit" class="btn-gradient" disabled>
                Upload Revisi
            </button>
        </div>
        <?php endif; ?>
    </form>

    <div class="info-box">
        <span class="material-symbols-rounded">info</span>
        Pastikan dokumen yang diunggah ulang sudah dalam format PDF dan memiliki ukuran maksimal 2MB per file.
    </div>
</div>
</div>

<script>
document.querySelectorAll('input[type="file"]').forEach(input=>{
    input.addEventListener('change',function(){
        if(!this.files.length)return;
        const item=this.closest('.doc-revisi-item');
        item.querySelector('.file-current').textContent='File baru: '+this.files[0].name;
        item.querySelector('.file-current').classList.add('file-updated');
        item.querySelector('.doc-icon').classList.add('success');
        item.querySelector('.doc-icon span').textContent='check';
        item.querySelector('.btn-file').classList.add('success');
    });
});
</script>
<script>
const submitBtn = document.querySelector('.btn-gradient');
const fileInputs = document.querySelectorAll('input[type="file"]');

function checkFileSelected() {
    let hasFile = false;

    fileInputs.forEach(input => {
        if (input.files.length > 0) {
            hasFile = true;
        }
    });

    submitBtn.disabled = !hasFile;
}

// cek awal (pastikan disabled)
checkFileSelected();

// event change tiap input file
fileInputs.forEach(input => {
    input.addEventListener('change', checkFileSelected);
});
</script>
</body>
</html>