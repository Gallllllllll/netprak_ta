<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit;
}

$uploadDir = "../../uploads/semhas/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

function upload($name) {
    global $uploadDir;
    $file = time().'_'.$name.'_'.$_FILES[$name]['name'];
    move_uploaded_file($_FILES[$name]['tmp_name'], $uploadDir.$file);
    return $file;
}

$file_berita_acara        = upload('file_berita_acara');
$file_persetujuan_laporan = upload('file_persetujuan_laporan');
$file_pendaftaran_ujian   = upload('file_pendaftaran_ujian');
$file_buku_konsultasi     = upload('file_buku_konsultasi');

/* ambil TA */
$stmt = $pdo->prepare("
    SELECT id FROM pengajuan_ta 
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC LIMIT 1
");
$stmt->execute([$_SESSION['user']['id']]);
$ta = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    INSERT INTO pengajuan_semhas (
        mahasiswa_id, pengajuan_ta_id,
        file_berita_acara, file_persetujuan_laporan,
        file_pendaftaran_ujian, file_buku_konsultasi
    ) VALUES (?,?,?,?,?,?)
");
$stmt->execute([
    $_SESSION['user']['id'],
    $ta['id'],
    $file_berita_acara,
    $file_persetujuan_laporan,
    $file_pendaftaran_ujian,
    $file_buku_konsultasi
]);

header("Location: status.php");
exit;
