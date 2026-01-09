<?php
session_start();
require "../../config/connection.php";

$id = $_GET['id'] ?? 0;

// ambil daftar dosen
$dosen = $pdo->query("SELECT * FROM dosen")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dosen1 = $_POST['dosen1'];
    $dosen2 = $_POST['dosen2'];

    if ($dosen1 == $dosen2) {
        die("Dosen 1 dan Dosen 2 tidak boleh sama!");
    }

    // insert ke dosbing_ta, satu per baris
    $stmt = $pdo->prepare("INSERT INTO dosbing_ta (pengajuan_id, dosen_id, role) VALUES (?, ?, ?)");

    $stmt->execute([$id, $dosen1, 'dosbing_1']);
    $stmt->execute([$id, $dosen2, 'dosbing_2']);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Plot Dosen Pembimbing</title>
<link rel="stylesheet" href="../../style.css">
<style>
body { font-family:Arial,sans-serif; background:#f4f6f8; margin:0; }
.container { display:flex; min-height:100vh; }
.content { flex:1; padding:20px; }
.card { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); max-width:500px; margin:auto; }
select { width:100%; padding:10px; margin-top:10px; border:1px solid #ccc; border-radius:4px; }
button { margin-top:15px; padding:10px 20px; background:#007bff; color:#fff; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#0056b3; }
</style>
</head>
<body>

<div class="container">
    <?php include "../sidebar.php"; ?>

    <div class="content">
        <div class="card">
            <h2>Plot Dosen Pembimbing</h2>
            <form method="POST">
                <label>Dosen 1</label>
                <select name="dosen1" required>
                    <option value="">-- Pilih Dosen 1 --</option>
                    <?php foreach($dosen as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nama']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Dosen 2</label>
                <select name="dosen2" required>
                    <option value="">-- Pilih Dosen 2 --</option>
                    <?php foreach($dosen as $d): ?>
                        <option value="<?= htmlspecialchars($d['id']) ?>"><?= htmlspecialchars($d['nama']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Simpan</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
