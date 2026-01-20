<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/connection.php';

$currentUri = $_SERVER['REQUEST_URI'];
$currentPage = basename($_SERVER['PHP_SELF']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

// Fungsi menu aktif
function isActive($path)
{
    return strpos($_SERVER['REQUEST_URI'], $path) !== false;
}

function isAnyActive(array $paths)
{
    foreach ($paths as $path) {
        if (isActive($path)) return true;
    }
    return false;
}
?>

<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="assets\img\Logo.webp">
</head>

<style>
/* ==============================
   ROOT & GLOBAL
============================== */
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
}

body {
    background-color: #FFF1E5;
    font-family: 'Inter', sans-serif !important;
    margin: 0;
}

/* ==============================
   SIDEBAR
============================== */
.sidebar {
    width: 250px;
    height: 100vh;
    background: #ffffff;
    position: fixed;
    left: 0;
    top: 0;
    padding: 20px 16px;
    z-index: 1000;
}

/* HEADER */
.sidebar-header {
    text-align: center;
    margin-bottom: 20px;
}

/* LOGO */
.logo img {
    width: 225px;
    margin: 10px 0;
}

/* ==============================
   MENU UTAMA
============================== */
.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    margin-bottom: 6px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 9px 12px;
    border-radius: 12px;
    font-size: 14px;
    color: #5f5f5f;
    text-decoration: none;
    transition: background .25s ease, color .25s ease;
    cursor: pointer;
}

/* ICON */
.sidebar-menu .material-symbols-rounded {
    font-size: 22px;
    min-width: 22px;
    color: #5f5f5f;
    transition: color .25s ease;
    font-variation-settings:
        'FILL' 1,
        'wght' 500,
        'GRAD' 0,
        'opsz' 24;
}

/* ==============================
   HOVER & ACTIVE (FULL BLOCK)
============================== */
.sidebar-menu a:hover {
    background: var(--gradient);
    color: #ffffff;
}

.sidebar-menu a:hover .material-symbols-rounded {
    color: #ffffff;
}

.sidebar-menu a.active {
    background: var(--gradient);
    color: #ffffff;
    font-weight: 600;
}

.sidebar-menu a.active .material-symbols-rounded {
    color: #ffffff;
}

/* ==============================
   SUBMENU
============================== */
.has-submenu > .submenu {
    display: none;
    padding-left: 40px;
    margin-top: 6px;
}

.has-submenu.open > .submenu {
    display: block;
}

/* SUBMENU ITEM */
.submenu li {
    margin-bottom: 4px;
    list-style: none;
}

.submenu a {
    padding: 8px 12px;
    font-size: 13px;
    border-radius: 8px;
    color: #777;
}

.submenu a:hover {
    background: rgba(255, 152, 61, 0.15);
    color: var(--orange);
}

.submenu a.active {
    color: #ffffff;
    font-weight: 600;
    background: var(--gradient);
}

.submenu a.active .material-symbols-rounded {
    color: #ffffff !important;
}

.submenu a:hover .material-symbols-rounded {
    color: var(--orange);
}

/* ==============================
   SUBMENU ARROW
============================== */
.submenu-arrow {
    margin-left: auto;
    transition: transform .25s ease;
}

.has-submenu.open .submenu-arrow {
    transform: rotate(180deg);
}

/* ==============================
   LOGOUT
============================== */
.sidebar-menu a.logout {
    color: #777;
}

.sidebar a.logout .material-symbols-rounded {
    color: #777;
}

.sidebar-menu a.logout:hover {
    background: rgba(255, 107, 107, 0.15);
    color: #ff3b3b;
}

.sidebar-menu a.logout:hover .material-symbols-rounded {
    color: #ff3b3b;
}

/* ==============================
   MAIN CONTENT
============================== */
.main-content {
    margin-left: 270px;
    padding: 28px 32px;
    margin-bottom: 60px;
}

/* HEADER DASHBOARD */
.dashboard-header {
    background: #ffffff;
    border-radius: 16px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #f1dcdc;
}

.dashboard-header h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #2f3e55;
}

.dashboard-header p {
    margin-top: 6px;
    font-size: 14px;
    color: #6b7280;
}
</style>

