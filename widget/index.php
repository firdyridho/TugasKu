<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$user = getUser();
$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget Realtime - TugasKu</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

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
            --badge-bg: rgba(255, 255, 255, 0.06);
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
        }

        /* Ambient Glow Backdrop */
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
            max-width: 580px;
            width: 100%;
            margin: 0 auto;
            padding: 1.25rem 1rem 3rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
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
            width: 32px;
            height: 32px;
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

        /* Quick Stat Badges inside Clock Card */
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
            font-size: 0.88rem;
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

        /* Left status accent strip */
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

        .badge-ongoing {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-upcoming {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }

        .badge-passed {
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
        }

        .badge-dl-overdue {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .badge-dl-today {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-dl-urgent {
            background: rgba(59, 130, 246, 0.15);
            color: #93c5fd;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .badge-dl-normal {
            background: rgba(255, 255, 255, 0.06);
            color: #cbd5e1;
        }

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

        .item-sub svg {
            color: #64748b;
            flex-shrink: 0;
        }

        /* Progress Bar for Ongoing Courses */
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

        /* Task Checkbox & Actions */
        .task-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .task-checkbox-wrap {
            padding-top: 2px;
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

        /* Bottom Footer Sync Bar */
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

        /* Loading Skeleton */
        .skeleton {
            background: linear-gradient(90deg, #1f2937 25%, #2d3748 50%, #1f2937 75%);
            background-size: 200% 100%;
            animation: skeletonLoading 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes skeletonLoading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        @media (max-width: 480px) {
            .digital-clock {
                font-size: 1.95rem;
            }
            .clock-quick-stats {
                gap: 0.4rem;
            }
            .quick-stat-pill {
                font-size: 0.74rem;
                padding: 0.35rem 0.55rem;
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
                    Tugas<span>Ku</span> <span style="font-size:0.75rem; font-weight:500; color:#818cf8; background:rgba(99,102,241,0.15); padding:2px 7px; border-radius:12px; margin-left:3px;">Live Widget</span>
                </div>
            </a>
            <div class="header-actions">
                <button type="button" class="btn-icon" id="btnRefresh" onclick="fetchWidgetData(true)" title="Perbarui Data Sekarang">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                </button>
                <a href="<?= BASE_URL ?>/dashboard/" class="btn-icon" title="Kembali ke Dashboard Web">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </a>
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
                <div class="skeleton" style="height: 72px;"></div>
                <div class="skeleton" style="height: 72px;"></div>
            </div>
            <div id="jadwalContent" style="display: none;" class="widget-list"></div>
        </section>

        <!-- TAB 2: Deadline Tugas List -->
        <section id="paneDeadlines" class="widget-list" style="display: none;">
            <div id="deadlinesLoading" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div class="skeleton" style="height: 76px;"></div>
                <div class="skeleton" style="height: 76px;"></div>
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
                <a href="<?= BASE_URL ?>/jadwal/">+ Jadwal</a>
                <a href="<?= BASE_URL ?>/kuliah/">+ Tugas</a>
                <a href="<?= BASE_URL ?>/dashboard/">Dashboard</a>
            </div>
        </footer>
    </div>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        let widgetData = null;
        let activeTab = 'jadwal';
        let serverTimeOffset = 0; // ms offset between local & server

        // 1. Live Realtime Digital Clock Ticker (Ticking Every 1000ms)
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

        // 3. Fetch Widget Data via API
        async function fetchWidgetData(isManual = false) {
            const refreshBtn = document.getElementById('btnRefresh');
            if (refreshBtn && isManual) refreshBtn.classList.add('spinning');

            try {
                const res = await fetch(BASE_URL + '/api/widget.php', {
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
            // Update Date & Counts
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

            // Render Jadwal
            renderJadwalList(data.jadwal || []);

            // Render Deadlines
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
                        <a href="${BASE_URL}/jadwal/" class="task-action-btn">+ Tambah Jadwal Mandiri</a>
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
                        <a href="${BASE_URL}/kuliah/" class="task-action-btn">Lihat Semua Tugas</a>
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

        // 7. Toggle Task Done with AJAX
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

                const res = await fetch(BASE_URL + '/api/widget.php', {
                    method: 'POST',
                    body: fd
                });
                const resp = await res.json();
                if (resp.status === 'success') {
                    // Animate out card smoothly
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

        // Initial Load & Auto-Polling every 30 seconds
        fetchWidgetData();
        setInterval(() => fetchWidgetData(false), 30000);
    </script>
</body>
</html>
