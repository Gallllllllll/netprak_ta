<?php
session_start();
require "../../config/connection.php";
require_once "../../config/base_url.php";

// ===============================
// CEK ROLE MAHASISWA
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';

$id = $_GET['id'] ?? 0;
$upload_dir = "../../uploads/ta/";

// ===============================
// AMBIL DATA PENGAJUAN TA
// ===============================
$stmt = $pdo->prepare("
    SELECT * 
    FROM pengajuan_ta 
    WHERE id = ? AND mahasiswa_id = ?
");
$stmt->execute([$id, $_SESSION['user']['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Pengajuan tidak ditemukan.");
}

// ===============================
// MAPPING FILE
// ===============================
$files = [
    'bukti_pembayaran' => [
        'db' => 'bukti_pembayaran',
        'status' => 'status_bukti_pembayaran',
        'catatan' => 'catatan_bukti_pembayaran',
        'label' => 'Bukti Pembayaran'
    ],
    'formulir' => [
        'db' => 'formulir_pendaftaran',
        'status' => 'status_formulir_pendaftaran',
        'catatan' => 'catatan_formulir_pendaftaran',
        'label' => 'Formulir Pendaftaran'
    ],
    'transkrip' => [
        'db' => 'transkrip_nilai',
        'status' => 'status_transkrip_nilai',
        'catatan' => 'catatan_transkrip_nilai',
        'label' => 'Transkrip Nilai'
    ],
    'magang' => [
        'db' => 'bukti_magang',
        'status' => 'status_bukti_magang',
        'catatan' => 'catatan_bukti_magang',
        'label' => 'Bukti Kelulusan Magang'
    ],
];

// ===============================
// PROSES UPLOAD REVISI
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $updates = [];
    $adaUpload = false;

    foreach ($files as $input => $f) {

        // hanya boleh upload kalau status = revisi
        if (
            ($data[$f['status']] ?? '') === 'revisi' &&
            !empty($_FILES[$input]['name'])
        ) {

            $adaUpload = true;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $nama = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES[$input]['name']);
            $target = $upload_dir . $nama;

            if (move_uploaded_file($_FILES[$input]['tmp_name'], $target)) {

                $updates[] = "{$f['db']} = " . $pdo->quote($nama);
                $updates[] = "{$f['status']} = 'diajukan'";
                $updates[] = "{$f['catatan']} = NULL";

                // histori revisi (opsional)
                $pdo->prepare("
                    INSERT INTO revisi_ta (pengajuan_id, nama_file)
                    VALUES (?, ?)
                ")->execute([$id, $nama]);
            }
        }
    }

    if ($adaUpload && $updates) {
        $sql = "
            UPDATE pengajuan_ta SET
                " . implode(", ", $updates) . ",
                status = 'diajukan'
            WHERE id = ?
        ";
        $pdo->prepare($sql)->execute([$id]);
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
<title>Revisi Pengajuan TA</title>
<style>
body{
    background:#FFF1E5 !important;
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

.card {
    background:#fff;
    border-radius:18px;
    padding:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    overflow-x: hidden;
}

.divider {
    border: none;
    height: 0.5px;
    width: 100% !important;
    background: #FF983D;
    display: block;
    margin: 12px 0;

}.revisi-wrapper {
    max-width:auto;
    margin:auto;
}

.label-muted {
    font-size:13px;
    font-weight:700;
    color:#999;
}

.readonly-box {
    margin-top:8px;
    background:#FFF7F1;
    border:1px solid #FF8C42;
    padding:14px;
    border-radius:12px;
    font-weight:600;
    width: auto;
    font-size:14px;
}

.doc-revisi-item {
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#F9FAFB;
    border-radius:14px;
    padding:14px;
    margin-bottom:12px;
}

.doc-left {
    display:flex;
    gap:12px;
    align-items:center;
}

.doc-icon {
    width:36px;
    height:36px;
    border-radius:10px;
    background:#FFE4E6;
    color:#EF4444;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
}

.btn-file {
    background:#3B82F6;
    color:#fff;
    padding:8px 16px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
}

.center-action {
    display:flex;
    justify-content:center;
    margin:25px 0;
}

.btn-gradient {
    background:linear-gradient(135deg,#FF74C7,#FF983D);
    color:#fff;
    padding:14px 34px;
    border-radius:999px;
    border:none;
    font-weight:600;
    cursor:pointer;
    font-size:14px;
}

.btn-gradient:hover {
    opacity:0.9;
}

/* === BUTTON DISABLED STATE === */
.btn-gradient:disabled {
    background: #E5E7EB !important;
    color: #9CA3AF !important;
    cursor: not-allowed;
    box-shadow: none;
    opacity: 0.9;
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

.file-updated {
    color:#16A34A;
    font-weight:700;
}

.doc-icon.success {
    background:#DCFCE7;
    color:#16A34A;
}

.btn-file.success {
    background:#16A34A;
}

</style>
</head>
<body>

<div class="container">

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="topbar">
        <h1>Form Revisi Pengajuan</h1>

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

    <form method="POST" enctype="multipart/form-data" class="revisi-wrapper">

    <div class="card">
        <div class="judul-head">
            <div class="judul-icon">
                <span class="material-symbols-rounded">docs</span>
            </div>
            <h4 class="judul-title">Penyesuaian Judul TA</h4>
        </div>
        <hr class="divider">

        <label class="label-muted">JUDUL (Tidak bisa diganti)</label>
        <div class="readonly-box">
            <?= htmlspecialchars($data['judul_ta']) ?>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="judul-head">
            <div class="judul-icon">
                <span class="material-symbols-rounded">file_upload</span>
            </div>
            <h4 class="judul-title">Unggah Ulang Dokumen</h4>
        </div>
        <hr class="divider">

        <?php
        $has_revisi = false;
        foreach ($files as $input => $f):
            if (($data[$f['status']] ?? '') === 'revisi'):
                $has_revisi = true;
        ?>
            <div class="doc-revisi-item">
                <div class="doc-left">
                    <div class="doc-icon">
                        <span class="material-symbols-rounded">close</span>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars($f['label']) ?></strong><br>
                        <small class="file-current">
                            File saat ini: <?= htmlspecialchars($data[$f['db']]) ?>
                        </small>

                    </div>
                </div>

                <label class="btn-file">
                    Pilih File Baru
                    <input type="file" name="<?= $input ?>" accept=".pdf" hidden>
                </label>
            </div>
        <?php
            endif;
        endforeach;
        ?>

        <?php if (!$has_revisi): ?>
            <div class="info-box">
                Tidak ada dokumen yang perlu direvisi.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($has_revisi): ?>
        <div class="center-action">
            <button type="submit" class="btn-gradient" disabled>
    Upload Revisi
</button>

        </div>
        
    <?php endif; ?>

    </form>
    <!-- INFO -->
    <div class="info-box">
        <span class="material-symbols-rounded">info</span>
        Pastikan dokumen yang diunggah ulang sudah dalam format PDF dan memiliki ukuran maksimal 2MB per file.
    </div>

    </div>
    

</form>

</div>
</div>
<script>
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        if (!this.files.length) return;

        const fileName = this.files[0].name;

        const item = this.closest('.doc-revisi-item');
        const text = item.querySelector('.file-current');
        const icon = item.querySelector('.doc-icon');
        const iconSpan = icon.querySelector('span');
        const button = item.querySelector('.btn-file');

        // update text
        text.textContent = 'File baru: ' + fileName;
        text.classList.add('file-updated');

        // update icon
        icon.classList.add('success');
        iconSpan.textContent = 'check';

        // update button color
        button.classList.add('success');
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

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* SWEETALERT CUSTOM */
.swal2-popup {
    border-radius: 20px !important;
    padding: 30px !important;
}

.swal2-title {
    color: #FF983D !important;
    font-size: 22px !important;
    font-weight: 700 !important;
}

.swal2-html-container {
    color: #555 !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
}

.swal2-icon.swal2-warning {
    border-color: #FF983D !important;
    color: #FF983D !important;
}

.swal2-confirm {
    background: linear-gradient(135deg, #FF74C7, #FF983D) !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 12px 30px !important;
    font-weight: 700 !important;
    font-size: 14px !important;
}

.swal2-cancel {
    background: #e5e7eb !important;
    color: #374151 !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 12px 30px !important;
    font-weight: 700 !important;
    font-size: 14px !important;
}

.swal2-styled:focus {
    box-shadow: none !important;
}
</style>

<script>
// SWEETALERT KONFIRMASI REVISI
const form = document.querySelector('form.revisi-wrapper');

if (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Cek file yang akan diupload
        const fileInputs = form.querySelectorAll('input[type="file"]');
        let fileList = '';
        let hasFile = false;

        fileInputs.forEach(input => {
            if (input.files.length > 0) {
                hasFile = true;
                const item = input.closest('.doc-revisi-item');
                const label = item.querySelector('strong').textContent;
                
                fileList += `<div style="text-align:left; margin: 8px 0; padding: 8px; background: #f9fafb; border-radius: 8px;">
                    <strong style="color: #FF983D;">✓ ${label}</strong><br>
                    <small style="color: #6b7280;">${input.files[0].name}</small>
                </div>`;
            }
        });

        if (!hasFile) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Ada File yang Dipilih',
                html: 'Silakan pilih file revisi terlebih dahulu sebelum mengunggah.',
                confirmButtonText: 'Baik, Saya Mengerti',
                customClass: {
                    confirmButton: 'swal2-confirm'
                }
            });
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Upload Revisi',
            html: `
                <div style="text-align: left; margin-top: 15px;">
                    <p style="color: #374151; margin-bottom: 12px; font-weight: 600;">
                        Apakah Anda yakin ingin mengunggah ulang dokumen berikut?
                    </p>
                    ${fileList}
                    <div style="margin-top: 16px; padding: 12px; background: #FFF7ED; border: 1px solid #FDBA74; border-radius: 10px;">
                        <small style="color: #9a3412; font-weight: 600;">
                            ⚠️ Dokumen yang diunggah akan menggantikan file lama dan akan diverifikasi ulang oleh admin.
                        </small>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ya, Upload Sekarang',
            cancelButtonText: 'Periksa Kembali',
            customClass: {
                confirmButton: 'swal2-confirm',
                cancelButton: 'swal2-cancel'
            },
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Mengunggah Revisi...',
                    html: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                form.submit();
            }
        });
    });
}
</script>

</body>
</html>