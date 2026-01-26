<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/connection.php';

$currentUri = $_SERVER['REQUEST_URI'];
$currentPage = basename($_SERVER['PHP_SELF']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/coba/config/base_url.php';

// Fungsi menu aktif
function isActive(...$paths)
{
    foreach ($paths as $path) {
        if (strpos($_SERVER['REQUEST_URI'], $path) !== false) {
            return true;
        }
    }
    return false;
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="assets\img\Logo.webp">
</head>

<style>
/* ==============================
   ROOT & GLOBAL
============================== */
:root {
    --primary: #FF6B9D;
    --secondary: #FF8E3C;
    --gradient: linear-gradient(135deg, #FF6B9D 0%, #FF8E3C 100%);
    --gradient-soft: linear-gradient(135deg, rgba(255, 107, 157, 0.1) 0%, rgba(255, 142, 60, 0.1) 100%);
    --sidebar-bg: #FFFFFF;
    --text-primary: #1F2937;
    --text-secondary: #6B7280;
    --text-muted: #9CA3AF;
    --hover-bg: #F9FAFB;
    --border-color: #E5E7EB;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

body {
    background-color: #F3F4F6;
    font-family: 'Inter', sans-serif !important;
    margin: 0;
}

/* ==============================
   SIDEBAR
============================== */
.sidebar {
    width: 280px;
    height: 100vh;
    background: var(--sidebar-bg);
    position: fixed;
    left: 0;
    top: 0;
    padding: 0;
    z-index: 1000;
    box-shadow: var(--shadow-lg);
    display: flex;
    flex-direction: column;
}

/* ==============================
   HEADER WITH GRADIENT
============================== */
.sidebar-header {
    background: var(--gradient);
    padding: 28px 24px;
    position: relative;
    overflow: hidden;
}

.sidebar-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.sidebar-header::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -20%;
    width: 150px;
    height: 150px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
}

/* LOGO */
.logo {
    position: relative;
    z-index: 1;
    text-align: center;
}

.logo img {
    width: 200px;
    filter: drop-shadow(0 2px 8px rgba(255, 255, 255, 0.4)) 
            drop-shadow(0 4px 16px rgba(255, 255, 255, 0.3))
            drop-shadow(0 0 20px rgba(255, 255, 255, 0.2));
    transition: transform 0.3s ease, filter 0.3s ease;
}

.logo img:hover {
    transform: scale(1.05);
    filter: drop-shadow(0 4px 12px rgba(255, 255, 255, 0.6)) 
            drop-shadow(0 6px 20px rgba(255, 255, 255, 0.4))
            drop-shadow(0 0 30px rgba(255, 255, 255, 0.3));
}

/* USER INFO (Optional - bisa ditambahkan) */
.user-info {
    position: relative;
    z-index: 1;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.user-avatar .material-symbols-rounded {
    color: white;
    font-size: 28px;
}

.user-name {
    color: white;
    font-size: 14px;
    font-weight: 600;
    margin: 0;
}

.user-role {
    color: rgba(255, 255, 255, 0.8);
    font-size: 12px;
    margin: 2px 0 0;
}

/* ==============================
   MENU CONTAINER
============================== */
.sidebar-menu-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px 16px;
}

.sidebar-menu-container::-webkit-scrollbar {
    width: 6px;
}

.sidebar-menu-container::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-menu-container::-webkit-scrollbar-thumb {
    background: #E5E7EB;
    border-radius: 10px;
}

.sidebar-menu-container::-webkit-scrollbar-thumb:hover {
    background: #D1D5DB;
}

/* ==============================
   MENU SECTION
============================== */
.menu-section {
    margin-bottom: 24px;
}

.menu-section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    padding: 0 12px 8px;
    margin: 0 0 8px;
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
    margin-bottom: 4px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary);
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
}

/* ICON */
.sidebar-menu .material-symbols-rounded {
    font-size: 22px;
    min-width: 22px;
    color: var(--text-secondary);
    transition: all 0.2s ease;
    font-variation-settings:
        'FILL' 0,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24;
}

/* ==============================
   HOVER STATES
============================== */
.sidebar-menu a:hover {
    background: var(--gradient-soft);
    color: var(--primary);
    transform: translateX(4px);
}

.sidebar-menu a:hover .material-symbols-rounded {
    color: var(--primary);
    font-variation-settings:
        'FILL' 1,
        'wght' 500,
        'GRAD' 0,
        'opsz' 24;
}

/* ==============================
   ACTIVE STATE
============================== */
.sidebar-menu a.active {
    background: var(--gradient);
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3);
}

