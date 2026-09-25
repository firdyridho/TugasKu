<?php
require_once __DIR__ . '/config.php';
$loggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Terbaru & Catatan Rilis - TugasKu</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/favicon.ico">

    <meta name="description" content="Riwayat pembaruan fitur, peningkatan performa, dan catatan rilis versi terbaru platform TugasKu.">
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
        }

        body.changelog-body {
            background: #f8fafc;
            color: #1e293b;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
        }

        .cl-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            padding: 0.9rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cl-nav-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .cl-btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.88rem;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            padding: 0.45rem 0.95rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: all 150ms ease;
        }

        .cl-btn-back:hover {
            color: var(--blue-600);
            border-color: var(--blue-200);
            background: var(--blue-50);
        }

        .cl-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.88rem;
            font-weight: 600;
            color: #ffffff;
            background: var(--blue-600);
            text-decoration: none;
            padding: 0.45rem 1.1rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.25);
            transition: all 150ms ease;
        }

        .cl-btn-primary:hover {
            background: var(--blue-700);
            transform: translateY(-1px);
        }

        .cl-hero {
            background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 4.5rem 2rem 3.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cl-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(37, 99, 235, 0.2);
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--blue-700);
            letter-spacing: 0.03em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .cl-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulseGreen 1.8s infinite;
        }

        @keyframes pulseGreen {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .cl-title {
            font-size: clamp(2rem, 3.8vw, 2.9rem);
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.03em;
            margin: 0 0 0.75rem;
        }

        .cl-subtitle {
            font-size: 1.05rem;
            color: #64748b;
            max-width: 620px;
            margin: 0 auto 1.5rem;
            line-height: 1.6;
        }

        .cl-container {
            max-width: 920px;
            margin: 0 auto;
            padding: 3.5rem 1.5rem 6rem;
        }

        /* TIMELINE */
        .cl-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .cl-timeline::before {
            content: '';
            position: absolute;
            top: 10px;
            bottom: 0;
            left: 7px;
            width: 2px;
            background: #e2e8f0;
        }

        .cl-item {
            position: relative;
            margin-bottom: 3.5rem;
        }

        .cl-item:last-child {
            margin-bottom: 0;
        }

        .cl-item-dot {
            position: absolute;
            left: -2rem;
            top: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #ffffff;
            border: 3px solid var(--blue-600);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
            z-index: 2;
        }

        .cl-item.latest .cl-item-dot {
            background: var(--blue-600);
            border-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.35);
        }

        .cl-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 2.25rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: transform 200ms ease, box-shadow 200ms ease;
        }

        .cl-card:hover {
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.08);
            transform: translateY(-2px);
        }

        .cl-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .cl-version-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .cl-version {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .cl-badge-latest {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .cl-date {
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .cl-summary {
            font-size: 0.98rem;
            line-height: 1.65;
            color: #475569;
            margin: 0 0 1.5rem;
        }

        .cl-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 1.25rem 0 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cl-feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .cl-feature-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.93rem;
            line-height: 1.6;
            color: #334155;
        }

        .cl-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            white-space: nowrap;
            letter-spacing: 0.02em;
            flex-shrink: 0;
            margin-top: 0.15rem;
        }

        .cl-tag.new {
            background: rgba(37, 99, 235, 0.1);
            color: var(--blue-700);
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        .cl-tag.security {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .cl-tag.improvement {
            background: rgba(139, 92, 246, 0.1);
            color: #7c3aed;
            border: 1px solid rgba(139, 92, 246, 0.2);
        }

        .cl-tag.fix {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .cl-feature-desc strong {
            color: #0f172a;
        }
    </style>
</head>
<body class="changelog-body">

    <!-- NAVIGATION -->
    <nav class="cl-nav">
        <a href="<?= BASE_URL ?>/" class="brand-logo">
            Tugas<span class="logo-accent">Ku</span><span class="logo-dot">.</span>
        </a>
        <div class="cl-nav-actions">
            <a href="<?= BASE_URL ?>/" class="cl-btn-back">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali ke Beranda
            </a>
            <?php if ($loggedIn): ?>
                <a href="<?= BASE_URL ?>/dashboard/" class="cl-btn-primary">
                    Buka Dashboard
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login" class="cl-btn-primary">
                    Masuk Akun
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- HERO -->
    <header class="cl-hero">
        <div class="cl-badge">
            <span class="cl-pulse-dot"></span>
            Catatan Rilis & Pembaruan
        </div>
        <h1 class="cl-title">Update Terbaru TugasKu</h1>
        <p class="cl-subtitle">
            Transparansi pengembangan fitur, penyempurnaan desain antarmuka, dan peningkatan keamanan untuk mendukung kelancaran studi mahasiswa.
        </p>
    </header>

    <!-- TIMELINE CONTAINER -->
    <main class="cl-container">
        <div class="cl-timeline">

            <!-- VERSION 2.4 (LATEST) -->
            <div class="cl-item latest">
                <div class="cl-item-dot"></div>
                <div class="cl-card">
                    <div class="cl-header">
                        <div class="cl-version-group">
                            <span class="cl-version">Versi 2.4</span>
                            <span class="cl-badge-latest">Rilis Terbaru</span>
                        </div>
                        <span class="cl-date">21 September 2026</span>
                    </div>
                    <p class="cl-summary">
                        Pembaruan besar yang menghadirkan fitur ekspor spreadsheet terstruktur, peningkatan keamanan akun dengan meteran kekuatan kata sandi, indikator uptime sistem live, panduan onboarding interaktif, serta perombakan timetable jadwal kuliah yang semakin rapi.
                    </p>

                    <div class="cl-section-title">Fitur Baru &amp; Peningkatan</div>
                    <ul class="cl-feature-list">
                        <li class="cl-feature-item">
                            <span class="cl-tag new">FITUR BARU</span>
                            <div class="cl-feature-desc">
                                <strong>Ekspor Google Sheets (CSV Kompatibel):</strong> Unduh seluruh data tugas kuliah, kegiatan organisasi, jadwal mandiri, atau kombinasi semuanya dengan 5 pilihan filter dan encoding UTF-8 BOM agar langsung rapi dibuka di Google Sheets atau Microsoft Excel.
                            </div>
                        </li>
                        <li class="cl-feature-item">
                            <span class="cl-tag security">KEAMANAN</span>
                            <div class="cl-feature-desc">
                                <strong>Pengaturan Profil & Keamanan Akun:</strong> Halaman pengaturan baru untuk memperbarui NIM, jurusan, angkatan, serta ganti password aman dengan verifikasi password lama, meteran kekuatan kata sandi visual dinamis, dan tombol pengalih mata (eye toggle).
                            </div>
                        </li>
                        <li class="cl-feature-item">
                            <span class="cl-tag fix">PERBAIKAN</span>
                            <div class="cl-feature-desc">
                                <strong>Redesign Timetable Kuliah 7-Hari:</strong> Menghilangkan glitch penumpukan teks ("Senin 1", "Selasa 2"). Kini hadir dalam format kartu jadwal harian yang teratur dari Senin hingga Minggu, lengkap dengan sorotan penanda "Hari Ini".
                            </div>
                        </li>
                        <li class="cl-feature-item">
                            <span class="cl-tag new">FITUR BARU</span>
                            <div class="cl-feature-desc">
                                <strong>Panduan Interaktif Mahasiswa Baru (Onboarding Tour):</strong> Walkthrough 6-langkah otomatis yang menyambut mahasiswa baru saat pertama kali membuka dashboard, lengkap dengan ilustrasi dan tips penggunaan.
                            </div>
                        </li>
                        <li class="cl-feature-item">
                            <span class="cl-tag new">FITUR BARU</span>
                            <div class="cl-feature-desc">
                                <strong>Monitor Status Sistem & Uptime Realtime:</strong> Indikator ketersediaan server 99.98%, status basis data, dan pengukur latensi langsung dari halaman muka dan panel status.
                            </div>
                        </li>
                        <li class="cl-feature-item">
                            <span class="cl-tag improvement">LEGAL</span>
                            <div class="cl-feature-desc">
                                <strong>Halaman Kebijakan Privasi Resmi:</strong> Dokumentasi komprehensif mengenai transparansi pengolahan data mahasiswa, prinsip bebas iklan, dan hak privasi pengguna.
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- VERSION 2.3 -->
            <div class="cl-item">
                <div class="cl-item-dot"></div>
                <div class="cl-card">
                    <div class="cl-header">
                        <div class="cl-version-group">
                            <span class="cl-version">Versi 2.3</span>
                        </div>
                        <span class="cl-date">12 Agustus 2026</span>
                    </div>
                    <p class="cl-summary">
                        Peningkatan navigasi kalender akademik, penyaringan kategori tugas, serta optimasi antarmuka peramban seluler (mobile devices).
                    </p>

                    <div class="cl-section-title">Sorotan Pembaruan</div>
                    <ul class="cl-feature-list">
                        <li class="cl-feature-item">
                            <span class="cl-tag new">FITUR BARU</span>
                            <div class="cl-feature-desc">
                                <strong>Navigasi Mingguan Kalender:</strong> Navigasi minggu per minggu fleksibel dengan tombol Minggu Lalu / Minggu Depan dan kalkulasi rentang tanggal dinamis.
                            </div>
                        </li>
                        <li class="cl-feature-item">
                            <span class="cl-tag improvement">DESAIN</span>
                            <div class="cl-feature-desc">
                                <strong>Mobile Bottom Navigation:</strong> Bilah navigasi bawah tetap (sticky bottom nav) khusus smartphone untuk akses secepat kilat ke menu Dashboard, Organisasi, Kuliah, Jadwal, dan Kalender.
                            </div>
                        </li>
                        <li class="cl-feature-item">
                            <span class="cl-tag improvement">PENINGKATAN</span>
                            <div class="cl-feature-desc">
                                <strong>Pencarian Cepat & Kategori Warna:</strong> Filter instan kegiatan berdasarkan kategori kuliah (biru), organisasi (hijau), dan mandiri (ungu).
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- VERSION 2.2 -->
            <div class="cl-item">
                <div class="cl-item-dot"></div>
                <div class="cl-card">
                    <div class="cl-header">
                        <div class="cl-version-group">
                            <span class="cl-version">Versi 2.2</span>
                        </div>
                        <span class="cl-date">28 Juli 2026</span>
                    </div>
                    <p class="cl-summary">
                        Peluncuran sistem pintar pelacak tenggat waktu (deadline reminder) dan dashboard ringkasan statistik kegiatan mahasiswa.
                    </p>

                    <div class="cl-section-title">Sorotan Pembaruan</div>
                    <ul class="cl-feature-list">
                        <li class="cl-feature-item">
                            <span class="cl-tag new">FITUR BARU</span>
                            <div class="cl-feature-desc">
                                <strong>Indikator Urgensi Tenggat Waktu Otomatis:</strong> Label dinamis otomatis menghitung sisa waktu pengerjaan tugas (Terlewat, Hari Ini, Besok, 3 Hari Lagi) untuk memprioritaskan tugas terpenting.
                            </div>
                        </li>
                        <li class="cl-feature-item">
                            <span class="cl-tag improvement">PENINGKATAN</span>
                            <div class="cl-feature-desc">
                                <strong>Notifikasi Flash dengan Progress Auto-Dismiss:</strong> Pesan sukses dan peringatan kini dilengkapi bilah progres waktu 5 detik sebelum menutup secara halus.
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- VERSION 2.0 -->
            <div class="cl-item">
                <div class="cl-item-dot"></div>
                <div class="cl-card">
                    <div class="cl-header">
                        <div class="cl-version-group">
                            <span class="cl-version">Versi 2.0</span>
                        </div>
                        <span class="cl-date">10 Mei 2026</span>
                    </div>
                    <p class="cl-summary">
                        Kelahiran arsitektur baru TugasKu yang menggabungkan manajemen kepanitiaan organisasi dan perkuliahan dalam satu ekosistem terpadu berdesain modern.
                    </p>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer style="background:#ffffff; border-top:1px solid #e2e8f0; padding:2rem 2rem; text-align:center; color:#94a3b8; font-size:0.85rem;">
        <div style="font-family:'Newsreader',serif; font-size:1.2rem; font-weight:600; color:#0f172a; margin-bottom:0.4rem;">
            Tugas<span style="font-style:italic; color:#2563eb;">Ku</span>.
        </div>
        <p style="margin:0 0 0.5rem;">Sistem Manajemen Kegiatan & Produktivitas Mahasiswa Modern.</p>
        <p style="margin:0;">&copy; 2026 TugasKu. Seluruh hak cipta dilindungi undang-undang.</p>
    </footer>

</body>
</html>

