<?php
session_start();
require "../../config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/ta_netprak/config/base_url.php';

/* ===============================
   CEK LOGIN ADMIN
================================ */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: " . base_url('login.php'));
    exit;
}

/* ===============================
   VALIDASI ID SEMPRO
================================ */
$sempro_id = $_GET['pengajuan_id'] ?? null;
if (!$sempro_id) die("ID tidak valid");

/* ===============================
   AMBIL DATA SEMPRO + TA
================================ */
$stmt = $pdo->prepare("
    SELECT 
        s.tanggal_sempro,
        s.pengajuan_ta_id,
        m.nama AS nama_mahasiswa,
        m.nim,
        p.judul_ta
    FROM pengajuan_sempro s
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN pengajuan_ta p ON s.pengajuan_ta_id = p.id
    WHERE s.id = ?
");
$stmt->execute([$sempro_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Data sempro tidak ditemukan");

$pengajuan_ta_id = $data['pengajuan_ta_id'];

/* ===============================
   CEK AKSES
================================ */
$akses_ditutup = empty($data['tanggal_sempro']);

/* ===============================
   AMBIL DOSBING (PAKAI ID TA ✅)
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
    ORDER BY 
        CASE db.role
            WHEN 'dosbing_1' THEN 1
            WHEN 'dosbing_2' THEN 2
        END
");
$stmt->execute([$pengajuan_ta_id]);
$dosbing = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$dosbing) die("Dosen pembimbing belum ditentukan");

/* ===============================
   NILAI LAMA (BERDASARKAN SEMPRO)
================================ */
$nilaiLama = [];
$stmt = $pdo->prepare("
    SELECT peran, nilai
    FROM nilai_sempro
    WHERE pengajuan_id = ?
");
$stmt->execute([$sempro_id]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $n) {
    $nilaiLama[$n['peran']] = $n['nilai'];
}

/* ===============================
   SIMPAN NILAI
================================ */
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$akses_ditutup) {
    $pdo->beginTransaction();
    try {
        foreach ($_POST['nilai'] as $peran => $item) {
            $nilai = floatval($item['nilai']);
            $dosen_id = intval($item['dosen_id']);

            if ($nilai < 0 || $nilai > 100) {
                throw new Exception("Nilai harus 0–100");
            }

            $stmt = $pdo->prepare("
                INSERT INTO nilai_sempro (pengajuan_id, dosen_id, peran, nilai)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
            ");
            $stmt->execute([$sempro_id, $dosen_id, $peran, $nilai]);
        }

        $pdo->commit();
        header("Location: index.php?success=1");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai SEMPRO</title>
    <link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        :root {
            --primary-gradient: linear-gradient(90deg, #ff5f9e, #ff9f43);
            --bg-beige: #FDF2E9;
            --white: #FFFFFF;
            --text-dark: #2D3436;
            --text-muted: #636E72;
            --border-color: #f1dcdc;
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

        .main-content {
            margin-left: 280px;
            padding: 32px;
            min-height: 100vh;
            transition: all 0.3s ease;
            width: calc(100vw - 280px);
            max-width: calc(100vw - 280px);
            box-sizing: border-box;
            overflow-x: hidden;
        }


        /* HEADER */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            background: var(--primary-gradient) !important;
            border-radius: 20px;
            padding: 24px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(255, 95, 158, 0.15);
            gap: 20px;
        }

        .header-text {
            flex: 1;
        }

        .dashboard-header h1 {
            margin: 0;
            color: #fff !important;
            -webkit-text-fill-color: initial !important;
            background: none !important;
            -webkit-background-clip: initial !important;
            font-size: 24px;
            font-weight: 700;
        }

        .dashboard-header p {
            margin: 8px 0 0;
            font-size: 14px;
            color: #fff !important;
            opacity: 0.9;
            font-weight: 400;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
            margin-top: 5px;
        }

        .admin-profile .text {
            text-align: right;
            max-width: 90px;
            line-height: 1.2;
            color: #fff;
        }

        .admin-profile small { 
            font-size: 11px;
            display: block;
            opacity: 0.8;
        }

        .admin-profile b { 
            font-size: 13px; 
            display: block; 
        }

        .avatar {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* CARD */
        .form-card {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            max-width: 850px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid var(--border-color);
        }

        .info-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #eee;
        }

        .info-header b { color: #ff5f9e; }

        /* FORM FIELD */
        .input-group {
            margin-bottom: 25px;
        }

        .input-group label {
            display: block;
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 10px;
            color: #444;
        }

        .input-control {
            width: 100%;
            padding: 14px 20px;
            border-radius: 14px;
            border: 1.5px solid #ff9f43;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            color: #2D3436;
        }

        .input-control:focus {
            outline: none;
            border-color: #ff5f9e;
            box-shadow: 0 0 0 4px rgba(255, 95, 158, 0.1);
        }

        .input-control::placeholder {
            color: #ccc;
        }

        /* BUTTON */
        .btn-submit {
            background: var(--primary-gradient);
            color: #fff;
            border: none;
            padding: 14px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(255, 95, 158, 0.2);
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 95, 158, 0.3);
            opacity: 0.95;
        }

        /* MESSAGES */
        .error-msg {
            background: #fff5f5;
            color: #e53e3e;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #e53e3e;
            font-size: 14px;
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 70px !important;
                padding: 20px !important;
                width: calc(100vw - 70px) !important;
                max-width: calc(100vw - 70px) !important;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 60px !important;
                padding: 15px !important;
                width: calc(100vw - 60px) !important;
                max-width: calc(100vw - 60px) !important;
            }

            .dashboard-header {
                padding: 15px;
                gap: 10px;
            }

            .dashboard-header h1 {
                font-size: 18px;
            }

            .admin-profile {
                gap: 10px;
            }

            .admin-profile .text {
                max-width: 80px;
            }

            .avatar {
                width: 36px;
                height: 36px;
            }

            .form-card { padding: 25px; }
        }
    </style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    <div class="dashboard-header">
        <div class="header-text">
            <h1>Input Nilai Seminar Proposal</h1>
            <p><?= htmlspecialchars($data['nama_mahasiswa']) ?> (<?= $data['nim'] ?>)</p>
        </div>
        <div class="admin-profile">
            <div class="text">
                <small>Selamat Datang,</small>
                <b><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Admin') ?></b>
            </div>
            <div class="avatar">
                <span class="material-symbols-rounded" style="color:#fff">person</span>
            </div>
        </div>
    </div>

    <div class="form-card">
        
        <div class="info-header">
            Mahasiswa: <b><?= htmlspecialchars($data['nama_mahasiswa']) ?></b><br>
            NIM: <b><?= htmlspecialchars($data['nim']) ?></b><br>   
            Judul: <i><?= htmlspecialchars($data['judul_ta']) ?></i>
        </div>

        <?php if ($akses_ditutup): ?>
            <div class="error-msg">
                ⛔ Jadwal SEMPRO belum ditentukan. Input nilai hanya dapat dilakukan setelah jadwal ditentukan.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" <?= ($akses_ditutup) ? 'style="display:none"' : '' ?>>
            <?php foreach ($dosbing as $d): ?>
                <div class="input-group">
                    <label><?= strtoupper(str_replace('_',' ',$d['peran'])) ?> - <?= htmlspecialchars($d['nama']) ?></label>
                    <input type="hidden" name="nilai[<?= $d['peran'] ?>][dosen_id]" value="<?= $d['dosen_id'] ?>">
                    <input type="number"
                           name="nilai[<?= $d['peran'] ?>][nilai]"
                           min="0" max="100"
                           step="0.01"
                           value="<?= $nilaiLama[$d['peran']] ?? '' ?>"
                           class="input-control"
                           placeholder="input nilai disini"
                           required>
                </div>
            <?php endforeach; ?>

            <button class="btn-submit">Simpan Nilai</button>
        </form>

    </div>
</div>

<script>
document.querySelectorAll('input[type="number"]').forEach(i=>{
    i.addEventListener('input',()=>{
        if(i.value>100) i.value=100;
        if(i.value<0) i.value=0;
    });
});
</script>

</body>
</html>