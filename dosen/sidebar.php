<?php
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="assets\img\Logo.webp">
    <!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
    --sidebar-width: 280px;
    --sidebar-collapsed-width: 70px;
}

body {
    background-color: #F3F4F6;
    font-family: 'Inter', sans-serif !important;
    margin: 0;
}

/* ==============================
   OVERLAY EFFECT
============================== */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.sidebar-overlay.active {
    opacity: 1;
    pointer-events: auto;
}

/* ==============================
   TOGGLE ARROW BUTTON
============================== */
.sidebar-toggle {
    display: none;
    position: fixed;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1100;
    width: 28px;
    height: 56px;
    background: var(--gradient);
    border: none;
    border-radius: 0 8px 8px 0;
    cursor: pointer;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    align-items: center;
    justify-content: center;
}

.sidebar-toggle:hover {
    width: 32px;
    box-shadow: 3px 0 12px rgba(255, 107, 157, 0.4);
}

.sidebar-toggle span {
    color: white;
    font-size: 20px;
    transition: transform 0.3s ease;
}

/* Position based on sidebar state */
.sidebar-toggle.collapsed {
    left: var(--sidebar-collapsed-width);
}

.sidebar-toggle.expanded {
    left: var(--sidebar-width);
}

/* ==============================
   SIDEBAR
============================== */
.sidebar {
    width: var(--sidebar-width);
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
    transition: all 0.3s ease;
}

/* ==============================
   HEADER WITH GRADIENT
============================== */
.sidebar-header {
    background: var(--gradient);
    padding: 28px 24px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
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
    transition: all 0.3s ease;
}

.logo-desktop {
    width: 200px;
    display: block;
    margin: 0 auto;
    filter: drop-shadow(0 2px 8px rgba(255, 255, 255, 0.4)) 
            drop-shadow(0 4px 16px rgba(255, 255, 255, 0.3))
            drop-shadow(0 0 20px rgba(255, 255, 255, 0.2));
    transition: all 0.3s ease;
}

.logo-mobile {
    width: 40px;
    display: none;
    margin: 0 auto;
    filter: drop-shadow(0 2px 8px rgba(255, 255, 255, 0.4)) 
            drop-shadow(0 4px 16px rgba(255, 255, 255, 0.3))
            drop-shadow(0 0 20px rgba(255, 255, 255, 0.2));
    transition: all 0.3s ease;
}

.logo img:hover {
    transform: scale(1.05);
    filter: drop-shadow(0 4px 12px rgba(255, 255, 255, 0.6)) 
            drop-shadow(0 6px 20px rgba(255, 255, 255, 0.4))
            drop-shadow(0 0 30px rgba(255, 255, 255, 0.3));
}

