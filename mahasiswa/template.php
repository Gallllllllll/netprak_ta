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
<title>Template untuk Mahasiswa</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f6f8; padding:20px; }
.card { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
th { background:#eee; }
a.download-btn { display:inline-block; background:#28a745; color:#fff; padding:6px 12px; border-radius:6px; text-decoration:none; }
a.download-btn:hover { background:#218838; }
.search-input { width: 100%; padding: 8px; border-radius:6px; border:1px solid #ccc; margin-bottom:10px; font-size:14px; }
</style>
</head>
<body>

<?php include __DIR__ . "/sidebar.php"; ?>

<div class="main-content">
    <h1>Template Dokumen</h1>

    <!-- Live Search Input -->
    <input type="text" id="search" class="search-input" placeholder="Cari template...">

    <div class="card">
        <table id="templateTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Template</th>
                    <th>File</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$templates): ?>
                    <tr><td colspan="4">Belum ada template tersedia.</td></tr>
                <?php else: $no = 1; foreach($templates as $t): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td class="template-name"><?= htmlspecialchars($t['nama']) ?></td>
                    <td><?= $t['file'] ? htmlspecialchars($t['file']) : '-' ?></td>
                    <td>
                        <?php if ($t['file']): 
                            $file_path = '../uploads/templates/' . $t['file'];
                        ?>
                            <a class="download-btn" href="<?= $file_path ?>" target="_blank">Download</a>
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
// Live search
document.getElementById('search').addEventListener('keyup', function() {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('#templateTable tbody tr');

    rows.forEach(row => {
        const name = row.querySelector('.template-name').textContent.toLowerCase();
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
