<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';
?>

<style>
.sidebar {
    width:220px;
    background:#222;
    color:#fff;
    padding:20px;
}
.sidebar h2 { font-size:18px; }
.sidebar ul { list-style:none; padding:0; }
.sidebar ul li { margin:15px 0; }
.sidebar ul li a {
    color:#fff;
    text-decoration:none;
}
.sidebar ul li a:hover {
    text-decoration:underline;
}
.menu-title {
    margin-top:20px;
    font-weight:bold;
    color:#aaa;
}
</style>

<div class="sidebar">
    <h2>Mahasiswa</h2>
    <ul>
        <li><a href="<?= base_url('mahasiswa/dashboard.php') ?>">Dashboard</a></li>

        <li class="menu-title">Tugas Akhir</li>
        <li><a href="<?= base_url('mahasiswa/pengajuan/form.php') ?>">Pengajuan TA</a></li>
        <li><a href="<?= base_url('mahasiswa/pengajuan/status.php') ?>">Status & Feedback</a></li>
        <li><a href="<?= base_url('mahasiswa/template.php') ?>">Download Template</a></li>

        <li class="menu-title">Akun</li>
        <li><a href="<?= base_url('logout.php') ?>">Logout</a></li>
    </ul>
</div>

<link rel="stylesheet" href="<?= base_url('style.css') ?>">
