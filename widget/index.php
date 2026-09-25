<?php
require_once __DIR__ . '/../config.php';

$token = trim($_GET['token'] ?? '');
$user = null;

if (!empty($token)) {
    $user = getUserByWidgetToken($token);
}

if (!$user) {
    if (isLoggedIn()) {
        $user = getUser();
        $token = getUserWidgetToken($_SESSION['user_id']);
    } else {
        requireLogin();
    }
}

$userId = (int)$user['id'];
$widgetToken = $token ?: getUserWidgetToken($userId);

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$fullWidgetUrl = $protocol . $host . BASE_URL . '/widget?token=' . urlencode($widgetToken);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>TugasKu Widget Realtime</title>

    <!-- PWA & Mobile Web App Meta (Untuk Pasang di Layar Utama HP / Desktop) -->
    <link rel="manifest" href="<?= BASE_URL ?>/widget/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TugasKu Widget">
    <meta name="theme-color" content="#0b0f19">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-page: #0b0f19;
            --bg-surface: #111827;
            --bg-card: #1f2937;
            --bg-card-hover: #283548;
            --border-color: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(99, 102, 241, 0.35);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.25);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            user-select: none;
        }

        .ambient-glow {
            position: fixed;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: 480px;
            height: 250px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.18) 0%, rgba(11, 15, 25, 0) 70%);
            pointer-events: none;
            z-index: 0;
        }

        .widget-container {
            position: relative;
            z-index: 1;
            max-width: 540px;
            width: 100%;
            margin: 0 auto;
            padding: 1rem 1rem 3rem;
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
        }

        /* Header Bar */
        .widget-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .widget-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            color: inherit;
        }

        .brand-icon-box {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        .brand-title {
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
        }

        .brand-title span {
            color: #a5b4fc;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-install-trigger {
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.35);
            color: #a5b4fc;
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-install-trigger:hover {
            background: #4f46e5;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-icon {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-icon:hover {
            color: #fff;
            background: var(--bg-card-hover);
            border-color: var(--border-hover);
        }

        .btn-icon.spinning svg {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        /* Hero Live Clock Banner */
        .live-clock-card {
            background: linear-gradient(135deg, rgba(31, 41, 55, 0.85), rgba(17, 24, 39, 0.95));
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.25rem 1.4rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(12px);
        }

        .clock-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(16, 185, 129, 0.12);
            color: #34d399;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            border: 1px solid rgba(16, 185, 129, 0.25);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: pulseDot 1.5s infinite;
        }

        @keyframes pulseDot {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
            100% { opacity: 1; transform: scale(1); }
        }

        .live-date-str {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .digital-clock {
            font-family: 'JetBrains Mono', monospace;
            font-size: 2.35rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #ffffff;
            display: flex;
            align-items: baseline;
            gap: 0.35rem;
            line-height: 1.1;
            margin-bottom: 0.8rem;
        }

        .clock-tz {
            font-size: 0.9rem;
            color: #818cf8;
            font-weight: 600;
        }

        .clock-quick-stats {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .quick-stat-pill {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 8px;
            padding: 0.4rem 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
        }

        .stat-badge-num {
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            padding: 1px 6px;
            border-radius: 5px;
            font-size: 0.75rem;
        }

        .stat-blue { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; }
        .stat-amber { background: rgba(245, 158, 11, 0.2); color: #fcd34d; }
        .stat-green { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; }

        /* Widget Tabs */
        .widget-tabs {
            display: flex;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 4px;
            gap: 4px;
        }

        .tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 0.65rem 0.85rem;
            border-radius: 9px;
            font-size: 0.86rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
        }

        .tab-count-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 1px 7px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .tab-btn:not(.active) .tab-count-badge {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
        }

        /* Content List Cards */
        .widget-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .item-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 0.95rem 1.1rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .item-card:hover {
            border-color: var(--border-hover);
            background: var(--bg-card);
            transform: translateY(-1px);
        }

        .item-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--border-color);
        }

        .item-card.card-ongoing::before { background: #10b981; }
        .item-card.card-upcoming::before { background: #6366f1; }
        .item-card.card-passed::before { background: rgba(255,255,255,0.15); opacity: 0.6; }
        .item-card.card-passed { opacity: 0.65; }

        .item-card.dl-overdue::before { background: #ef4444; }
        .item-card.dl-today::before { background: #f59e0b; }
        .item-card.dl-urgent::before { background: #3b82f6; }
        .item-card.dl-normal::before { background: #6366f1; }

        .item-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .item-type-badge {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .badge-ongoing { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-upcoming { background: rgba(99, 102, 241, 0.15); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3); }
        .badge-passed { background: rgba(255, 255, 255, 0.05); color: #94a3b8; }

        .badge-dl-overdue { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        .badge-dl-today { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-dl-urgent { background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); }
        .badge-dl-normal { background: rgba(255, 255, 255, 0.06); color: #cbd5e1; }

        .item-time-text {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
        }

        .item-title {
            font-size: 0.98rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.35;
        }

        .item-sub {
            font-size: 0.82rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .ongoing-progress-wrap {
            margin-top: 0.25rem;
            width: 100%;
            height: 5px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            overflow: hidden;
        }

        .ongoing-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #34d399);
            border-radius: 10px;
            transition: width 0.4s ease;
        }

        /* Task Checkbox */
        .task-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .custom-task-checkbox {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #475569;
            border-radius: 6px;
            outline: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            transition: all 0.2s ease;
            position: relative;
        }

        .custom-task-checkbox:checked {
            background: #10b981;
            border-color: #10b981;
        }

        .custom-task-checkbox:checked::after {
            content: '';
            width: 5px;
            height: 9px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            position: absolute;
            top: 2px;
        }

        .task-info-col {
            flex: 1;
            min-width: 0;
        }

        .task-action-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.75rem;
            padding: 4px 9px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .task-action-btn:hover {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            background: var(--bg-surface);
            border: 1px dashed var(--border-color);
            border-radius: 16px;
        }

        .empty-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.1);
            color: #818cf8;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.9rem;
        }

        .empty-title {
            font-weight: 700;
            font-size: 1rem;
            color: #ffffff;
            margin-bottom: 0.3rem;
        }

        .empty-desc {
            font-size: 0.84rem;
            color: var(--text-muted);
            max-width: 320px;
            margin: 0 auto 1rem;
            line-height: 1.5;
        }

        /* Footer Sync Bar */
        .widget-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #64748b;
        }

        .sync-status {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .sync-pulse {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
        }

        .footer-links a {
            color: #818cf8;
            text-decoration: none;
            margin-left: 0.75rem;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* Modal Overlay for External / Desktop / Mobile installation */
        .ext-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(6px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.25s ease;
        }

        .ext-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .ext-modal-card {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            max-width: 520px;
            width: 100%;
            padding: 1.6rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
            transform: scale(0.95);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ext-modal-overlay.active .ext-modal-card {
            transform: scale(1);
        }

        .ext-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ext-modal-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .ext-modal-close {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.5rem;
            cursor: pointer;
            line-height: 1;
        }

        .ext-option-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .ext-option-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .ext-option-desc {
            font-size: 0.8rem;
            color: #94a3b8;
            line-height: 1.45;
        }

        .btn-action-ext {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: var(--primary);
            color: #ffffff;
            text-decoration: none;
            padding: 0.55rem 1rem;
            border-radius: 8px;
            font-size: 0.84rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-action-ext:hover {
            background: #4f46e5;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        .copy-input-row {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.3rem;
        }

        .copy-input {
            flex: 1;
            background: #0b0f19;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-family: 'JetBrains Mono', monospace;
            outline: none;
        }

        @media (max-width: 480px) {
            .digital-clock {
                font-size: 1.95rem;
            }
        }
    </style>
</head>
<body>

    <div class="ambient-glow" aria-hidden="true"></div>

    <div class="widget-container">
        <!-- Top Nav / Brand Bar -->
        <header class="widget-header">
            <a href="<?= BASE_URL ?>/dashboard/" class="widget-brand" title="Buka Dashboard Utama TugasKu">
                <div class="brand-icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </div>
                <div class="brand-title">
                    Tugas<span>Ku</span> <span style="font-size:0.75rem; font-weight:500; color:#818cf8; background:rgba(99,102,241,0.15); padding:2px 7px; border-radius:12px; margin-left:3px;">Widget Bebas Login</span>
                </div>
            </a>
            <div class="header-actions">
                <button type="button" class="btn-install-trigger" onclick="openInstallModal()" title="Pasang di Luar Web (Desktop & HP)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>Pasang di HP / PC</span>
                </button>
                <button type="button" class="btn-icon" id="btnRefresh" onclick="fetchWidgetData(true)" title="Perbarui Data Sekarang">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                </button>
            </div>
        </header>

        <!-- Hero Realtime Clock Card -->
        <section class="live-clock-card">
            <div class="clock-header">
                <div class="live-indicator">
                    <span class="live-dot"></span>
                    <span>Realtime Sync</span>
                </div>
                <span class="live-date-str" id="clockDate">Memuat tanggal...</span>
            </div>

            <div class="digital-clock" id="digitalClockWrap">
                <span id="clockHours">00</span>:<span id="clockMinutes">00</span>:<span id="clockSeconds">00</span>
                <span class="clock-tz">WIB</span>
            </div>

            <div class="clock-quick-stats">
                <div class="quick-stat-pill">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Jadwal Hari Ini:</span>
                    <span class="stat-badge-num stat-blue" id="statTodayCount">0</span>
                </div>
                <div class="quick-stat-pill">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>DL Mendesak:</span>
                    <span class="stat-badge-num stat-amber" id="statUrgentCount">0</span>
                </div>
                <div class="quick-stat-pill" id="statOngoingPill" style="display: none;">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981;"></span>
                    <span>Kuliah Berlangsung:</span>
                    <span class="stat-badge-num stat-green" id="statOngoingCount">0</span>
                </div>
            </div>
        </section>

        <!-- Tab Controls -->
        <div class="widget-tabs" role="tablist">
            <button type="button" class="tab-btn active" id="tabBtnJadwal" onclick="switchWidgetTab('jadwal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Jadwal Hari Ini</span>
                <span class="tab-count-badge" id="badgeTabJadwal">0</span>
            </button>
            <button type="button" class="tab-btn" id="tabBtnDeadlines" onclick="switchWidgetTab('deadlines')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span>Deadline Tugas (DL)</span>
                <span class="tab-count-badge" id="badgeTabDeadlines">0</span>
            </button>
        </div>

        <!-- TAB 1: Jadwal Hari Ini List -->
        <section id="paneJadwal" class="widget-list">
            <div id="jadwalLoading" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div class="item-card" style="opacity: 0.5;"><div style="height: 48px;"></div></div>
                <div class="item-card" style="opacity: 0.3;"><div style="height: 48px;"></div></div>
            </div>
            <div id="jadwalContent" style="display: none;" class="widget-list"></div>
        </section>

        <!-- TAB 2: Deadline Tugas List -->
        <section id="paneDeadlines" class="widget-list" style="display: none;">
            <div id="deadlinesLoading" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div class="item-card" style="opacity: 0.5;"><div style="height: 52px;"></div></div>
                <div class="item-card" style="opacity: 0.3;"><div style="height: 52px;"></div></div>
            </div>
            <div id="deadlinesContent" style="display: none;" class="widget-list"></div>
        </section>

        <!-- Footer Info & Auto-Sync Bar -->
        <footer class="widget-footer">
            <div class="sync-status">
                <span class="sync-pulse"></span>
                <span>Auto-refresh setiap 30 detik</span>
            </div>
            <div class="footer-links">
                <a href="javascript:void(0)" onclick="openInstallModal()">+ Pasang di HP/PC</a>
                <a href="<?= BASE_URL ?>/dashboard/">Buka Web Lengkap</a>
            </div>
        </footer>
    </div>

    <!-- Modal Panduan Pemasangan di Luar Web (Desktop & HP) -->
    <div class="ext-modal-overlay" id="installModalOverlay" onclick="if(event.target===this) closeInstallModal()">
        <div class="ext-modal-card">
            <div class="ext-modal-header">
                <div class="ext-modal-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Pasang Widget di Luar Web
                </div>
                <button type="button" class="ext-modal-close" onclick="closeInstallModal()">&times;</button>
            </div>

            <!-- Opsi 1: Desktop Windows -->
            <div class="ext-option-card">
                <div class="ext-option-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    1. Pasang di Komputer / Laptop (Windows)
                </div>
                <p class="ext-option-desc">
                    Unduh file shortcut sekali klik. Cukup letakkan di Desktop atau Taskbar, klik 2x maka widget akan langsung terbuka sebagai aplikasi mengambang tanpa frame/tab browser!
                </p>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="<?= BASE_URL ?>/widget/download-shortcut?token=<?= urlencode($widgetToken) ?>" class="btn-action-ext">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Unduh Shortcut Windows (.bat)
                    </a>
                </div>
            </div>

            <!-- Opsi 2: Smartphone HP -->
            <div class="ext-option-card">
                <div class="ext-option-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    2. Pasang di Layar Utama HP (Android & iOS)
                </div>
                <p class="ext-option-desc">
                    Buka tautan ini di HP kamu, lalu:
                    <br>&bull; <strong>Android (Chrome)</strong>: Klik titik tiga (⋮) kanan atas &gt; pilih <strong>"Tambahkan ke Layar Utama"</strong>.
                    <br>&bull; <strong>iPhone (Safari)</strong>: Klik ikon Bagikan (kotak panah atas) &gt; pilih <strong>"Tambahkan ke Layar Utama"</strong>.
                    <br>Widget akan tersimpan seperti aplikasi mandiri di HP tanpa perlu buka web dulu!
                </p>
            </div>

            <!-- Opsi 3: Tautan Bebas Login -->
            <div class="ext-option-card">
                <div class="ext-option-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    3. Tautan Widget Pribadi Kamu (Bebas Login)
                </div>
                <div class="copy-input-row">
                    <input type="text" class="copy-input" id="inputWidgetLink" value="<?= htmlspecialchars($fullWidgetUrl) ?>" readonly>
                    <button type="button" class="btn-action-ext" onclick="copyWidgetLink()" id="btnCopyLink">
                        Salin
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const WIDGET_TOKEN = '<?= htmlspecialchars($widgetToken, ENT_QUOTES, 'UTF-8') ?>';
        let widgetData = null;
        let activeTab = 'jadwal';
        let serverTimeOffset = 0;

        function openInstallModal() {
            const m = document.getElementById('installModalOverlay');
            if (m) m.classList.add('active');
        }

        function closeInstallModal() {
            const m = document.getElementById('installModalOverlay');
            if (m) m.classList.remove('active');
        }

        function copyWidgetLink() {
            const inp = document.getElementById('inputWidgetLink');
            const btn = document.getElementById('btnCopyLink');
            if (inp) {
                inp.select();
                navigator.clipboard.writeText(inp.value).then(() => {
                    if (btn) {
                        btn.textContent = 'Tersalin!';
                        setTimeout(() => btn.textContent = 'Salin', 2000);
                    }
                }).catch(() => {
                    document.execCommand('copy');
                    if (btn) {
                        btn.textContent = 'Tersalin!';
                        setTimeout(() => btn.textContent = 'Salin', 2000);
                    }
                });
            }
        }

        // 1. Live Realtime Digital Clock
        function updateDigitalClock() {
            const now = new Date(Date.now() + serverTimeOffset);
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');

            const elH = document.getElementById('clockHours');
            const elM = document.getElementById('clockMinutes');
            const elS = document.getElementById('clockSeconds');

            if (elH) elH.textContent = h;
            if (elM) elM.textContent = m;
            if (elS) elS.textContent = s;
        }

        setInterval(updateDigitalClock, 1000);

        // 2. Tab Switcher
        function switchWidgetTab(tab) {
            activeTab = tab;
            const btnJadwal = document.getElementById('tabBtnJadwal');
            const btnDl = document.getElementById('tabBtnDeadlines');
            const paneJadwal = document.getElementById('paneJadwal');
            const paneDl = document.getElementById('paneDeadlines');

            if (tab === 'jadwal') {
                btnJadwal.classList.add('active');
                btnDl.classList.remove('active');
                paneJadwal.style.display = 'flex';
                paneDl.style.display = 'none';
            } else {
                btnDl.classList.add('active');
                btnJadwal.classList.remove('active');
                paneDl.style.display = 'flex';
                paneJadwal.style.display = 'none';
            }
        }

        // 3. Fetch Data from API using Token
        async function fetchWidgetData(isManual = false) {
            const refreshBtn = document.getElementById('btnRefresh');
            if (refreshBtn && isManual) refreshBtn.classList.add('spinning');

            try {
                const url = BASE_URL + '/api/widget.php' + (WIDGET_TOKEN ? ('?token=' + encodeURIComponent(WIDGET_TOKEN)) : '');
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });

                if (res.status === 401) {
                    window.location.href = BASE_URL + '/auth/login';
                    return;
                }

                const data = await res.json();
                if (data.status === 'success') {
                    widgetData = data;
                    if (data.timestamp) {
                        serverTimeOffset = (data.timestamp * 1000) - Date.now();
                    }
                    renderWidgetUI(data);
                }
            } catch (err) {
                console.error('Gagal mengambil data widget:', err);
            } finally {
                if (refreshBtn && isManual) {
                    setTimeout(() => refreshBtn.classList.remove('spinning'), 500);
                }
            }
        }

        // 4. Render Widget UI
        function renderWidgetUI(data) {
            const elDate = document.getElementById('clockDate');
            if (elDate) elDate.textContent = data.date_display || 'Hari Ini';

            const statToday = document.getElementById('statTodayCount');
            const statUrgent = document.getElementById('statUrgentCount');
            const badgeJadwal = document.getElementById('badgeTabJadwal');
            const badgeDl = document.getElementById('badgeTabDeadlines');
            const statOngoingPill = document.getElementById('statOngoingPill');
            const statOngoingCount = document.getElementById('statOngoingCount');

            if (statToday) statToday.textContent = data.counts.total_jadwal_today;
            if (statUrgent) statUrgent.textContent = data.counts.urgent_dl;
            if (badgeJadwal) badgeJadwal.textContent = data.counts.total_jadwal_today;
            if (badgeDl) badgeDl.textContent = data.counts.total_active_dl;

            if (data.counts.ongoing_jadwal > 0) {
                if (statOngoingPill) statOngoingPill.style.display = 'inline-flex';
                if (statOngoingCount) statOngoingCount.textContent = data.counts.ongoing_jadwal;
            } else {
                if (statOngoingPill) statOngoingPill.style.display = 'none';
            }

            renderJadwalList(data.jadwal || []);
            renderDeadlinesList(data.deadlines || []);
        }

        // 5. Render Jadwal
        function renderJadwalList(items) {
            const container = document.getElementById('jadwalContent');
            const loader = document.getElementById('jadwalLoading');
            if (loader) loader.style.display = 'none';
            if (!container) return;
            container.style.display = 'flex';

            if (!items.length) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon-wrap">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div class="empty-title">Tidak Ada Jadwal Hari Ini</div>
                        <p class="empty-desc">Hari ini bebas jadwal kuliah maupun agenda mandiri. Kamu bisa istirahat atau mencicil tugas!</p>
                    </div>
                `;
                return;
            }

            let html = '';
            items.forEach(item => {
                const cardClass = 'card-' + item.status;
                const badgeClass = 'badge-' + item.status;
                const typeLabel = item.type === 'kuliah' ? 'Kuliah' : 'Mandiri';
                const isOngoing = item.status === 'ongoing';

                html += `
                    <div class="item-card ${cardClass}">
                        <div class="item-top">
                            <span class="item-type-badge ${badgeClass}">
                                ${isOngoing ? '<span class="live-dot" style="width:6px;height:6px;"></span>' : ''}
                                ${typeLabel} • ${item.status_text}
                            </span>
                            <span class="item-time-text">
                                ${item.time_range} WIB
                            </span>
                        </div>
                        <div class="item-title">${escapeHtml(item.title)}</div>
                        <div class="item-sub">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>${escapeHtml(item.location)}</span>
                            ${item.info ? `<span>•</span><span>${escapeHtml(item.info)}</span>` : ''}
                        </div>
                        ${isOngoing && item.progress_pct > 0 ? `
                            <div class="ongoing-progress-wrap" title="Progres durasi: ${item.progress_pct}%">
                                <div class="ongoing-progress-bar" style="width: ${item.progress_pct}%;"></div>
                            </div>
                        ` : ''}
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        // 6. Render Deadlines
        function renderDeadlinesList(items) {
            const container = document.getElementById('deadlinesContent');
            const loader = document.getElementById('deadlinesLoading');
            if (loader) loader.style.display = 'none';
            if (!container) return;
            container.style.display = 'flex';

            if (!items.length) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon-wrap" style="background: rgba(16, 185, 129, 0.1); color: #34d399;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div class="empty-title">Semua Tugas Beres! 🎉</div>
                        <p class="empty-desc">Tidak ada deadline tugas yang tertunda saat ini. Kamu hebat, pertahankan prestasimu!</p>
                    </div>
                `;
                return;
            }

            let html = '';
            items.forEach(task => {
                const urgencyBadgeClass = 'badge-' + task.badge_class;
                const cardClass = task.badge_class;
                const typeLabel = task.task_type === 'kuliah' ? 'Tugas Kuliah' : 'Tugas Organisasi';

                html += `
                    <div class="item-card ${cardClass}" id="task-card-${task.task_type}-${task.id}">
                        <div class="item-top">
                            <span class="item-type-badge ${urgencyBadgeClass}">
                                ${typeLabel} • ${task.time_left_text}
                            </span>
                            <a href="${task.url}" target="_blank" class="task-action-btn" title="Buka Detail Tugas">
                                Buka
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </div>
                        <div class="task-row">
                            <div class="task-checkbox-wrap">
                                <input type="checkbox" class="custom-task-checkbox" 
                                    onchange="toggleTaskDone('${task.task_type}', ${task.id}, this)"
                                    title="Tandai Selesai Langsung">
                            </div>
                            <div class="task-info-col">
                                <div class="item-title" style="margin-bottom: 0.25rem;">${escapeHtml(task.title)}</div>
                                <div class="item-sub">
                                    <span>${escapeHtml(task.source)}</span>
                                    <span>•</span>
                                    <span>DL: ${escapeHtml(task.deadline_formatted)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        // 7. Toggle Task Done via AJAX with Token
        async function toggleTaskDone(taskType, taskId, checkboxEl) {
            const card = document.getElementById(`task-card-${taskType}-${taskId}`);
            if (card) {
                card.style.opacity = '0.4';
                card.style.transform = 'scale(0.98)';
            }

            try {
                const fd = new FormData();
                fd.append('action', 'toggle_task');
                fd.append('task_type', taskType);
                fd.append('task_id', taskId);
                fd.append('status', 'selesai');
                if (WIDGET_TOKEN) fd.append('token', WIDGET_TOKEN);

                const res = await fetch(BASE_URL + '/api/widget.php', {
                    method: 'POST',
                    body: fd
                });
                const resp = await res.json();
                if (resp.status === 'success') {
                    if (card) {
                        card.style.transition = 'all 0.35s ease';
                        card.style.transform = 'translateX(100px)';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            fetchWidgetData(false);
                        }, 350);
                    }
                } else {
                    alert(resp.message || 'Gagal mengubah status tugas');
                    if (card) {
                        card.style.opacity = '1';
                        card.style.transform = 'none';
                        checkboxEl.checked = false;
                    }
                }
            } catch (err) {
                console.error(err);
                if (card) {
                    card.style.opacity = '1';
                    card.style.transform = 'none';
                    checkboxEl.checked = false;
                }
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        fetchWidgetData();
        setInterval(() => fetchWidgetData(false), 30000);
    </script>
</body>
</html>
