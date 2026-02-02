<?php
session_start();
require "../config/connection.php";

/* ===============================
   CEK LOGIN DOSEN
================================ */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: ../login.php");
    exit;
}

$dosen_id = $_SESSION['user']['id'];

/* ===============================
   DATA DOSEN
================================ */
$stmt = $pdo->prepare("SELECT nama FROM dosen WHERE id = ?");
$stmt->execute([$dosen_id]);
$nama_dosen = $stmt->fetchColumn() ?? 'Dosen';

/* ===============================
   STATISTIK DASHBOARD
================================ */

// TOTAL MAHASISWA BIMBINGAN
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT pengajuan_id)
    FROM dosbing_ta
    WHERE dosen_id = ?
");
$stmt->execute([$dosen_id]);
$totalMahasiswa = (int)$stmt->fetchColumn();

// TOTAL TA
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM dosbing_ta
    WHERE dosen_id = ?
");
$stmt->execute([$dosen_id]);
$totalTA = (int)$stmt->fetchColumn();

// TOTAL SEMPRO (yang sudah upload persetujuan)
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM dosbing_ta
    WHERE dosen_id = ?
      AND persetujuan_sempro IS NOT NULL
");
$stmt->execute([$dosen_id]);
$totalSempro = (int)$stmt->fetchColumn();

// TOTAL SEMHAS (yang sudah disetujui)
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM dosbing_ta
    WHERE dosen_id = ?
      AND status_persetujuan_semhas = 'disetujui'
");
$stmt->execute([$dosen_id]);
$totalSemhas = (int)$stmt->fetchColumn();

