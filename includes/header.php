<?php
$user = getUser();
$currentScript = $_SERVER['SCRIPT_NAME'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'TugasKu' ?> - TugasKu</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/favicon.ico">

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= defined('APP_VERSION') ? APP_VERSION : '2.6.1' ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400..700;1,6..72,400..700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="app-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?= BASE_URL ?>/dashboard/" class="brand-logo">
                Tugas<span class="logo-accent">Ku</span><span class="logo-dot">.</span>
            </a>
            <button class="sidebar-close" id="sidebarClose" aria-label="Tutup Menu">&times;</button>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= BASE_URL ?>/dashboard/" class="nav-item <?= strpos($currentScript, 'dashboard') !== false ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="<?= BASE_URL ?>/organisasi/" class="nav-item <?= strpos($currentScript, 'organisasi') !== false ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Organisasi</span>
            </a>
            <a href="<?= BASE_URL ?>/kuliah/" class="nav-item <?= strpos($currentScript, 'kuliah') !== false ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                <span>Kuliah</span>
            </a>
            <a href="<?= BASE_URL ?>/jadwal/" class="nav-item <?= strpos($currentScript, 'jadwal') !== false ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Jadwal Mandiri</span>
            </a>
            <a href="<?= BASE_URL ?>/kalender/" class="nav-item <?= strpos($currentScript, 'kalender') !== false ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Kalender</span>
            </a>
            <div class="nav-divider"></div>
            <a href="<?= BASE_URL ?>/ekspor" class="nav-item <?= strpos($currentScript, 'ekspor') !== false ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Ekspor Data</span>
            </a>
            <a href="<?= BASE_URL ?>/setting/" class="nav-item <?= strpos($currentScript, 'setting') !== false ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span>Pengaturan</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>/setting/" class="user-info" style="text-decoration: none; flex: 1; min-width: 0;" title="Pengaturan Akun">
                <div class="user-avatar"><?= strtoupper(substr($user['nama'] ?? 'U', 0, 1)) ?></div>
                <div class="user-details">
                    <span class="user-name"><?= htmlspecialchars($user['nama'] ?? 'Mahasiswa') ?></span>
                    <span class="user-nim"><?= htmlspecialchars($user['nim'] ?? ($user['jurusan'] ?? 'Akun Mahasiswa')) ?></span>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/auth/logout" class="btn-logout" onclick="openLogoutModal(event)" title="Keluar dari Akun">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </div>
    </aside>

    <!-- Mobile Bottom Navigation (5 Menu Utama Rapi & Tidak Numpuk) -->
    <nav class="mobile-bottom-nav" id="mobileBottomNav" role="navigation" aria-label="Navigasi utama">
        <div class="mobile-bottom-nav-inner">
            <a href="<?= BASE_URL ?>/dashboard/" class="mobile-nav-item <?= strpos($currentScript, 'dashboard') !== false ? 'active' : '' ?>" aria-label="Dashboard">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="<?= BASE_URL ?>/kuliah/" class="mobile-nav-item <?= strpos($currentScript, 'kuliah') !== false ? 'active' : '' ?>" aria-label="Kuliah">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                <span>Kuliah</span>
            </a>
            <a href="<?= BASE_URL ?>/organisasi/" class="mobile-nav-item <?= strpos($currentScript, 'organisasi') !== false ? 'active' : '' ?>" aria-label="Organisasi">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Organisasi</span>
            </a>
            <a href="<?= BASE_URL ?>/kalender/" class="mobile-nav-item <?= strpos($currentScript, 'kalender') !== false ? 'active' : '' ?>" aria-label="Kalender">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Kalender</span>
            </a>
            <a href="<?= BASE_URL ?>/setting/" class="mobile-nav-item <?= strpos($currentScript, 'setting') !== false ? 'active' : '' ?>" aria-label="Akun">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span>Akun</span>
            </a>
        </div>
    </nav>

    <main class="main-content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Buka Menu">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="topbar-right">
                <button type="button" class="btn-tour-trigger" id="btnTourTrigger" onclick="openTugasKuTour(true)" title="Buka Panduan Pengguna (Tutorial)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Panduan</span>
                </button>
                <span class="date-display">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span id="dateDisplay"></span>
                </span>
                <!-- Mobile User Profile & Logout Shortcut in Topbar -->
                <div class="mobile-topbar-actions">
                    <a href="<?= BASE_URL ?>/setting/" class="mobile-topbar-user" title="Pengaturan Profil (<?= htmlspecialchars($user['nama'] ?? 'Mahasiswa') ?>)">
                        <span class="mobile-user-avatar"><?= strtoupper(substr($user['nama'] ?? 'U', 0, 1)) ?></span>
                    </a>
                    <a href="<?= BASE_URL ?>/auth/logout" class="mobile-topbar-logout" onclick="openLogoutModal(event)" title="Keluar dari Akun">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span>Keluar</span>
                    </a>
                </div>
            </div>
        </header>
        <div class="content-wrapper">
        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success" id="flashAlert">
                <div class="alert-accent-bar"></div>
                <div class="alert-inner">
                    <div class="alert-left">
                        <div class="alert-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <span class="alert-text"><?= htmlspecialchars($msg) ?></span>
                    </div>
                    <span class="alert-close" onclick="this.closest('.alert').remove()">&times;</span>
                </div>
                <div class="alert-progress"></div>
            </div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-error" id="flashAlertError">
                <div class="alert-accent-bar"></div>
                <div class="alert-inner">
                    <div class="alert-left">
                        <div class="alert-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <span class="alert-text"><?= htmlspecialchars($msg) ?></span>
                    </div>
                    <span class="alert-close" onclick="this.closest('.alert').remove()">&times;</span>
                </div>
                <div class="alert-progress"></div>
            </div>
        <?php endif; ?>
