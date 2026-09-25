<?php
require_once __DIR__ . '/../config.php';
if (isLoggedIn()) redirect('/dashboard/');

$loginError = '';
$registerError = '';
$initialMode = $_GET['mode'] ?? 'login';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['auth_action'] ?? 'login';

    if ($action === 'login') {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $loginError = 'Silakan masukkan email dan kata sandi Anda.';
        } else {
            $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                flash('success', 'Selamat datang kembali, ' . htmlspecialchars($user['nama']) . '!');
                redirect('/dashboard/');
            } else {
                $loginError = 'Email atau kata sandi tidak sesuai.';
            }
        }
        $initialMode = 'login';
    } elseif ($action === 'register') {
        $nama = sanitize($_POST['reg_nama'] ?? '');
        $email = sanitize($_POST['reg_email'] ?? '');
        $password = $_POST['reg_password'] ?? '';
        $confirm = $_POST['reg_confirm'] ?? '';

        if (empty($nama) || empty($email) || empty($password)) {
            $registerError = 'Nama, email, dan kata sandi wajib diisi.';
            $initialMode = 'register';
        } elseif (strlen($password) < 6) {
            $registerError = 'Kata sandi minimal 6 karakter.';
            $initialMode = 'register';
        } elseif ($password !== $confirm) {
            $registerError = 'Konfirmasi kata sandi tidak cocok.';
            $initialMode = 'register';
        } else {
            $check = $conn->prepare('SELECT id FROM users WHERE email = ?');
            $check->bind_param('s', $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $registerError = 'Email ini sudah terdaftar. Silakan masuk.';
                $initialMode = 'register';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $nim = '';
                $jurusan = '';
                $angkatan = (int)date('Y');
                $stmt = $conn->prepare('INSERT INTO users (nama, email, nim, jurusan, angkatan, password) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssis', $nama, $email, $nim, $jurusan, $angkatan, $hash);
                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $stmt->insert_id;
                    flash('success', 'Akun berhasil dibuat. Selamat datang di TugasKu!');
                    redirect('/dashboard/');
                } else {
                    $registerError = 'Gagal mendaftarkan akun. Silakan coba lagi.';
                    $initialMode = 'register';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk & Daftar - TugasKu</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/favicon.ico">

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400..700;1,6..72,400..700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-page">

    <div class="auth-sliding-box <?= $initialMode === 'register' ? 'mode-register' : '' ?>" id="authSlidingBox">
        
        <!-- Mobile Selector (Tabs on Small Devices) -->
        <div class="auth-mobile-tabs">
            <button type="button" class="auth-mobile-tab <?= $initialMode !== 'register' ? 'active' : '' ?>" id="mobileLoginBtn">Masuk</button>
            <button type="button" class="auth-mobile-tab <?= $initialMode === 'register' ? 'active' : '' ?>" id="mobileRegisterBtn">Daftar</button>
        </div>

        <!-- 1. FORM DAFTAR (SIGN UP - KIRI) -->
        <div class="auth-form-side sign-up-form-side">
            <form method="POST" action="<?= BASE_URL ?>/auth/login">
                <input type="hidden" name="auth_action" value="register">

                <div style="margin-bottom: 1.25rem;">
                    <a href="<?= BASE_URL ?>/" class="brand-logo">
                        Tugas<span class="logo-accent">Ku</span><span class="logo-dot">.</span>
                    </a>
                </div>

                <h2>Buat Akun Baru</h2>
                <p class="auth-subtitle">Kelola tugas kuliah, catatan, dan organisasi dalam satu ruang terpadu.</p>

                <?php if ($registerError): ?>
                    <div class="alert alert-error" style="padding: 0.65rem 0.95rem; margin-bottom: 1.15rem;">
                        <span><?= htmlspecialchars($registerError) ?></span>
                        <span class="alert-close">&times;</span>
                    </div>
                <?php endif; ?>

                <div class="auth-input-group">
                    <label for="reg_nama">Nama Lengkap</label>
                    <input type="text" id="reg_nama" name="reg_nama" class="auth-input" placeholder="Contoh: Faraz Haidet" required value="<?= htmlspecialchars($_POST['reg_nama'] ?? '') ?>">
                </div>

                <div class="auth-input-group">
                    <label for="reg_email">Alamat Email</label>
                    <input type="email" id="reg_email" name="reg_email" class="auth-input" placeholder="nama@email.com" required value="<?= htmlspecialchars($_POST['reg_email'] ?? '') ?>" autocomplete="email">
                </div>

                <div class="form-row">
                    <div class="auth-input-group" style="margin-bottom: 0;">
                        <label for="reg_password">Kata Sandi</label>
                        <div style="position:relative;">
                            <input type="password" id="reg_password" name="reg_password" class="auth-input" placeholder="Min. 8 karakter" required autocomplete="new-password" oninput="regCheckStrength(this.value)" style="padding-right:2.75rem;">
                            <button type="button" onclick="regTogglePw('reg_password',this)" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;display:flex;align-items:center;" title="Tampilkan/Sembunyikan">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-show"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-hide" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        <!-- Strength Meter -->
                        <div id="regStrengthWrap" style="display:none; margin-top:0.4rem;">
                            <div style="display:flex;gap:3px;margin-bottom:0.2rem;">
                                <div class="pw-strength-bar" id="regBar1"></div>
                                <div class="pw-strength-bar" id="regBar2"></div>
                                <div class="pw-strength-bar" id="regBar3"></div>
                                <div class="pw-strength-bar" id="regBar4"></div>
                            </div>
                            <span class="pw-strength-label" id="regStrLabel" style="font-size:0.72rem;">Lemah</span>
                        </div>
                    </div>
                    <div class="auth-input-group" style="margin-bottom: 0;">
                        <label for="reg_confirm">Ulangi Sandi</label>
                        <div style="position:relative;">
                            <input type="password" id="reg_confirm" name="reg_confirm" class="auth-input" placeholder="Konfirmasi" required autocomplete="new-password" style="padding-right:2.75rem;">
                            <button type="button" onclick="regTogglePw('reg_confirm',this)" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;display:flex;align-items:center;" title="Tampilkan/Sembunyikan">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-show"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-hide" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn" style="margin-top: 1.25rem;">
                    Daftar Akun
                </button>

                <p class="auth-footer-link">
                    Sudah memiliki akun? <a href="javascript:void(0)" class="trigger-slide-login">Masuk sekarang</a>
                </p>

                <div style="text-align: center; margin-top: 0.75rem;">
                    <a href="<?= BASE_URL ?>/" style="font-size: 0.8rem; color: #94a3b8; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        Kembali ke Halaman Depan
                    </a>
                </div>
            </form>
        </div>

        <!-- 2. FORM MASUK (SIGN IN - KANAN) -->
        <div class="auth-form-side sign-in-form-side">
            <form method="POST" action="<?= BASE_URL ?>/auth/login">
                <input type="hidden" name="auth_action" value="login">

                <div style="margin-bottom: 1.25rem;">
                    <a href="<?= BASE_URL ?>/" class="brand-logo">
                        Tugas<span class="logo-accent">Ku</span><span class="logo-dot">.</span>
                    </a>
                </div>

                <h2>Masuk ke Akun</h2>
                <p class="auth-subtitle">Akses semua tugas, jadwal, dan deadline Anda kapan saja di satu tempat.</p>

                <?php if ($loginError): ?>
                    <div class="alert alert-error" style="padding: 0.65rem 0.95rem; margin-bottom: 1.15rem;">
                        <span><?= htmlspecialchars($loginError) ?></span>
                        <span class="alert-close">&times;</span>
                    </div>
                <?php endif; ?>

                <div class="auth-input-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" id="email" name="email" class="auth-input" placeholder="farazhaidet786@gmail.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="email">
                </div>

                <div class="auth-input-group">
                    <label for="password">Kata Sandi</label>
                    <div style="position:relative;">
                        <input type="password" id="password" name="password" class="auth-input" placeholder="Masukkan kata sandi akun" required autocomplete="current-password" style="padding-right:2.75rem;">
                        <button type="button" onclick="regTogglePw('password',this)" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;display:flex;align-items:center;" title="Tampilkan/Sembunyikan">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-show"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-hide" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn">
                    Masuk Sekarang
                </button>

                <p class="auth-footer-link">
                    Belum punya akun? <a href="javascript:void(0)" class="trigger-slide-register">Daftar sekarang</a>
                </p>

                <div style="text-align: center; margin-top: 1rem;">
                    <a href="<?= BASE_URL ?>/" style="font-size: 0.8rem; color: #94a3b8; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        Kembali ke Halaman Depan
                    </a>
                </div>
            </form>
        </div>

        <!-- 3. KARTU PINTU GESER (GRADIENT INSET SLIDING CARD) -->
        <div class="sliding-gradient-card">
            
            <!-- Tampilan saat posisi di KIRI (Mode Login) -->
            <div class="gradient-card-inner" id="gradientLoginView">
                <div class="gradient-top-mark">
                    <a href="<?= BASE_URL ?>/" class="brand-logo" style="color: #ffffff; font-size: 1.55rem;">
                        Tugas<span style="font-style: italic; color: #c7d2fe;">Ku</span><span style="color: #c7d2fe;">.</span>
                    </a>
                </div>
                
                <div class="gradient-bottom-text">
                    <span class="mini-tagline">Akses Cepat & Terpadu</span>
                    <h3>Pusat produktivitas pribadi untuk kejernihan dan keteraturan kuliahmu.</h3>
                    <button type="button" class="gradient-switch-btn" id="btnSlideToRegister">
                        Buat Akun Baru
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
            </div>

            <!-- Tampilan saat posisi di KANAN (Mode Register) -->
            <div class="gradient-card-inner" id="gradientRegisterView" style="display: none;">
                <div class="gradient-top-mark">
                    <a href="<?= BASE_URL ?>/" class="brand-logo" style="color: #ffffff; font-size: 1.55rem;">
                        Tugas<span style="font-style: italic; color: #c7d2fe;">Ku</span><span style="color: #c7d2fe;">.</span>
                    </a>
                </div>
                
                <div class="gradient-bottom-text">
                    <span class="mini-tagline">Selamat Datang Kembali</span>
                    <h3>Lanjutkan aktivitas dan kelola semua deadline tugasmu hari ini.</h3>
                    <button type="button" class="gradient-switch-btn" id="btnSlideToLogin">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15 18 9 12 15 6"/></svg>
                        Masuk ke Akun
                    </button>
                </div>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const box = document.getElementById('authSlidingBox');
            const loginView = document.getElementById('gradientLoginView');
            const registerView = document.getElementById('gradientRegisterView');
            const mobileLoginBtn = document.getElementById('mobileLoginBtn');
            const mobileRegisterBtn = document.getElementById('mobileRegisterBtn');

            function updateGradientContent(isRegister) {
                if (isRegister) {
                    setTimeout(() => {
                        loginView.style.display = 'none';
                        registerView.style.display = 'flex';
                    }, 250);
                } else {
                    setTimeout(() => {
                        registerView.style.display = 'none';
                        loginView.style.display = 'flex';
                    }, 250);
                }
            }

            function setRegisterMode() {
                box.classList.add('mode-register');
                updateGradientContent(true);
                if (mobileRegisterBtn) {
                    mobileRegisterBtn.classList.add('active');
                    mobileLoginBtn.classList.remove('active');
                }
                history.replaceState(null, '', '?mode=register');
            }

            function setLoginMode() {
                box.classList.remove('mode-register');
                updateGradientContent(false);
                if (mobileLoginBtn) {
                    mobileLoginBtn.classList.add('active');
                    mobileRegisterBtn.classList.remove('active');
                }
                history.replaceState(null, '', '?mode=login');
            }

            // Initial view setup
            if (box.classList.contains('mode-register')) {
                loginView.style.display = 'none';
                registerView.style.display = 'flex';
            }

            // Trigger buttons
            document.querySelectorAll('.trigger-slide-register').forEach(el => {
                el.addEventListener('click', setRegisterMode);
            });
            document.querySelectorAll('.trigger-slide-login').forEach(el => {
                el.addEventListener('click', setLoginMode);
            });

            const btnSlideToReg = document.getElementById('btnSlideToRegister');
            if (btnSlideToReg) btnSlideToReg.addEventListener('click', setRegisterMode);

            const btnSlideToLog = document.getElementById('btnSlideToLogin');
            if (btnSlideToLog) btnSlideToLog.addEventListener('click', setLoginMode);

            if (mobileRegisterBtn) mobileRegisterBtn.addEventListener('click', setRegisterMode);
            if (mobileLoginBtn) mobileLoginBtn.addEventListener('click', setLoginMode);

            // Alert dismiss
            document.querySelectorAll('.alert-close').forEach(btn => {
                btn.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    if (alert) alert.remove();
                });
            });
        });

        // Eye toggle function (used for both register & login forms)
        function regTogglePw(id, btn) {
            const inp = document.getElementById(id);
            const isHidden = inp.type === 'password';
            inp.type = isHidden ? 'text' : 'password';
            const eyeShow = btn.querySelector('.eye-show');
            const eyeHide = btn.querySelector('.eye-hide');
            if (eyeShow) eyeShow.style.display = isHidden ? 'none' : '';
            if (eyeHide) eyeHide.style.display = isHidden ? '' : 'none';
        }

        // Password strength check for register
        function regCheckStrength(val) {
            const wrap = document.getElementById('regStrengthWrap');
            const lbl  = document.getElementById('regStrLabel');
            if (!wrap) return;
            if (!val) { wrap.style.display = 'none'; return; }
            wrap.style.display = 'block';
            let score = 0;
            if (val.length >= 8)          score++;
            if (/[A-Z]/.test(val))        score++;
            if (/[0-9]/.test(val))        score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            const lbls = ['', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];
            if (lbl) { lbl.textContent = lbls[score]; lbl.className = 'pw-strength-label strength-' + score; }
            [1,2,3,4].forEach(n => {
                const bar = document.getElementById('regBar' + n);
                if (bar) bar.className = 'pw-strength-bar' + (n <= score ? ' active-' + score : '');
            });
        }
    </script>
</body>
</html>
