<?php
session_start();
require "../../config/connection.php";
require_once '../../config/base_url.php';

/* ===============================
   CEK LOGIN MAHASISWA
================================ */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ".base_url('login.php'));
    exit;
}
$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';
$mahasiswa_id = $_SESSION['user']['id'];

$pesan_error  = '';
$boleh_upload = false;

/* ===============================
   CEK SUDAH AJUKAN SEMHAS
================================ */
$cek = $pdo->prepare("
    SELECT id, status, created_at
    FROM pengajuan_semhas
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$cek->execute([$mahasiswa_id]);
$semhas = $cek->fetch(PDO::FETCH_ASSOC);

if ($semhas) {
    $pesan_error = "Anda sudah pernah mengajukan Seminar Hasil. Silakan cek detail pengajuan Anda di bawah ini untuk melihat progres evaluasi dari Admin.";
}

/* ===============================
   LANJUT CEK SYARAT (JIKA BELUM AJUKAN)
================================ */
if (!$pesan_error) {

    /* ===============================
       CEK TUGAS AKHIR
    ================================ */
    $stmt = $pdo->prepare("
        SELECT id, status 
        FROM pengajuan_ta
        WHERE mahasiswa_id = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$mahasiswa_id]);
    $ta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ta || $ta['status'] !== 'disetujui') {
        $pesan_error = "Tugas Akhir belum disetujui.";

    } else {

        /* ===============================
           CEK SEMPRO
        ================================ */
        $cek = $pdo->prepare("
            SELECT sp.status, ns.nilai
            FROM pengajuan_sempro sp
            LEFT JOIN nilai_sempro ns ON sp.id = ns.pengajuan_id
            WHERE sp.mahasiswa_id = ?
            ORDER BY sp.created_at DESC
            LIMIT 1
        ");
        $cek->execute([$mahasiswa_id]);
        $sempro = $cek->fetch(PDO::FETCH_ASSOC);

        if (!$sempro || $sempro['status'] !== 'disetujui') {
            $pesan_error = "Mohon maaf, Anda belum dapat melakukan pengajuan Seminar Hasil. Sistem mendeteksi bahwa status Seminar Proposal Anda belum disetujui atau belum selesai diproses.";

        } elseif ($sempro['nilai'] === null) {
            $pesan_error = "Nilai Seminar Proposal belum tersedia.";

        } elseif ($sempro['nilai'] < 65) {
            $pesan_error = "Anda tidak lulus Seminar Proposal.";

        } else {

            /* ===============================
               CEK DOSEN PEMBIMBING
            ================================ */
            $cek = $pdo->prepare("
                SELECT COUNT(*) 
                FROM dosbing_ta
                WHERE pengajuan_id = ?
                  AND status_persetujuan_semhas = 'disetujui'
            ");
            $cek->execute([$ta['id']]);
            $jumlah_setuju = $cek->fetchColumn();

            if ($jumlah_setuju < 2) {
                $pesan_error = "Persetujuan Seminar Hasil dari kedua dosen pembimbing belum lengkap.";
            } else {
                $boleh_upload = true;
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
<title>Pengajuan Seminar Hasil</title>

<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
}
/* TOP */
body{
    background:#FFF1E5 !important;
}
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
.card h2{
    text-align: center;
    color:#ff8c42;
}
.divider {
    border: none;
    height: 0.5px;
    width: 100% !important;
    background: #FF983D;
    display: block;
    margin: 12px 0;
}

/* ==============================
   ERROR CARD (SUDAH PERNAH AJUAN)
============================== */
.ta-error-card{
    background: #FFDFE0;
    border-radius: 18px;
    padding: 15px 15px;
    border: #FF3A3D 1px solid;
    box-shadow: 0 3px 15px rgba(0,0,0,.10);
}

.ta-error-head{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom: auto;
}

.ta-error-icon{
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display:flex;
    align-items:center;
    justify-content:center;
    background: #fff;
    border: 1px solid #FF3A3D;
    flex-shrink: 0;
}

.ta-error-icon span{
    color:#FF3A3D;
    font-size: 22px;
}

.ta-error-title{
    margin:0;
    font-size: 16px;
    font-weight: 800;
    color:#FF3A3D;
}

.ta-error-desc{
    margin: 2px 0 0;
    font-size: 14px;
    color:#6b7280;
}

/* isi message */
.ta-error-body{
    background: #f5f5f5;
    border: 1px solid #9f9f9f;
    border-radius: 14px;
    padding: 14px 16px;
    color: #555;
    font-size: 14px;
    margin-top: 8px;
}

.ta-error-label{
    margin-top: 20px;
    font-size: 14px;
    font-weight: 700;
    color: #ff8c42;
}

.ta-textbox { 
    background:#f5f5f5; 
    color:#555;
    font-size:14px;
    font-weight:700; 
    padding:8px; 
    border-radius:6px;
    margin-top:8px;
    margin-bottom:15px; 
    width: fit-content; 
}

.ta-info-header {
    display:flex;
    align-items:center;
    gap:10px;
    font-weight:700;
    color:#ff8c42;
    margin-bottom:12px;
}

.ta-info-header .material-symbols-rounded {
    font-size:20px;
}

.ta-badge {
    margin-left:auto;
    padding:6px 14px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    border:1px solid;
}

/* STATUS COLORS */
.badge-green {
    color:#16A34A;
    background:rgba(22,163,74,.15);
    border-color:rgba(22,163,74,.4);
}
.badge-blue {
    color:#2563EB;
    background:rgba(37,99,235,.15);
    border-color:rgba(37,99,235,.4);
}
.badge-orange {
    color:#FF983D;
    background:rgba(255,152,61,.18);
    border-color:rgba(255,152,61,.4);
}
.badge-red {
    color:#DC2626;
    background:rgba(220,38,38,.15);
    border-color:rgba(220,38,38,.4);
}
.badge-gray {
    color:#6B7280;
    background:#f3f4f6;
    border-color:#d1d5db;
}

.ta-judul {
    font-size:14px;
    font-weight:700;
    margin-bottom:6px;
    color:#ff8c42;
}

.ta-judul-box {
    background:#f9fafb;
    border-radius:12px;
    padding:14px 16px;
    font-size:14px;
    font-weight:600;
    color:#444;
    line-height:1.5;
}

.dosen-section {
    margin-top:16px;
}

.dosen-item {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:12px 0;
    border-bottom:1px solid #e5e7eb;
}

.dosen-item:last-child {
    border-bottom:none;
}

.dosen-left {
    display:flex;
    gap:12px;
    align-items:center;
}

.dosen-avatar {
    width:40px;
    height:40px;
    background:#f3f4f6;
    border-radius:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#6b7280;
}

.dosen-item div:nth-child(2) {
    font-size:14px;
    font-weight:600;
}

.dosen-role {
    font-size:11px;
    color:#9CA3AF;
}

/* STATUS BADGE */
.status-wait {
    padding:6px 14px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    color:#FB923C;
    border:1px solid #FDBA74;
    background:#FFF7ED;
}

.status-ok {
    padding:6px 14px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    color:#22C55E;
    border:1px solid #86EFAC;
    background:#ECFDF5;
}

/* INFO BOX */
.info-box {
    background: #5F5F5F;
    color: #ffffff;
    border: 1px solid rgba(255, 152, 61, 0.35);
    border-radius: 14px;
    padding: 16px 18px;
    margin-top: 20px;
    font-size: 14px;
}

.info-box strong {
    display:flex;
    align-items:center;
    gap:6px;
    margin-bottom:8px;
    justify-content: center;
    font-size: 18px;
}

.info-box li {
    margin-bottom:4px;
    color:#ffffff;
}

.pretty-ol {
    list-style: none;        
    padding-left: 0;
    margin: 0;
    counter-reset: step;     
}

.pretty-ol li {
    counter-increment: step;
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 10px;
    color: #fff;             
    font-size: 14px;
    line-height: 1.5;
}

.pretty-ol li::before {
    content: counter(step);
    min-width: 28px;
    height: 28px;
    border: 1px solid #fff;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}

.material-symbols-rounded {
    font-size: 20px;
    vertical-align: middle;
}

/* tombol */
.ta-error-actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top: 14px;
}
.ta-btn-secondary{
    background:#e5e7eb;
    color:#374151;
    border:none;
    padding:10px 16px;
    border-radius:12px;
    font-weight:700;
    cursor:pointer;
    text-decoration:none;
    font-size:13px;
}
.ta-btn-secondary:hover{
    opacity:.9;
}

/* ACTION */
.ta-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 18px;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 152, 61, 0.25);
}

.ta-btn {
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:10px 16px;
    border-radius:10px;
    font-size:13px;
    font-weight:600;
    text-decoration:none;
    letter-spacing: .3px;
    background: linear-gradient(135deg, #FF74C7, #FF983D);
    color: #ffffff;
    border:1px solid #FF983D;
    cursor: pointer;
    width: fit-content;
    margin: auto;
}
.ta-btn-primary:hover {
    opacity: .9;
}

/* UPLOAD GRID */
.ta-upload-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 10px;
}

.ta-upload-item {
    flex: 1 1 calc(50% - 8px);
    min-width: 240px;
}

/* UPLOAD BOX */
.ta-upload-box {
    position: relative;
    background: #fff7f1;
    border: 1px dashed rgba(255, 152, 61, 0.7);
    border-radius: 16px;
    padding: 16px;
    transition: .2s ease;
    cursor: pointer;
}

.ta-upload-box:hover {
    border-color: #FF74C7;
    background: rgba(255, 116, 199, 0.07);
}

.ta-upload-inner {
    display: flex;
    gap: 12px;
    align-items: center;
}

.ta-upload-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(255,116,199,.25), rgba(255,152,61,.22));
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid rgba(255, 152, 61, 0.35);
}

