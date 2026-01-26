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
    LIMIT 5
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
            --shadow: 0 10px 30px rgba(255, 140, 80, 0.12);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            color: var(--text-dark);
        }

        .main-content {
            margin-left: 250px;
            padding: 40px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
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

        .admin-profile small { color: var(--text-muted); font-size: 12px; }
        .admin-profile b { color: var(--accent-orange); font-size: 14px; display: block; }

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

        /* STAT CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* Force 1 row on desktop */
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
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .stat-card:hover { transform: translateY(-5px); }

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

        .stat-info span { font-size: 14px; color: var(--text-muted); font-weight: 500; }
        .stat-info h2 { margin: 4px 0 0; font-size: 32px; font-weight: 700; color: #2d3436; }

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
            box-shadow: var(--shadow);
            text-align: center;
        }

        .chart-card h4 { margin: 0 0 20px; color: var(--text-dark); font-weight: 600; }

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
            box-shadow: var(--shadow);
        }

        .queue-card h4 {
            margin: 0 0 25px;
            text-align: center;
            font-size: 18px;
            color: #2d3436;
        }

        .queue-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .queue-item:last-child { border-bottom: none; }

        .m-avatar {
            width: 45px;
            height: 45px;
            background: #fff5f0;
            border: 1px solid #ffe5d9;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-orange);
            font-weight: 700;
            font-size: 18px;
        }

        .m-info { flex: 1; margin-left: 20px; }
        .m-info b { font-size: 15px; color: #2d3436; }
        .m-info p { margin: 4px 0 0; font-size: 13px; color: var(--text-muted); font-style: italic; }

        .m-status { text-align: right; }
        .m-status small { display: block; margin-bottom: 6px; color: var(--text-muted); font-size: 11px; }

        .badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge.disetujui { background: #E3FCEF; color: #006644; }
        .badge.diajukan, .badge.menunggu { background: #FFF9E6; color: #916A00; }
        .badge.ditolak { background: #FFEBEB; color: #BF2600; }
        .badge.direvisi { background: #EAE6FF; color: #403294; }

        @media screen and (max-width: 1024px) {
            .main-content { margin-left: 0; padding: 100px 20px 40px; }
            .topbar { flex-direction: column; align-items: flex-start; gap: 20px; }
            .admin-profile { width: 100%; justify-content: flex-end; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media screen and (max-width: 600px) {
            .stats-grid { grid-template-columns: 1fr; }
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
                <span>Selamat Datang,</span>
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
        <h4>Antrean Validasi Terkini</h4>
        
        <?php if (!$antrian): ?>
            <p style="text-align:center; color:#999; padding:20px">No recent activity detected.</p>
        <?php else: ?>
            <?php foreach ($antrian as $row): 
                $badge_class = strtolower($row['status']);
                if ($badge_class == 'disetujui') $st_class = 'disetujui';
                elseif ($badge_class == 'ditolak') $st_class = 'ditolak';
                elseif ($badge_class == 'revisi') $st_class = 'direvisi';
                else $st_class = 'diajukan';
            ?>
                <div class="queue-item">
                    <div class="m-avatar"><?= substr($row['nama'], 0, 1) ?></div>
                    <div class="m-info">
                        <b><?= htmlspecialchars($row['nama']) ?></b>
                        <p><?= htmlspecialchars($row['tipe']) ?></p>
                    </div>
                    <div class="m-status">
                        <small><?= time_ago($row['created_at']) ?></small>
                        <span class="badge <?= $st_class ?>"><?= $row['status'] ?: 'Diajukan' ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</div>

<script>
const commonOptions = {
    cutout: '75%',
    plugins: {
        legend: { position: 'bottom', labels: { usePointStyle: true, font: { family: 'Outfit', size: 11 } } },
        tooltip: { callbacks: { label: (context) => ` ${context.label}: ${context.raw}` } }
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