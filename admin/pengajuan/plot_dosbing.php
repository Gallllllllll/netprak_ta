<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

// ===============================
// CEK ROLE ADMIN
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized");
}

$id = $_GET['id'] ?? 0;
if (!$id) die("ID tidak valid");

// ===============================
// AMBIL DOSEN
// ===============================
$dosen = $pdo->query("SELECT * FROM dosen ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);

// ===============================
// SIMPAN PLOT DOSEN
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dosen1 = $_POST['dosen1'];
    $dosen2 = $_POST['dosen2'];

    if ($dosen1 == $dosen2) {
        die("Dosen 1 dan Dosen 2 tidak boleh sama!");
    }

    // hapus jika sudah pernah diplot
    $pdo->prepare("DELETE FROM dosbing_ta WHERE pengajuan_id=?")
        ->execute([$id]);

    $stmt = $pdo->prepare("
        INSERT INTO dosbing_ta (pengajuan_id, dosen_id, role)
        VALUES (?, ?, ?)
    ");

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
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Plot Dosen Pembimbing</title>

<style>
body{
    background:#f3a4a4;
    font-family:Arial, sans-serif;
}

.main-content{
    padding:40px;
}

/* card utama */
.card{
    background:#fff3e8;
    padding:28px;
    border-radius:18px;
    max-width:650px;
    margin:auto;
}

/* header */
.dashboard-header{
    background:linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
    padding:18px 22px;
    border-radius:14px;
    margin-bottom:22px;
}


.dashboard-header h1{
    font-size:18px;
    margin:0;
}

.dashboard-header p{
    font-size:13px;
    margin:4px 0 0;
    opacity:.9;
}

/* form */
form{
    background:#fff;
    padding:22px;
    border-radius:14px;
}

/* label */
label{
    font-size:14px;
    font-weight:600;
    display:block;
    margin-top:16px;
}

/* select */
select{
    width:100%;
    padding:12px 14px;
    margin-top:6px;
    border-radius:10px;
    border:2px solid #ff8a8a;
    outline:none;
    font-size:14px;
    background:#fff;
}

/* tombol */
button{
    margin-top:22px;
    padding:10px 22px;
    border:none;
    border-radius:999px;
    /* background:#E78F00; */
    background:linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
    
    color:#fff;
    font-weight:600;
    cursor:pointer;
    font-size:13px;
}

button:hover{
    opacity:.9;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<?php include "../sidebar.php"; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="card">

        <div class="dashboard-header">
            <h1>Plot Dosen Pembimbing</h1>
            <p>Tentukan dosen pembimbing 1 dan 2</p>
        </div>

        <form method="POST">

            <label>Dosen Pembimbing 1</label>
            <select name="dosen1" required>
                <option value="">-- Pilih Dosen --</option>
                <?php foreach($dosen as $d): ?>
                    <option value="<?= $d['id'] ?>">
                        <?= htmlspecialchars($d['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Dosen Pembimbing 2</label>
            <select name="dosen2" required>
                <option value="">-- Pilih Dosen --</option>
                <?php foreach($dosen as $d): ?>
                    <option value="<?= $d['id'] ?>">
                        <?= htmlspecialchars($d['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Simpan Plot Dosen</button>

        </form>

    </div>

</div>

</body>
</html>
