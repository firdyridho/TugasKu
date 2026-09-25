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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>TugasKu - Weather Style Home Screen Widget</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --glass-bg: rgba(15, 23, 42, 0.78);
            --glass-border: rgba(255, 255, 255, 0.12);
            --card-inner-bg: rgba(255, 255, 255, 0.05);
            --card-inner-border: rgba(255, 255, 255, 0.08);
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
            --accent-indigo: #818cf8;
            --accent-emerald: #34d399;
            --accent-amber: #fbbf24;
            --accent-rose: #f87171;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* Transparent background so it blends seamlessly with phone wallpaper */
        html, body {
            width: 100%;
            height: 100%;
            background: transparent;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-primary);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
        }

        /* The Weather-Style Android/iOS Home Screen Widget Container (4x2 / 4x3 ratio) */
        .weather-widget-frame {
            width: 100%;
            max-width: 480px;
            min-height: 190px;
            background: var(--glass-bg);
            backdrop-filter: blur(28px) saturate(180%);
            -webkit-backdrop-filter: blur(28px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            padding: 1.15rem 1.25rem;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.85rem;
            position: relative;
        }

        /* Ambient glow inside widget */
        .widget-internal-glow {
            position: absolute;
            top: -20px;
            right: -20px;
            width: 130px;
            height: 130px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, rgba(15, 23, 42, 0) 70%);
            pointer-events: none;
            border-radius: 50%;
        }

        /* Top Row: Weather Style Header (Time, Date, Status Pill) */
        .widget-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .clock-date-group {
            display: flex;
            align-items: baseline;
            gap: 0.6rem;
        }

        .live-clock {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.65rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1;
            color: #ffffff;
        }

        .date-day-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: capitalize;
        }

        /* Weather Condition-Style Status Badge */
        .productivity-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #c7d2fe;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .pulse-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: dotGlow 1.6s infinite;
        }

        @keyframes dotGlow {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(0.8); opacity: 0.4; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Middle Grid: 2 Cards (Left = Jadwal Sekarang/Berikutnya, Right = DL Tugas Mendesak) */
        .widget-two-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            flex: 1;
        }

        .w-card {
            background: var(--card-inner-bg);
            border: 1px solid var(--card-inner-border);
            border-radius: 18px;
            padding: 0.8rem 0.95rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.4rem;
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .w-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.18);
        }

        /* Card Header */
        .w-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
        }

        .w-card-header svg {
            color: var(--accent-indigo);
        }

        .w-card-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .w-card-sub {
            font-size: 0.74rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .w-card-sub svg {
            flex-shrink: 0;
            color: #64748b;
        }

        /* Status Indicator Tag on Cards */
        .w-status-pill {
            font-size: 0.67rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: auto;
            width: fit-content;
        }

        .pill-ongoing { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .pill-upcoming { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; }
        .pill-passed { background: rgba(255, 255, 255, 0.08); color: #94a3b8; }
        .pill-empty { background: rgba(255, 255, 255, 0.06); color: #94a3b8; }

        .pill-urgent { background: rgba(245, 158, 11, 0.22); color: #fbbf24; }
        .pill-overdue { background: rgba(239, 68, 68, 0.22); color: #f87171; }
        .pill-normal { background: rgba(99, 102, 241, 0.2); color: #cbd5e1; }
        .pill-done { background: rgba(16, 185, 129, 0.2); color: #34d399; }

        /* Quick Task Checkbox inside Card */
        .task-action-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .w-checkbox {
            appearance: none;
            width: 17px;
            height: 17px;
            border: 2px solid #64748b;
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .w-checkbox:checked {
            background: #10b981;
            border-color: #10b981;
        }

        .w-checkbox:checked::after {
            content: '';
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            position: absolute;
            top: 1px;
            left: 4px;
        }

        /* Bottom Minimal Branding & Refresh */
        .widget-bottom-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.7rem;
            color: #64748b;
            padding-top: 0.25rem;
        }

        .brand-signature {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #94a3b8;
            font-weight: 700;
            text-decoration: none;
        }

        .brand-signature span {
            color: #818cf8;
        }

        .widget-refresh-link {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 0.7rem;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: color 0.15s ease;
        }

        .widget-refresh-link:hover {
            color: #ffffff;
        }

        .widget-refresh-link.spinning svg {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 380px) {
            .weather-widget-frame {
                border-radius: 22px;
                padding: 1rem;
            }
            .widget-two-cards {
                grid-template-columns: 1fr;
                gap: 0.6rem;
            }
        }
    </style>
</head>
<body>

    <div class="weather-widget-frame" id="weatherWidgetFrame">
        <div class="widget-internal-glow" aria-hidden="true"></div>

        <!-- 1. Top Bar: Digital Clock & Productivity Status -->
        <div class="widget-top-bar">
            <div class="clock-date-group">
                <span class="live-clock" id="wClock">00:00:00</span>
                <span class="date-day-label" id="wDate">Hari Ini</span>
            </div>
            <div class="productivity-badge" id="wStatusBadge">
                <span class="pulse-dot"></span>
                <span id="wStatusText">Memuat...</span>
            </div>
        </div>

        <!-- 2. Two Main Cards: Jadwal (Kiri) & Deadline Tugas (Kanan) -->
        <div class="widget-two-cards">
            
            <!-- KARTU 1: JADWAL KULIAH / MANDIRI HARI INI -->
            <div class="w-card" id="cardJadwal">
                <div class="w-card-header">
                    <span>Jadwal Hari Ini</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <div class="w-card-title" id="jadwalTitle">Memeriksa jadwal...</div>
                    <div class="w-card-sub" id="jadwalSub">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span id="jadwalLoc">-</span>
                    </div>
                </div>
                <div class="w-status-pill pill-upcoming" id="jadwalPill">Menghubungkan...</div>
            </div>

            <!-- KARTU 2: DEADLINE TUGAS (DL) TERDEKAT -->
            <div class="w-card" id="cardDeadline">
                <div class="w-card-header">
                    <span>Deadline Tugas</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <div class="task-action-check">
                        <input type="checkbox" class="w-checkbox" id="checkTaskDone" onchange="toggleDoneCurrentTask()" title="Centang jika sudah selesai">
                        <div class="w-card-title" id="deadlineTitle" style="flex: 1;">Memeriksa DL...</div>
                    </div>
                    <div class="w-card-sub" id="deadlineSub" style="margin-top: 0.2rem;">
                        <span id="deadlineSource">-</span>
                    </div>
                </div>
                <div class="w-status-pill pill-urgent" id="deadlinePill">-</div>
            </div>

        </div>

        <!-- 3. Bottom Bar: Brand & Auto-Refresh -->
        <div class="widget-bottom-bar">
            <a href="<?= BASE_URL ?>/dashboard/" target="_blank" class="brand-signature">
                Tugas<span>Ku</span> <span style="font-size:0.6rem; opacity:0.8; font-weight:normal;">• Widget</span>
            </a>
            <button type="button" class="widget-refresh-link" id="btnRefresh" onclick="fetchData(true)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                <span id="refreshLabel">Sync</span>
            </button>
        </div>
    </div>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const WIDGET_TOKEN = '<?= htmlspecialchars($widgetToken, ENT_QUOTES, 'UTF-8') ?>';
        let serverOffset = 0;
        let currentTaskData = null;

        // 1. Live Clock
        function updateClock() {
            const now = new Date(Date.now() + serverOffset);
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const elClock = document.getElementById('wClock');
            if (elClock) elClock.textContent = `${h}:${m}:${s}`;
        }
        setInterval(updateClock, 1000);

        // 2. Fetch Data
        async function fetchData(isManual = false) {
            const btn = document.getElementById('btnRefresh');
            if (btn && isManual) btn.classList.add('spinning');

            try {
                const url = BASE_URL + '/api/widget.php' + (WIDGET_TOKEN ? ('?token=' + encodeURIComponent(WIDGET_TOKEN)) : '');
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                const data = await res.json();

                if (data.status === 'success') {
                    if (data.timestamp) {
                        serverOffset = (data.timestamp * 1000) - Date.now();
                    }
                    renderWeatherWidget(data);
                }
            } catch (e) {
                console.error('Fetch error:', e);
            } finally {
                if (btn && isManual) {
                    setTimeout(() => btn.classList.remove('spinning'), 500);
                }
            }
        }

        // 3. Render Weather Style Widget
        function renderWeatherWidget(data) {
            // Date & Badge
            const elDate = document.getElementById('wDate');
            if (elDate) elDate.textContent = data.date_display || 'Hari Ini';

            const statusText = document.getElementById('wStatusText');
            const totalJadwal = data.counts.total_jadwal_today || 0;
            const urgentDl = data.counts.urgent_dl || 0;
            const ongoing = data.counts.ongoing_jadwal || 0;

            if (ongoing > 0) {
                statusText.textContent = `${ongoing} Berlangsung Sekarang`;
            } else if (urgentDl > 0) {
                statusText.textContent = `${urgentDl} DL Tugas Mendesak`;
            } else if (totalJadwal > 0) {
                statusText.textContent = `${totalJadwal} Jadwal Hari Ini`;
            } else {
                statusText.textContent = 'Bebas Jadwal & DL ✨';
            }

            // KARTU 1: JADWAL
            const jTitle = document.getElementById('jadwalTitle');
            const jLoc = document.getElementById('jadwalLoc');
            const jPill = document.getElementById('jadwalPill');

            const schedules = data.jadwal || [];
            if (!schedules.length) {
                jTitle.textContent = 'Bebas Jadwal Hari Ini';
                jLoc.textContent = 'Tidak ada kelas / kegiatan';
                jPill.textContent = 'Santai Sejenak ☕';
                jPill.className = 'w-status-pill pill-empty';
            } else {
                // Prioritize ongoing, then first upcoming, then first passed
                let activeItem = schedules.find(s => s.status === 'ongoing') ||
                                 schedules.find(s => s.status === 'upcoming') ||
                                 schedules[0];

                jTitle.textContent = activeItem.title;
                jLoc.textContent = `${activeItem.time_range} • ${activeItem.location}`;

                if (activeItem.status === 'ongoing') {
                    jPill.textContent = '🟢 ' + activeItem.status_text;
                    jPill.className = 'w-status-pill pill-ongoing';
                } else if (activeItem.status === 'upcoming') {
                    jPill.textContent = '⏳ ' + activeItem.status_text;
                    jPill.className = 'w-status-pill pill-upcoming';
                } else {
                    jPill.textContent = '⚪ ' + activeItem.status_text;
                    jPill.className = 'w-status-pill pill-passed';
                }
            }

            // KARTU 2: DEADLINE TUGAS (DL)
            const dTitle = document.getElementById('deadlineTitle');
            const dSource = document.getElementById('deadlineSource');
            const dPill = document.getElementById('deadlinePill');
            const dCheck = document.getElementById('checkTaskDone');

            const deadlines = data.deadlines || [];
            if (!deadlines.length) {
                currentTaskData = null;
                dTitle.textContent = 'Semua Tugas Beres! 🎉';
                dSource.textContent = 'Tidak ada tugas yang tertunda';
                dPill.textContent = 'Target Tercapai';
                dPill.className = 'w-status-pill pill-done';
                if (dCheck) {
                    dCheck.style.display = 'none';
                    dCheck.checked = false;
                }
            } else {
                const nearestTask = deadlines[0];
                currentTaskData = nearestTask;
                dTitle.textContent = nearestTask.title;
                dSource.textContent = `${nearestTask.source} • ${nearestTask.deadline_formatted}`;
                dPill.textContent = nearestTask.time_left_text;

                if (nearestTask.urgency === 'overdue') {
                    dPill.className = 'w-status-pill pill-overdue';
                } else if (nearestTask.urgency === 'today' || nearestTask.urgency === 'tomorrow') {
                    dPill.className = 'w-status-pill pill-urgent';
                } else {
                    dPill.className = 'w-status-pill pill-normal';
                }

                if (dCheck) {
                    dCheck.style.display = 'inline-block';
                    dCheck.checked = false;
                }
            }
        }

        // 4. Quick Complete Task from Home Screen Widget
        async function toggleDoneCurrentTask() {
            if (!currentTaskData) return;
            const card = document.getElementById('cardDeadline');
            if (card) card.style.opacity = '0.5';

            try {
                const fd = new FormData();
                fd.append('action', 'toggle_task');
                fd.append('task_type', currentTaskData.task_type);
                fd.append('task_id', currentTaskData.id);
                fd.append('status', 'selesai');
                if (WIDGET_TOKEN) fd.append('token', WIDGET_TOKEN);

                const res = await fetch(BASE_URL + '/api/widget.php', { method: 'POST', body: fd });
                const r = await res.json();
                if (r.status === 'success') {
                    fetchData(false);
                } else {
                    alert(r.message || 'Gagal');
                }
            } catch (err) {
                console.error(err);
            } finally {
                if (card) card.style.opacity = '1';
            }
        }

        fetchData();
        setInterval(() => fetchData(false), 30000);
    </script>
</body>
</html>
