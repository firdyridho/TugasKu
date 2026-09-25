        </div>
    </main>
</div>

<!-- On-Page DOM Spotlight Highlighter ("Di Kotakin Langsung di Halaman") -->
<div id="spotlightMask" class="spotlight-mask-overlay" style="display: none;" role="dialog" aria-modal="true">
    <div id="spotlightBox" class="spotlight-box"></div>
    <div id="spotlightPopover" class="spotlight-popover">
        <div class="spotlight-popover-header">
            <span class="spotlight-step-tag" id="spotlightStepTag">Langkah 1 dari 6</span>
            <button type="button" class="spotlight-btn-close" onclick="closeSpotlightTour(true)" aria-label="Tutup Panduan">&times;</button>
        </div>
        <div class="spotlight-popover-body">
            <div class="spotlight-popover-title-row">
                <div class="spotlight-popover-icon" id="spotlightIcon"></div>
                <h3 class="spotlight-popover-title" id="spotlightTitle">Judul Panduan</h3>
            </div>
            <p class="spotlight-popover-desc" id="spotlightDesc">Deskripsi langkah panduan.</p>
            <div class="spotlight-popover-tip" id="spotlightTip">
                <svg class="spotlight-tip-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span id="spotlightTipText">Tips singkat fitur ini.</span>
            </div>
        </div>
        <div class="spotlight-popover-footer">
            <div class="spotlight-dots" id="spotlightDots"></div>
            <div class="spotlight-actions">
                <button type="button" class="spotlight-btn-skip" onclick="closeSpotlightTour(true)">Lewati</button>
                <button type="button" class="spotlight-btn-prev" id="spotlightBtnPrev" onclick="prevSpotlightStep()">Sebelumnya</button>
                <button type="button" class="spotlight-btn-next" id="spotlightBtnNext" onclick="nextSpotlightStep()">
                    <span id="spotlightNextLabel">Lanjut</span>
                    <svg id="spotlightNextIcon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Modern Logout Confirmation Modal -->
<div id="logoutConfirmModal" class="logout-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
    <div class="logout-modal-card">
        <div class="logout-modal-icon-wrap">
            <div class="logout-modal-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </div>
        </div>
        <div class="logout-modal-content">
            <h3 id="logoutModalTitle" class="logout-modal-title">Konfirmasi Keluar</h3>
            <p class="logout-modal-desc">
                Apakah Anda yakin ingin mengakhiri sesi dan keluar dari akun TugasKu?
            </p>
        </div>
        <div class="logout-modal-actions">
            <button type="button" class="btn btn-secondary logout-btn-cancel" onclick="closeLogoutModal()">
                Tetap di Sini
            </button>
            <a href="<?= BASE_URL ?>/auth/logout" class="btn btn-danger logout-btn-confirm" id="confirmLogoutLink" onclick="triggerLogoutAnimation(event, this)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Ya, Keluar
            </a>
        </div>
    </div>
</div>

<!-- Fullscreen Modern Logout Transition Overlay -->
<div id="logoutScreenOverlay" class="logout-screen-overlay" aria-hidden="true">
    <div class="logout-screen-backdrop"></div>
    <div class="logout-screen-card">
        <div class="logout-spinner-wrap">
            <div class="logout-spinner-ring"></div>
            <div class="logout-screen-icon-center">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
        </div>
        <div class="logout-screen-brand">
            Tugas<span style="font-style: italic; color: #818cf8;">Ku</span><span style="color: #818cf8;">.</span>
        </div>
        <h3 class="logout-screen-title">Mengamankan Sesi...</h3>
        <p class="logout-screen-sub">Menutup akun secara aman dan menghapus data sesi aktif.</p>
        <div class="logout-progress-bar">
            <div class="logout-progress-fill"></div>
        </div>
    </div>
</div>

<script>
    window.BASE_URL = '<?= BASE_URL ?>';

    function openLogoutModal(event) {
        if (event) event.preventDefault();
        const modal = document.getElementById('logoutConfirmModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeLogoutModal() {
        const modal = document.getElementById('logoutConfirmModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function triggerLogoutAnimation(event, link) {
        if (event) event.preventDefault();
        const targetUrl = link ? link.getAttribute('href') : (window.BASE_URL + '/auth/logout');
        
        // 1. Tutup modal konfirmasi
        closeLogoutModal();

        // 2. Munculkan fullscreen overlay transisi
        const overlay = document.getElementById('logoutScreenOverlay');
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // 3. Redirect mulus setelah animasi bar progress selesai (850ms)
        setTimeout(function() {
            window.location.href = targetUrl;
        }, 850);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLogoutModal();
    });

    document.addEventListener('click', function(e) {
        const modal = document.getElementById('logoutConfirmModal');
        if (modal && modal.classList.contains('active') && e.target === modal) {
            closeLogoutModal();
        }
    });
</script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script src="<?= BASE_URL ?>/assets/js/ai-assistant.js"></script>
</body>
</html>

