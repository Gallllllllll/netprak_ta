<?php
session_start();
require "../../config/connection.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id=?");
    $stmt->execute([$id]);
}

header("Location: index.php");
exit;
