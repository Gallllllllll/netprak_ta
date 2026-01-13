<?php
session_start();
require_once "../../config/connection.php";

// cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// ambil semua data dosen
$stmt = $pdo->prepare("SELECT * FROM dosen ORDER BY id ASC");
$stmt->execute();
$dosen_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRUD Dosen</title>

<style>
h1 { margin-bottom: 15px; }

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}
th, td {
    border: 1px solid #ccc;
    padding: 10px;
}
th { background: #eee; }

a.btn {
    padding: 5px 10px;
    background: #007BFF;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin-right: 5px;
}
a.btn:hover { background: #0056b3; }

a.btn.delete {
    background: #dc3545;
}
a.btn.delete:hover {
    background: #b02a37;
}

#search {
    padding: 6px;
    width: 300px;
    margin-bottom: 10px;
}
</style>
</head>
<body>

<?php require_once __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">
    <h1>Daftar Dosen</h1>

    <!-- Search -->
    <input type="text" id="search" placeholder="Cari dosen (nama / NIP / username)...">

    <a href="add.php" class="btn">Tambah Dosen</a>

    <div id="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dosen_list) > 0): ?>
                    <?php foreach ($dosen_list as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['id']); ?></td>
                        <td><?= htmlspecialchars($d['nama']); ?></td>
                        <td><?= htmlspecialchars($d['nip']); ?></td>
                        <td><?= htmlspecialchars($d['username']); ?></td>
                        <td><?= htmlspecialchars($d['email'] ?? '-'); ?></td>
                        <td>
                            <a href="edit.php?id=<?= $d['id']; ?>" class="btn">Edit</a>
                            <a href="delete.php?id=<?= $d['id']; ?>" 
                               class="btn delete"
                               onclick="return confirm('Yakin ingin hapus dosen ini?')">
                               Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">
                            Data dosen belum tersedia.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('search').addEventListener('keyup', function () {
    let keyword = this.value;

    let xhr = new XMLHttpRequest();
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
