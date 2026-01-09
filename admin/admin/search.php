<?php
session_start();
require_once "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit;
}

$keyword = $_GET['keyword'] ?? '';
$keyword = "%$keyword%";

$stmt = $pdo->prepare("
    SELECT id, nama, username 
    FROM admin 
    WHERE nama LIKE ? OR username LIKE ?
    ORDER BY id ASC
");
$stmt->execute([$keyword, $keyword]);
$admin_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

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
