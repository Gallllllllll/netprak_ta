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

// ========================
// CEK PENGAJUAN SEBELUM INSERT
// ========================
$cek = $pdo->prepare("SELECT id FROM pengajuan_ta WHERE mahasiswa_id = ? LIMIT 1");
$cek->execute([$mahasiswa_id]);

if ($cek->rowCount() > 0) {
    die("Anda sudah pernah mengajukan Tugas Akhir.");
}

// ========================
// FUNCTION UPLOAD
// ========================
function uploadFile($file, $dir)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        die("File tidak valid atau gagal diupload.");
    }

    $nama = time() . '_' . basename($file['name']);

    if (!move_uploaded_file($file['tmp_name'], $dir . $nama)) {
        die("Gagal upload file: " . $file['name']);
    }

    return $nama;
}

// ========================
// INSERT DATA
// ========================
$stmt = $pdo->prepare("
    INSERT INTO pengajuan_ta
    (mahasiswa_id, judul_ta, bukti_pembayaran, formulir_pendaftaran, transkrip_nilai, bukti_magang)
    VALUES (?,?,?,?,?,?)
");

$stmt->execute([
    $mahasiswa_id,
    $_POST['judul'] ?? '',
    uploadFile($_FILES['bukti_pembayaran'], $upload_dir),
    uploadFile($_FILES['formulir'], $upload_dir),
    uploadFile($_FILES['transkrip'], $upload_dir),
    uploadFile($_FILES['magang'], $upload_dir)
]);

header("Location: status.php");
exit;
