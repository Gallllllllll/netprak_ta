<?php
session_start();
require "../../config/connection.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("ID template tidak diberikan.");

$stmt = $pdo->prepare("DELETE FROM template WHERE id=?");
$stmt->execute([$id]);

header("Location: index.php");
exit;
