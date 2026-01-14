<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("ID template tidak diberikan.");

$stmt = $pdo->prepare("SELECT * FROM template WHERE id=?");
$stmt->execute([$id]);
$template = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$template) die("Template tidak ditemukan.");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $file = $_FILES['file'] ?? null;

    $filename = $template['file']; // default tetap file lama
    if ($file && $file['tmp_name']) {
        // pakai nama asli file
        $filename = basename($file['name']);
        move_uploaded_file($file['tmp_name'], "../../uploads/templates/$filename");
    }

    if (!$nama) $error = "Nama template wajib diisi.";
    else {
        $stmt = $pdo->prepare("UPDATE template SET nama=?, file=? WHERE id=?");
        $stmt->execute([$nama, $filename, $id]);
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Template</title>
<link rel="stylesheet" href="../../style.css">
</head>
<body>
<?php include "../sidebar.php"; ?>
<div class="main-content">
<h1>Edit Template</h1>

<?php if ($error): ?>
<div style="color:red;"><?= $error ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label>Nama Template</label>
    <input type="text" name="nama" value="<?= htmlspecialchars($template['nama']) ?>" required>

    <label>File (opsional)</label>
    <?php if ($template['file']): ?>
        <p>File saat ini: <?= htmlspecialchars($template['file']) ?></p>
    <?php endif; ?>
    <input type="file" name="file">

    <button type="submit">Update</button>
</form>
</div>
</body>
</html>
