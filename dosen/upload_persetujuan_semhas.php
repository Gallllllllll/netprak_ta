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

// Ambil nama dosen dari database
$stmt = $pdo->prepare("SELECT nama FROM dosen WHERE id = ?");
$stmt->execute([$dosen_id]);
$dosen = $stmt->fetch(PDO::FETCH_ASSOC);
$nama_dosen = $dosen['nama'] ?? 'Dosen';

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
$success = false;

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

                $success = true;

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root {
    --gradient: linear-gradient(135deg, #FF6B9D 0%, #FF8E3C 100%);
    --bg: #FFF1E5;
    --text: #1F2937;
    --muted: #6B7280;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    color: var(--text);
}

.main-content {
    margin-left: 280px;
    padding: 40px;
    min-height: 100vh;
    background: var(--bg);
}

/* HEADER */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.greeting h1 {
    font-size: 28px;
    margin: 0;
    color: #FF8E3C;
    font-weight: 700;
}

.greeting p {
    margin: 5px 0 0;
    color: var(--muted);
    font-size: 15px;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-profile .text {
    text-align: right;
    line-height: 1.3;
}

.admin-profile small {
    color: var(--muted);
    font-size: 12px;
    display: block;
}

.admin-profile b {
    color: #FF8E3C;
    font-size: 14px;
    display: block;
}

.avatar {
    width: 48px;
    height: 48px;
    background: #FF8E3C;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(255, 142, 60, 0.3);
}

/* CARD HEADER */
.card-header {
    background: var(--gradient);
    padding: 32px;
    border-radius: 24px;
    color: white;
    margin-bottom: 24px;
}

.card-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}

.card-header p {
    margin: 12px 0 0;
    opacity: 0.95;
    font-size: 15px;
    line-height: 1.6;
}

/* FORM CARD */
.form-card {
    background: white;
    padding: 32px;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(255, 140, 80, 0.12);
}

.form-card h3 {
    margin: 0 0 8px;
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
}

.form-card .subtitle {
    color: var(--muted);
    font-size: 14px;
    margin: 0 0 24px;
}

/* INFO BOX */
.info-box {
    background: #FFF0F5;
    border-left: 4px solid #FF6B9D;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 28px;
}

.info-row {
    display: flex;
    gap: 12px;
    margin-bottom: 8px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    font-weight: 600;
    color: var(--text);
    min-width: 100px;
}

.info-value {
    color: var(--text);
}

/* ALERT BOX */
.alert-box {
    background: #FEF2F2;
    border-left: 4px solid #EF4444;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.alert-box .material-symbols-rounded {
    color: #EF4444;
    font-size: 24px;
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-weight: 700;
    color: #991B1B;
    margin-bottom: 4px;
}

.alert-text {
    color: #7F1D1D;
    font-size: 14px;
    line-height: 1.5;
}

/* FILE UPLOAD AREA */
.upload-section {
    margin-top: 28px;
}

.upload-label {
    font-weight: 600;
    font-size: 15px;
    color: var(--text);
    display: block;
    margin-bottom: 12px;
}

.upload-area {
    border: 2px dashed #FFB4C8;
    background: #FFF5F8;
    border-radius: 16px;
    padding: 48px 32px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.upload-area:hover {
    border-color: #FF6B9D;
    background: #FFE4EC;
}

.upload-area.dragover {
    border-color: #FF6B9D;
    background: #FFE4EC;
    transform: scale(1.02);
}

.upload-area.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.upload-icon {
    width: 80px;
    height: 80px;
    background: var(--gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.upload-icon .material-symbols-rounded {
    font-size: 40px;
    color: white;
}

.upload-text {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 8px;
}

.upload-subtext {
    font-size: 14px;
    color: var(--muted);
}

#fileInput {
    display: none;
}

.file-info {
    display: none;
    margin-top: 16px;
    padding: 16px;
    background: #E0F2FE;
    border-radius: 12px;
    text-align: left;
}

.file-info.show {
    display: block;
}

.file-name {
    font-weight: 600;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}

.file-name .material-symbols-rounded {
    color: #0369A1;
}

/* FORMAT INFO */
.format-info {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 16px;
    padding: 12px 16px;
    background: #FEF3C7;
    border-radius: 10px;
    font-size: 13px;
    color: #92400E;
}

.format-info .material-symbols-rounded {
    font-size: 20px;
}

/* BUTTON */
.btn-submit {
    width: 100%;
    padding: 16px;
    background: var(--gradient);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 24px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 107, 157, 0.4);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}


/* RESPONSIVE */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 20px;
    }

    .footer {
        left: 0;
    }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .admin-profile {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">

<!-- HEADER -->
<div class="topbar">
    <div class="greeting">
        
    </div>
    <div class="admin-profile">
        <div class="text">
            <small>Selamat Datang,</small>
            <b><?= htmlspecialchars($nama_dosen) ?></b>
        </div>
        <div class="avatar">
            <span class="material-symbols-rounded" style="color:white">person</span>
        </div>
    </div>
</div>

<!-- CARD HEADER -->
<div class="card-header">
    <h2>Upload Pengajuan Semhas</h2>
    <p>Silakan mengunggah dokumen persetujuan seminar hasil untuk mahasiswa. Pastikan file yang diunggah sudah sesuai dengan format dan telah menjelaskan persetujuan dan pembimbing.</p>
</div>

<!-- FORM CARD -->
<div class="form-card">
    <h3>Formulir Upload</h3>
    <p class="subtitle">Silahkan lengkapi data dan Upload file persetujuan seminar hasil anda</p>

    <!-- INFO BOX -->
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Mahasiswa</span>
            <span class="info-value">: <?= htmlspecialchars($data['nama']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Judul TA</span>
            <span class="info-value">: <?= htmlspecialchars($data['judul_ta']) ?></span>
        </div>
    </div>

    <?php if ($blokir_upload): ?>
    <!-- ALERT BOX -->
    <div class="alert-box">
        <span class="material-symbols-rounded">error</span>
        <div class="alert-content">
            <div class="alert-title">Upload Diblokir</div>
            <div class="alert-text">
                Nilai Seminar Proposal belum diinput atau masih 0.<br>
                Upload persetujuan SEMHAS tidak dapat dilakukan.
            </div>
        </div>
    </div>
    <?php else: ?>
    
    <form method="post" enctype="multipart/form-data" id="uploadForm">
        
        <!-- FILE UPLOAD SECTION -->
        <div class="upload-section">
            <label class="upload-label">File Persetujuan</label>
            
            <div class="upload-area" id="uploadArea">
                <div class="upload-icon">
                    <span class="material-symbols-rounded">cloud_upload</span>
                </div>
                <div class="upload-text">Klik atau tarik file ke sini</div>
                <div class="upload-subtext">Unggah File persetujuan seminar hasil anda</div>
                <input type="file" name="file" id="fileInput" accept="application/pdf" required>
            </div>

            <div class="file-info" id="fileInfo">
                <div class="file-name">
                    <span class="material-symbols-rounded">description</span>
                    <span id="fileName"></span>
                </div>
            </div>

            <div class="format-info">
                <span class="material-symbols-rounded">info</span>
                <span>Format : PDF atau Doc (Maksimal 10 MB)</span>
            </div>
        </div>

        <!-- SUBMIT BUTTON -->
        <button type="submit" class="btn-submit" id="submitBtn">
            <span class="material-symbols-rounded">upload</span>
            <span>Upload & Setujui</span>
        </button>

    </form>
    
    <?php endif; ?>
</div>

</div>



<?php if (!$blokir_upload): ?>
<script>
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('fileInput');
const fileInfo = document.getElementById('fileInfo');
const fileName = document.getElementById('fileName');
const uploadForm = document.getElementById('uploadForm');
const submitBtn = document.getElementById('submitBtn');

// Click to upload
uploadArea.addEventListener('click', () => {
    fileInput.click();
});

// File selected
fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        const file = e.target.files[0];
        fileName.textContent = file.name;
        fileInfo.classList.add('show');
    }
});

