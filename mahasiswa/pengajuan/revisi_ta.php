<?php
session_start();
require "../../config/connection.php";

// ===============================
// CEK ROLE MAHASISWA
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

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
<title>Revisi Pengajuan TA</title>
<link rel="stylesheet" href="../../style.css">
<style>
body { font-family:Arial,sans-serif; background:#f4f6f8; padding:20px; }
form { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); max-width:600px; margin:auto; }
label { display:block; margin-top:15px; font-weight:bold; }
input[type=file], input[type=text] { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:4px; }
button { margin-top:15px; padding:10px 20px; background:#2563eb; color:#fff; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#1d4ed8; }
.alert { background:#fff3cd; padding:12px; border-radius:6px; margin-bottom:15px; }
</style>
</head>
<body>

<h1>Revisi Pengajuan Tugas Akhir</h1>

<form method="POST" enctype="multipart/form-data">

    <label>Judul TA</label>
    <input type="text" value="<?= htmlspecialchars($data['judul_ta']) ?>" readonly>

    <?php
    $has_revisi = false;
    foreach ($files as $input => $f):
        if (($data[$f['status']] ?? '') === 'revisi'):
            $has_revisi = true;
    ?>
        <label><?= htmlspecialchars($f['label']) ?></label>
        <input type="file" id="<?= $input ?>" name="<?= $input ?>" required accept=".pdf">
        <small>Format: PDF</small>
    <?php
        endif;
    endforeach;

    if (!$has_revisi):
        echo "<div class='alert'>Tidak ada file yang perlu direvisi.</div>";
    else:
    ?>
        <button type="submit">Upload Revisi</button>
    <?php endif; ?>

</form>

</body>
</html>
