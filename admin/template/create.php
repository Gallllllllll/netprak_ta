<?php
session_start();
require "../../config/connection.php";

// cek admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $file = $_FILES['file'] ?? null;

    $filename = null;
    if ($file && $file['tmp_name']) {
        $filename = basename($file['name']); // pakai nama asli file
        move_uploaded_file($file['tmp_name'], "../../uploads/templates/$filename");
    }

    if (!$nama) $error = "Nama template wajib diisi.";
    else {
        $stmt = $pdo->prepare("INSERT INTO template (nama, file) VALUES (?, ?)");
        $stmt->execute([$nama, $filename]);
        header("Location: index.php");
        exit;
    }
}
?>