/* ===============================
   ANTRIAN VALIDASI (3 TERBARU)
================================ */
$stmt = $pdo->prepare("
    SELECT m.nama, d.status_persetujuan, d.created_at
    FROM dosbing_ta d
    JOIN pengajuan_ta p ON d.pengajuan_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    WHERE d.dosen_id = ?
    ORDER BY d.created_at DESC
    LIMIT 3
");
$stmt->execute([$dosen_id]);
$antrian = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   DATA PIE CHART
================================ */

// STATUS TA
$stmt = $pdo->prepare("
    SELECT status_persetujuan, COUNT(*) total
    FROM dosbing_ta
    WHERE dosen_id = ?
    GROUP BY status_persetujuan
");
$stmt->execute([$dosen_id]);
$statusTA = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// STATUS SEMPRO (NULL / NOT NULL)
$stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN persetujuan_sempro IS NULL THEN 'Belum Sempro'
            ELSE 'disetujui'
        END AS status,
        COUNT(*) total
    FROM dosbing_ta
    WHERE dosen_id = ?
    GROUP BY status
");
$stmt->execute([$dosen_id]);
$statusSempro = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// STATUS SEMHAS
$stmt = $pdo->prepare("
    SELECT status_persetujuan_semhas, COUNT(*) total
    FROM dosbing_ta
    WHERE dosen_id = ?
    GROUP BY status_persetujuan_semhas
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
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
    --primary: #FF6B9D;
    --secondary: #FF8E3C;
    --gradient: linear-gradient(135deg, #FF6B9D 0%, #FF8E3C 100%);
}

body {
    font-family: 'Inter', sans-serif;
    background: #FFF1E5 !important;
    margin: 0;
}

.container {
    background: #FFF1E5 !important;
}

.main-content {
    margin-left: 280px;
    padding: 32px;
    min-height: 100vh;
    background: #FFF1E5 !important;
}

/* HEADER - SAMA SEPERTI ADMIN */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.greeting h1 {
    font-size: 28px;
    margin: 0;
    color: #FF8E3C;
    font-weight: 700;
}

.greeting p {
    margin: 5px 0 0;
    color: #6B7280;
    font-size: 15px;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-profile .text {
    text-align: right;
    line-height: 1.3;
}

.admin-profile small { 
    color: #6B7280; 
    font-size: 12px;
    display: block;
}

.admin-profile b { 
    color: #FF8E3C; 
    font-size: 14px; 
    display: block; 
}

.avatar {
    width: 48px;
    height: 48px;
    background: #FF8E3C;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(255, 142, 60, 0.3);
}

/* STAT CARDS */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.stat-card:nth-child(1) {
    border-color: rgba(88, 86, 86, 0.2);
}

.stat-card:nth-child(1) .stat-icon {
    background: #585656;
}

.stat-card:nth-child(2) {
    border-color: rgba(255, 142, 60, 0.2);
}

.stat-card:nth-child(2) .stat-icon {
    background: #FF8E3C;
}

.stat-card:nth-child(3) {
    border-color: rgba(255, 107, 157, 0.2);
}

.stat-card:nth-child(3) .stat-icon {
    background: #FF6B9D;
}

.stat-card:nth-child(4) {
    border-color: rgba(139, 92, 246, 0.2);
}

.stat-card:nth-child(4) .stat-icon {
    background: #8B5CF6;
}

.stat-card-inner {
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon .material-symbols-rounded {
    font-size: 28px;
    color: white;
    font-variation-settings: 'FILL' 1, 'wght' 500;
}

.stat-info {
    flex: 1;
}

.stat-info span {
    font-size: 13px;
    color: #6B7280;
    font-weight: 500;
    display: block;
}

.stat-info h3 {
    margin: 4px 0 0;
    font-size: 32px;
    font-weight: 700;
    color: #1F2937;
}

/* PANELS */
.panels {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.panel {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.panel h4 {
    text-align: center;
    margin: 0 0 20px;
    font-size: 16px;
    font-weight: 600;
    color: #1F2937;
}

.chart-wrapper {
    width: 180px;
    height: 180px;
    margin: 0 auto;
}

.chart-wrapper canvas {
    width: 100% !important;
    height: 100% !important;
}

/* ANTRIAN LIST */
.list-box {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.list-box h4 {
    text-align: center;
    margin: 0 0 20px;
    font-size: 18px;
    font-weight: 700;
    color: #1F2937;
}

.list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    margin-bottom: 12px;
    background: #F9FAFB;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.list-item:hover {
    background: #F3F4F6;
    transform: translateX(4px);
}

.list-item-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.list-item-avatar {
    width: 48px;
    height: 48px;
    background: var(--gradient);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    font-weight: 700;
    flex-shrink: 0;
}

.list-item-info b {
    display: block;
    color: #1F2937;
    font-size: 15px;
    margin-bottom: 4px;
}

.list-item-info small {
    color: #6B7280;
    font-size: 13px;
}

.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.badge.disetujui {
    background: #D1FAE5;
    color: #065F46;
}

.badge.menunggu {
    background: #FEF3C7;
    color: #92400E;
}

.badge.ditolak {
    background: #FEE2E2;
    color: #991B1B;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #9CA3AF;
}

.empty-state .material-symbols-rounded {
    font-size: 64px;
    opacity: 0.3;
    margin-bottom: 12px;
}

.btn-selengkapnya {
    display: block;
    width: 100%;
    padding: 12px;
    margin-top: 16px;
    background: var(--gradient);
    color: white;
    text-align: center;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3);
}

.btn-selengkapnya:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(255, 107, 157, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
    
    .topbar {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
    }
    
    .admin-profile {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
</head>

<body>
<div class="container">

<?php include "sidebar.php"; ?>

<div class="main-content">

<!-- HEADER - SAMA SEPERTI ADMIN -->
<div class="topbar">
    <div class="greeting">
        <h1>Dashboard</h1>
        <p>Monitoring progres Tugas Akhir, Seminar Proposal, dan Seminar Hasil</p>
    </div>
    <div class="admin-profile">
        <div class="text">
            <small>Selamat Datang,</small>
            <b><?= htmlspecialchars($nama_dosen) ?></b>
        </div>
        <div class="avatar">
            <span class="material-symbols-rounded" style="color:white">person</span>
        </div>
    </div>
</div>

<!-- STAT CARDS -->
<div class="stats">
    <div class="stat-card">
        <div class="stat-card-inner">
            <div class="stat-icon">
                <span class="material-symbols-rounded">school</span>
            </div>
            <div class="stat-info">
                <span>Total Mahasiswa</span>
                <h3><?= $totalMahasiswa ?></h3>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-inner">
            <div class="stat-icon">
                <span class="material-symbols-rounded">assignment</span>
            </div>
            <div class="stat-info">
                <span>Total Tugas Akhir</span>
                <h3><?= $totalTA ?></h3>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-inner">
            <div class="stat-icon">
                <span class="material-symbols-rounded">co_present</span>
            </div>
            <div class="stat-info">
                <span>Total Sempro</span>
                <h3><?= $totalSempro ?></h3>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-inner">
            <div class="stat-icon">
                <span class="material-symbols-rounded">task</span>
            </div>
            <div class="stat-info">
                <span>Total Semhas</span>
                <h3><?= $totalSemhas ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- CHART PANELS -->
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

<!-- ANTRIAN VALIDASI -->
<div class="list-box">
    <h4>Antrian Validasi Terkini</h4>

    <?php if (empty($antrian)): ?>
    <div class="empty-state">
        <span class="material-symbols-rounded">inbox</span>
        <p>Belum ada antrian validasi</p>
    </div>
    <?php else: ?>
        <?php foreach ($antrian as $a): 
        $initials = strtoupper(substr($a['nama'], 0, 1));
        ?>
        <div class="list-item">
            <div class="list-item-left">
                <div class="list-item-avatar"><?= $initials ?></div>
                <div class="list-item-info">
                    <b><?= htmlspecialchars($a['nama']) ?></b>
                    <small>Tugas Akhir</small>
                </div>
            </div>
            <span class="badge <?= $a['status_persetujuan'] ?>">
                <?= ucfirst($a['status_persetujuan']) ?>
            </span>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="mahasiswa_bimbingan.php" class="btn-selengkapnya">
        Selengkapnya
    </a>
</div>

</div>
</div>

<script>
const pieOptions = {
    type: 'doughnut',
    options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 12,
                    font: {
                        size: 11,
                        family: 'Inter'
                    },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
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
            backgroundColor: ['#10B981','#F59E0B','#EF4444'],
            borderWidth: 0
        }]
    }
});

new Chart(document.getElementById('semproChart'), {
    ...pieOptions,
    data: {
        labels: <?= json_encode(array_keys($statusSempro)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusSempro)) ?>,
            backgroundColor: ['#3B82F6','#F59E0B','#10B981'],
            borderWidth: 0
        }]
    }
});

new Chart(document.getElementById('semhasChart'), {
    ...pieOptions,
    data: {
        labels: <?= json_encode(array_keys($statusSemhas)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusSemhas)) ?>,
            backgroundColor: ['#06B6D4','#F59E0B','#10B981'],
            borderWidth: 0
        }]
    }
});
</script>

</body>
</html>