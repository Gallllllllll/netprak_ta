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
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-beige);
            margin: 0;
            color: var(--text-dark);
        }

        .main-content {
            margin-left: 280px;
            padding: 32px;
            min-height: 100vh;
        }

        /* HEADER */
        .dashboard-header {
            background: var(--primary-gradient) !important;
            border-radius: 20px;
            padding: 24px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(255, 95, 158, 0.15);
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
            color: #666;
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

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px; }
            .form-card { padding: 25px; }
        }
    </style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
    
    <div class="dashboard-header">
        <h1>Input Nilai</h1>
        <p>Tempat Penguji Menginput nilai Seminar Proposal</p>
    </div>

    <div class="form-card">
        
        <div class="info-header">
            Mahasiswa: <b><?= htmlspecialchars($data['nama_mahasiswa']) ?></b><br>
            NIM: <b><?= htmlspecialchars($data['nim']) ?></b><br>   
            Judul: <i><?= htmlspecialchars($data['judul_ta']) ?></i>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php foreach ($dosbing as $d): ?>
                <div class="input-group">
                    <label>
                        <?= strtoupper(str_replace('_',' ', $d['peran'])) ?> 
                        - <?= htmlspecialchars($d['nama']) ?>
                    </label>

                    <input type="hidden"
                           name="nilai[<?= $d['peran'] ?>][dosen_id]"
                           value="<?= $d['dosen_id'] ?>">

                    <input type="number"
                        class="input-control"
                        name="nilai[<?= $d['peran'] ?>][nilai]"
                        min="0"
                        max="100"
                        step="0.01"
                        placeholder="input nilai disini"
                        required
                        value="<?= $nilaiLama[$d['peran']] ?? '' ?>">
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn-submit">Simpan Nilai</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('input[type="number"]').forEach(el=>{
    el.addEventListener('input',()=>{
        if(el.value > 100) el.value = 100;
        if(el.value < 0) el.value = 0;
    });
});
</script>

</body>
</html>
