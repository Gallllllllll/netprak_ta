<?php
session_start();
require "../../config/connection.php";

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
// SIMPAN PLOT DOSEN (BACKEND VALIDATION)
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
<title>Plot Dosen Pembimbing</title>
<link rel="stylesheet" href="../../style.css">

<style>
.card {
    background:#fff;
    padding:24px;
    border-radius:16px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
    max-width:500px;
}

label {
    font-weight:600;
    display:block;
    margin-top:14px;
}

select {
    width:100%;
    padding:10px;
    margin-top:6px;
    border-radius:8px;
    border:1px solid #d1d5db;
}

.error {
    margin-top:10px;
    color:#dc2626;
    font-weight:600;
    display:none;
}

button {
    margin-top:20px;
    padding:10px 18px;
    border:none;
    border-radius:12px;
    background:linear-gradient(135deg,#10b981,#059669);
    color:#fff;
    font-weight:600;
    cursor:pointer;
}
button:disabled {
    background:#9ca3af;
    cursor:not-allowed;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<?php include "../sidebar.php"; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="dashboard-header">
        <h1>Plot Dosen Pembimbing</h1>
        <p>Tentukan dosen pembimbing 1 dan 2</p>
    </div>

    <div class="card">
        <form method="POST" id="formPlot">

            <label>Dosen Pembimbing 1</label>
            <select name="dosen1" id="dosen1" required>
                <option value="">-- Pilih Dosen --</option>
                <?php foreach($dosen as $d): ?>
                    <option value="<?= $d['id'] ?>">
                        <?= htmlspecialchars($d['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Dosen Pembimbing 2</label>
            <select name="dosen2" id="dosen2" required>
                <option value="">-- Pilih Dosen --</option>
                <?php foreach($dosen as $d): ?>
                    <option value="<?= $d['id'] ?>">
                        <?= htmlspecialchars($d['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="error" id="errorMsg">
                ❌ Dosen Pembimbing 1 dan 2 tidak boleh sama
            </div>

            <button type="submit" id="btnSubmit">Simpan Plot Dosen</button>
        </form>
    </div>

</div>

<script>
const dosen1 = document.getElementById('dosen1');
const dosen2 = document.getElementById('dosen2');
const errorMsg = document.getElementById('errorMsg');
const btnSubmit = document.getElementById('btnSubmit');

function validateDosen() {
    if (dosen1.value && dosen1.value === dosen2.value) {
        errorMsg.style.display = 'block';
        btnSubmit.disabled = true;
    } else {
        errorMsg.style.display = 'none';
        btnSubmit.disabled = false;
    }
}

dosen1.addEventListener('change', validateDosen);
dosen2.addEventListener('change', validateDosen);
</script>

</body>
</html>
