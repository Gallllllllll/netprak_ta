<?php
session_start();
require "../config/connection.php";

// ===============================
// CEK LOGIN DOSEN
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: ../login.php");
    exit;
}

$dosen_id = $_SESSION['user']['id'];

/* =====================================================
   STATISTIK DASHBOARD
===================================================== */

// TOTAL MAHASISWA BIMBINGAN
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT pengajuan_id)
    FROM dosbing_ta
    WHERE dosen_id = ?
");
$stmt->execute([$dosen_id]);
$totalMahasiswa = (int)$stmt->fetchColumn();

// TOTAL TA (semua pengajuan TA yang dibimbing)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT p.id)
    FROM pengajuan_ta p
    JOIN dosbing_ta d ON d.pengajuan_id = p.id
    WHERE d.dosen_id = ?
");
$stmt->execute([$dosen_id]);
$totalTA = (int)$stmt->fetchColumn();

// TOTAL SEMPRO
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT s.id)
    FROM pengajuan_sempro s
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    JOIN dosbing_ta d ON d.pengajuan_id = p.id
    WHERE d.dosen_id = ?
");
$stmt->execute([$dosen_id]);
$totalSempro = (int)$stmt->fetchColumn();

// TOTAL SEMHAS
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT sh.id)
    FROM pengajuan_semhas sh
    JOIN pengajuan_ta p ON sh.pengajuan_ta_id = p.id
    JOIN dosbing_ta d ON d.pengajuan_id = p.id
    WHERE d.dosen_id = ?
");
$stmt->execute([$dosen_id]);
$totalSemhas = (int)$stmt->fetchColumn();

