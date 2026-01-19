<?php
session_start();
require "../../config/connection.php";

// ===============================
// CEK LOGIN
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

// ===============================
// FOLDER UPLOAD
// ===============================
$uploadDir = "../../uploads/semhas/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ===============================
// VALIDASI FILE
// ===============================
$allowedMime = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

$allowedExt = ['pdf', 'doc', 'docx'];

function uploadFile($field, $id_semhas)
{
    global $uploadDir, $allowedMime, $allowedExt;

    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        die("File $field wajib diupload.");
    }

    $tmp  = $_FILES[$field]['tmp_name'];
    $mime = mime_content_type($tmp);
    $ext  = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));

    if (!in_array($mime, $allowedMime) || !in_array($ext, $allowedExt)) {
        die("Format file $field tidak valid. Hanya PDF atau Word.");
    }

    $filename = $id_semhas . '_' . $field . '.' . $ext;
    move_uploaded_file($tmp, $uploadDir . $filename);

    return $filename;
}

// ===============================
// AMBIL PENGAJUAN TA TERAKHIR
// ===============================
$stmt = $pdo->prepare("
    SELECT id 
    FROM pengajuan_ta
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$_SESSION['user']['id']]);
$ta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ta) {
    die("Pengajuan TA tidak ditemukan.");
}

// ===============================
// CEK SUDAH SEMHAS?
// ===============================
$cek = $pdo->prepare("
    SELECT id 
    FROM pengajuan_semhas
    WHERE mahasiswa_id = ?
    LIMIT 1
");
$cek->execute([$_SESSION['user']['id']]);

if ($cek->rowCount() > 0) {
    die("Anda sudah mengajukan Seminar Hasil.");
}

// ===============================
// GENERATE ID_SEMHAS
// ===============================
$tahun = date('Y');

$stmt = $pdo->prepare("
    SELECT id_semhas
    FROM pengajuan_semhas
    WHERE id_semhas LIKE ?
    ORDER BY id_semhas DESC
    LIMIT 1
");
$stmt->execute(["SH{$tahun}%"]);
$last = $stmt->fetch(PDO::FETCH_ASSOC);

$urut = 1;
if ($last) {
    $urut = (int) substr($last['id_semhas'], -3) + 1;
}

$id_semhas = 'SH' . $tahun . str_pad($urut, 3, '0', STR_PAD_LEFT);

// ===============================
// UPLOAD FILE
// ===============================
$file_berita_acara        = uploadFile('file_berita_acara', $id_semhas);
$file_persetujuan_laporan = uploadFile('file_persetujuan_laporan', $id_semhas);
$file_pendaftaran_ujian   = uploadFile('file_pendaftaran_ujian', $id_semhas);
$file_buku_konsultasi     = uploadFile('file_buku_konsultasi', $id_semhas);

// ===============================
// INSERT DATA (ENUM BARU)
// ===============================
$stmt = $pdo->prepare("
    INSERT INTO pengajuan_semhas (
        id_semhas,
        mahasiswa_id,
        pengajuan_ta_id,
        file_berita_acara,
        file_persetujuan_laporan,
        file_pendaftaran_ujian,
        file_buku_konsultasi,
        status,
        status_file_berita_acara,
        status_file_persetujuan_laporan,
        status_file_pendaftaran_ujian,
        status_file_buku_konsultasi
    ) VALUES (
        ?,?,?,?,?,?,
        ?, 'diajukan',
        'diajukan','diajukan','diajukan','diajukan'
    )
");

$stmt->execute([
    $id_semhas,
    $_SESSION['user']['id'],
    $ta['id'],
    $file_berita_acara,
    $file_persetujuan_laporan,
    $file_pendaftaran_ujian,
    $file_buku_konsultasi
]);

// ===============================
// REDIRECT
// ===============================
header("Location: status.php");
exit;