.ta-upload-icon span {
    color: #FF983D;
    font-size: 24px;
}

.ta-upload-text {
    flex: 1;
}

.ta-upload-text strong {
    display: block;
    font-size: 13px;
    color: #374151;
    margin-bottom: 2px;
}

.ta-upload-text small {
    font-size: 12px;
    color: #6b7280;
}

/* INPUT FILE HIDDEN FULL COVER */
.ta-upload-box input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

/* ===== STATUS FILE ===== */
.file-status {
    margin-top: 6px;
    font-size: 12px;
    color: #9ca3af;
}

.file-status.uploaded {
    color: #16a34a;
    font-weight: 700;
}

.ta-upload-box.is-uploaded {
    border-style: solid;
    border-color: rgba(22, 163, 74, 0.6);
    background: rgba(22, 163, 74, 0.06);
}

/* INFO FOOTER */
.info-footer {
    margin-top:18px;
    background:#EFF6FF;
    border:1px solid #BFDBFE;
    border-radius:16px;
    padding:16px;
    color:#2563EB;
    font-size:13px;
    display:flex;
    gap:10px;
    align-items:center;
}

.alert{
    background:#fff7ed;
    color:#9a3412;
    padding:14px;
    border-radius:12px;
    margin-bottom:16px;
    white-space:pre-line;
}
label{
    margin-top:14px;
    display:block;
    font-weight:600
}
input[type=file]{
    margin-top:6px
}
button{
    margin-top:20px;
    width:100%;
    padding:12px;
    border:none;
    border-radius:14px;
    background:linear-gradient(135deg,#FF74C7,#FF983D);
    color:#fff;
    font-weight:600
}
small{
    color:#6b7280
}

.ta-field {
    margin-bottom: 16px;
}

.ta-label {
    display: block;
    font-weight: 700;
    font-size: 14px;
    color: #374151;
    margin-bottom: 8px;
}

.message-box {
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    border-radius: 12px;
    padding: 14px 16px;
    color: #2563EB;
    font-size: 13px;
    margin-bottom: 16px;
    display: flex;
    gap: 10px;
    align-items: center;
}

.message-box strong {
    display: flex;
    gap: 6px;
    align-items: center;
}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="topbar">
        <h1>Pengajuan Seminar Hasil</h1>

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

    <?php if ($boleh_upload): ?>
    <!-- INFO UMUM (DI LUAR CARD) -->
    <div class="info-box" style="margin-top:20px;">
        <strong>
            <span class="material-symbols-rounded">info</span>
            Informasi Penting
        </strong>
        <hr class="divider">
        <ol class="pretty-ol">
            <li>Pastikan naskah laporan TA telah disetujui oleh kedua Dosen Pembimbing</li>
            <li>Upload Surat dengan format PDF</li>
            <li>Format penamaan dokumen: NIM_Nama File_Nama</li>
            <li>Maksimal ukuran dokumen 2 MB</li>
            <li>Pastikan dokumen yang diunggah sudah benar</li>
        </ol>
    </div>
    <?php endif; ?>

    <div class="card" style="margin-top:20px;">
        <h2>Pengajuan Seminar Hasil</h2>
        <hr class="divider">

        <?php if ($pesan_error): ?>
        <div class="ta-error-card">
            <div class="ta-error-head">
                <div class="ta-error-icon">
                    <span class="material-symbols-rounded">error_outline</span>
                </div>
                <div>
                    <h3 class="ta-error-title">INFORMASI SISTEM</h3>
                    <p class="ta-error-desc"><?= nl2br(htmlspecialchars($pesan_error)) ?></p>    
                </div>
            </div>
        </div>
        
        <div class="ta-actions">
            <a href="<?= base_url('mahasiswa/semhas/status.php') ?>" 
            class="ta-btn ta-btn-primary">
                <span class="material-symbols-rounded">history</span>
                Lihat Riwayat Ajuan
            </a>
        </div>

        <div class="info-footer">
            <span class="material-symbols-rounded">info</span>
            <div>
                Jadwal seminar akan muncul di menu Status Pengajuan setelah dokumen divalidasi oleh admin.
            </div>
        </div>
        <?php endif; ?>

        <?php if ($boleh_upload): ?>
        <form action="simpan.php" method="POST" enctype="multipart/form-data">

            <div class="ta-upload-grid">

                <div class="ta-upload-item">
                    <div class="ta-field">
                        <label class="ta-label">Lembar Berita Acara Seminar Proposal</label>
                        <label class="ta-upload-box">
                            <div class="ta-upload-inner">
                                <div class="ta-upload-icon">
                                    <span class="material-symbols-rounded">upload</span>
                                </div>
                                <div class="ta-upload-text">
                                    <strong>Klik untuk pilih file</strong>
                                    <small>File yang boleh diunggah format <b>.pdf</b> dan maksimal ukuran 2MB</small>
                                    <div class="file-status" id="status-berita-acara">Tidak ada file yang dipilih</div>
                                </div>
                            </div>
                            <input type="file" id="file-berita-acara" name="file_berita_acara" required accept=".pdf">
                        </label>
                    </div>
                </div>

                <div class="ta-upload-item">
                    <div class="ta-field">
                        <label class="ta-label">Persetujuan Laporan TA</label>
                        <label class="ta-upload-box">
                            <div class="ta-upload-inner">
                                <div class="ta-upload-icon">
                                    <span class="material-symbols-rounded">upload</span>
                                </div>
                                <div class="ta-upload-text">
                                    <strong>Klik untuk pilih file</strong>
                                    <small>File yang boleh diunggah format <b>.pdf</b> dan maksimal ukuran 2MB</small>
                                    <div class="file-status" id="status-persetujuan">Tidak ada file yang dipilih</div>
                                </div>
                            </div>
                            <input type="file" id="file-persetujuan" name="file_persetujuan_laporan" required accept=".pdf">
                        </label>
                    </div>
                </div>

                <div class="ta-upload-item">
                    <div class="ta-field">
                        <label class="ta-label">Form Pendaftaran Ujian TA</label>
                        <label class="ta-upload-box">
                            <div class="ta-upload-inner">
                                <div class="ta-upload-icon">
                                    <span class="material-symbols-rounded">upload</span>
                                </div>
                                <div class="ta-upload-text">
                                    <strong>Klik untuk pilih file</strong>
                                    <small>File yang boleh diunggah format <b>.pdf</b> dan maksimal ukuran 2MB</small>
                                    <div class="file-status" id="status-pendaftaran">Tidak ada file yang dipilih</div>
                                </div>
                            </div>
                            <input type="file" id="file-pendaftaran" name="file_pendaftaran_ujian" required accept=".pdf">
                        </label>
                    </div>
                </div>

                <div class="ta-upload-item">
                    <div class="ta-field">
                        <label class="ta-label">Buku Konsultasi TA</label>
                        <label class="ta-upload-box">
                            <div class="ta-upload-inner">
                                <div class="ta-upload-icon">
                                    <span class="material-symbols-rounded">upload</span>
                                </div>
                                <div class="ta-upload-text">
                                    <strong>Klik untuk pilih file</strong>
                                    <small>File yang boleh diunggah format <b>.pdf</b> dan maksimal ukuran 2MB</small>
                                    <div class="file-status" id="status-konsultasi">Tidak ada file yang dipilih</div>
                                </div>
                            </div>
                            <input type="file" id="file-konsultasi" name="file_buku_konsultasi" required accept=".pdf">
                        </label>
                    </div>
                </div>

            </div>

            <div class="message-box">
                <strong>
                    <span class="material-symbols-rounded">info</span> 
                    Kesalahan data atau dokumen yang diupload dapat menyebabkan penolakan pendaftaran. Pastikan semua berkas adalah dokumen asli yang telah discan.
                </strong>
            </div>

            <div class="ta-actions">
                <button type="submit" class="ta-btn ta-btn-primary">
                    <span class="material-symbols-rounded">send</span>
                    Kirim Pengajuan Seminar Hasil
                </button>
            </div>

        </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

<script>
function setFileStatus(inputId, statusId) {
    const input = document.getElementById(inputId);
    const status = document.getElementById(statusId);

    if (!input || !status) return;

    input.addEventListener("change", function () {
        const wrapper = input.closest(".ta-upload-box");

        if (this.files && this.files.length > 0) {
            const fileName = this.files[0].name;

            status.textContent = "File dipilih: " + fileName;
            status.classList.add("uploaded");

            if(wrapper) wrapper.classList.add("is-uploaded");
        } else {
            status.textContent = "Tidak ada file yang dipilih";
            status.classList.remove("uploaded");

            if(wrapper) wrapper.classList.remove("is-uploaded");
        }
    });
}

// Init file status for all uploads
setFileStatus('file-berita-acara', 'status-berita-acara');
setFileStatus('file-persetujuan', 'status-persetujuan');
setFileStatus('file-pendaftaran', 'status-pendaftaran');
setFileStatus('file-konsultasi', 'status-konsultasi');
</script>

