<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    die("Unauthorized");
}

if (!isset($_SESSION['user']['id'])) {
    die("Session ID mahasiswa belum terset. Silakan login ulang.");
}

$mahasiswa_id = $_SESSION['user']['id'];
$upload_dir = "../../uploads/ta/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* ========================
   CEK SUDAH PERNAH AJUAN
======================== */
$cek = $pdo->prepare("
    SELECT id 
    FROM pengajuan_ta 
    WHERE mahasiswa_id = ? 
    LIMIT 1
");
$cek->execute([$mahasiswa_id]);

if ($cek->rowCount() > 0) {
    die("Anda sudah pernah mengajukan Tugas Akhir.");
}

/* ========================
   GENERATE ID PENGAJUAN TA
   Format: TA2026001
======================== */
$tahun = date('Y');

$q = $pdo->prepare("
    SELECT id_pengajuan
    FROM pengajuan_ta
    WHERE id_pengajuan LIKE ?
    ORDER BY id_pengajuan DESC
    LIMIT 1
");
$q->execute(["TA$tahun%"]);
$last = $q->fetchColumn();

$urutan = $last ? ((int) substr($last, -3) + 1) : 1;
$id_pengajuan = "TA" . $tahun . str_pad($urutan, 3, '0', STR_PAD_LEFT);

/* ========================
   FUNCTION UPLOAD FILE
======================== */
function uploadFile($file, $dir)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        die("File tidak valid atau gagal diupload.");
    }

    $allowedExt  = ['pdf', 'doc', 'docx'];
    $allowedMime = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);

    if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMime)) {
        die("File harus PDF atau Word (.doc / .docx)");
    }

    $namaFile = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $file['name']);

    if (!move_uploaded_file($file['tmp_name'], $dir . $namaFile)) {
        die("Gagal upload file.");
    }

    return $namaFile;
}

/* ========================
   INSERT DATA (ENUM FIX)
======================== */
$stmt = $pdo->prepare("
    INSERT INTO pengajuan_ta
    (
        id_pengajuan,
        mahasiswa_id,
        judul_ta,
        bukti_pembayaran,
        formulir_pendaftaran,
        transkrip_nilai,
        bukti_magang,
        status,
        status_bukti_pembayaran,
        status_formulir_pendaftaran,
        status_transkrip_nilai,
        status_bukti_magang
    )
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->execute([
    $id_pengajuan,
    $mahasiswa_id,
    $_POST['judul'],
    uploadFile($_FILES['bukti_pembayaran'], $upload_dir),
    uploadFile($_FILES['formulir'], $upload_dir),
    uploadFile($_FILES['transkrip'], $upload_dir),
    uploadFile($_FILES['magang'], $upload_dir),
    'diajukan',
    'diajukan',
    'diajukan',
    'diajukan',
    'diajukan'
]);

header("Location: status.php");
exit;
