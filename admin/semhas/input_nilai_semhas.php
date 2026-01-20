<?php
session_start();
require "../../config/connection.php";

// ===============================
// CEK ADMIN
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized");
}

$pengajuan_id = $_GET['id'] ?? 0;
if (!$pengajuan_id) die("ID tidak valid");

// ===============================
// AMBIL TIM SEMHAS
// ===============================
$stmt = $pdo->prepare("
    SELECT 
        'dosbing_1' AS peran,
        d.id AS dosen_id,
        d.nama
    FROM dosbing_ta db
    JOIN dosen d ON db.dosen_id = d.id
    WHERE db.pengajuan_id = ? AND db.role = 'dosbing_1'

    UNION ALL

    SELECT 
        'dosbing_2' AS peran,
        d.id AS dosen_id,
        d.nama
    FROM dosbing_ta db
    JOIN dosen d ON db.dosen_id = d.id
    WHERE db.pengajuan_id = ? AND db.role = 'dosbing_2'

    UNION ALL

    SELECT 
        'penguji' AS peran,
        d.id AS dosen_id,
        d.nama
    FROM tim_semhas t
    JOIN dosen d ON t.dosen_id = d.id
    WHERE t.pengajuan_id = ?
");
$stmt->execute([$pengajuan_id, $pengajuan_id, $pengajuan_id]);
$tim = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$tim) {
    die("Tim semhas belum ditentukan");
}

// ===============================
// CEK ADA PENGUJI ATAU TIDAK
// ===============================
$adaPenguji = false;
foreach ($tim as $t) {
    if ($t['peran'] === 'penguji') {
        $adaPenguji = true;
        break;
    }
}

// ===============================
// AMBIL NILAI YANG SUDAH ADA
// ===============================
$nilai = [];
$stmt = $pdo->prepare("
    SELECT peran, nilai
    FROM nilai_semhas
    WHERE pengajuan_id = ?
");
$stmt->execute([$pengajuan_id]);

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $n) {
    $nilai[$n['peran']] = $n['nilai'];
}

// ===============================
// SIMPAN NILAI
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pdo->beginTransaction();
    try {

        foreach ($_POST['nilai'] as $peran => $data) {

            $nilaiAngka = floatval($data['nilai']);

            // VALIDASI KERAS BACKEND
            if ($nilaiAngka < 0 || $nilaiAngka > 100) {
                throw new Exception("Nilai harus antara 0 - 100");
            }

            $stmt = $pdo->prepare("
                INSERT INTO nilai_semhas (pengajuan_id, dosen_id, peran, nilai)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
            ");

            $stmt->execute([
                $pengajuan_id,
                $data['dosen_id'],
                $peran,
                $nilaiAngka
            ]);
        }

        $pdo->commit();
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Input Nilai Semhas</title>
<link rel="stylesheet" href="../../style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.card{
    background:#fff;
    padding:28px;
    border-radius:18px;
    max-width:700px;
    margin:auto;
}
label{
    font-weight:600;
    margin-top:18px;
    display:block;
}
input[type=number]{
    width:100%;
    padding:10px;
    border-radius:10px;
    margin-top:6px;
    border:1px solid #ccc;
}
button{
    margin-top:25px;
    padding:10px 24px;
    border:none;
    border-radius:999px;
    background:linear-gradient(90deg,#ff5f9e,#ff9f43);
    color:#fff;
    font-weight:600;
    cursor:pointer;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<?php include "../sidebar.php"; ?>

<?php if (!$adaPenguji): ?>
<script>
Swal.fire({
    icon: 'warning',
    title: 'Penguji Belum Ditentukan',
    text: 'Silakan tentukan dosen penguji terlebih dahulu sebelum input nilai.',
    confirmButtonText: 'Kembali',
    allowOutsideClick: false
}).then(() => {
    window.location.href = 'index.php';
});
</script>
<?php endif; ?>

<?php if (isset($error)): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal Menyimpan',
    text: '<?= htmlspecialchars($error) ?>'
});
</script>
<?php endif; ?>

<div class="main-content">
<div class="card">

<h2>Input Nilai Seminar Hasil</h2>

<form method="POST" <?= !$adaPenguji ? 'style="display:none"' : '' ?>>

<?php foreach ($tim as $t): ?>
    <label>
        <?= strtoupper(str_replace('_',' ', $t['peran'])) ?>
        (<?= htmlspecialchars($t['nama']) ?>)
    </label>

    <input type="hidden"
           name="nilai[<?= $t['peran'] ?>][dosen_id]"
           value="<?= $t['dosen_id'] ?>">

    <input type="number"
           name="nilai[<?= $t['peran'] ?>][nilai]"
           step="0.01"
           min="0"
           max="100"
           value="<?= $nilai[$t['peran']] ?? '' ?>"
           required>
<?php endforeach; ?>

<button type="submit">Simpan Nilai</button>

</form>

</div>
</div>

<!-- LOCK NILAI > 100 DI FRONTEND -->
<script>
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', function () {
        if (this.value > 100) this.value = 100;
        if (this.value < 0) this.value = 0;
    });
});
</script>

</body>
</html>
