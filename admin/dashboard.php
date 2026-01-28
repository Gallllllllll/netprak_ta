<?php
session_start();
require "../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/coba/config/base_url.php';

// ===============================
// CEK LOGIN ADMIN
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user']['nama'] ?? 'Admin';

/* =====================================================
   STATISTIK AKADEMIK (GLOBAL)
===================================================== */

// TOTAL MAHASISWA
$totalMahasiswa = (int)$pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();

// TOTAL TA
$totalTA = (int)$pdo->query("SELECT COUNT(*) FROM pengajuan_ta")->fetchColumn();

// TOTAL SEMPRO
$totalSempro = (int)$pdo->query("SELECT COUNT(*) FROM pengajuan_sempro")->fetchColumn();

// TOTAL SEMHAS
$totalSemhas = (int)$pdo->query("SELECT COUNT(*) FROM pengajuan_semhas")->fetchColumn();

/* =====================================================
   DATA STATUS UNTUK CHART
===================================================== */

// STATUS TUGAS AKHIR
$statusTA = $pdo->query("SELECT status, COUNT(*) as total FROM pengajuan_ta GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

// STATUS SEMPRO
$statusSempro = $pdo->query("SELECT status, COUNT(*) as total FROM pengajuan_sempro GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

// STATUS SEMHAS
$statusSemhas = $pdo->query("SELECT status, COUNT(*) as total FROM pengajuan_semhas GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

/* =====================================================
   ANTRIAN VALIDASI TERKINI (CONSOLIDATED)
===================================================== */
$queueQuery = "
    (SELECT m.nama, 'Pengajuan TA' as tipe, p.status, p.created_at 
     FROM pengajuan_ta p 
     JOIN mahasiswa m ON p.mahasiswa_id = m.id)
    UNION 
    (SELECT m.nama, 'Pengajuan Sempro' as tipe, s.status, s.created_at 
     FROM pengajuan_sempro s 
     JOIN mahasiswa m ON s.mahasiswa_id = m.id)
    UNION 
    (SELECT m.nama, 'Pengajuan Semhas' as tipe, sh.status, sh.created_at 
     FROM pengajuan_semhas sh 
     JOIN mahasiswa m ON sh.mahasiswa_id = m.id)
    ORDER BY created_at DESC 
    LIMIT 3
";
$antrian = $pdo->query($queueQuery)->fetchAll(PDO::FETCH_ASSOC);

function time_ago($timestamp) {
    if (!$timestamp) return "-";
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return $diff . "s ago";
    if ($diff < 3600) return floor($diff / 60) . "m ago";
    if ($diff < 86400) return floor($diff / 3600) . "h ago";
    return date('d M', $time);
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Politeknik Nest</title>
    <link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
    
    <!-- FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <!-- CHARTS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --accent-orange: #FF8C61;
            --soft-orange: #FFF1E5;
            --bg-color: #FDF2E9;
            --text-dark: #2D3436;
            --text-muted: #636E72;
            --white: #FFFFFF;
            --shadow-orange: 0 10px 30px rgba(255, 140, 80, 0.12);
        }

        * { box-sizing: border-box; }

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

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .greeting h1 {
            font-size: 28px;
            margin: 0;
            color: var(--accent-orange);
            font-weight: 700;
        }

        .greeting p {
            margin: 5px 0 0;
            color: var(--text-muted);
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
            color: var(--text-muted); 
            font-size: 12px;
            display: block;
        }
        
        .admin-profile b { 
            color: var(--accent-orange); 
            font-size: 14px; 
            display: block; 
        }

        .avatar {
            width: 48px;
            height: 48px;
            background: var(--accent-orange);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(255, 140, 97, 0.3);
        }

        /* STAT CARDS - UKURAN SAMA SEPERTI DOSEN */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 24px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: var(--shadow-orange);
            transition: transform 0.3s ease;
        }

        .stat-card:hover { 
            transform: translateY(-5px); 
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .bg-black { background: #333; color: white; }
        .bg-orange { background: #FF983D; color: white; }
        .bg-pink { background: #FF74C7; color: white; }
        .bg-purple { background: #9B6CFF; color: white; }

        .stat-info span { 
            font-size: 14px; 
            color: var(--text-muted); 
            font-weight: 500; 
        }
        
        .stat-info h2 { 
            margin: 4px 0 0; 
            font-size: 32px; 
            font-weight: 700; 
            color: var(--text-dark); 
        }

        /* CHARTS SECTION */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .chart-card {
            background: var(--white);
            border-radius: 24px;
            padding: 24px;
            box-shadow: var(--shadow-orange);
            text-align: center;
        }

        .chart-card h4 { 
            margin: 0 0 20px; 
            color: var(--text-dark); 
            font-weight: 600; 
        }

        .chart-container {
            width: 180px;
            height: 180px;
            margin: 0 auto;
        }

        /* QUEUE LIST */
        .queue-card {
            background: var(--white);
            border-radius: 24px;
            padding: 30px;
            box-shadow: var(--shadow-orange);
        }

        .queue-card h4 {
            margin: 0 0 25px;
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .queue-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 25px;
            background: #F8F9FA;
            border-radius: 16px;
            margin-bottom: 12px;
            transition: transform 0.2s ease;
        }

        .queue-item:hover {
            transform: scale(1.01);
        }

        .m-avatar {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #FF74C7, #FF983D);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 22px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(255, 116, 199, 0.2);
        }

        .m-info { 
            flex: 1; 
            margin-left: 20px; 
        }
        
        .m-info b { 
            font-size: 16px; 
            color: var(--text-dark); 
            display: block; 
            margin-bottom: 2px; 
        }
        
        .m-info p { 
            margin: 0; 
            font-size: 14px; 
            color: var(--text-muted); 
            font-weight: 400; 
        }

        .m-status { 
            text-align: right; 
        }

        .status-text {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .status-badge {
            padding: 8px 18px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 700;
        }

        .status-badge.disetujui {
            background: #E6FAF1;
            color: #2ECC71;
        }

        .status-badge.ditolak {
            background: #FFECEC;
            color: #E74C3C;
        }

        .status-badge.diajukan {
            background: #FFF4E5;
            color: #FF983D;
        }

        .status-badge.revisi {
            background: #EBF4FF;
            color: #3498DB;
        }

        .btn-more {
            display: block;
            width: 100%;
            padding: 16px;
            background: linear-gradient(90deg, #FF74C7, #FF983D);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 700;
            font-size: 16px;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 116, 199, 0.2);
        }

        .btn-more:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 116, 199, 0.3);
        }

        @media screen and (max-width: 1024px) {
            .main-content { 
                margin-left: 0; 
                padding: 100px 20px 40px; 
            }
            
            .topbar { 
                flex-direction: column; 
                align-items: flex-start; 
                gap: 20px; 
            }
            
            .admin-profile { 
                width: 100%; 
                justify-content: flex-end; 
            }
            
            .stats-grid { 
                grid-template-columns: repeat(2, 1fr); 
            }
        }

        @media screen and (max-width: 600px) {
            .stats-grid { 
                grid-template-columns: 1fr; 
            }
        }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
    
    <!-- TOPBAR -->
    <div class="topbar">
        <div class="greeting">
            <h1>Dashboard</h1>
            <p>Monitoring progres Tugas Akhir, Seminar Proposal, dan Seminar Hasil.</p>
        </div>
        <div class="admin-profile">
            <div class="text">
                <small>Selamat Datang,</small>
                <b><?= htmlspecialchars($username) ?></b>
            </div>
            <div class="avatar">
                <span class="material-symbols-rounded" style="color:white">person</span>
            </div>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon-box bg-black">
                <span class="material-symbols-rounded">school</span>
            </div>
            <div class="stat-info">
                <span>Total Mahasiswa</span>
                <h2><?= $totalMahasiswa ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="icon-box bg-orange">
                <span class="material-symbols-rounded">menu_book</span>
            </div>
            <div class="stat-info">
                <span>Total Tugas Akhir</span>
                <h2><?= $totalTA ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="icon-box bg-pink">
                <span class="material-symbols-rounded">description</span>
            </div>
            <div class="stat-info">
                <span>Total Sempro</span>
                <h2><?= $totalSempro ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="icon-box bg-purple">
                <span class="material-symbols-rounded">task</span>
            </div>
            <div class="stat-info">
                <span>Total Semhas</span>
                <h2><?= $totalSemhas ?></h2>
            </div>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="charts-grid">
        <div class="chart-card">
            <h4>Status Tugas Akhir</h4>
            <div class="chart-container">
                <canvas id="taChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h4>Status Seminar Proposal</h4>
            <div class="chart-container">
                <canvas id="semproChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h4>Status Seminar Hasil</h4>
            <div class="chart-container">
                <canvas id="semhasChart"></canvas>
            </div>
        </div>
    </div>

    <!-- QUEUE LIST -->
    <div class="queue-card">
        <h4>Antrian Validasi Terkini</h4>
        
        <?php if (!$antrian): ?>
            <p style="text-align:center; color:#999; padding:20px">No recent activity detected.</p>
        <?php else: ?>
            <?php foreach ($antrian as $row): 
                $badge_class = strtolower($row['status']);
                $display_status = $row['status'] ?: 'Pending';
            ?>
                <div class="queue-item">
                    <div class="m-avatar"><?= substr($row['nama'], 0, 1) ?></div>
                    <div class="m-info">
                        <b><?= htmlspecialchars($row['nama']) ?></b>
                        <p><?= htmlspecialchars($row['tipe']) ?></p>
                    </div>
                    <div class="m-status">
                        <?php 
                            $st_lower = strtolower($display_status);
                            if ($st_lower === 'disetujui' || $st_lower === 'approved'): 
                        ?>
                            <span class="status-badge disetujui">Disetujui</span>
                        <?php elseif ($st_lower === 'ditolak' || $st_lower === 'rejected'): ?>
                            <span class="status-badge ditolak">Ditolak</span>
                        <?php elseif ($st_lower === 'revisi'): ?>
                            <span class="status-badge revisi">Revisi</span>
                        <?php else: ?>
                            <span class="status-badge diajukan"><?= ucwords($display_status) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="verifikasi.php" class="btn-more">Selengkapnya</a>
    </div>

</div>

<script>
const commonOptions = {
    cutout: '75%',
    plugins: {
        legend: { 
            position: 'bottom', 
            labels: { 
                usePointStyle: true, 
                font: { 
                    family: 'Outfit', 
                    size: 11 
                } 
            } 
        },
        tooltip: { 
            callbacks: { 
                label: (context) => ` ${context.label}: ${context.raw}` 
            } 
        }
    },
    responsive: true,
    maintainAspectRatio: false
};

// TA CHART
new Chart(document.getElementById('taChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($statusTA)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusTA)) ?>,
            backgroundColor: ['#28C76F', '#FF9F43', '#EA5455', '#7367F0'],
            borderWidth: 0
        }]
    },
    options: commonOptions
});

// SEMPRO CHART
new Chart(document.getElementById('semproChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($statusSempro)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusSempro)) ?>,
            backgroundColor: ['#FF9F43', '#7367F0', '#28C76F', '#EA5455'],
            borderWidth: 0
        }]
    },
    options: commonOptions
});

// SEMHAS CHART
new Chart(document.getElementById('semhasChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($statusSemhas)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusSemhas)) ?>,
            backgroundColor: ['#7367F0', '#FF9F43', '#28C76F', '#EA5455'],
            borderWidth: 0
        }]
    },
    options: commonOptions
});
</script>

</body>
</html>