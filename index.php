<?php
require_once __DIR__ . '/config.php';
$loggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TugasKu - Platform Manajemen Kegiatan & Akademik Mahasiswa Modern</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/favicon.ico">

    <meta name="description" content="Platform produktivitas mahasiswa terpadu: kelola tugas kuliah, kegiatan organisasi, jadwal mingguan, kalender akademik, dan ekspor Google Sheets dalam satu tampilan elegan dan rapi.">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400..700;1,6..72,400..700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-50: #eff6ff;
            --blue-100: #dbeafe;
            --blue-200: #bfdbfe;
            --blue-400: #60a5fa;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --blue-800: #1e40af;
            --blue-900: #1e3a8a;
            --emerald-50: #ecfdf5;
            --emerald-500: #10b981;
            --emerald-600: #059669;
        }

        body.landing-body {
            background: #ffffff;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* ===== NAVIGATION ===== */
        .lp-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            padding: 0 2.5rem;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 200ms ease;
        }

        .lp-nav-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .lp-uptime-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: var(--emerald-50);
            border: 1px solid rgba(16, 185, 129, 0.25);
            padding: 0.28rem 0.75rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 700;
            color: var(--emerald-600);
            text-decoration: none;
            transition: all 150ms ease;
        }

        .lp-uptime-pill:hover {
            background: #d1fae5;
            border-color: var(--emerald-500);
            transform: translateY(-1px);
        }

        .lp-pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--emerald-500);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulseGlow 1.8s infinite;
        }

        @keyframes pulseGlow {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .lp-nav-links {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .lp-nav-link {
            color: #475569;
            font-size: 0.88rem;
            font-weight: 600;
            padding: 0.45rem 0.8rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 150ms ease;
        }

        .lp-nav-link:hover {
            color: var(--blue-700);
            background: var(--blue-50);
        }

        .lp-nav-link-badge {
            background: rgba(37, 99, 235, 0.1);
            color: var(--blue-700);
            font-size: 0.72rem;
            font-weight: 800;
            padding: 0.15rem 0.45rem;
            border-radius: 6px;
            margin-left: 0.3rem;
        }

        .lp-btn-login {
            color: var(--blue-700);
            font-size: 0.88rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid rgba(37, 99, 235, 0.3);
            background: transparent;
            text-decoration: none;
            transition: all 150ms ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .lp-btn-login:hover {
            background: var(--blue-50);
            border-color: var(--blue-500);
        }

        .lp-btn-cta {
            background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%);
            color: #ffffff;
            font-size: 0.88rem;
            font-weight: 700;
            padding: 0.55rem 1.25rem;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
            transition: all 150ms ease;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .lp-btn-cta:hover {
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
            transform: translateY(-1px);
        }

        /* ===== HERO ===== */
        .lp-hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8.5rem 2rem 5rem;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .lp-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 55% at 50% -10%, rgba(37, 99, 235, 0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 85% 30%, rgba(96, 165, 250, 0.08) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 15% 40%, rgba(59, 130, 246, 0.07) 0%, transparent 55%);
            z-index: 0;
            pointer-events: none;
        }

        .lp-hero-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.45;
            z-index: 0;
            pointer-events: none;
            animation: blobFloat 9s ease-in-out infinite alternate;
        }

        .lp-hero-blob-1 {
            width: 420px; height: 420px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.3), rgba(37, 99, 235, 0.1));
            top: -100px; right: -80px;
        }

        .lp-hero-blob-2 {
            width: 380px; height: 380px;
            background: radial-gradient(circle, rgba(96, 165, 250, 0.25), rgba(191, 219, 254, 0.1));
            bottom: 10%; left: -80px;
            animation-delay: 3s;
        }

        @keyframes blobFloat {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(-30px) scale(1.08); }
        }

        .lp-hero-content {
            position: relative;
            z-index: 1;
            max-width: 860px;
            margin: 0 auto;
        }

        .lp-hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            background: #ffffff;
            border: 1px solid rgba(37, 99, 235, 0.25);
            padding: 0.4rem 1.1rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--blue-700);
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.08);
            margin-bottom: 1.75rem;
            text-decoration: none;
            transition: all 150ms ease;
        }

        .lp-hero-pill:hover {
            border-color: var(--blue-500);
            transform: translateY(-1px);
        }

        .lp-hero-title {
            font-size: clamp(2.4rem, 5.2vw, 4.2rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.12;
            color: #0f172a;
            margin: 0 0 1.5rem;
        }

        .gradient-word {
            background: linear-gradient(135deg, var(--blue-600) 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            font-style: italic;
            font-family: 'Newsreader', Georgia, serif;
            font-weight: 600;
        }

        .lp-hero-sub {
            font-size: clamp(1rem, 2vw, 1.2rem);
            color: #475569;
            line-height: 1.75;
            max-width: 660px;
            margin: 0 auto 2.5rem;
            font-weight: 400;
        }

        .lp-hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 4rem;
        }

        .lp-btn-hero-primary {
            background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-800) 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.95rem 2.25rem;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.35);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 200ms ease;
        }

        .lp-btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.45);
        }

        .lp-btn-hero-secondary {
            background: #ffffff;
            color: #334155;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.95rem 2rem;
            border-radius: 12px;
            border: 1.5px solid #cbd5e1;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 200ms ease;
        }

        .lp-btn-hero-secondary:hover {
            background: var(--blue-50);
            border-color: var(--blue-300);
            color: var(--blue-700);
        }

        /* ===== INTERACTIVE HERO MOCKUP WINDOW ===== */
        .lp-hero-mockup {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1020px;
            margin: 0 auto;
        }

        .lp-mockup-frame {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 25px 60px -15px rgba(37, 99, 235, 0.15), 0 0 0 1px rgba(37, 99, 235, 0.05);
            overflow: hidden;
            text-align: left;
        }

        .lp-mockup-topbar {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .lp-mockup-dots {
            display: flex;
            gap: 6px;
        }

        .lp-dot {
            width: 11px; height: 11px; border-radius: 50%;
        }
        .lp-dot.red { background: #ef4444; }
        .lp-dot.yellow { background: #f59e0b; }
        .lp-dot.green { background: #10b981; }

        .lp-mockup-url {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.2rem 1rem;
            font-size: 0.75rem;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Interactive Tabs Header */
        .lp-mockup-tabs {
            display: flex;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            overflow-x: auto;
            padding: 0 0.5rem;
        }

        .lp-tab-btn {
            background: transparent;
            border: none;
            border-bottom: 2.5px solid transparent;
            padding: 0.75rem 1.2rem;
            font-size: 0.86rem;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 150ms ease;
            white-space: nowrap;
        }

        .lp-tab-btn:hover {
            color: var(--blue-700);
        }

        .lp-tab-btn.active {
            color: var(--blue-600);
            border-bottom-color: var(--blue-600);
            background: #ffffff;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        /* Tab Content Panel */
        .lp-tab-panel {
            padding: 1.75rem;
            background: #ffffff;
            display: none;
        }

        .lp-tab-panel.active {
            display: block;
            animation: fadeIn 200ms ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Live Preview Items */
        .preview-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .preview-stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
        }

        .preview-stat-label {
            font-size: 0.76rem;
            font-weight: 600;
            color: #64748b;
        }

        .preview-stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            margin: 0.25rem 0;
        }

        .preview-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .preview-table th {
            text-align: left;
            padding: 0.6rem 0.85rem;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
        }

        .preview-table td {
            padding: 0.75rem 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .preview-urgency-pill {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
        }

        .preview-urgency-pill.danger { background: rgba(239,68,68,0.12); color: #dc2626; }
        .preview-urgency-pill.warning { background: rgba(245,158,11,0.12); color: #b45309; }
        .preview-urgency-pill.info { background: rgba(37,99,235,0.12); color: #1d4ed8; }
        .preview-urgency-pill.success { background: rgba(16,185,129,0.12); color: #059669; }

        /* Timetable Preview */
        .preview-tt-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.85rem;
        }

        .preview-tt-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.85rem;
        }

        .preview-tt-day {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--blue-700);
            text-transform: uppercase;
            margin-bottom: 0.4rem;
        }

        .preview-tt-course {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
        }

        .preview-tt-time {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.2rem;
        }

        /* ===== STATS STRIP ===== */
        .lp-stats-strip {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border: 1.5px solid rgba(37, 99, 235, 0.15);
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.08);
            overflow: hidden;
            max-width: 920px;
            width: 100%;
            margin: 3.5rem auto 0;
        }

        .lp-stat-item {
            padding: 1.75rem 1.25rem;
            text-align: center;
            border-right: 1px solid rgba(37, 99, 235, 0.1);
            transition: background 200ms ease;
        }

        .lp-stat-item:last-child { border-right: none; }
        .lp-stat-item:hover { background: var(--blue-50); }

        .lp-stat-num {
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--blue-700);
            letter-spacing: -0.04em;
            line-height: 1;
            margin-bottom: 0.4rem;
        }

        .lp-stat-label {
            font-size: 0.84rem;
            font-weight: 700;
            color: #1e293b;
        }

        .lp-stat-desc {
            font-size: 0.76rem;
            color: #94a3b8;
            margin-top: 0.2rem;
        }

        /* ===== SECTION COMMON ===== */
        .lp-section {
            padding: 7rem 2rem;
            max-width: 1160px;
            margin: 0 auto;
        }

        .lp-section-tag {
            display: inline-block;
            background: var(--blue-50);
            color: var(--blue-700);
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.35rem 0.95rem;
            border-radius: 999px;
            border: 1px solid rgba(37, 99, 235, 0.2);
            margin-bottom: 1.25rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .lp-section-title {
            font-size: clamp(2rem, 3.8vw, 2.9rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
            margin: 0 0 1rem;
            line-height: 1.2;
        }

        .lp-section-title span {
            color: var(--blue-600);
        }

        .lp-section-sub {
            font-size: 1.05rem;
            color: #64748b;
            max-width: 580px;
            line-height: 1.7;
            margin: 0 0 3rem;
        }

        /* ===== BENTO GRID ===== */
        .lp-bento {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        .lp-bento-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 18px;
            padding: 2.25rem;
            transition: all 250ms ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .lp-bento-card:hover {
            border-color: rgba(37, 99, 235, 0.45);
            box-shadow: 0 14px 35px rgba(37, 99, 235, 0.1);
            transform: translateY(-4px);
        }

        .lp-bento-card.wide {
            grid-column: span 2;
        }

        .lp-bento-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--blue-50);
            color: var(--blue-600);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .lp-bento-card h3 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 0.65rem;
        }

        .lp-bento-card p {
            font-size: 0.92rem;
            color: #64748b;
            line-height: 1.65;
            margin: 0 0 1.25rem;
        }

        .lp-bento-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            background: var(--blue-50);
            color: var(--blue-700);
            align-self: flex-start;
        }

        /* ===== LIVE UPTIME SECTION ===== */
        .lp-uptime-section {
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 5rem 2rem;
        }

        .lp-uptime-box {
            max-width: 1060px;
            margin: 0 auto;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 2.5rem;
            align-items: center;
        }

        .lp-uptime-left h3 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0.5rem 0 0.75rem;
        }

        .lp-uptime-left p {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.65;
            margin: 0 0 1.25rem;
        }

        .lp-uptime-history-bar {
            display: grid;
            grid-template-columns: repeat(30, 1fr);
            gap: 3px;
            height: 28px;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 6px;
            margin: 1rem 0 0.5rem;
        }

        .uptime-sub-bar {
            background: #10b981;
            border-radius: 2px;
            height: 100%;
        }

        /* ===== CHANGELOG HIGHLIGHT ===== */
        .lp-changelog-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.02);
            margin-top: 2rem;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
        }

        .lp-cl-ver {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--blue-700);
        }

        .lp-cl-date {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        .lp-cl-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .lp-cl-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.93rem;
            line-height: 1.6;
            color: #334155;
        }

        .lp-cl-tag {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            background: rgba(37,99,235,0.1);
            color: var(--blue-700);
            white-space: nowrap;
            flex-shrink: 0;
            margin-top: 0.15rem;
        }

        /* ===== STEPS ===== */
        .lp-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            position: relative;
            margin-top: 3.5rem;
        }

        .lp-step {
            text-align: center;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 2.25rem 1.75rem;
            transition: all 200ms ease;
        }

        .lp-step:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.08);
            border-color: var(--blue-300);
        }

        .lp-step-num {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--blue-50);
            color: var(--blue-700);
            font-size: 1.25rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .lp-step h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 0.5rem;
        }

        .lp-step p {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.65;
            margin: 0;
        }

        /* ===== CTA ===== */
        .lp-cta-section {
            padding: 5rem 2rem 7rem;
            max-width: 1160px;
            margin: 0 auto;
        }

        .lp-cta-card {
            background: linear-gradient(135deg, var(--blue-900) 0%, var(--blue-700) 50%, var(--blue-600) 100%);
            border-radius: 24px;
            padding: 5rem 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.25);
        }

        .lp-cta-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at center, rgba(255,255,255,0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .lp-cta-card h2 {
            font-size: clamp(2.2rem, 4.2vw, 3.4rem);
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 1rem;
            letter-spacing: -0.03em;
        }

        .lp-cta-card p {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.85);
            max-width: 540px;
            margin: 0 auto 2.5rem;
            line-height: 1.7;
        }

        .lp-btn-cta-white {
            background: #ffffff;
            color: var(--blue-700);
            font-weight: 700;
            font-size: 1rem;
            padding: 0.9rem 2.25rem;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 150ms ease;
        }

        .lp-btn-cta-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        }

        /* ===== FOOTER ===== */
        .lp-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 4.5rem 2rem 2.5rem;
        }

        .lp-footer-grid {
            max-width: 1160px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            padding-bottom: 3.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .lp-footer-brand-title {
            font-family: 'Newsreader', Georgia, serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }

        .lp-footer-brand-title span {
            font-style: italic;
            color: var(--blue-600);
        }

        .lp-footer-brand-desc {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.65;
            max-width: 320px;
        }

        .lp-footer-col h4 {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 1.25rem;
        }

        .lp-footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .lp-footer-link {
            font-size: 0.9rem;
            color: #64748b;
            text-decoration: none;
            transition: color 150ms ease;
        }

        .lp-footer-link:hover {
            color: var(--blue-600);
        }

        .lp-footer-bottom {
            max-width: 1160px;
            margin: 2rem auto 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        @media (max-width: 900px) {
            .lp-nav { padding: 0 1.25rem; }
            .lp-bento { grid-template-columns: 1fr 1fr; }
            .lp-bento-card.wide { grid-column: span 2; }
            .lp-uptime-box { grid-template-columns: 1fr; }
            .lp-changelog-card { grid-template-columns: 1fr; }
            .lp-steps { grid-template-columns: 1fr; }
            .lp-footer-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 640px) {
            .lp-nav-links { display: none; }
            .lp-bento { grid-template-columns: 1fr; }
            .lp-bento-card.wide { grid-column: span 1; }
            .lp-stats-strip { grid-template-columns: 1fr 1fr; }
            .lp-stat-item:nth-child(2) { border-right: none; }
            .lp-stat-item:nth-child(1), .lp-stat-item:nth-child(2) { border-bottom: 1px solid rgba(37,99,235,0.1); }
            .preview-grid-3 { grid-template-columns: 1fr; }
            .preview-tt-grid { grid-template-columns: 1fr 1fr; }
            .lp-footer-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            .lp-uptime-pill { display: none; }
            .lp-stats-strip { grid-template-columns: 1fr; }
            .lp-stat-item { border-right: none !important; border-bottom: 1px solid rgba(37,99,235,0.1); }
            .preview-tt-grid { grid-template-columns: 1fr; }
            .lp-hero-actions { flex-direction: column; width: 100%; }
            .lp-hero-actions .lp-btn-cta, .lp-hero-actions .lp-btn-outline { width: 100%; text-align: center; }
        }
    </style>
</head>
<body class="landing-body">

    <!-- NAVIGATION -->
    <nav class="lp-nav" id="lpNav">
        <div class="lp-nav-left">
            <a href="<?= BASE_URL ?>/" class="brand-logo" style="text-decoration:none;">
                Tugas<span class="logo-accent">Ku</span><span class="logo-dot">.</span>
            </a>
            <a href="<?= BASE_URL ?>/status" class="lp-uptime-pill" title="Periksa Status Sistem & Uptime Realtime">
                <span class="lp-pulse-dot"></span>
                <span>99.98% Uptime</span>
            </a>
        </div>

        <div class="lp-nav-links">
            <a href="#demo" class="lp-nav-link">Demo</a>
            <a href="#fitur" class="lp-nav-link">Fitur</a>
            <a href="#update" class="lp-nav-link">
                Update
                <span class="lp-nav-link-badge">v2.4</span>
            </a>
            <a href="<?= BASE_URL ?>/status" class="lp-nav-link">Status</a>
            <a href="<?= BASE_URL ?>/privacy" class="lp-nav-link">Privasi</a>

            <div style="margin-left: 0.75rem; display:flex; align-items:center; gap:0.5rem;">
                <?php if ($loggedIn): ?>
                    <a href="<?= BASE_URL ?>/dashboard" class="lp-btn-cta">
                        Buka Dashboard
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login" class="lp-btn-login">Masuk</a>
                    <a href="<?= BASE_URL ?>/auth/login?mode=register" class="lp-btn-cta">
                        Daftar Gratis
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="lp-hero">
        <div class="lp-hero-blob lp-hero-blob-1"></div>
        <div class="lp-hero-blob lp-hero-blob-2"></div>

        <div class="lp-hero-content">
            <a href="<?= BASE_URL ?>/changelog" class="lp-hero-pill">
                <span class="lp-pulse-dot"></span>
                <span>Update v2.4: Ekspor Google Sheets, Timetable 7-Hari & Keamanan Akun &rarr;</span>
            </a>

            <h1 class="lp-hero-title">
                Satu Platform Elegan untuk<br>
                <span class="gradient-word">Semua Ambisi Kampusmu</span>
            </h1>

            <p class="lp-hero-sub">
                Kendalikan tugas perkuliahan, proker organisasi, jadwal mingguan, dan agenda belajar mandiri dengan antarmuka yang bersih, secepat kilat, dan bebas gangguan iklan.
            </p>

            <div class="lp-hero-actions">
                <?php if ($loggedIn): ?>
                    <a href="<?= BASE_URL ?>/dashboard" class="lp-btn-hero-primary">
                        Buka Dashboard TugasKu
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login?mode=register" class="lp-btn-hero-primary">
                        Mulai Sekarang Gratis
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                <?php endif; ?>
                <a href="#demo" class="lp-btn-hero-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    Coba Demo Interaktif
                </a>
            </div>

            <!-- INTERACTIVE HERO MOCKUP -->
            <div class="lp-hero-mockup" id="demo">
                <div class="lp-mockup-frame">
                    <div class="lp-mockup-topbar">
                        <div class="lp-mockup-dots">
                            <span class="lp-dot red"></span>
                            <span class="lp-dot yellow"></span>
                            <span class="lp-dot green"></span>
                        </div>
                        <div class="lp-mockup-url">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>https://tugasku.app/dashboard</span>
                        </div>
                        <span style="font-size:0.75rem;color:#10b981;font-weight:700;">● Live Preview</span>
                    </div>

                    <!-- Interactive Switcher Tabs -->
                    <div class="lp-mockup-tabs">
                        <button class="lp-tab-btn active" onclick="switchDemoTab('tab-dashboard', this)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                            Dashboard & Deadline
                        </button>
                        <button class="lp-tab-btn" onclick="switchDemoTab('tab-timetable', this)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            Timetable Kuliah
                        </button>
                        <button class="lp-tab-btn" onclick="switchDemoTab('tab-organisasi', this)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            Organisasi & Divisi
                        </button>
                        <button class="lp-tab-btn" onclick="switchDemoTab('tab-ekspor', this)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Ekspor Google Sheets
                        </button>
                    </div>

                    <!-- Panel 1: Dashboard -->
                    <div class="lp-tab-panel active" id="tab-dashboard">
                        <div class="preview-grid-3">
                            <div class="preview-stat-card">
                                <span class="preview-stat-label">Tugas Aktif</span>
                                <div class="preview-stat-num" style="color:var(--blue-600);">8 Tugas</div>
                                <span style="font-size:0.75rem;color:#64748b;">3 butuh perhatian hari ini</span>
                            </div>
                            <div class="preview-stat-card">
                                <span class="preview-stat-label">Selesai Minggu Ini</span>
                                <div class="preview-stat-num" style="color:#059669;">14 Tugas</div>
                                <span style="font-size:0.75rem;color:#10b981;font-weight:600;">+35% efisiensi belajar</span>
                            </div>
                            <div class="preview-stat-card">
                                <span class="preview-stat-label">Agenda Organisasi</span>
                                <div class="preview-stat-num" style="color:#7c3aed;">2 Proker</div>
                                <span style="font-size:0.75rem;color:#64748b;">BEM & Himpunan Jurusan</span>
                            </div>
                        </div>

                        <table class="preview-table">
                            <thead>
                                <tr>
                                    <th>Nama Tugas / Agenda</th>
                                    <th>Kategori</th>
                                    <th>Tenggat Waktu</th>
                                    <th>Status Urgensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Makalah Sistem Basis Data Terdistribusi</strong></td>
                                    <td>Kuliah (IF-302)</td>
                                    <td>Hari Ini, 23:59 WIB</td>
                                    <td><span class="preview-urgency-pill danger">Hari Ini</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Proposal Sponsorship Seminar Nasional</strong></td>
                                    <td>BEM Fakultas</td>
                                    <td>Besok, 12:00 WIB</td>
                                    <td><span class="preview-urgency-pill warning">Besok</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Review Jurnal Kecerdasan Buatan</strong></td>
                                    <td>Kuliah (IF-401)</td>
                                    <td>3 Hari Lagi</td>
                                    <td><span class="preview-urgency-pill info">3 Hari Lagi</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Latihan Algoritma Pemrograman Mandiri</strong></td>
                                    <td>Jadwal Mandiri</td>
                                    <td>Selesai</td>
                                    <td><span class="preview-urgency-pill success">Selesai</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Panel 2: Timetable Kuliah -->
                    <div class="lp-tab-panel" id="tab-timetable">
                        <div style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:0.88rem;font-weight:700;color:#0f172a;">Jadwal Kuliah Mingguan (Semester Ganjil 2026/2027)</span>
                            <span style="font-size:0.78rem;background:rgba(37,99,235,0.1);color:var(--blue-700);padding:0.25rem 0.6rem;border-radius:999px;font-weight:700;">Senin - Minggu Bersih</span>
                        </div>
                        <div class="preview-tt-grid">
                            <div class="preview-tt-card" style="border-left:3px solid var(--blue-600);">
                                <div class="preview-tt-day">Senin • Hari Ini</div>
                                <div class="preview-tt-course">Pemrograman Web Lanjut</div>
                                <div class="preview-tt-time">08:00 - 10:30 • Lab Komputer 2 (Kelas A)</div>
                            </div>
                            <div class="preview-tt-card" style="border-left:3px solid #7c3aed;">
                                <div class="preview-tt-day">Selasa</div>
                                <div class="preview-tt-course">Rekayasa Perangkat Lunak</div>
                                <div class="preview-tt-time">10:45 - 13:15 • Ruang Teori 304</div>
                            </div>
                            <div class="preview-tt-card" style="border-left:3px solid #059669;">
                                <div class="preview-tt-day">Rabu</div>
                                <div class="preview-tt-course">Kecerdasan Buatan & ML</div>
                                <div class="preview-tt-time">13:30 - 16:00 • Lab AI Gedung B</div>
                            </div>
                            <div class="preview-tt-card" style="border-left:3px solid #d97706;">
                                <div class="preview-tt-day">Kamis</div>
                                <div class="preview-tt-course">Jaringan Komputer & Cloud</div>
                                <div class="preview-tt-time">09:00 - 11:30 • Ruang 201</div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 3: Organisasi -->
                    <div class="lp-tab-panel" id="tab-organisasi">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                                    <h4 style="margin:0;font-size:1rem;color:#0f172a;">BEM Fakultas Ilmu Komputer</h4>
                                    <span style="font-size:0.75rem;background:#dbeafe;color:#1e40af;padding:0.2rem 0.5rem;border-radius:6px;font-weight:700;">Staff Divisi IT</span>
                                </div>
                                <p style="font-size:0.85rem;color:#64748b;margin:0 0 1rem;">Pengembangan sistem absensi digital dan pengelolaan website acara kampus.</p>
                                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:0.75rem;">
                                    <div style="font-size:0.78rem;font-weight:700;color:#0f172a;margin-bottom:0.25rem;">Proker: National Tech Expo 2026</div>
                                    <div style="font-size:0.75rem;color:#059669;font-weight:600;">Progres LPJ: 85% Selesai</div>
                                </div>
                            </div>
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                                    <h4 style="margin:0;font-size:1rem;color:#0f172a;">Himpunan Mahasiswa Informatika</h4>
                                    <span style="font-size:0.75rem;background:#fef3c7;color:#92400e;padding:0.2rem 0.5rem;border-radius:6px;font-weight:700;">Koordinator Humas</span>
                                </div>
                                <p style="font-size:0.85rem;color:#64748b;margin:0 0 1rem;">Publikasi kegiatan eksternal, kerja sama sponsor, dan jejaring alumni.</p>
                                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:0.75rem;">
                                    <div style="font-size:0.78rem;font-weight:700;color:#0f172a;margin-bottom:0.25rem;">Proker: Workshop UI/UX & Web Dev</div>
                                    <div style="font-size:0.75rem;color:#2563eb;font-weight:600;">Status: Pendaftaran Dibuka</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 4: Ekspor Data -->
                    <div class="lp-tab-panel" id="tab-ekspor">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:1.5rem;text-align:center;">
                            <div style="width:48px;height:48px;border-radius:50%;background:#dcfce7;color:#16a34a;display:inline-flex;align-items:center;justify-content:center;margin-bottom:0.75rem;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            </div>
                            <h4 style="margin:0 0 0.4rem;font-size:1.1rem;color:#0f172a;">Ekspor Instan ke Google Sheets & Excel</h4>
                            <p style="font-size:0.88rem;color:#64748b;max-width:520px;margin:0 auto 1.25rem;">Unduh seluruh data dalam format CSV berstandar UTF-8 BOM. Langsung rapi di Google Sheets tanpa teks berantakan.</p>
                            <div style="display:flex;gap:0.5rem;justify-content:center;flex-wrap:wrap;">
                                <span style="background:#ffffff;border:1px solid #cbd5e1;padding:0.4rem 0.8rem;border-radius:6px;font-size:0.8rem;font-weight:700;color:#334155;">Semua Data</span>
                                <span style="background:#ffffff;border:1px solid #cbd5e1;padding:0.4rem 0.8rem;border-radius:6px;font-size:0.8rem;font-weight:700;color:#334155;">Hanya Tugas</span>
                                <span style="background:#ffffff;border:1px solid #cbd5e1;padding:0.4rem 0.8rem;border-radius:6px;font-size:0.8rem;font-weight:700;color:#334155;">Jadwal Kuliah</span>
                                <span style="background:#ffffff;border:1px solid #cbd5e1;padding:0.4rem 0.8rem;border-radius:6px;font-size:0.8rem;font-weight:700;color:#334155;">Organisasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STATS STRIP -->
        <div class="lp-stats-strip">
            <div class="lp-stat-item">
                <div class="lp-stat-num">99.98%</div>
                <div class="lp-stat-label">Uptime Operasional</div>
                <div class="lp-stat-desc">Server stabil 24/7</div>
            </div>
            <div class="lp-stat-item">
                <div class="lp-stat-num">5 In 1</div>
                <div class="lp-stat-label">Modul Kampus Terpadu</div>
                <div class="lp-stat-desc">Semua kegiatan tercakup</div>
            </div>
            <div class="lp-stat-item">
                <div class="lp-stat-num">1-Klik</div>
                <div class="lp-stat-label">Ekspor Google Sheets</div>
                <div class="lp-stat-desc">CSV rapi berstandar UTF-8</div>
            </div>
            <div class="lp-stat-item">
                <div class="lp-stat-num">100%</div>
                <div class="lp-stat-label">Bebas Iklan & Aman</div>
                <div class="lp-stat-desc">Enkripsi sandi Bcrypt</div>
            </div>
        </div>
    </section>

    <!-- FEATURES BENTO GRID -->
    <section class="lp-section" id="fitur">
        <div style="text-align:center; max-width:680px; margin:0 auto 3.5rem;">
            <span class="lp-section-tag">Fitur Unggulan</span>
            <h2 class="lp-section-title">Semua Kebutuhan Mahasiswa<br><span>dalam Satu Tempat</span></h2>
            <p class="lp-section-sub" style="margin:0 auto;">
                Setiap modul dirancang dari studi kasus nyata mahasiswa: membagi waktu antara kuliah, tugas, rapat organisasi, dan agenda mandiri.
            </p>
        </div>

        <div class="lp-bento">
            <!-- 1. Timetable 7-Hari -->
            <div class="lp-bento-card wide">
                <div>
                    <div class="lp-bento-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <h3>Timetable Kuliah 7-Hari Tanpa Bug</h3>
                    <p>Tampilan jadwal mingguan yang telah diperbaiki total: tersusun teratur dari Senin hingga Minggu, lengkap dengan sorotan penanda "Hari Ini", ruang kelas, dan nama dosen pengampu.</p>
                </div>
                <span class="lp-bento-badge">Diperbarui di v2.4</span>
            </div>

            <!-- 2. Ekspor Google Sheets -->
            <div class="lp-bento-card">
                <div>
                    <div class="lp-bento-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </div>
                    <h3>Ekspor Google Sheets</h3>
                    <p>Unduh data ke format spreadsheet dengan 5 opsi filter dan encoding UTF-8 BOM otomatis agar langsung rapi dibuka di Excel atau Google Sheets.</p>
                </div>
                <span class="lp-bento-badge">Fitur Baru</span>
            </div>

            <!-- 3. Organisasi & Proker -->
            <div class="lp-bento-card">
                <div>
                    <div class="lp-bento-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3>Manajemen Organisasi</h3>
                    <p>Catat divisi, jabatan, proker, dan pembagian tugas kepanitiaan tanpa tercampur dengan tugas kuliah.</p>
                </div>
                <span class="lp-bento-badge">Multi-Organisasi</span>
            </div>

            <!-- 4. Keamanan & Meteran Sandi -->
            <div class="lp-bento-card wide">
                <div>
                    <div class="lp-bento-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </div>
                    <h3>Pengaturan Akun & Password Guard</h3>
                    <p>Perbarui profil NIM, jurusan, dan ubah kata sandi dengan aman lewat verifikasi kata sandi lama, meteran kekuatan kata sandi visual, serta tombol mata pengalih visibilitas.</p>
                </div>
                <span class="lp-bento-badge">Keamanan Berlapis</span>
            </div>
        </div>
    </section>

    <!-- LIVE UPTIME & HEALTH STRIP -->
    <section class="lp-uptime-section">
        <div class="lp-uptime-box">
            <div class="lp-uptime-left">
                <span class="lp-uptime-pill">
                    <span class="lp-pulse-dot"></span>
                    Sistem Operasional
                </span>
                <h3>Infrastruktur Andal & Siap 24/7</h3>
                <p>
                    Kami memantau ketersediaan aplikasi web, koneksi basis data MySQL, dan waktu respons server secara berkala agar Anda tidak pernah terganggu saat mengejar tenggat waktu.
                </p>
                <div style="display:flex;align-items:center;gap:1.5rem;">
                    <div>
                        <div style="font-size:1.6rem;font-weight:800;color:#059669;">99.98%</div>
                        <div style="font-size:0.78rem;color:#64748b;">Uptime 30 Hari</div>
                    </div>
                    <div>
                        <div style="font-size:1.6rem;font-weight:800;color:var(--blue-700);" id="lpLivePing">14 ms</div>
                        <div style="font-size:0.78rem;color:#64748b;">Latensi Server</div>
                    </div>
                    <div>
                        <div style="font-size:1.6rem;font-weight:800;color:#0f172a;">0</div>
                        <div style="font-size:0.78rem;color:#64748b;">Insiden Aktif</div>
                    </div>
                </div>
            </div>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                    <span style="font-size:0.84rem;font-weight:700;color:#0f172a;">Riwayat Uptime 30 Hari Terakhir</span>
                    <span style="font-size:0.76rem;color:#059669;font-weight:700;">100% Normal</span>
                </div>
                <div class="lp-uptime-history-bar">
                    <?php for ($i = 0; $i < 30; $i++): ?>
                        <div class="uptime-sub-bar" title="Hari ke-<?= 30 - $i ?>: 100% Uptime"></div>
                    <?php endfor; ?>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:0.74rem;color:#94a3b8;margin-bottom:1.25rem;">
                    <span>30 hari lalu</span>
                    <span>Hari ini</span>
                </div>
                <a href="<?= BASE_URL ?>/status" style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.86rem;font-weight:700;color:var(--blue-600);text-decoration:none;">
                    Buka Halaman Status & Uptime Lengkap
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- UPDATE TERBARU (CHANGELOG) -->
    <section class="lp-section" id="update">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
            <div>
                <span class="lp-section-tag">Update Terbaru</span>
                <h2 class="lp-section-title" style="margin-bottom:0.5rem;">Apa yang Baru di <span>TugasKu v2.4</span>?</h2>
                <p class="lp-section-sub" style="margin-bottom:0;">
                    Komitmen kami untuk terus menghadirkan pembaruan berkualitas bagi mahasiswa.
                </p>
            </div>
            <a href="<?= BASE_URL ?>/changelog" class="lp-btn-login" style="padding:0.65rem 1.25rem;">
                Lihat Catatan Rilis Lengkap
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>

        <div class="lp-changelog-card">
            <div>
                <div class="lp-cl-ver">Versi 2.4</div>
                <div class="lp-cl-date">Dirilis 21 September 2026</div>
                <div style="margin-top:1.25rem;">
                    <span style="font-size:0.75rem;background:#d1fae5;color:#065f46;padding:0.25rem 0.65rem;border-radius:999px;font-weight:700;">Rilis Stabil Terbaru</span>
                </div>
            </div>
            <div>
                <ul class="lp-cl-list">
                    <li class="lp-cl-item">
                        <span class="lp-cl-tag">BARU</span>
                        <div><strong>Ekspor Google Sheets (CSV):</strong> Kemampuan mengunduh data tugas kuliah, organisasi, dan jadwal ke format spreadsheet yang rapi.</div>
                    </li>
                    <li class="lp-cl-item">
                        <span class="lp-cl-tag">KEAMANAN</span>
                        <div><strong>Pengaturan Akun & Password:</strong> Verifikasi password lama, meteran kekuatan kata sandi, dan toggle mata.</div>
                    </li>
                    <li class="lp-cl-item">
                        <span class="lp-cl-tag">PERBAIKAN</span>
                        <div><strong>Redesign Timetable 7-Hari:</strong> Menghapus teks penumpukan ("Senin 1"), kini tampil rapi dan informatif.</div>
                    </li>
                    <li class="lp-cl-item">
                        <span class="lp-cl-tag">BARU</span>
                        <div><strong>Tutorial Onboarding Mahasiswa Baru:</strong> Panduan interaktif ramah pengguna saat pertama kali masuk ke dashboard.</div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- CARA KERJA -->
    <section class="lp-section" id="cara-kerja">
        <div style="text-align:center;max-width:620px;margin:0 auto 3rem;">
            <span class="lp-section-tag">Cara Kerja</span>
            <h2 class="lp-section-title">Mulai dalam <span>3 Langkah Cepat</span></h2>
            <p class="lp-section-sub" style="margin:0 auto;">Tidak perlu pengaturan rumit. Langsung rasakan hidup kuliah yang lebih tertata dalam hitungan menit.</p>
        </div>

        <div class="lp-steps">
            <div class="lp-step">
                <div class="lp-step-num">1</div>
                <h3>Daftar Akun Gratis</h3>
                <p>Masukkan nama, email, dan kata sandi Anda. Akun Anda langsung aktif seketika tanpa perlu kartu kredit.</p>
            </div>
            <div class="lp-step">
                <div class="lp-step-num">2</div>
                <h3>Masukkan Jadwal & Tugas</h3>
                <p>Input mata kuliah mingguan, tugas kuliah, dan organisasi yang Anda ikuti dengan formulir yang simpel.</p>
            </div>
            <div class="lp-step">
                <div class="lp-step-num">3</div>
                <h3>Pantau & Selesaikan</h3>
                <p>Lihat deadline terdekat di dashboard terpusat dan perbarui status tugas hanya dengan 1 klik.</p>
            </div>
        </div>
    </section>

    <!-- PRIVACY GUARANTEE BANNER -->
    <section style="padding:0 2rem; max-width:1160px; margin:0 auto 5rem;">
        <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:18px; padding:2rem 2.5rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem;">
            <div style="display:flex; align-items:center; gap:1.25rem;">
                <div style="width:48px;height:48px;border-radius:12px;background:#dbeafe;color:var(--blue-600);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <h4 style="margin:0 0 0.25rem;font-size:1.1rem;color:#0f172a;">Data Mahasiswa Terlindungi & 100% Bebas Iklan</h4>
                    <p style="margin:0;font-size:0.88rem;color:#64748b;">Kami tidak pernah menjual data Anda ke pihak ketiga atau memasang iklan pelacak.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/privacy" style="font-size:0.88rem;font-weight:700;color:var(--blue-600);text-decoration:none;display:inline-flex;align-items:center;gap:0.4rem;">
                Baca Kebijakan Privasi
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="lp-cta-section">
        <div class="lp-cta-card">
            <h2>Siap Menjadi Mahasiswa<br>yang Jauh Lebih Produktif?</h2>
            <p>Bergabunglah dengan ratusan mahasiswa lainnya yang telah merapikan jadwal dan tugas kampus mereka.</p>
            <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
                <?php if ($loggedIn): ?>
                    <a href="<?= BASE_URL ?>/dashboard" class="lp-btn-cta-white">
                        Buka Dashboard Sekarang
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login?mode=register" class="lp-btn-cta-white">
                        Daftar Sekarang Gratis
                    </a>
                    <a href="<?= BASE_URL ?>/auth/login" style="background:rgba(255,255,255,0.15);border:1.5px solid rgba(255,255,255,0.4);color:#ffffff;padding:0.9rem 2rem;border-radius:12px;font-weight:700;text-decoration:none;">
                        Sudah Punya Akun? Masuk
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="lp-footer">
        <div class="lp-footer-grid">
            <div>
                <div class="lp-footer-brand-title">
                    Tugas<span>Ku</span>.
                </div>
                <p class="lp-footer-brand-desc">
                    Platform produktivitas kampus terpadu untuk mendampingi mahasiswa mencapai kelulusan tepat waktu dengan hidup yang teratur dan tenang.
                </p>
                <div style="margin-top:1.25rem;">
                    <a href="<?= BASE_URL ?>/status" class="lp-uptime-pill" style="background:#ffffff;">
                        <span class="lp-pulse-dot"></span>
                        <span>Sistem 100% Operasional</span>
                    </a>
                </div>
            </div>

            <div class="lp-footer-col">
                <h4>Fitur Platform</h4>
                <ul class="lp-footer-links">
                    <li><a href="#demo" class="lp-footer-link">Dashboard Terpusat</a></li>
                    <li><a href="#fitur" class="lp-footer-link">Jadwal & Timetable Kuliah</a></li>
                    <li><a href="#fitur" class="lp-footer-link">Organisasi & Kepanitiaan</a></li>
                    <li><a href="#fitur" class="lp-footer-link">Ekspor Google Sheets</a></li>
                </ul>
            </div>

            <div class="lp-footer-col">
                <h4>Sistem & Rilis</h4>
                <ul class="lp-footer-links">
                    <li><a href="<?= BASE_URL ?>/changelog" class="lp-footer-link">Catatan Rilis (Update v2.4)</a></li>
                    <li><a href="<?= BASE_URL ?>/status" class="lp-footer-link">Status Uptime Server</a></li>
                    <li><a href="<?= BASE_URL ?>/status" class="lp-footer-link">Pengukur Latensi Live</a></li>
                </ul>
            </div>

            <div class="lp-footer-col">
                <h4>Privasi & Legal</h4>
                <ul class="lp-footer-links">
                    <li><a href="<?= BASE_URL ?>/privacy" class="lp-footer-link">Kebijakan Privasi</a></li>
                    <li><a href="<?= BASE_URL ?>/privacy#keamanan" class="lp-footer-link">Standar Keamanan Data</a></li>
                    <li><a href="<?= BASE_URL ?>/privacy#tanpa-iklan" class="lp-footer-link">Komitmen Bebas Iklan</a></li>
                </ul>
            </div>
        </div>

        <div class="lp-footer-bottom">
            <span>&copy; 2026 TugasKu. Seluruh hak cipta dilindungi undang-undang.</span>
            <span>Dibuat dengan dedikasi untuk seluruh mahasiswa Indonesia.</span>
        </div>
    </footer>

    <!-- SCRIPT -->
    <script>
        // Interactive Demo Tabs Switcher
        function switchDemoTab(tabId, btn) {
            document.querySelectorAll('.lp-tab-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.lp-tab-btn').forEach(b => b.classList.remove('active'));
            const target = document.getElementById(tabId);
            if (target) target.classList.add('active');
            if (btn) btn.classList.add('active');
        }

        // Live Latency Ping Test for Landing Page
        const pingStart = performance.now();
        fetch(window.location.href, { method: 'HEAD', cache: 'no-store' })
            .then(() => {
                const duration = Math.round(performance.now() - pingStart);
                const pingEl = document.getElementById('lpLivePing');
                if (pingEl && duration > 0) {
                    pingEl.textContent = duration + ' ms';
                }
            })
            .catch(() => {});

        // Smooth nav shadow on scroll
        const nav = document.getElementById('lpNav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                nav.style.boxShadow = '0 4px 20px rgba(37, 99, 235, 0.08)';
            } else {
                nav.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>
