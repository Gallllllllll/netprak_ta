<?php
session_start();
require_once "../../config/connection.php";

// cek login admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// ambil semua data mahasiswa
$stmt = $pdo->query("SELECT * FROM mahasiswa ORDER BY id ASC");
$mahasiswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRUD Mahasiswa</title>
<style>
h1 { margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; background: #fff; }
th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
th { background: #eee; }
a.btn { padding: 5px 10px; background: #007BFF; color: white; text-decoration: none; border-radius: 4px; margin-right:5px; }
a.btn:hover { background: #0056b3; }
a.btn.delete { background: #dc3545; }
a.btn.delete:hover { background: #b02a37; }
#search { padding:5px; width:300px; margin-bottom:10px; }
</style>
</head>
<body>

<?php require_once __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">
    <h1>Daftar Mahasiswa</h1>

    <!-- Search Bar -->
    <input type="text" id="search" placeholder="Cari mahasiswa...">

    <a href="add.php" class="btn">Tambah Mahasiswa</a>

    <div id="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Prodi</th>
                    <th>Kelas</th>
                    <th>Nomor Telepon</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($mahasiswa_list as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['id']); ?></td>
                    <td><?= htmlspecialchars($m['nama']); ?></td>
                    <td><?= htmlspecialchars($m['nim']); ?></td>
                    <td><?= htmlspecialchars($m['prodi']); ?></td>
                    <td><?= htmlspecialchars($m['kelas']); ?></td>
                    <td><?= htmlspecialchars($m['nomor_telepon']); ?></td>
                    <td><?= htmlspecialchars($m['email'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($m['username']); ?></td>
                    <td>
                        <a href="edit.php?id=<?= $m['id']; ?>" class="btn">Edit</a>
                        <a href="delete.php?id=<?= $m['id']; ?>" class="btn delete" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('search').addEventListener('keyup', function() {
    let keyword = this.value;

    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'search.php?keyword=' + encodeURIComponent(keyword), true);
    xhr.onload = function() {
        if(xhr.status === 200) {
            document.getElementById('table-container').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
});
</script>

</body>
</html>