/* USER INFO (Optional) */
.user-info {
    position: relative;
    z-index: 1;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
    transition: all 0.3s ease;
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
    transition: all 0.3s ease;
    white-space: nowrap;
    overflow: hidden;
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
    white-space: nowrap;
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

.menu-text {
    transition: all 0.3s ease;
    overflow: hidden;
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
   MAIN CONTENT
============================== */
.main-content {
    margin-left: var(--sidebar-width);
    padding: 32px;
    min-height: 100vh;
    margin-bottom: 50px;
    transition: all 0.3s ease;
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
   RESPONSIVE - TABLET & MOBILE
============================== */
@media (max-width: 1024px) {
    /* Show overlay */
    .sidebar-overlay {
        display: block;
    }

    /* Show toggle button */
    .sidebar-toggle {
        display: flex;
    }

    /* Sidebar collapsed by default */
    .sidebar {
        width: var(--sidebar-collapsed-width);
    }

    /* EXPANDED STATE - Sidebar floats on top */
    .sidebar.expanded {
        width: var(--sidebar-width);
        z-index: 1001; /* Above overlay */
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
    }

    /* Switch logo */
    .sidebar:not(.expanded) .logo-desktop {
        display: none;
    }

    .sidebar:not(.expanded) .logo-mobile {
        display: block;
    }

    .sidebar.expanded .logo-desktop {
        display: block;
    }

    .sidebar.expanded .logo-mobile {
        display: none;
    }

    /* Hide text in collapsed mode */
    .sidebar:not(.expanded) .menu-text {
        opacity: 0;
        width: 0;
        display: none;
    }

    .sidebar:not(.expanded) .menu-section-title {
        opacity: 0;
        height: 0;
        padding: 0;
        margin: 0;
        overflow: hidden;
    }

    .sidebar:not(.expanded) .sidebar-header {
        padding: 20px 15px;
    }

    .sidebar:not(.expanded) .user-info {
        display: none;
    }

    .sidebar:not(.expanded) .sidebar-menu a {
        justify-content: center;
        padding: 11px 0;
        gap: 0;
    }

    .sidebar:not(.expanded) .sidebar-menu a:hover {
        transform: translateX(0);
    }

    .sidebar:not(.expanded) .sidebar-footer {
        padding: 16px 8px;
    }

    .sidebar:not(.expanded) .sidebar-menu-container {
        padding: 20px 8px;
    }

    /* Show text in expanded mode */
    .sidebar.expanded .menu-text {
        opacity: 1;
        width: auto;
        display: block;
    }

    .sidebar.expanded .menu-section-title {
        opacity: 1;
        height: auto;
    }

    /* Adjust main content - ALWAYS fixed margin */
    .main-content {
        margin-left: var(--sidebar-collapsed-width);
        padding: 24px 20px;
    }

    /* Main content stays in place when sidebar expands */
    .sidebar.expanded ~ .main-content {
        margin-left: var(--sidebar-collapsed-width);
    }

    /* Tooltip for collapsed state */
    .sidebar:not(.expanded) .sidebar-menu a {
        position: relative;
    }

    .sidebar:not(.expanded) .sidebar-menu a::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background: var(--text-primary);
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        white-space: nowrap;
        margin-left: 10px;
        z-index: 1001;
        box-shadow: var(--shadow-md);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }

    .sidebar:not(.expanded) .sidebar-menu a::before {
        content: '';
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        margin-left: 4px;
        border: 5px solid transparent;
        border-right-color: var(--text-primary);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }

    .sidebar:not(.expanded) .sidebar-menu a:hover::after,
    .sidebar:not(.expanded) .sidebar-menu a:hover::before {
        opacity: 1;
    }

    .dashboard-header h1 {
        font-size: 24px;
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 20px 16px;
    }

    .dashboard-header {
        padding: 20px;
    }

    .dashboard-header h1 {
        font-size: 22px;
    }
}

@media (max-width: 480px) {
    .sidebar:not(.expanded) {
        width: 60px;
    }

    .sidebar-toggle.collapsed {
        left: 60px;
    }

    .main-content {
        margin-left: 60px;
        padding: 16px 12px;
    }

    .sidebar:not(.expanded) ~ .main-content {
        margin-left: 60px;
    }

    .sidebar.expanded ~ .main-content {
        margin-left: 60px;
    }

    .dashboard-header {
        padding: 16px;
    }

    .dashboard-header h1 {
        font-size: 20px;
    }
}

    /* Custom SweetAlert2 Styles - Theme Matched */
.swal2-popup {
    font-family: 'Inter', sans-serif !important;
    border-radius: 16px !important;
    padding: 32px !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
}

.swal2-title {
    font-weight: 700 !important;
    font-size: 24px !important;
    color: #1F2937 !important;
    margin-bottom: 12px !important;
}

.swal2-html-container {
    font-size: 16px !important;
    color: #6B7280 !important;
    font-weight: 500 !important;
}

.swal2-icon.swal2-warning {
    border-color: #FF6B9D !important;
    color: #FF6B9D !important;
}

.swal2-icon.swal2-warning .swal2-icon-content {
    color: #FF6B9D !important;
}

.swal2-confirm {
    background: linear-gradient(135deg, #FF6B9D 0%, #FF8E3C 100%) !important;
    border: none !important;
    border-radius: 10px !important;
    padding: 12px 32px !important;
    font-weight: 600 !important;
    font-size: 15px !important;
    box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3) !important;
    transition: all 0.3s ease !important;
}

.swal2-confirm:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(255, 107, 157, 0.4) !important;
}

.swal2-cancel {
    background: #F3F4F6 !important;
    color: #6B7280 !important;
    border: 1px solid #E5E7EB !important;
    border-radius: 10px !important;
    padding: 12px 32px !important;
    font-weight: 600 !important;
    font-size: 15px !important;
    transition: all 0.3s ease !important;
}

.swal2-cancel:hover {
    background: #E5E7EB !important;
    color: #1F2937 !important;
    transform: translateY(-2px) !important;
}

.swal2-actions {
    gap: 12px !important;
    margin-top: 24px !important;
}

/* Loading state */
.swal2-loading .swal2-confirm {
    background: linear-gradient(135deg, #FF6B9D 0%, #FF8E3C 100%) !important;
}

.swal2-loader {
    border-color: #FF6B9D transparent #FF6B9D transparent !important;
}
</style>

<!-- Overlay dengan blur effect -->
<div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Toggle Arrow Button -->
<button class="sidebar-toggle collapsed" id="toggleBtn" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
    <span class="material-symbols-rounded" id="toggleIcon">chevron_right</span>
</button>

<div class="sidebar" id="sidebar">
    <!-- Header dengan Gradient -->
    <div class="sidebar-header">
        <div class="logo">
            <img src="<?= base_url('assets/img/logo2.png') ?>" alt="Logo Politeknik" class="logo-desktop">
            <img src="<?= base_url('assets/img/Logo.webp') ?>" alt="Logo Politeknik" class="logo-mobile">
        </div>
        
        <!-- Optional: Dosen Info Section -->
        <!-- <div class="user-info">
            <div class="user-avatar">
                <span class="material-symbols-rounded">school</span>
            </div>
            <p class="user-name">Nama Dosen</p>
            <p class="user-role">Dosen Pembimbing</p>
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
                    <a href="<?= base_url('dosen/dashboard.php') ?>"
                       class="<?= isActive('/dosen/dashboard.php') ? 'active' : '' ?>"
                       data-tooltip="Dashboard">
                        <span class="material-symbols-rounded">dashboard</span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Bimbingan Section -->
        <div class="menu-section">
            <h6 class="menu-section-title">Bimbingan</h6>
            <ul class="sidebar-menu">
                <!-- MAHASISWA BIMBINGAN -->
                <li>
                    <a href="<?= base_url('dosen/mahasiswa_bimbingan.php') ?>"
                       class="<?= isActive('/dosen/mahasiswa_bimbingan.php') ? 'active' : '' ?>"
                       data-tooltip="Mahasiswa Bimbingan">
                        <span class="material-symbols-rounded">groups</span>
                        <span class="menu-text">Mahasiswa Bimbingan</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Logout di Footer -->
    <div class="sidebar-footer">
        <ul class="sidebar-menu">
            <li>
                <a href="#" class="logout" data-tooltip="Log Out" onclick="confirmLogout(event)">
    <span class="material-symbols-rounded">logout</span>
    <span class="menu-text">Log Out</span>
</a>
            </li>
        </ul>
    </div>
</div>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Logout confirmation with SweetAlert2
function confirmLogout(event) {
    event.preventDefault();
    
    Swal.fire({
        title: 'Konfirmasi Logout',
        html: '<p style="margin: 0;">Apakah Anda yakin ingin keluar dari sistem?</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<span style="display: flex; align-items: center; gap: 8px;"><i class="material-symbols-rounded" style="font-size: 20px;">logout</i> Ya, Logout</span>',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true,
        allowEnterKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Logging out...',
                html: '<p style="margin: 0; color: #6B7280;">Mohon tunggu sebentar</p>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Redirect to logout page
            setTimeout(() => {
                window.location.href = '<?= base_url('logout.php') ?>';
            }, 500);
        }
    });
}
</script>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const toggleBtn = document.getElementById('toggleBtn');
    const toggleIcon = document.getElementById('toggleIcon');
    
    sidebar.classList.toggle('expanded');
    overlay.classList.toggle('active');
    toggleBtn.classList.toggle('collapsed');
    toggleBtn.classList.toggle('expanded');
    
    // Update arrow direction
    if (sidebar.classList.contains('expanded')) {
        toggleIcon.textContent = 'chevron_left';
    } else {
        toggleIcon.textContent = 'chevron_right';
    }
}

// Handle resize
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggleBtn = document.getElementById('toggleBtn');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (window.innerWidth > 1024) {
            // Desktop mode - remove toggle button and expanded class
            sidebar.classList.remove('expanded');
            overlay.classList.remove('active');
            toggleBtn.classList.remove('expanded');
            toggleBtn.classList.add('collapsed');
            toggleIcon.textContent = 'chevron_right';
        }
    }, 250);
});

// Initialize toggle button position on load
window.addEventListener('load', () => {
    const toggleBtn = document.getElementById('toggleBtn');
    if (window.innerWidth <= 1024) {
        toggleBtn.classList.add('collapsed');
    }
});
</script>

<div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/coba/dosen/footer.php'; ?>
</div>