.sidebar-menu a.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 60%;
    background: white;
    border-radius: 0 4px 4px 0;
}

.sidebar-menu a.active .material-symbols-rounded {
    color: #ffffff;
    font-variation-settings:
        'FILL' 1,
        'wght' 600,
        'GRAD' 0,
        'opsz' 24;
}

/* ==============================
   SUBMENU
============================== */
.has-submenu > .submenu {
    display: none;
    padding-left: 44px;
    margin-top: 4px;
    margin-bottom: 8px;
}

.has-submenu.open > .submenu {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* SUBMENU ITEM */
.submenu li {
    margin-bottom: 2px;
    list-style: none;
}

.submenu a {
    padding: 9px 12px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    color: var(--text-secondary);
    position: relative;
}

.submenu a::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    background: var(--border-color);
    border-radius: 50%;
    transition: all 0.2s ease;
}

.submenu a:hover::before {
    background: var(--primary);
    transform: translateY(-50%) scale(1.3);
}

.submenu a:hover {
    background: var(--gradient-soft);
    color: var(--primary);
    transform: translateX(4px);
}

.submenu a.active {
    color: #ffffff;
    font-weight: 600;
    background: var(--gradient);
    box-shadow: 0 2px 8px rgba(255, 107, 157, 0.25);
}

.submenu a.active::before {
    background: white;
}

.submenu a.active .material-symbols-rounded {
    color: #ffffff !important;
}

.submenu a:hover .material-symbols-rounded {
    color: var(--primary);
}

/* ==============================
   SUBMENU ARROW
============================== */
.submenu-arrow {
    margin-left: auto;
    transition: transform 0.3s ease;
    font-size: 20px !important;
}

.has-submenu.open .submenu-arrow {
    transform: rotate(180deg);
}

/* ==============================
   LOGOUT BUTTON
============================== */
.sidebar-footer {
    padding: 16px;
    border-top: 1px solid var(--border-color);
    background: linear-gradient(to bottom, transparent, rgba(249, 250, 251, 0.5));
}

.sidebar-menu a.logout {
    color: #EF4444;
    background: rgba(239, 68, 68, 0.05);
    font-weight: 500;
}

.sidebar-menu a.logout .material-symbols-rounded {
    color: #EF4444;
}

.sidebar-menu a.logout:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #DC2626;
    transform: translateX(0);
}

.sidebar-menu a.logout:hover .material-symbols-rounded {
    color: #DC2626;
}

/* ==============================
   BADGE (Optional - untuk notifikasi)
============================== */
.menu-badge {
    margin-left: auto;
    background: var(--gradient);
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}

/* ==============================
   MAIN CONTENT
============================== */
.main-content {
    margin-left: 280px;
    padding: 32px;
    min-height: 100vh;
}

