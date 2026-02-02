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
// AMBIL DOSBING YANG SUDAH ADA
// ===============================
$existingDosbing = [
    'dosbing_1' => null,
    'dosbing_2' => null
];

$stmt = $pdo->prepare("
    SELECT dosen_id, role 
    FROM dosbing_ta 
    WHERE pengajuan_id = ?
");
$stmt->execute([$id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['role'] === 'dosbing_1') {
        $existingDosbing['dosbing_1'] = $row['dosen_id'];
    } elseif ($row['role'] === 'dosbing_2') {
        $existingDosbing['dosbing_2'] = $row['dosen_id'];
    }
}


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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

<style>
body{
    background:#f3a4a4;
    font-family:'Outfit', sans-serif;
    margin: 0;
}

.main-content {
    margin-left: 280px;
    padding: 30px;
    transition: all 0.3s ease;
    width: calc(100vw - 280px);
    max-width: calc(100vw - 280px);
    box-sizing: border-box;
    overflow-x: hidden;
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
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding:18px 22px;
    border-radius:14px;
    margin-bottom:22px;
    background:linear-gradient(90deg, #ff5f9e, #ff9f43) !important;
    gap: 20px;
}

.header-text {
    flex: 1;
}

.dashboard-header h1 {
    margin:0;
    color:#fff !important;
    -webkit-text-fill-color: initial !important;
    background: none !important;
    -webkit-background-clip: initial !important;
    font-size: 20px;
    font-weight: 700;
}

.dashboard-header p{
    font-size:13px;
    margin:4px 0 0;
    opacity:.9;
    color:#fff !important;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-shrink: 0;
    margin-top: 5px;
}

.admin-profile .text {
    text-align: right;
    max-width: 90px;
    line-height: 1.2;
    color: #fff;
}

.admin-profile small { 
    font-size: 11px;
    display: block;
    opacity: 0.8;
}

.admin-profile b { 
    font-size: 13px; 
    display: block; 
}

.avatar {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
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

/* ================= RESPONSIVE ================= */
@media (max-width:1024px){
    .main-content {
        margin-left: 70px !important;
        padding: 20px !important;
        width: calc(100vw - 70px) !important;
        max-width: calc(100vw - 70px) !important;
    }
}

@media (max-width:768px){
    .main-content {
        margin-left: 60px !important;
        padding: 15px !important;
        width: calc(100vw - 60px) !important;
        max-width: calc(100vw - 60px) !important;
    }

    .dashboard-header {
        padding: 15px;
        gap: 10px;
    }

    .dashboard-header h1 {
        font-size: 18px;
    }

    .admin-profile {
        gap: 10px;
    }

    .admin-profile .text {
        max-width: 80px;
    }

    .avatar {
        width: 36px;
        height: 36px;
    }
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
            <div class="header-text">
                <h1>Plot Dosen Pembimbing</h1>
                <p>Tentukan dosen pembimbing 1 dan 2</p>
            </div>
            <div class="admin-profile">
                <div class="text">
                    <small>Selamat Datang,</small>
                    <b><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Admin') ?></b>
                </div>
                <div class="avatar">
                    <span class="material-symbols-rounded" style="color:#fff">person</span>
                </div>
            </div>
        </div>

        <form method="POST" id="plotForm">

            <label>Dosen Pembimbing 1</label>
            <select name="dosen1" id="dosen1" required>
                <option value="">-- Pilih Dosen --</option>
                <?php foreach($dosen as $d): ?>
                    <option value="<?= $d['id'] ?>"
                        <?= ($existingDosbing['dosbing_1'] == $d['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>


            <label>Dosen Pembimbing 2</label>
            <select name="dosen2" id="dosen2" required>
                <option value="">-- Pilih Dosen --</option>
                <?php foreach($dosen as $d): ?>
                    <option value="<?= $d['id'] ?>"
                        <?= ($existingDosbing['dosbing_2'] == $d['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Simpan Plot Dosen</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('plotForm').addEventListener('submit', function(e) {
    const dosen1 = document.getElementById('dosen1').value;
    const dosen2 = document.getElementById('dosen2').value;

    if (dosen1 === dosen2) {
        e.preventDefault(); // stop submit

        Swal.fire({
            icon: 'error',
            title: 'Dosen tidak valid',
            text: 'Dosen Pembimbing 1 dan 2 tidak boleh sama!',
            confirmButtonColor: '#ff5f9e'
        });
    }
});
</script>

</body>
</html>
