<?php
session_start();
require_once "../../config/connection.php";

// cek login admin
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit("Unauthorized");
}

$keyword = $_GET['keyword'] ?? '';

if($keyword != '') {
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nama LIKE :keyword OR nim LIKE :keyword OR prodi LIKE :keyword ORDER BY id ASC");
    $stmt->execute(['keyword' => "%$keyword%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM mahasiswa ORDER BY id ASC");
}

$mahasiswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

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
        <?php if(count($mahasiswa_list) > 0): ?>
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
        <?php else: ?>
            <tr>
                <td colspan="9" style="text-align:center;">Data tidak ditemukan.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
