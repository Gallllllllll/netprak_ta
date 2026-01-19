<?php
session_start();
require "../../config/connection.php";

/* ========================
   CEK LOGIN MAHASISWA
======================== */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    die("Unauthorized");
}

$mahasiswa_id = $_SESSION['user']['id'] ?? null;
if (!$mahasiswa_id) {
    die("Session mahasiswa tidak valid.");
}

/* ========================
   CEK PENGAJUAN TA TERAKHIR
======================== */
$stmt = $pdo->prepare("
    SELECT id, status 
    FROM pengajuan_ta 
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$mahasiswa_id]);
$ta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ta) {
    die("Anda belum mengajukan Tugas Akhir.");
}

if ($ta['status'] !== 'disetujui') {
    die("Pengajuan TA belum disetujui.");
}

/* ========================
   CEK PERSETUJUAN DOSBING
======================== */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM dosbing_ta
    WHERE pengajuan_id = ?
    AND status_persetujuan = 'disetujui'
");
$stmt->execute([$ta['id']]);

if ($stmt->fetchColumn() < 2) {
    die("Persetujuan dari kedua dosen pembimbing belum lengkap.");
}

/* ========================
   CEK SUDAH AJUKAN SEMPRO
======================== */
$cek = $pdo->prepare("
    SELECT id 
    FROM pengajuan_sempro 
    WHERE mahasiswa_id = ?
    LIMIT 1
");
$cek->execute([$mahasiswa_id]);

if ($cek->rowCount() > 0) {
    die("Anda sudah pernah mengajukan Seminar Proposal.");
}

/* ========================
   GENERATE ID SEMPRO
   FORMAT: SPYYYY001
======================== */
$tahun = date('Y');

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM pengajuan_sempro
    WHERE YEAR(created_at) = ?
");
$stmt->execute([$tahun]);

$urutan = $stmt->fetchColumn() + 1;
$id_sempro = 'SP' . $tahun . str_pad($urutan, 3, '0', STR_PAD_LEFT);

/* ========================
   SETUP FOLDER UPLOAD
======================== */
$upload_dir = "../../uploads/sempro/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* ========================
   FUNCTION UPLOAD PDF
======================== */
function uploadPDF($file, $dir, $prefix)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        die("File $prefix gagal diupload.");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        die("File $prefix harus berformat PDF.");
    }

    $filename = $prefix . '_' . time() . '_' . rand(100,999) . '.pdf';

    if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        die("Gagal menyimpan file $prefix.");
    }

    return $filename;
}

/* ========================
   UPLOAD FILE
======================== */
$file_pendaftaran = uploadPDF($_FILES['file_pendaftaran'], $upload_dir, 'pendaftaran');
$file_persetujuan = uploadPDF($_FILES['file_persetujuan'], $upload_dir, 'persetujuan');
$file_konsultasi  = uploadPDF($_FILES['file_konsultasi'],  $upload_dir, 'konsultasi');

/* ========================
   SIMPAN KE DATABASE
======================== */
$stmt = $pdo->prepare("
    INSERT INTO pengajuan_sempro
    (
        id_sempro,
        mahasiswa_id,
        pengajuan_ta_id,
        file_pendaftaran,
        file_persetujuan,
        file_buku_konsultasi,
        status
    )
    VALUES (?,?,?,?,?,?, 'diajukan')
");

$stmt->execute([
    $id_sempro,
    $mahasiswa_id,
    $ta['id'],
    $file_pendaftaran,
    $file_persetujuan,
    $file_konsultasi
]);

/* ========================
   REDIRECT
======================== */
header("Location: status.php?success=1");
exit;