/* HEADER DASHBOARD */
.dashboard-header {
    background: linear-gradient(135deg, #FFFFFF 0%, #F9FAFB 100%);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

.dashboard-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    background: var(--gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.dashboard-header p {
    margin-top: 8px;
    font-size: 14px;
    color: var(--text-secondary);
    font-weight: 500;
}

/* ==============================
   RESPONSIVE
============================== */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
}
</style>

<div class="sidebar">
    <!-- Header dengan Gradient -->
    <div class="sidebar-header">
        <div class="logo">
            <img src="<?= base_url('assets/img/logo2.png') ?>" alt="Logo Politeknik">
        </div>
        
        <!-- Optional: User Info Section -->
        <!-- <div class="user-info">
            <div class="user-avatar">
                <span class="material-symbols-rounded">person</span>
            </div>
            <p class="user-name">Nama Mahasiswa</p>
            <p class="user-role">Mahasiswa</p>
        </div> -->
    </div>

    <!-- Menu Container dengan Scroll -->
    <div class="sidebar-menu-container">
        <!-- Main Menu Section -->
        <div class="menu-section">
            <h6 class="menu-section-title">Menu Utama</h6>
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
            </ul>
        </div>

        <!-- Tugas Akhir Section -->
        <div class="menu-section">
            <h6 class="menu-section-title">Tugas Akhir</h6>
            <ul class="sidebar-menu">
                <!-- PENGAJUAN TUGAS AKHIR -->
                <li class="has-submenu <?= isAnyActive(['/mahasiswa/pengajuan/']) ? 'open' : '' ?>">
                    <a class="submenu-toggle">
                        <span class="material-symbols-rounded">assignment</span>
                        <span>Pengajuan TA</span>
                        <span class="submenu-arrow material-symbols-rounded">expand_more</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="<?= base_url('mahasiswa/pengajuan/form.php') ?>"
                               class="<?= isActive('/mahasiswa/pengajuan/form.php') ? 'active' : '' ?>">
                                <span class="material-symbols-rounded">contract_edit</span>
                                <span>Form Pengajuan</span>
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
                <li class="has-submenu <?= isAnyActive(['/mahasiswa/sempro/']) ? 'open' : '' ?>">
                    <a class="submenu-toggle">
                        <span class="material-symbols-rounded">co_present</span>
                        <span>Seminar Proposal</span>
                        <span class="submenu-arrow material-symbols-rounded">expand_more</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="<?= base_url('mahasiswa/sempro/form.php') ?>"
                               class="<?= isActive('/mahasiswa/sempro/form.php') ? 'active' : '' ?>">
                                <span class="material-symbols-rounded">contract_edit</span>
                                <span>Form Pengajuan</span>
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
                <li class="has-submenu <?= isAnyActive(['/mahasiswa/semhas/']) ? 'open' : '' ?>">
                    <a class="submenu-toggle">
                        <span class="material-symbols-rounded">task</span>
                        <span>Seminar Hasil</span>
                        <span class="submenu-arrow material-symbols-rounded">expand_more</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="<?= base_url('mahasiswa/semhas/form.php') ?>"
                               class="<?= isActive('/mahasiswa/semhas/form.php') ? 'active' : '' ?>">
                                <span class="material-symbols-rounded">contract_edit</span>
                                <span>Form Pengajuan</span>
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
            </ul>
        </div>

        <!-- Resources Section -->
        <div class="menu-section">
            <h6 class="menu-section-title">Resources</h6>
            <ul class="sidebar-menu">
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
            </ul>
        </div>
    </div>

    <!-- Logout di Footer -->
    <div class="sidebar-footer">
        <ul class="sidebar-menu">
            <li>
                <a href="<?= base_url('logout.php') ?>" class="logout">
                    <span class="material-symbols-rounded">logout</span>
                    <span>Log Out</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/coba/mahasiswa/footer.php'; ?>
</div>

<script>
document.querySelectorAll('.submenu-toggle').forEach(toggle => {
    toggle.addEventListener('click', function () {
        const parent = this.closest('.has-submenu');
        
        // Optional: Accordion style (tutup yang lain)
        // document.querySelectorAll('.has-submenu').forEach(item => {
        //     if (item !== parent) item.classList.remove('open');
        // });
        
        parent.classList.toggle('open');
    });
});
</script>