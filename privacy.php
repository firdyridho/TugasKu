<?php
require_once __DIR__ . '/config.php';
$loggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi - TugasKu</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/favicon.ico">

    <meta name="description" content="Kebijakan privasi platform TugasKu. Memahami bagaimana kami menjaga kerahasiaan dan keamanan data kegiatan mahasiswa Anda.">
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

        body.legal-body {
            background: #f8fafc;
            color: #1e293b;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
        }

        .legal-nav {
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

        .legal-nav-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .legal-btn-back {
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

        .legal-btn-back:hover {
            color: var(--blue-600);
            border-color: var(--blue-200);
            background: var(--blue-50);
        }

        .legal-btn-primary {
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

        .legal-btn-primary:hover {
            background: var(--blue-700);
            transform: translateY(-1px);
        }

        .legal-hero {
            background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 4.5rem 2rem 3.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .legal-hero::before {
            content: '';
            position: absolute;
            top: -120px;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            height: 350px;
            background: radial-gradient(ellipse at center, rgba(37, 99, 235, 0.12) 0%, rgba(255,255,255,0) 70%);
            pointer-events: none;
        }

        .legal-badge {
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

        .legal-title {
            font-size: clamp(2rem, 3.8vw, 2.9rem);
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.03em;
            margin: 0 0 0.75rem;
        }

        .legal-subtitle {
            font-size: 1.05rem;
            color: #64748b;
            max-width: 620px;
            margin: 0 auto 1.5rem;
            line-height: 1.6;
        }

        .legal-meta {
            display: inline-flex;
            align-items: center;
            gap: 1.5rem;
            font-size: 0.84rem;
            color: #94a3b8;
        }

        .legal-meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .legal-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 3rem 1.5rem 6rem;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 3rem;
            align-items: start;
        }

        /* TOC SIDEBAR */
        .legal-toc {
            position: sticky;
            top: 86px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 18px rgba(0,0,0,0.02);
        }

        .legal-toc-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
            padding-bottom: 0.6rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .legal-toc-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .legal-toc-link {
            display: block;
            font-size: 0.88rem;
            color: #64748b;
            text-decoration: none;
            padding: 0.45rem 0.65rem;
            border-radius: 8px;
            transition: all 150ms ease;
            line-height: 1.4;
        }

        .legal-toc-link:hover, .legal-toc-link.active {
            color: var(--blue-700);
            background: var(--blue-50);
            font-weight: 600;
        }

        /* CONTENT */
        .legal-content {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .legal-section {
            padding-bottom: 2.75rem;
            margin-bottom: 2.75rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .legal-section:last-child {
            padding-bottom: 0;
            margin-bottom: 0;
            border-bottom: none;
        }

        .legal-sec-num {
            display: inline-block;
            color: var(--blue-600);
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 0.35rem;
        }

        .legal-sec-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin: 0 0 1rem;
        }

        .legal-content p {
            font-size: 0.96rem;
            line-height: 1.75;
            color: #475569;
            margin: 0 0 1rem;
        }

        .legal-card-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin: 1.25rem 0;
        }

        .legal-card-box h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legal-card-box p {
            font-size: 0.9rem;
            margin: 0;
            color: #64748b;
            line-height: 1.6;
        }

        .legal-list {
            padding-left: 1.25rem;
            margin: 0.75rem 0 1.25rem;
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .legal-list li {
            margin-bottom: 0.5rem;
        }

        .legal-pill-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .legal-pill {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
        }

        @media (max-width: 860px) {
            .legal-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .legal-toc {
                display: none;
            }
            .legal-content {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body class="legal-body">

    <!-- NAVIGATION -->
    <nav class="legal-nav">
        <a href="<?= BASE_URL ?>/" class="brand-logo">
            Tugas<span class="logo-accent">Ku</span><span class="logo-dot">.</span>
        </a>
        <div class="legal-nav-actions">
            <a href="<?= BASE_URL ?>/" class="legal-btn-back">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali ke Beranda
            </a>
            <?php if ($loggedIn): ?>
                <a href="<?= BASE_URL ?>/dashboard/" class="legal-btn-primary">
                    Buka Dashboard
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login" class="legal-btn-primary">
                    Masuk Akun
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- HERO -->
    <header class="legal-hero">
        <div class="legal-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Transparansi & Keamanan Data
        </div>
        <h1 class="legal-title">Kebijakan Privasi TugasKu</h1>
        <p class="legal-subtitle">
            Kami menghormati privasi Anda sebagai mahasiswa. Dokumen ini menjelaskan dengan transparan bagaimana data kegiatan, tugas, dan identitas Anda dikelola dan dilindungi secara aman.
        </p>
        <div class="legal-meta">
            <div class="legal-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Versi 2.4 (September 2026)
            </div>
            <div class="legal-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Bebas Iklan & Pelacak Pihak Ketiga
            </div>
        </div>
    </header>

    <!-- MAIN BODY -->
    <main class="legal-container">
        <!-- TABLE OF CONTENTS -->
        <aside class="legal-toc">
            <div class="legal-toc-title">Daftar Isi</div>
            <ul class="legal-toc-list">
                <li><a href="#pengantar" class="legal-toc-link">1. Komitmen Privasi</a></li>
                <li><a href="#data-dikumpulkan" class="legal-toc-link">2. Data yang Dikumpulkan</a></li>
                <li><a href="#penggunaan-data" class="legal-toc-link">3. Pemanfaatan Data</a></li>
                <li><a href="#keamanan" class="legal-toc-link">4. Keamanan & Enkripsi</a></li>
                <li><a href="#cookies-session" class="legal-toc-link">5. Cookie & Sesi Lokal</a></li>
                <li><a href="#hak-pengguna" class="legal-toc-link">6. Hak & Ekspor Data</a></li>
                <li><a href="#tanpa-iklan" class="legal-toc-link">7. Komitmen Bebas Iklan</a></li>
                <li><a href="#kontak" class="legal-toc-link">8. Kontak & Bantuan</a></li>
            </ul>
        </aside>

        <!-- CONTENT -->
        <article class="legal-content">
            <!-- 1. PENGANTAR -->
            <section class="legal-section" id="pengantar">
                <span class="legal-sec-num">Bagian 01</span>
                <h2 class="legal-sec-title">Komitmen Privasi Kami</h2>
                <p>
                    TugasKu dirancang dari bawah ke atas sebagai sistem penunjang produktivitas akademik mahasiswa yang bersih, independen, dan berorientasi privasi. Kami memegang teguh prinsip bahwa data perkuliahan, kepanitiaan organisasi, dan catatan belajar Anda adalah milik penuh Anda seutuhnya.
                </p>
                <div class="legal-card-box">
                    <h4>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Prinsip Minimisasi Data
                    </h4>
                    <p>Kami hanya mengumpulkan informasi yang mutlak dibutuhkan agar fitur manajemen jadwal, tugas, dan organisasi dapat berjalan optimal tanpa meminta izin akses data pribadi yang tidak relevan.</p>
                </div>
            </section>

            <!-- 2. DATA YANG DIKUMPULKAN -->
            <section class="legal-section" id="data-dikumpulkan">
                <span class="legal-sec-num">Bagian 02</span>
                <h2 class="legal-sec-title">Data yang Kami Kumpulkan</h2>
                <p>Saat Anda membuat akun dan menggunakan platform TugasKu, kami memproses data berikut:</p>
                <ul class="legal-list">
                    <li><strong>Informasi Profil Akun:</strong> Nama lengkap, alamat email aktif, Nomor Induk Mahasiswa (NIM), program studi/jurusan, dan tahun angkatan.</li>
                    <li><strong>Kredensial Keamanan:</strong> Kata sandi (disimpan secara satu arah melalui hashing kriptografi Bcrypt dan tidak dapat dibaca oleh staf atau pengembang mana pun).</li>
                    <li><strong>Data Akademik & Kuliah:</strong> Daftar mata kuliah, kode matkul, nama dosen, ruang kelas, jadwal hari/jam, serta daftar tugas kuliah beserta tenggat waktunya.</li>
                    <li><strong>Data Kegiatan Organisasi:</strong> Nama organisasi, struktur divisi, peran/jabatan, proker, dan tugas kepanitiaan internal.</li>
                    <li><strong>Agenda & Jadwal Mandiri:</strong> Kegiatan to-do pribadi dan catatan belajar yang Anda tambahkan sendiri.</li>
                </ul>
                <p>
                    Kami <em>tidak pernah</em> meminta informasi keuangan, nomor identitas kependudukan (KTP), ataupun data biometrik Anda.
                </p>
            </section>

            <!-- 3. PEMANFAATAN DATA -->
            <section class="legal-section" id="penggunaan-data">
                <span class="legal-sec-num">Bagian 03</span>
                <h2 class="legal-sec-title">Tujuan & Pemanfaatan Data</h2>
                <p>Semua informasi yang tersimpan di TugasKu dimanfaatkan semata-mata untuk penyediaan fungsionalitas aplikasi:</p>
                <ul class="legal-list">
                    <li>Menyajikan dashboard terpusat yang menampilkan ringkasan tugas dan agenda perkuliahan Anda.</li>
                    <li>Menghitung otomatis urgensi tenggat waktu (deadline) seperti status <em>Terlewat</em>, <em>Hari Ini</em>, dan <em>Besok</em>.</li>
                    <li>Menyusun jadwal mingguan (timetable) dan kalender interaktif.</li>
                    <li>Menghasilkan berkas ekspor data CSV berformat rapi untuk Google Sheets atau Excel.</li>
                    <li>Memvalidasi otentikasi login serta verifikasi perubahan profil dan keamanan kata sandi.</li>
                </ul>
            </section>

            <!-- 4. KEAMANAN DATA -->
            <section class="legal-section" id="keamanan">
                    <p>Setiap kueri basis data diverifikasi secara ketat berdasarkan ID sesi pengguna yang sedang login. Mahasiswa lain tidak memiliki celah atau akses untuk melihat catatan tugas, organisasi, maupun profil Anda.</p>
                </div>
            </section>

            <!-- 5. COOKIES & LOCAL STORAGE -->
            <section class="legal-section" id="cookies-session">
                <span class="legal-sec-num">Bagian 05</span>
                <h2 class="legal-sec-title">Penyimpanan Sesi & LocalStorage</h2>
                <p>
                    TugasKu menggunakan teknologi penyimpanan peramban yang sangat minimalis untuk kenyamanan navigasi:
                </p>
                <ul class="legal-list">
                    <li><strong>PHP Session Cookie:</strong> Cookie sesi sementara yang berfungsi menjaga Anda tetap terautentikasi saat berpindah halaman dalam aplikasi. Cookie ini terhapus secara aman saat Anda menekan tombol keluar (logout).</li>
                    <li><strong>Peramban LocalStorage:</strong> Digunakan untuk mengingat status preferensi antarmuka pengguna, seperti apakah Anda telah menyelesaikan <em>Tutorial Onboarding Pertama Kali</em> agar tidak memunculkan panduan berulang.</li>
                </ul>
                <p>Kami tidak menggunakan cookie pelacak pihak ketiga (third-party tracking cookies) seperti pelacak periklanan atau data broker.</p>
            </section>

            <!-- 6. HAK PENGGUNA & EKSPOR -->
            <section class="legal-section" id="hak-pengguna">
                <span class="legal-sec-num">Bagian 06</span>
                <h2 class="legal-sec-title">Hak Pengguna & Portabilitas Data</h2>
                <p>
                    Sebagai pemilik sah atas data Anda, Anda memiliki hak-hak utama berikut:
                </p>
                <ul class="legal-list">
                    <li><strong>Hak Akses & Koreksi:</strong> Anda dapat memperbarui nama, NIM, jurusan, angkatan, maupun kata sandi Anda kapan saja melalui halaman <em>Pengaturan Akun</em>.</li>
                    <li><strong>Hak Portabilitas Data (Ekspor):</strong> Melalui modul <em>Ekspor Data</em>, Anda dapat mengunduh salinan lengkap tugas kuliah, organisasi, dan jadwal mandiri ke format CSV yang kompatibel dengan Google Sheets maupun Microsoft Excel dengan 1 klik.</li>
                    <li><strong>Hak Penghapusan:</strong> Anda memiliki kendali penuh untuk menghapus entri tugas, jadwal mata kuliah, atau divisi organisasi yang sudah tidak Anda perlukan.</li>
                </ul>
            </section>

            <!-- 7. TANPA IKLAN -->
            <section class="legal-section" id="tanpa-iklan">
                <span class="legal-sec-num">Bagian 07</span>
                <h2 class="legal-sec-title">Komitmen 100% Bebas Iklan</h2>
                <p>
                    TugasKu dibuat dengan niat tulus mendampingi perjuangan studi mahasiswa Indonesia:
                </p>
                <p>
                    Kami <strong>tidak pernah menjual, menyewakan, atau menukar data pribadi Anda</strong> kepada pihak periklanan, korporasi data, atau pihak ketiga mana pun untuk tujuan monetisasi. Platform ini bebas dari iklan banner, pop-up promosi, maupun pelacakan komersial.
                </p>
            </section>

            <!-- 8. KONTAK -->
            <section class="legal-section" id="kontak">
                <span class="legal-sec-num">Bagian 08</span>
                <h2 class="legal-sec-title">Pembaruan Kebijakan & Bantuan</h2>
                <p>
                    Jika terdapat penyesuaian regulasi atau penambahan fitur keamanan di masa mendatang, kami akan mencantumkan tanggal pembaruan di bagian atas halaman ini dan menyertakannya dalam catatan rilis <em>Update Terbaru</em>.
                </p>
                <p>
                    Apabila Anda memiliki pertanyaan, kendala privasi, atau saran mengenai perlindungan data di TugasKu, silakan hubungi tim kami melalui menu bantuan di dalam aplikasi atau repositori resmi TugasKu.
                </p>
            </section>
        </article>
    </main>

    <!-- FOOTER -->
    <footer style="background:#ffffff; border-top:1px solid #e2e8f0; padding:2rem 2rem; text-align:center; color:#94a3b8; font-size:0.85rem;">
        <div style="font-family:'Newsreader',serif; font-size:1.2rem; font-weight:600; color:#0f172a; margin-bottom:0.4rem;">
            Tugas<span style="font-style:italic; color:#2563eb;">Ku</span>.
        </div>
        <p style="margin:0 0 0.5rem;">Sistem Manajemen Kegiatan & Produktivitas Mahasiswa Modern.</p>
        <p style="margin:0;">&copy; 2026 TugasKu. Seluruh hak cipta dilindungi undang-undang.</p>
    </footer>

    <script>
        // Smooth scroll for TOC links
        document.querySelectorAll('.legal-toc-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const targetId = link.getAttribute('href');
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    document.querySelectorAll('.legal-toc-link').forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
