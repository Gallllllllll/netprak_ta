<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) die("ID tidak valid");

// toggle is_visible
$stmt = $pdo->prepare("
    UPDATE template
    SET is_visible = IF(is_visible = 1, 0, 1)
    WHERE id = ?
");
$stmt->execute([$id]);

header("Location: index.php");
exit;
