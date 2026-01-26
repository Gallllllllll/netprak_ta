<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

$mahasiswa_id = $_SESSION['user']['id'];
$tab = $_GET['tab'] ?? 'sempro';

function nilaiHuruf($nilai) {
    if ($nilai >= 85) return 'A';
    if ($nilai >= 75) return 'B';
    if ($nilai >= 65) return 'C';
    if ($nilai >= 55) return 'D';
    return 'E';
}

function ambilNilai($pdo, $pengajuanTable, $nilaiTable, $kodeField, $mahasiswa_id) {
    $stmt = $pdo->prepare("
        SELECT id, $kodeField
        FROM $pengajuanTable
        WHERE mahasiswa_id = ?
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$mahasiswa_id]);
    $pengajuan = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pengajuan) return null;

    $stmt = $pdo->prepare("
        SELECT n.peran, n.nilai, d.nama
        FROM $nilaiTable n
        JOIN dosen d ON n.dosen_id = d.id
        WHERE n.pengajuan_id = ?
        ORDER BY FIELD(n.peran,'dosbing_1','dosbing_2','penguji')
    ");
    $stmt->execute([$pengajuan['id']]);
    $nilai = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$nilai) return null;

    $rata = round(array_sum(array_column($nilai,'nilai')) / count($nilai), 2);

    return [
        'kode' => $pengajuan[$kodeField],
        'nilai' => $nilai,
        'rata' => $rata,
        'huruf' => nilaiHuruf($rata),
        'status' => in_array(nilaiHuruf($rata), ['A', 'B', 'C']) ? 'LULUS' : 'TIDAK LULUS'
    ];
}

$data = $tab === 'semhas'
    ? ambilNilai($pdo,'pengajuan_semhas','nilai_semhas','id_semhas',$mahasiswa_id)
    : ambilNilai($pdo,'pengajuan_sempro','nilai_sempro','id_sempro',$mahasiswa_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hasil Penilaian Seminar</title>

<link rel="stylesheet" href="../../style.css">

<style>
.main-content{
    padding:32px;
    background:#fff3e9;
    min-height:100vh;
}
.header h1{
    color:#ff7a00;
    margin-bottom:6px;
}
.tabs{
    display:flex;
    gap:12px;
    margin:24px 0;
}
.tab{
    padding:10px 22px;
    border-radius:24px;
    background:#ffe1c8;
    color:#ff7a00;
    text-decoration:none;
    font-weight:600;
}
.tab.active{
    background:linear-gradient(90deg,#ff7a00,#ff9d42);
    color:#fff;
}
.card{
    background:#fff;
    border-radius:18px;
    padding:24px;
    margin-bottom:24px;
    box-shadow:0 8px 22px rgba(0,0,0,.08);
}
.card h3{
    margin-top:0;
    color:#ff7a00;
}
.dosen{
    background:#fff6ef;
    border:1px solid #ffd6b8;
    border-radius:14px;
    padding:16px;
    margin-bottom:12px;
}
.hasil{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
}
.box{
    background:#fff6ef;
    border-radius:16px;
    padding:22px;
    text-align:center;
}
.box .big{
    font-size:44px;
    font-weight:700;
    color:#ff7a00;
}
.status{
    font-size:18px;
    font-weight:700;
}
.lulus{color:#28a745;}
.tidak{color:#dc3545;}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">

<div class="header">
    <h1>Hasil Penilaian Seminar</h1>
    <p>Daftar nilai resmi Seminar Proposal dan Seminar Hasil</p>
</div>

<div class="tabs">
    <a class="tab <?= $tab==='sempro'?'active':'' ?>" href="?tab=sempro">📄 Seminar Proposal</a>
    <a class="tab <?= $tab==='semhas'?'active':'' ?>" href="?tab=semhas">📘 Seminar Hasil</a>
</div>

<?php if(!$data): ?>
<div class="card">Nilai belum tersedia.</div>
<?php else: ?>

<div class="card">
<h3>Detail Penilaian Dosen</h3>
<?php foreach($data['nilai'] as $n): ?>
<div class="dosen">
    <b><?= strtoupper(str_replace('_',' ',$n['peran'])) ?></b><br>
    <?= htmlspecialchars($n['nama']) ?> — Nilai: <b><?= number_format($n['nilai'],0) ?></b>
</div>
<?php endforeach; ?>
</div>

<div class="card">
<h3>Hasil Akhir</h3>
<div class="hasil">
    <div class="box">
        <div>Rata-rata Nilai</div>
        <div class="big"><?= $data['rata'] ?></div>
    </div>
    <div class="box">
        <div>Nilai Huruf</div>
        <div class="big"><?= $data['huruf'] ?></div>
    </div>
    <div class="box">
        <div>Status</div>
        <div class="status <?= $data['status']==='LULUS'?'lulus':'tidak' ?>">
            <?= $data['status'] ?>
        </div>
    </div>
</div>
</div>

<?php endif; ?>

</div>
</body>
</html>
