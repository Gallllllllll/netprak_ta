<?php
session_start();
require "../../config/connection.php";

// cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// ambil semua template
$stmt = $pdo->query("SELECT * FROM template ORDER BY created_at DESC");
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manajemen Template</title>
<link rel="stylesheet" href="../../style.css">
<style>
.card { background:#fff; padding:20px; border-radius:8px; margin-bottom:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
th { background:#eee; cursor:pointer; }
th.sort-asc::after { content: " ▲"; }
th.sort-desc::after { content: " ▼"; }
a { color:#007bff; text-decoration:none; margin-right:10px; }
a:hover { text-decoration:underline; }
.btn-add { background:#28a745; color:#fff; padding:6px 14px; border-radius:6px; text-decoration:none; display:inline-block; margin-bottom:10px; }
</style>
</head>
<body>

<?php include "../sidebar.php"; ?>

<div class="main-content">
<h1>Manajemen Template</h1>

<a href="create.php" class="btn-add">+ Tambah Template</a>

<div class="card">
    <table id="templateTable">
        <thead>
            <tr>
                <th>No</th>
                <th data-column="nama">Nama Template</th>
                <th data-column="file">File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$templates): ?>
                <tr><td colspan="4">Belum ada template.</td></tr>
            <?php else: $no=1; foreach($templates as $t): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td class="nama"><?= htmlspecialchars($t['nama']) ?></td>
                <td class="file">
                    <?php if ($t['file']): 
                        $file_path = '../../uploads/templates/' . $t['file'];
                    ?>
                        <a href="<?= $file_path ?>" target="_blank">Download <?= htmlspecialchars($t['file']) ?></a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit.php?id=<?= $t['id'] ?>">Edit</a>
                    <a href="delete.php?id=<?= $t['id'] ?>" onclick="return confirm('Yakin hapus template ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</div>

<script>
// Sorting table
document.querySelectorAll('#templateTable th[data-column]').forEach(header => {
    header.addEventListener('click', () => {
        const table = header.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnClass = header.dataset.column;
        const currentSort = header.classList.contains('sort-asc') ? 'asc' : header.classList.contains('sort-desc') ? 'desc' : null;

        // reset all headers
        table.querySelectorAll('th').forEach(th => th.classList.remove('sort-asc','sort-desc'));

        const newSort = currentSort === 'asc' ? 'desc' : 'asc';
        header.classList.add(newSort === 'asc' ? 'sort-asc' : 'sort-desc');

        rows.sort((a,b) => {
            const aText = a.querySelector('.' + columnClass)?.textContent.trim().toLowerCase() || '';
            const bText = b.querySelector('.' + columnClass)?.textContent.trim().toLowerCase() || '';
            if(aText < bText) return newSort === 'asc' ? -1 : 1;
            if(aText > bText) return newSort === 'asc' ? 1 : -1;
            return 0;
        });

        rows.forEach(row => tbody.appendChild(row));
    });
});
</script>

</body>
</html>
