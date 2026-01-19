<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

/* ===============================
   CEK LOGIN MAHASISWA
================================ */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . base_url('login.php'));
    exit;
}

$pesan_error  = '';
$boleh_upload = false;

/* ===============================
   CEK PENGAJUAN TA TERAKHIR
================================ */
$stmt = $pdo->prepare("
    SELECT id, id_pengajuan, judul_ta, status
    FROM pengajuan_ta
    WHERE mahasiswa_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$_SESSION['user']['id']]);
$ta = $stmt->fetch(PDO::FETCH_ASSOC);

/* ===============================
   VALIDASI ALUR TA
================================ */
if (!$ta) {

    $pesan_error = "Anda belum mengajukan Tugas Akhir.\nSilakan ajukan Tugas Akhir terlebih dahulu.";

} elseif ($ta['status'] !== 'disetujui') {

    $pesan_error = "Pengajuan Tugas Akhir belum selesai.\nStatus saat ini: {$ta['status']}";

} else {

    /* ===============================
       CEK PERSETUJUAN DOSEN PEMBIMBING
    ================================ */
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM dosbing_ta
        WHERE pengajuan_id = ?
        AND status_persetujuan = 'disetujui'
    ");
    $stmt->execute([$ta['id']]);

    if ($stmt->fetchColumn() < 2) {

        $pesan_error = "Pengajuan Seminar Proposal belum dapat dilakukan.\nMenunggu persetujuan dari Dosen Pembimbing 1 dan 2.";

    } else {

        /* ===============================
           CEK SUDAH AJUKAN SEMPRO
        ================================ */
        $cek = $pdo->prepare("
            SELECT id 
            FROM pengajuan_sempro 
            WHERE pengajuan_ta_id = ?
            LIMIT 1
        ");
        $cek->execute([$ta['id']]);

        if ($cek->rowCount() > 0) {

            $pesan_error = "Anda sudah pernah mengajukan Seminar Proposal.";

        } else {

            // ⬅️ TIDAK ADA PEMBUATAN ID APA PUN DI SINI
            $boleh_upload = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengajuan Seminar Proposal</title>

<style>
.card {
    background:#fff;
    padding:26px;
    max-width:720px;
    border-radius:16px;
    box-shadow:0 6px 14px rgba(0,0,0,.08);
}
.alert {
    background:#fff7ed;
    color:#9a3412;
    padding:14px;
    border-radius:12px;
    margin-bottom:16px;
    white-space:pre-line;
}
label {
    display:block;
    margin-top:14px;
    font-weight:600;
}
button {
    margin-top:20px;
    width:100%;
    padding:12px;
    background:linear-gradient(135deg,#FF74C7,#FF983D);
    color:#fff;
    border:none;
    border-radius:14px;
    font-weight:600;
    cursor:pointer;
}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">

    <h1>Pengajuan Seminar Proposal</h1>

    <div class="card">

        <?php if ($pesan_error): ?>
            <div class="alert"><?= htmlspecialchars($pesan_error) ?></div>

        <?php elseif ($boleh_upload): ?>

            <form action="simpan.php" method="POST" enctype="multipart/form-data">

                <!-- HANYA KIRIM ID TA -->
                <input type="hidden" name="pengajuan_ta_id" value="<?= $ta['id'] ?>">

                <label>Form Pendaftaran Seminar Proposal (PDF)</label>
                <input type="file" name="file_pendaftaran" accept="application/pdf" required>

                <label>Lembar Persetujuan Proposal TA (PDF)</label>
                <input type="file" name="file_persetujuan" accept="application/pdf" required>

                <label>Buku Konsultasi Tugas Akhir (PDF)</label>
                <input type="file" name="file_konsultasi" accept="application/pdf" required>

                <button type="submit">Ajukan Seminar Proposal</button>
            </form>

        <?php endif; ?>

        <?php if ($ta): ?>
            <hr>
            <p><b>Judul TA:</b><br><?= htmlspecialchars($ta['judul_ta']) ?></p>
            <p><b>Status TA:</b> <?= htmlspecialchars($ta['status']) ?></p>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
