<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';
?>

<div class="sidebar">
    <ul>
        <li><a href="<?= base_url('admin/dashboard.php') ?>">Dashboard</a></li>
        <li><a href="<?= base_url('admin/mahasiswa/index.php') ?>">CRUD Mahasiswa</a></li>
        <li><a href="<?= base_url('admin/dosen/index.php') ?>">CRUD Dosen</a></li>
        <li><a href="<?= base_url('admin/admin/index.php') ?>">CRUD Admin</a></li>
        <li><a href="<?= base_url('admin/pengajuan/index.php') ?>">Pengajuan TA</a></li>
        <li><a href="<?= base_url('admin/sempro/index.php') ?>">Pengajuan Sempro</a></li>
        <li><a href="<?= base_url('admin/template/index.php') ?>">Template Dokumen</a></li>
        <li><a href="<?= base_url('logout.php') ?>">Logout</a></li>
    </ul>
</div>

<link rel="stylesheet" href="<?= base_url('style.css') ?>">