<div class="sidebar">
    <!-- Logo Politeknik Nest -->
    <div class="sidebar-header">
        <div class="logo">
            <img src="<?= base_url('assets/img/logo2.png') ?>" alt="Logo">
        </div>
    </div>

    <ul class="sidebar-menu">
        <!-- DASHBOARD -->
        <li>
            <a href="<?= base_url('mahasiswa/dashboard.php') ?>"
               class="<?= isActive('/mahasiswa/dashboard.php') ? 'active' : '' ?>">
                <span class="material-symbols-rounded">dashboard</span>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- ALUR PANDUAN -->
        <li>
            <a href="<?= base_url('mahasiswa/alurpanduan.php') ?>"
               class="<?= isActive('/mahasiswa/alurpanduan.php') ? 'active' : '' ?>">
                <span class="material-symbols-rounded">book_5</span>
                <span>Alur & Panduan</span>
            </a>
        </li>

        <!-- TUGAS AKHIR -->
        <li class="has-submenu <?= isAnyActive([
            '/mahasiswa/pengajuan/'
        ]) ? 'open' : '' ?>">

            <a class="submenu-toggle">
                <span class="material-symbols-rounded">assignment</span>
                <span>Pengajuan Tugas Akhir</span>
                <span class="submenu-arrow material-symbols-rounded">expand_more</span>
            </a>

            <ul class="submenu">
                <li>
                    <a href="<?= base_url('mahasiswa/pengajuan/form.php') ?>"
                    class="<?= isActive('/mahasiswa/pengajuan/form.php') ? 'active' : '' ?>">
                        <span class="material-symbols-rounded">contract_edit</span>
                        <span>Pengajuan TA</span>
                    </a>
                </li>

                <li>
                    <a href="<?= base_url('mahasiswa/pengajuan/status.php') ?>"
                       class="<?= isActive('/mahasiswa/pengajuan/status.php') ? 'active' : '' ?>">
                       <span class="material-symbols-rounded">assignment_turned_in</span> 
                       <span>Status & Feedback</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- SEMINAR PROPOSAL -->
        <li class="has-submenu <?= isAnyActive([
            '/mahasiswa/sempro/'
        ]) ? 'open' : '' ?>">

            <a class="submenu-toggle">
                <span class="material-symbols-rounded">co_present</span>
                <span>Pengajuan Seminar Proposal</span>
                <span class="submenu-arrow material-symbols-rounded">expand_more</span>
            </a>

            <ul class="submenu">
                <li>
                    <a href="<?= base_url('mahasiswa/sempro/form.php') ?>"
                    class="<?= isActive('/mahasiswa/sempro/form.php') ? 'active' : '' ?>">
                        <span class="material-symbols-rounded">contract_edit</span>
                        <span>Pengajuan Sempro</span>
                    </a>
                </li>

                <li>
                    <a href="<?= base_url('mahasiswa/sempro/status.php') ?>"
                       class="<?= isActive('/mahasiswa/sempro/status.php') ? 'active' : '' ?>">
                       <span class="material-symbols-rounded">assignment_turned_in</span> 
                       <span>Status & Feedback</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- SEMINAR HASIL -->
        <li class="has-submenu <?= isAnyActive([
            '/mahasiswa/semhas/'
        ]) ? 'open' : '' ?>">

            <a class="submenu-toggle">
                <span class="material-symbols-rounded">task</span>
                <span>Pengajuan Seminar Hasil</span>
                <span class="submenu-arrow material-symbols-rounded">expand_more</span>
            </a>

            <ul class="submenu">
                <li>
                    <a href="<?= base_url('mahasiswa/semhas/form.php') ?>"
                    class="<?= isActive('/mahasiswa/semhas/form.php') ? 'active' : '' ?>">
                        <span class="material-symbols-rounded">contract_edit</span>
                        <span>Pengajuan Semhas</span>
                    </a>
                </li>

                <li>
                    <a href="<?= base_url('mahasiswa/semhas/status.php') ?>"
                       class="<?= isActive('/mahasiswa/semhas/status.php') ? 'active' : '' ?>">
                       <span class="material-symbols-rounded">assignment_turned_in</span> 
                       <span>Status & Feedback</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- CEK NILAI -->
        <li>
            <a href="<?= base_url('mahasiswa/semhas/cek_nilai.php') ?>"
               class="<?= isActive('/mahasiswa/semhas/cek_nilai.php') ? 'active' : '' ?>">
                <span class="material-symbols-rounded">fact_check</span>
                <span>Cek Nilai</span>
            </a>
        </li>

        <!-- TEMPLATE DOKUMEN -->
        <li>
            <a href="<?= base_url('mahasiswa/template.php') ?>"
               class="<?= isActive('/mahasiswa/template.php') ? 'active' : '' ?>">
                <span class="material-symbols-rounded">description</span>
                <span>Dokumen</span>
            </a>
        </li>

        <!-- LOGOUT -->
        <li>
            <a href="<?= base_url('logout.php') ?>" class="logout">
                <span class="material-symbols-rounded">logout</span>
                <span>Log Out</span>
            </a>
        </li>
    </ul>
</div>

<div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/coba/mahasiswa/footer.php'; ?>
</div>

<script>
document.querySelectorAll('.submenu-toggle').forEach(toggle => {
    toggle.addEventListener('click', function () {
        const parent = this.closest('.has-submenu');

        // Tutup submenu lain (optional, accordion)
        //document.querySelectorAll('.has-submenu').forEach(item => {
        //    if (item !== parent) item.classList.remove('open');
        //});

        parent.classList.toggle('open');
    });
});
</script>
