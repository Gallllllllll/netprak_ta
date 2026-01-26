<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

/* ===============================
   CEK LOGIN ADMIN
================================ */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: " . base_url('login.php'));
    exit;
}

/* ===============================
   VALIDASI ID PENGAJUAN
================================ */
$pengajuan_id = $_GET['pengajuan_id'] ?? null;
if (!$pengajuan_id) die("ID tidak valid");

$stmt = $pdo->prepare("
    SELECT 
        s.id AS pengajuan_sempro_id,
        s.id_sempro,
        s.status,

        m.nama AS nama_mahasiswa,
        m.nim,

        p.judul_ta
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    WHERE s.id = ?
");
$stmt->execute([$pengajuan_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data sempro tidak ditemukan");
}



/* ===============================
   AMBIL DOSBING 1 & 2
================================ */
$stmt = $pdo->prepare("
    SELECT 
        db.role AS peran,
        d.id AS dosen_id,
        d.nama
    FROM dosbing_ta db
    JOIN dosen d ON db.dosen_id = d.id
    WHERE db.pengajuan_id = ?
      AND db.role IN ('dosbing_1','dosbing_2')
");
$stmt->execute([$pengajuan_id]);
$dosbing = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$dosbing) {
    die("Dosen pembimbing belum ditentukan.");
}

/* ===============================
   AMBIL NILAI LAMA (JIKA ADA)
================================ */
$nilaiLama = [];
$stmt = $pdo->prepare("
    SELECT peran, nilai
    FROM nilai_sempro
    WHERE pengajuan_id = ?
");
$stmt->execute([$pengajuan_id]);

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $n) {
    $nilaiLama[$n['peran']] = $n['nilai'];
}

/* ===============================
   SIMPAN NILAI
================================ */
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['nilai']) || !is_array($_POST['nilai'])) {
        $error = "Data nilai tidak valid.";
    } else {

        $pdo->beginTransaction();

        try {
            foreach ($_POST['nilai'] as $peran => $item) {

                $nilai    = floatval($item['nilai']);
                $dosen_id = intval($item['dosen_id']);

                if ($nilai < 0 || $nilai > 100) {
                    throw new Exception("Nilai harus antara 0 – 100");
                }

                $stmt = $pdo->prepare("
                    INSERT INTO nilai_sempro (pengajuan_id, dosen_id, peran, nilai)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
                ");

                $stmt->execute([
                    $pengajuan_id,
                    $dosen_id,
                    $peran,
                    $nilai
                ]);
            }

            $pdo->commit();
            header("Location: index.php?success=1");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Input Nilai SEMPRO</title>
<style>
body{
    font-family: Arial, sans-serif;
    background:#f4f6f8;
    padding:30px;
}
.card{
    background:#fff;
    max-width:600px;
    margin:auto;
    padding:20px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
}
h2{margin-bottom:15px}
.info{
    background:#f1f5f9;
    padding:12px;
    border-radius:6px;
    margin-bottom:15px;
    font-size:14px;
}
label{
    display:block;
    margin-top:15px;
    font-weight:bold;
}
input[type=number]{
    width:100%;
    padding:8px;
    margin-top:6px;
}
button{
    margin-top:20px;
    padding:10px 16px;
    border:none;
    background:#2563eb;
    color:#fff;
    font-weight:600;
    border-radius:6px;
    cursor:pointer;
}
button:hover{opacity:.9}
.error{
    background:#fee2e2;
    color:#991b1b;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
}
</style>
</head>

<body>

<div class="card">
    <h2>Input Nilai Seminar Proposal</h2>

    <div class="info">
        <b>Mahasiswa :</b> <?= htmlspecialchars($data['nama_mahasiswa']) ?><br>
        <b>NIM :</b> <?= htmlspecialchars($data['nim']) ?><br>
        <b>Judul TA :</b><br>
        <?= htmlspecialchars($data['judul_ta']) ?>
    </div>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <?php foreach ($dosbing as $d): ?>
            <label>
                <?= strtoupper(str_replace('_',' ', $d['peran'])) ?>
                (<?= htmlspecialchars($d['nama']) ?>)
            </label>

            <input type="hidden"
                   name="nilai[<?= $d['peran'] ?>][dosen_id]"
                   value="<?= $d['dosen_id'] ?>">

            <input type="number"
                name="nilai[<?= $d['peran'] ?>][nilai]"
                min="0"
                max="100"
                step="0.01"
                required
                value="<?= $nilaiLama[$d['peran']] ?? '' ?>"
                oninput="this.value = Math.min(100, Math.max(0, this.value))">
        <?php endforeach; ?>

        <button type="submit">Simpan Nilai</button>
    </form>
</div>

<script>
document.querySelectorAll('input[type="number"]').forEach(el=>{
    el.addEventListener('input',()=>{
        if(el.value>100) el.value=100;
        if(el.value<0) el.value=0;
    });
});
</script>

</body>
</html>
