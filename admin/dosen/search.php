<?php
session_start();
require_once "../../config/connection.php";

// cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit("Unauthorized");
}

$keyword = trim($_GET['keyword'] ?? '');

if ($keyword !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM dosen
        WHERE nama LIKE :k
           OR username LIKE :k
           OR nip LIKE :nip_prefix
        ORDER BY id ASC
    ");
    $stmt->execute([
        'k' => "%$keyword%",
        'nip_prefix' => "$keyword%"
    ]);
} else {
    $stmt = $pdo->query("SELECT * FROM dosen ORDER BY id ASC");
}

$dosen_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

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
        <?php if ($dosen_list): ?>
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
                    Data tidak ditemukan.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
