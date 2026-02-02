<?php
session_start();
require "../../config/connection.php";
require_once '../../config/base_url.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}

$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';
$mahasiswa_id = $_SESSION['user']['id'];
$tab = $_GET['tab'] ?? 'sempro';

function nilaiHuruf($nilai) {
    if ($nilai >= 85) return 'A';
    if ($nilai >= 75) return 'B';
    if ($nilai >= 65) return 'C';
    if ($nilai >= 55) return 'D';
    return 'E';
}

function nilaiBobot($huruf) {
    return match($huruf) {
        'A' => '4.0',
        'B' => '3.0',
        'C' => '2.0',
        'D' => '1.0',
        default => '0.0'
    };
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

    $rata = round(array_sum(array_column($nilai,'nilai')) / count($nilai), 0);
    $huruf = nilaiHuruf($rata);

    return [
        'nilai' => $nilai,
        'rata' => $rata,
        'huruf' => $huruf,
        'bobot' => nilaiBobot($huruf),
        'status' => in_array($huruf,['A','B','C']) ? 'LULUS' : 'TIDAK LULUS'
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet">
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Hasil Penilaian</title>

<style>
body{
    background:#FFF1E5 !important;
}
/* TOP */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px
}
.topbar h1{
    color:#ff8c42;
    font-size:28px
}

/* PROFILE */
.mhs-info{
    display:flex;
    align-items:left;
    gap:20px
}
.mhs-text span{
    font-size:13px;
    color:#555
}
.mhs-text b{
    color:#ff8c42;
    font-size:14px
}

.avatar{
    width:42px;
    height:42px;
    background:#ff8c42;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}
/* TAB WRAPPER */
.tabs{
    display:flex;
    gap:12px;
    margin:24px 0;
}

/* TAB ITEM */
.tab{
    display:flex;
    align-items:center;
    gap:10px;
    padding:10px 22px;
    border-radius:24px;
    background:#ffe1c8;
    color:#ff7a00;
    text-decoration:none;
    font-weight:700;
    transition:.2s ease;
}

/* ICON */
.tab-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
}

/* ACTIVE */
.tab.active{
    background:linear-gradient(90deg,#ff7a00,#ff9d42);
    color:#fff;
}

.tab.active .tab-icon{
    color:#fff;
}

/* HOVER */
.tab:hover{
    transform:translateY(-1px);
    box-shadow:0 6px 14px rgba(0,0,0,.12);
}
/* =====================
   CARD UTAMA
===================== */
.nilai-card{
    background:#fff;
    border-radius:20px;
    padding:24px;
    margin-bottom:24px;
    box-shadow:0 10px 24px rgba(0,0,0,.08);
}
.nilai-card h3{
    margin-bottom:20px;
    color:#ff7a00;
    font-size:20px;
}
.divider {
    border: none;
    height: 0.5px;
    width: 100% !important;
    background: #FF983D;
    display: block;
    margin: 12px 0;
    margin-bottom:20px;
}
/* =====================
   DETAIL DOSEN
===================== */
.dosen-row{
    display:grid;
    grid-template-columns:1fr auto;
    gap:12px;
    padding:14px 16px;
    border-radius:14px;
    background:#fff7f1;
    border:1px solid #ffd4b8;
    margin-bottom:12px;
}

.dosen-role{
    font-size:12px;
    font-weight:800;
    color:#ff7a00;
    letter-spacing:.4px;
}

.dosen-nama{
    font-size:14px;
    font-weight:600;
    color:#333;
}

.dosen-score{
    font-size:32px;
    font-weight:800;
    color:#444;
    align-self:center;
}

/* =====================
   HASIL AKHIR
===================== */
.summary-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.avg-block{
    display:flex;
    flex-direction:column;
}

.avg-label{
    font-size:12px;
    font-weight:800;
    color:#ff7a00;
}

.avg-value{
    font-size:42px;
    font-weight:900;
    color:#333;
}

.summary-icon span{
    font-size:58px;
    color:#ff8c42;
}

/* GRID */
.summary-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
}

.summary-box{
    border-radius:14px;
    padding:16px;
    text-align:center;
    font-weight:800;
}

.summary-box small{
    display:block;
    font-size:11px;
    margin-bottom:6px;
}

/* VARIAN */
.box-bobot{
    background:#eef3ff;
    border:1px solid #8aa8ff;
    color:#4b6fd8;
}
.box-huruf{
    background:#fff3e6;
    border:1px solid #ffa65c;
    color:#ff7a00;
}
.box-status{
    background:#ecffef;
    border:1px solid #7fd49b;
    color:#38a169;
}

.summary-box .big{
    font-size:26px;
}

/* CATATAN */
.note{
    margin-top:18px;
    padding:14px 16px;
    background:#fff1f1;
    border:1px solid #ffb3b3;
    border-radius:12px;
    color:#ff4d4d;
    font-size:13px;
    display:flex;
    gap:10px;
    align-items:center;
}
</style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">

    <!-- TOPBAR ASLI (TIDAK DIUBAH) -->
    <div class="topbar">
        <h1>Hasil Penilaian Seminar</h1>
        <div class="mhs-info">
            <div class="mhs-text">
                <span>Selamat Datang,</span><br>
                <b><?= htmlspecialchars($username) ?></b>
            </div>
            <div class="avatar">
                <span class="material-symbols-rounded" style="color:#fff">person</span>
            </div>
        </div>
    </div>

    <div class="tabs">
        <a class="tab <?= $tab==='sempro'?'active':'' ?>" href="?tab=sempro">
            <span class="material-symbols-rounded tab-icon">description</span>
            Seminar Proposal
        </a>
        <a class="tab <?= $tab==='semhas'?'active':'' ?>" href="?tab=semhas">
            <span class="material-symbols-rounded tab-icon">task</span>
            Seminar Hasil
        </a>
    </div>

<?php if(!$data): ?>
    <div class="nilai-card">Nilai belum tersedia.</div>
<?php else: ?>

    <!-- DETAIL DOSEN -->
    <div class="nilai-card">
        <h3>Detail Penilaian Dosen</h3>
        <hr class="divider">
        <?php foreach($data['nilai'] as $n): ?>
        <div class="dosen-row">
            <div>
                <div class="dosen-role"><?= strtoupper(str_replace('_',' ',$n['peran'])) ?></div>
                <div class="dosen-nama"><?= htmlspecialchars($n['nama']) ?></div>
            </div>
            <div class="dosen-score"><?= number_format($n['nilai'],0) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- HASIL AKHIR -->
    <div class="nilai-card">
        <h3>Hasil Akhir</h3>
        <hr class="divider">
        <div class="summary-top">
            <div class="avg-block">
                <span class="avg-label">Rata-rata Nilai</span>
                <span class="avg-value"><?= $data['rata'] ?></span>
            </div>
            <div class="summary-icon">
                <span class="material-symbols-rounded">school</span>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-box box-bobot">
                <small>BOBOT</small>
                <div class="big"><?= $data['bobot'] ?></div>
            </div>
            <div class="summary-box box-huruf">
                <small>HURUF</small>
                <div class="big"><?= $data['huruf'] ?></div>
            </div>
            <div class="summary-box box-status">
                <small>STATUS</small>
                <div class="big"><?= $data['status'] ?></div>
            </div>
        </div>

        <div class="note">
            <span class="material-symbols-rounded">info</span>
            Jika terdapat kesalahan penulisan nilai atau data yang tidak sesuai,
            silakan hubungi admin untuk pengecekan ulang.
        </div>
    </div>

<?php endif; ?>

</div>
</body>
</html>