/* =====================================================
   ANTRIAN VALIDASI TERKINI
===================================================== */
$stmt = $pdo->prepare("
    SELECT 
        m.nama,
        d.status_persetujuan,
        d.created_at
    FROM dosbing_ta d
    JOIN pengajuan_ta p ON d.pengajuan_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    WHERE d.dosen_id = ?
    ORDER BY d.created_at DESC
    LIMIT 5
");
$stmt->execute([$dosen_id]);
$antrian = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
   DATA PIE CHART
===================================================== */

// STATUS TUGAS AKHIR (dari dosbing_ta)
$stmt = $pdo->prepare("
    SELECT status_persetujuan, COUNT(*) total
    FROM dosbing_ta
    WHERE dosen_id = ?
    GROUP BY status_persetujuan
");
$stmt->execute([$dosen_id]);
$statusTA = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// STATUS SEMPRO
$stmt = $pdo->prepare("
    SELECT s.status, COUNT(*) total
    FROM pengajuan_sempro s
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    JOIN dosbing_ta d ON d.pengajuan_id = p.id
    WHERE d.dosen_id = ?
    GROUP BY s.status
");
$stmt->execute([$dosen_id]);
$statusSempro = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// STATUS SEMHAS
$stmt = $pdo->prepare("
    SELECT sh.status, COUNT(*) total
    FROM pengajuan_semhas sh
    JOIN pengajuan_ta p ON sh.pengajuan_ta_id = p.id
    JOIN dosbing_ta d ON d.pengajuan_id = p.id
    WHERE d.dosen_id = ?
    GROUP BY sh.status
");
$stmt->execute([$dosen_id]);
$statusSemhas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Dosen</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.main-content{
    padding:30px;
    background:#fff3e9;
    min-height:100vh;
}
.subtitle{color:#777;margin-bottom:30px}

/* STAT CARDS */
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
}
.stat-card{
    background:#fff;
    border-radius:18px;
    padding:22px;
    display:flex;
    gap:18px;
    box-shadow:0 10px 25px rgba(255,140,80,.15);
}
.stat-icon{
    width:56px;height:56px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:26px;color:#fff;
}
.bg-dark{background:#444}
.bg-orange{background:#ff8c42}
.bg-pink{background:#ff6f91}
.bg-purple{background:#9b6cff}
.stat-info span{font-size:14px;color:#777}
.stat-info h3{margin:4px 0 0;font-size:26px}

/* PANELS */
.panels{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
    margin-bottom:30px;
}
.panel{
    background:#fff;
    border-radius:18px;
    padding:20px;
    min-height:220px;
    box-shadow:0 10px 25px rgba(255,140,80,.12);
}
.panel h4{text-align:center}
.chart-wrapper{
    width: 160px;          /* DIAMETER pie */
    height: 160px;
    margin: 10px auto 0;   /* center */
}

.chart-wrapper canvas{
    width: 100% !important;
    height: 100% !important;
}


/* LIST */
.list-box{
    background:#fff;
    border-radius:18px;
    padding:20px;
    box-shadow:0 10px 25px rgba(255,140,80,.12);
}
.list-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:14px 0;
    border-bottom:1px solid #eee;
}
.list-item:last-child{border-bottom:none}
.badge{
    padding:5px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}
.success{background:#d4edda;color:#155724}
.warning{background:#fff3cd;color:#856404}
.danger{background:#f8d7da;color:#721c24}
</style>
</head>

<body>
<div class="container">

<?php include "sidebar.php"; ?>

<div class="main-content">

<h1>Dashboard</h1>
<p class="subtitle">
Monitoring progres Tugas Akhir, Seminar Proposal, dan Seminar Hasil
</p>

<!-- STAT -->
<div class="stats">
    <div class="stat-card">
        <div class="stat-icon bg-dark">🎓</div>
        <div class="stat-info">
            <span>Total Mahasiswa</span>
            <h3><?= $totalMahasiswa ?></h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-orange">📘</div>
        <div class="stat-info">
            <span>Total Tugas Akhir</span>
            <h3><?= $totalTA ?></h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-pink">📄</div>
        <div class="stat-info">
            <span>Total Sempro</span>
            <h3><?= $totalSempro ?></h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-purple">📑</div>
        <div class="stat-info">
            <span>Total Semhas</span>
            <h3><?= $totalSemhas ?></h3>
        </div>
    </div>
</div>

<!-- PANELS PLACEHOLDER -->
<div class="panels">
    <div class="panel">
        <h4>Status Tugas Akhir</h4>
        <div class="chart-wrapper">
            <canvas id="taChart"></canvas>
        </div>
    </div>

    <div class="panel">
        <h4>Status Seminar Proposal</h4>
        <div class="chart-wrapper">
            <canvas id="semproChart"></canvas>
        </div>
    </div>

    <div class="panel">
        <h4>Status Seminar Hasil</h4>
        <div class="chart-wrapper">
            <canvas id="semhasChart"></canvas>
        </div>
    </div>
</div>



<!-- LIST -->
<div class="list-box">
<h4 style="text-align:center;margin-bottom:12px">
Antrian Validasi Terkini
</h4>

<?php if (!$antrian): ?>
<p style="text-align:center;color:#777">Belum ada antrian.</p>
<?php endif; ?>

<?php foreach ($antrian as $a): 
$class = match ($a['status_persetujuan']) {
    'disetujui' => 'success',
    'menunggu'  => 'warning',
    'ditolak'   => 'danger',
    default     => 'warning'
};
?>
<div class="list-item">
    <div>
        <b><?= htmlspecialchars($a['nama']) ?></b><br>
        <small>Pengajuan TA</small>
    </div>
    <span class="badge <?= $class ?>">
        <?= ucfirst($a['status_persetujuan']) ?>
    </span>
</div>
<?php endforeach; ?>

</div>

</div>
</div>
<script>
const pieOptions = {
    type: 'doughnut',
    options: {
        responsive: true,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
};

new Chart(document.getElementById('taChart'), {
    ...pieOptions,
    data: {
        labels: <?= json_encode(array_keys($statusTA)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusTA)) ?>,
            backgroundColor: ['#28c76f','#ff9f43','#ea5455']
        }]
    }
});

new Chart(document.getElementById('semproChart'), {
    ...pieOptions,
    data: {
        labels: <?= json_encode(array_keys($statusSempro)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusSempro)) ?>,
            backgroundColor: ['#7367f0','#ff9f43','#28c76f']
        }]
    }
});

new Chart(document.getElementById('semhasChart'), {
    ...pieOptions,
    data: {
        labels: <?= json_encode(array_keys($statusSemhas)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusSemhas)) ?>,
            backgroundColor: ['#00cfe8','#ff9f43','#28c76f']
        }]
    }
});
</script>

</body>
</html>
