<?php
session_start();
require "../config/connection.php";
require_once '../config/base_url.php';

// ===============================
// CEK ROLE MAHASISWA
// ===============================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}
$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';
// ===============================
// AMBIL SEMUA TEMPLATE
// ===============================
$stmt = $pdo->prepare("
    SELECT *
    FROM template
    WHERE is_visible = 1
    ORDER BY created_at DESC
");
$stmt->execute();
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Template Dokumen</title>
<style>
body {
    font-family: 'Outfit', sans-serif;
    background: #FFF1E5 !important;
    margin: 0;
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
    color:#fff;
}

/* CARD */
.card {
    background:#fff;
    border-radius:18px;
    padding:18px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
    overflow-x: auto;
}

.search-box{
    background:#fff;
    padding:10px 15px;
    border-radius:25px;
    width:360px;
    display:flex;
    box-shadow:0 3px 10px rgba(0,0,0,.08);
    margin-bottom:15px;
}
.search-box input{
    border:none;
    outline:none;
    width:100%
}

/* TABLE STYLE */
#templateTable{width:100%;border-collapse:separate;border-spacing:0;font-size:14px;border-radius:12px;overflow:hidden}
#templateTable thead{background:linear-gradient(90deg,#FF74C7,#FF983D);color:#fff}
#templateTable thead th{background:transparent;color:#fff;padding:14px;text-align:left;border:none}
#templateTable thead th:first-child{border-top-left-radius:12px}
#templateTable thead th:last-child{border-top-right-radius:12px}
#templateTable thead th:nth-child(1){width:30px;text-align:center}
#templateTable thead th:nth-child(2){width:1fr}
#templateTable thead th:nth-child(3){width:150px;text-align:center} 

/* subtle row stripes */
#templateTable tbody tr:nth-child(even){background:#fff7f1}
#templateTable tbody td{padding:14px;vertical-align:middle;}
#templateTable tbody td:first-child{text-align:center}

.detail-cell{display:flex;gap:12px;align-items:center}
.file-icon-box{width:44px;height:44px;border-radius:10px;background:#fff;border:1px solid rgba(255,152,61,0.12);display:flex;align-items:center;justify-content:center;color:#FF74C7}
.template-name{font-weight:700;color:#333}
.template-file{font-size:12px;color:#999}

.badge-cat{display:inline-block;padding:6px 12px;border-radius:999px;border:1px solid rgba(255,152,61,0.5);color:#FF983D;font-weight:800;font-size:12px}

.btn-unduh{font-size:12px;background:transparent;border:1px solid #FF74C7;color:#ff2b8f;padding:6px 12px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;transition:background .22s ease,color .22s ease,transform .12s ease,box-shadow .22s ease}

/* make the icon a fixed-size flex container so it's perfectly centered */
.btn-unduh .material-symbols-rounded{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;font-size:18px;line-height:1;transform:none}

/* center button on small and large screens */
#templateTable td:last-child{display:flex;align-items:center;justify-content:center}

/* hover gradient */
.btn-unduh:hover{background:linear-gradient(90deg,#FF74C7,#FF983D);color:#fff;border-color:transparent;transform:translateY(-3px);box-shadow:0 8px 20px rgba(255,116,199,0.12)}

@media (max-width: 768px){
    #templateTable thead th:nth-child(2){width:auto}
    .search-box{width:100%}
}


</style>
</head>
<body>

<?php include __DIR__ . "/sidebar.php"; ?>

<div class="main-content">
        <div class="topbar">
            <h1>Template Dokumen</h1>

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

    <!-- Live Search Input -->
    <div class="search-box">
        <input type="text" id="search" placeholder="Search...">
    </div>

    <div class="card">
        <table id="templateTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Detail</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$templates): ?>
                    <tr><td colspan="3">Belum ada template tersedia.</td></tr>
                <?php else: $no = 1; foreach($templates as $t): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <div class="detail-cell">
                            <div class="file-icon-box">
                                <span class="material-symbols-rounded">description</span>
                            </div>
                            <div>
                                <div class="template-name"><?= htmlspecialchars($t['nama']) ?></div>
                                <div class="template-file"><?= htmlspecialchars($t['file'] ?: '-') ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($t['file']): 
                            $file_path = '../uploads/templates/' . $t['file'];
                        ?>
                            <a class="btn-unduh" href="<?= $file_path ?>" target="_blank">
                                <span class="material-symbols-rounded">download</span>
                                <span>UNDUH</span>
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
// Live search (name)
document.getElementById('search').addEventListener('keyup', function() {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('#templateTable tbody tr');

    rows.forEach(row => {
        const nameEl = row.querySelector('.template-name');
        const name = nameEl ? nameEl.textContent.toLowerCase() : '';

        if (name.indexOf(query) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

</body>
</html>
