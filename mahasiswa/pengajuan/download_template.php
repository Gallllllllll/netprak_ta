<?php
session_start();
require "../../config/base_url.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    http_response_code(403);
    exit('Akses ditolak');
}

$file = $_GET['file'] ?? '';
$file = basename($file); // cegah directory traversal

$path = __DIR__ . '/../../uploads/templates/' . $file;

if (!file_exists($path)) {
    http_response_code(404);
    exit('File tidak ditemukan');
}

// Paksa download
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($path);
exit;