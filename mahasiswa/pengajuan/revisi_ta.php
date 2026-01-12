<?php
session_start();
require "../../config/connection.php";

// cek role mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$upload_dir = "../../uploads/ta/";

// ambil data pengajuan
$stmt = $pdo->prepare("SELECT * FROM pengajuan_ta WHERE id=? AND mahasiswa_id=?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$data = $stmt->fetch();

if (!$data) die("Pengajuan tidak ditemukan.");

// mapping input name => kolom DB
$files = [
    'bukti_pembayaran' => ['db' => 'bukti_pembayaran', 'status' => 'status_bukti_pembayaran', 'label'=>'Bukti Pembayaran'],
    'formulir' => ['db' => 'formulir_pendaftaran', 'status' => 'status_formulir_pendaftaran', 'label'=>'Formulir Pendaftaran'],
    'transkrip' => ['db' => 'transkrip_nilai', 'status' => 'status_transkrip_nilai', 'label'=>'Transkrip Nilai'],
    'magang' => ['db' => 'bukti_magang', 'status' => 'status_bukti_magang', 'label'=>'Bukti Kelulusan Magang'],
];

// proses form
if($_SERVER['REQUEST_METHOD']=='POST'){
    $updates = [];
    foreach($files as $input_name => $f){
        if(!empty($_FILES[$input_name]['name'])){
            $nama = time().'_'.basename($_FILES[$input_name]['name']);
            if(!is_dir($upload_dir)) mkdir($upload_dir,0777,true);

            if(move_uploaded_file($_FILES[$input_name]['tmp_name'], $upload_dir.$nama)){
                // update pengajuan_ta
                $updates[] = $f['db']."='$nama'";

                // insert ke revisi_ta
                $stmt2 = $pdo->prepare("
                    INSERT INTO revisi_ta (pengajuan_id, nama_file)
                    VALUES (?, ?)
                ");
                $stmt2->execute([$id, $nama]);
            }
        }
    }

    if($updates){
        // setelah upload revisi, status global kembali ke 'proses'
        $sql = "UPDATE pengajuan_ta SET ".implode(", ", $updates).", status='proses' WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
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
<link rel="stylesheet" href="../style.css">
<style>
body { font-family:Arial,sans-serif; background:#f4f6f8; padding:20px; }
form { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); max-width:600px; margin:auto; }
label { display:block; margin-top:15px; font-weight:bold; }
input[type=file], input[type=text] { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:4px; }
button { margin-top:15px; padding:10px 20px; background:#17a2b8; color:#fff; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#138496; }
</style>
</head>
<body>

<h1>Revisi Pengajuan TA</h1>

<form method="POST" enctype="multipart/form-data">
    <label>Judul TA (tidak bisa diubah)</label>
    <input type="text" name="judul" value="<?= htmlspecialchars($data['judul_ta']) ?>" readonly>

    <?php
    $has_revisi = false;
    foreach($files as $input_name => $f):
        if(($data[$f['status']] ?? '') === 'revisi'):
            $has_revisi = true;
    ?>
        <label><?= $f['label'] ?></label>
        <input type="file" name="<?= $input_name ?>">
    <?php
        endif;
    endforeach;

    if(!$has_revisi):
        echo "<p>Semua file sudah disetujui. Tidak ada file yang perlu direvisi.</p>";
    else:
    ?>
        <button type="submit">Upload Revisi</button>
    <?php endif; ?>
</form>

</body>
</html>
