<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role']!=='mahasiswa') {
    header("Location: ../../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Pengajuan TA</title>
<link rel="stylesheet" href="../style.css">
<style>
body { margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container { display:flex; min-height:100vh; }
.content { flex:1; padding:20px; }
form { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); max-width:700px; margin:auto; }
form h2 { margin-top:0; margin-bottom:20px; }
form label { display:block; margin-top:15px; font-weight:bold; }
form input[type="text"],
form input[type="file"] {
    width:100%;
    padding:10px;
    margin-top:5px;
    border:1px solid #ccc;
    border-radius:4px;
}
form button {
    margin-top:20px;
    padding:12px 20px;
    background:#007bff;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:16px;
}
form button:hover {
    background:#0056b3;
}
</style>
</head>
<body>

<div class="container">
    <?php include "../sidebar.php"; ?>

    <div class="content">
        <form action="simpan.php" method="POST" enctype="multipart/form-data">
            <h2>Form Pengajuan Tugas Akhir</h2>

            <label for="judul">Judul TA:</label>
            <input type="text" id="judul" name="judul" required>

            <label for="bukti_pembayaran">Bukti Pembayaran:</label>
            <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" required>

            <label for="formulir">Formulir Pendaftaran:</label>
            <input type="file" id="formulir" name="formulir" required>

            <label for="transkrip">Transkrip Nilai:</label>
            <input type="file" id="transkrip" name="transkrip" required>

            <label for="magang">Bukti Kelulusan Magang:</label>
            <input type="file" id="magang" name="magang" required>

            <button type="submit">Ajukan TA</button>
        </form>
    </div>
</div>

</body>
</html>
