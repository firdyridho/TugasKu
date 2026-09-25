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

<script>
    window.BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script src="<?= BASE_URL ?>/assets/js/ai-assistant.js"></script>
</body>
</html>

