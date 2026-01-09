<?php
session_start();
require "../../config/connection.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// ambil data pengajuan
$stmt = $pdo->prepare("SELECT * FROM pengajuan_ta WHERE id=? AND mahasiswa_id=?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$data = $stmt->fetch();
if(!$data) die("Pengajuan tidak ditemukan.");
?>

<h1>Detail Pengajuan TA</h1>
<p><b>Judul TA:</b> <?= htmlspecialchars($data['judul_ta']) ?></p>
<p><b>Status:</b> <?= strtoupper($data['status']) ?></p>
<p><b>Catatan Admin/Dosen:</b> <?= $data['catatan_admin'] ? htmlspecialchars($data['catatan_admin']) : '-' ?></p>

<h3>Dokumen:</h3>
<?php if($data['bukti_pembayaran']): ?>
    <a href="../../uploads/ta/<?= $data['bukti_pembayaran'] ?>" target="_blank">Bukti Pembayaran</a><br>
<?php endif; ?>
<?php if($data['formulir_pendaftaran']): ?>
    <a href="../../uploads/ta/<?= $data['formulir_pendaftaran'] ?>" target="_blank">Formulir Pendaftaran</a><br>
<?php endif; ?>
<?php if($data['transkrip_nilai']): ?>
    <a href="../../uploads/ta/<?= $data['transkrip_nilai'] ?>" target="_blank">Transkrip Nilai</a><br>
<?php endif; ?>
<?php if($data['bukti_magang']): ?>
    <a href="../../uploads/ta/<?= $data['bukti_magang'] ?>" target="_blank">Bukti Kelulusan Magang</a>
<?php endif; ?>

<?php if(strtolower($data['status'])==='revisi'): ?>
    <p><a href="revisi_ta.php?id=<?= $data['id'] ?>">Upload Revisi</a></p>
<?php endif; ?>
