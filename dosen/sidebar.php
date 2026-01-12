<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';
?>

<div class="sidebar">
    <h2>Dosen Pembimbing</h2>

    <ul>
        <li>
            <a href="<?= base_url('dosen/dashboard.php') ?>">
                Dashboard
            </a>
        </li>

        <li>
            <a href="<?= base_url('dosen/mahasiswa_bimbingan.php') ?>">
                Mahasiswa Bimbingan
            </a>
        </li>

        <li>
            <a href="<?= base_url('logout.php') ?>">
                Logout
            </a>
        </li>
    </ul>
</div>

<link rel="stylesheet" href="<?= base_url('style.css') ?>">