// Drag and drop
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        const file = e.dataTransfer.files[0];
        fileName.textContent = file.name;
        fileInfo.classList.add('show');
    }
});

// Form submission with confirmation
uploadForm.addEventListener('submit', (e) => {
    e.preventDefault(); // Prevent default submission
    
    // Check if file is selected
    if (!fileInput.files || fileInput.files.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'File Belum Dipilih',
            text: 'Silakan pilih file PDF terlebih dahulu',
            confirmButtonText: 'OK',
            confirmButtonColor: '#FF6B9D',
            background: '#fff'
        });
        return;
    }
    
    // Show confirmation dialog
    Swal.fire({
        icon: 'question',
        title: 'Konfirmasi Upload',
        html: `
            <p style="margin-bottom: 12px;">Apakah Anda yakin ingin mengupload dan menyetujui persetujuan semhas untuk:</p>
            <div style="background: #FFF0F5; padding: 16px; border-radius: 12px; text-align: left; margin-top: 12px;">
                <p style="margin: 0 0 8px 0;"><strong>Mahasiswa:</strong> <?= htmlspecialchars($data['nama']) ?></p>
                <p style="margin: 0;"><strong>File:</strong> ${fileInput.files[0].name}</p>
            </div>
            <p style="margin-top: 12px; color: #6B7280; font-size: 14px;">Setelah disetujui, status akan otomatis berubah.</p>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ya, Upload & Setujui',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#FF6B9D',
        cancelButtonColor: '#6B7280',
        reverseButtons: true,
        customClass: {
            popup: 'custom-swal',
            confirmButton: 'custom-swal-btn',
            cancelButton: 'custom-swal-btn'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // User confirmed, proceed with upload
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-rounded">hourglass_empty</span><span>Uploading...</span>';
            uploadForm.submit(); // Now submit the form
        }
    });
});

<?php if ($success): ?>
// Success alert
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: 'File persetujuan semhas berhasil diupload',
    confirmButtonText: 'OK',
    confirmButtonColor: '#FF6B9D',
    background: '#fff',
    customClass: {
        popup: 'custom-swal',
        confirmButton: 'custom-swal-btn'
    }
}).then(() => {
    window.location.href = 'mahasiswa_bimbingan.php';
});
<?php endif; ?>

<?php if ($error): ?>
// Error alert
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '<?= addslashes($error) ?>',
    confirmButtonText: 'OK',
    confirmButtonColor: '#FF6B9D',
    background: '#fff'
});
<?php endif; ?>
</script>
<?php endif; ?>

<style>
/* Custom SweetAlert Style */
.custom-swal {
    border-radius: 20px !important;
    font-family: 'Inter', sans-serif !important;
}

.custom-swal-btn {
    border-radius: 10px !important;
    padding: 12px 32px !important;
    font-weight: 600 !important;
}

.swal2-icon.swal2-success {
    border-color: #10B981 !important;
    color: #10B981 !important;
}

.swal2-icon.swal2-success [class^='swal2-success-line'] {
    background-color: #10B981 !important;
}

.swal2-icon.swal2-success .swal2-success-ring {
    border-color: rgba(16, 185, 129, 0.3) !important;
}
</style>

</body>
</html>