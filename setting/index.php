<?php
$pageTitle = 'Pengaturan Akun';
require_once __DIR__ . '/../config.php';
requireLogin();

$user = getUser();
$userId = $_SESSION['user_id'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Pengaturan Akun</h1>
        <p>Kelola informasi profil dan keamanan akun kamu.</p>
    </div>
</div>

<div class="setting-layout">
    <!-- Sidebar Card -->
    <div>
        <div class="setting-sidebar-card">
            <div class="setting-avatar"><?= strtoupper(substr($user['nama'] ?? 'U', 0, 1)) ?></div>
            <div class="setting-user-name"><?= htmlspecialchars($user['nama'] ?? 'Mahasiswa') ?></div>
            <div class="setting-user-nim"><?= htmlspecialchars($user['nim'] ? 'NIM: ' . $user['nim'] : ($user['email'] ?? '-')) ?></div>
            <?php if ($user['jurusan']): ?>
                <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($user['jurusan']) ?> &bull; <?= htmlspecialchars($user['angkatan'] ?? '') ?></div>
            <?php endif; ?>
            <div class="setting-menu" style="margin-top: 1rem;">
                <button type="button" class="setting-menu-item active" id="menuProfil" onclick="switchTab('Profil')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Edit Profil
                </button>
                <button type="button" class="setting-menu-item" id="menuPassword" onclick="switchTab('Password')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Ubah Password
                </button>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="setting-content-area">

        <!-- === TAB: PROFIL === -->
        <div class="setting-section active" id="sectionProfil">
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <h3>Informasi Profil</h3>
                        <p>Perbarui nama, NIM, jurusan, dan angkatan kamu.</p>
                    </div>
                </div>
                <div class="setting-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/proses.php">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="s_nama">Nama Lengkap</label>
                                <input type="text" id="s_nama" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama'] ?? '') ?>" required placeholder="Nama lengkap kamu">
                            </div>
                            <div class="form-group">
                                <label for="s_nim">NIM</label>
                                <input type="text" id="s_nim" name="nim" class="form-control" value="<?= htmlspecialchars($user['nim'] ?? '') ?>" placeholder="Nomor Induk Mahasiswa">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="s_jurusan">Jurusan / Program Studi</label>
                                <input type="text" id="s_jurusan" name="jurusan" class="form-control" value="<?= htmlspecialchars($user['jurusan'] ?? '') ?>" placeholder="Contoh: Teknik Informatika">
                            </div>
                            <div class="form-group">
                                <label for="s_angkatan">Angkatan</label>
                                <input type="number" id="s_angkatan" name="angkatan" class="form-control" value="<?= htmlspecialchars($user['angkatan'] ?? date('Y')) ?>" min="2000" max="<?= date('Y') + 1 ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
                            <span style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.3rem; display: block;">Email tidak dapat diubah untuk keamanan akun.</span>
                        </div>
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Simpan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- === TAB: PASSWORD === -->
        <div class="setting-section" id="sectionPassword">
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-card-icon" style="background: rgba(239,68,68,0.1); color: var(--danger);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <h3>Ubah Password</h3>
                        <p>Masukkan password lama untuk verifikasi, lalu buat password baru.</p>
                    </div>
                </div>
                <div class="setting-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/proses.php" onsubmit="return validatePwForm()">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label for="pw_old">Password Lama</label>
                            <div class="password-field-wrap">
                                <input type="password" id="pw_old" name="pw_old" class="form-control" placeholder="Masukkan password lama" required autocomplete="current-password">
                                <button type="button" class="pw-eye-btn" onclick="togglePw('pw_old', this)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-show"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-hide" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="pw_new">Password Baru</label>
                            <div class="password-field-wrap">
                                <input type="password" id="pw_new" name="pw_new" class="form-control" placeholder="Minimal 8 karakter" required autocomplete="new-password" oninput="checkStrength(this.value)">
                                <button type="button" class="pw-eye-btn" onclick="togglePw('pw_new', this)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-show"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-hide" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <div class="pw-strength-wrap" id="pwStrengthWrap" style="display:none">
                                <div class="pw-strength-bars">
                                    <div class="pw-strength-bar" id="sBar1"></div>
                                    <div class="pw-strength-bar" id="sBar2"></div>
                                    <div class="pw-strength-bar" id="sBar3"></div>
                                    <div class="pw-strength-bar" id="sBar4"></div>
                                </div>
                                <span class="pw-strength-label" id="sStrLabel">Lemah</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="pw_confirm">Konfirmasi Password Baru</label>
                            <div class="password-field-wrap">
                                <input type="password" id="pw_confirm" name="pw_confirm" class="form-control" placeholder="Ulangi password baru" required autocomplete="new-password" oninput="checkMatch()">
                                <button type="button" class="pw-eye-btn" onclick="togglePw('pw_confirm', this)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-show"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-hide" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <div id="pwMatchWarn" style="display:none; font-size:0.8rem; color:var(--danger); margin-top:0.35rem;">&#9888; Password baru dan konfirmasi tidak cocok.</div>
                        </div>

                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary" id="submitPwBtn">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="setting-card" style="border-color:rgba(239,68,68,0.2); background:rgba(239,68,68,0.02);">
                <div class="setting-card-body" style="padding:1rem 1.5rem;">
                    <div style="display:flex; align-items:flex-start; gap:0.75rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" style="flex-shrink:0; margin-top:0.1rem;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <div>
                            <p style="font-size:0.85rem; font-weight:600; color:var(--danger); margin-bottom:0.25rem;">Tips Keamanan Password</p>
                            <p style="font-size:0.8rem; color:var(--text-muted); line-height:1.55;">Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol. Minimal 8 karakter. Jangan gunakan tanggal lahir atau nama yang mudah ditebak.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.setting-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.setting-menu-item').forEach(m => m.classList.remove('active'));
    document.getElementById('section' + tab).classList.add('active');
    document.getElementById('menu' + tab).classList.add('active');
}

function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const isHidden = inp.type === 'password';
    inp.type = isHidden ? 'text' : 'password';
    btn.querySelector('.eye-show').style.display = isHidden ? 'none' : '';
    btn.querySelector('.eye-hide').style.display = isHidden ? '' : 'none';
}

function checkStrength(val) {
    const wrap = document.getElementById('pwStrengthWrap');
    const lbl  = document.getElementById('sStrLabel');
    const bars = [1,2,3,4].map(n => document.getElementById('sBar' + n));
    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';
    let score = 0;
    if (val.length >= 8)        score++;
    if (/[A-Z]/.test(val))     score++;
    if (/[0-9]/.test(val))     score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const lbls = ['','Lemah','Sedang','Kuat','Sangat Kuat'];
    lbl.textContent = lbls[score];
    lbl.className = 'pw-strength-label strength-' + score;
    bars.forEach((b, i) => { b.className = 'pw-strength-bar' + (i < score ? ' active-' + score : ''); });
}

function checkMatch() {
    const v1 = document.getElementById('pw_new').value;
    const v2 = document.getElementById('pw_confirm').value;
    const warn = document.getElementById('pwMatchWarn');
    const btn  = document.getElementById('submitPwBtn');
    const mismatch = v2.length > 0 && v1 !== v2;
    warn.style.display = mismatch ? '' : 'none';
    btn.disabled = mismatch;
}

function validatePwForm() {
    const v1 = document.getElementById('pw_new').value;
    const v2 = document.getElementById('pw_confirm').value;
    if (v1 !== v2) { checkMatch(); return false; }
    if (v1.length < 8) { alert('Password baru minimal 8 karakter.'); return false; }
    return true;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

