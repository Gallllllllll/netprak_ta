<?php
session_start();
require_once "../../config/connection.php";

// cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// ambil semua data admin
$stmt = $pdo->query("SELECT id, nama, username FROM admin ORDER BY id ASC");
$admin_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRUD Admin</title>
<link rel="stylesheet" href="/coba/style.css">
<style>
body { font-family: Arial, sans-serif; margin:0; background:#f9f9f9; }
.content { padding: 20px; }
table { width:100%; border-collapse: collapse; background:#fff; }
th, td { border:1px solid #ccc; padding:10px; }
th { background:#eee; }
a.btn { padding:6px 10px; background:#007bff; color:#fff; text-decoration:none; border-radius:4px; }
a.btn.delete { background:#dc3545; }
a.btn:hover { opacity:0.9; }
#search { padding:6px; width:300px; margin-bottom:10px; }
</style>
</head>
<body>

<?php require_once __DIR__ . '/../sidebar.php'; ?>

<div class="content">
    <h1>Daftar Admin</h1>

    <input type="text" id="search" placeholder="Cari admin...">

    <br><br>
    <a href="add.php" class="btn">Tambah Admin</a>

    <div id="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admin_list as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['id']); ?></td>
                    <td><?= htmlspecialchars($a['nama']); ?></td>
                    <td><?= htmlspecialchars($a['username']); ?></td>
                    <td>
                        <a href="edit.php?id=<?= $a['id']; ?>" class="btn">Edit</a>
                        <a href="delete.php?id=<?= $a['id']; ?>" class="btn delete"
                           onclick="return confirm('Yakin ingin hapus admin ini?')">
                           Hapus
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('search').addEventListener('keyup', function () {
    const keyword = this.value;
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'search.php?keyword=' + encodeURIComponent(keyword), true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById('table-container').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
});
</script>

</body>
</html>
