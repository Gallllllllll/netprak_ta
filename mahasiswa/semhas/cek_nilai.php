<?php
session_start();
require "../../config/connection.php";

// ===============================
// CEK LOGIN MAHASISWA
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

$mahasiswa_id = $_SESSION['user']['id'];

// ===============================
// AMBIL PENGAJUAN SEMHAS TERAKHIR
// ===============================
$stmt = $pdo->prepare("
    SELECT id, id_semhas
    FROM pengajuan_semhas
    WHERE mahasiswa_id = ?
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute([$mahasiswa_id]);
$pengajuan = $stmt->fetch(PDO::FETCH_ASSOC);

// FLAG KONDISI
$popup = null;

// ===============================
// JIKA BELUM AJU SEMHAS
// ===============================
if (!$pengajuan) {
    $popup = [
        'title' => 'Oops!',
        'text'  => 'Anda belum mengajukan Seminar Hasil.',
        'icon'  => 'warning',
        'redirect' => '../dashboard.php'
    ];
} else {

    $pengajuan_id = $pengajuan['id'];

    // ===============================
    // AMBIL NILAI SEMHAS
    // ===============================
    $stmt = $pdo->prepare("
        SELECT 
            n.peran,
            n.nilai,
            d.nama
        FROM nilai_semhas n
        JOIN dosen d ON n.dosen_id = d.id
        WHERE n.pengajuan_id = ?
        ORDER BY FIELD(n.peran,'dosbing_1','dosbing_2','penguji')
    ");
    $stmt->execute([$pengajuan_id]);
    $nilaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===============================
    // JIKA NILAI BELUM ADA
    // ===============================
    if (count($nilaiList) === 0) {
        $popup = [
            'title' => 'Informasi',
            'text'  => 'Nilai Seminar Hasil belum diinput oleh dosen.',
            'icon'  => 'info',
            'redirect' => '../dashboard.php'
        ];
    } else {

        // ===============================
        // HITUNG RATA-RATA
        // ===============================
        $total = 0;
        foreach ($nilaiList as $n) {
            $total += $n['nilai'];
        }

        $jumlah = count($nilaiList);
        $rata_rata = round($total / $jumlah, 2);
        $status = $rata_rata >= 70 ? "LULUS" : "TIDAK LULUS";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cek Nilai Seminar Hasil</title>

<link rel="stylesheet" href="../../style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.card{
    background:#fff;
    padding:24px;
    border-radius:16px;
    max-width:800px;
    margin:auto;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:16px;
}
th,td{
    border:1px solid #ddd;
    padding:10px;
}
th{background:#f1f3f5;}
.badge{
    padding:6px 14px;
    border-radius:999px;
    font-weight:600;
}
.lulus{background:#d4edda;color:#155724;}
.tidak{background:#f8d7da;color:#721c24;}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
<h1>Cek Nilai Seminar Hasil</h1>

<?php if ($popup): ?>

<script>
Swal.fire({
    title: "<?= $popup['title'] ?>",
    text: "<?= $popup['text'] ?>",
    icon: "<?= $popup['icon'] ?>",
    confirmButtonText: "OK"
}).then(() => {
    window.location.href = "<?= $popup['redirect'] ?>";
});
</script>

<?php else: ?>

<div class="card">

<p><b>ID Seminar Hasil:</b><br>
<?= htmlspecialchars($pengajuan['id_semhas']) ?>
</p>

<table>
<tr>
    <th>Peran</th>
    <th>Dosen</th>
    <th>Nilai</th>
</tr>

<?php foreach ($nilaiList as $n): ?>
<tr>
    <td><?= strtoupper(str_replace('_',' ', $n['peran'])) ?></td>
    <td><?= htmlspecialchars($n['nama']) ?></td>
    <td><b><?= number_format($n['nilai'],2) ?></b></td>
</tr>
<?php endforeach; ?>
</table>

<p style="margin-top:16px">
    <b>Rata-rata:</b> <?= number_format($rata_rata,2) ?><br>
    <b>Status:</b>
    <span class="badge <?= $status == 'LULUS' ? 'lulus' : 'tidak' ?>">
        <?= $status ?>
    </span>
</p>

</div>

<?php endif; ?>

</div>

</body>
</html>
