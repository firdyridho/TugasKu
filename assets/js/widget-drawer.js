/**
 * TugasKu Realtime Widget Drawer
 * Live clock, schedule tracking, and instant deadline manager
 */
(function() {
    const baseUrl = (typeof window.BASE_URL === 'string') ? window.BASE_URL : (window.BASE_URL || '');
    let widgetDrawerEl = null;
    let widgetBackdropEl = null;
    let isWidgetOpen = false;
    let widgetActiveTab = 'jadwal';
    let localData = null;
    let timeOffset = 0;

    // Helper HTML Escaping
    function esc(s) {
        if (!s) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Initialize Widget Drawer DOM
    function initWidgetDrawerDOM() {
        if (document.getElementById('rtWidgetDrawer')) return;

        // 1. Backdrop
        widgetBackdropEl = document.createElement('div');
        widgetBackdropEl.id = 'rtWidgetBackdrop';
        widgetBackdropEl.className = 'rt-widget-backdrop';
        widgetBackdropEl.onclick = closeWidgetDrawer;
        document.body.appendChild(widgetBackdropEl);

        // 2. Drawer Container
        widgetDrawerEl = document.createElement('div');
        widgetDrawerEl.id = 'rtWidgetDrawer';
        widgetDrawerEl.className = 'rt-widget-drawer';
        widgetDrawerEl.setAttribute('role', 'dialog');
        widgetDrawerEl.setAttribute('aria-modal', 'true');

        widgetDrawerEl.innerHTML = `
            <div class="rt-drawer-header">
                <div class="rt-header-left">
                    <div class="rt-header-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <div class="rt-drawer-title">Widget Realtime</div>
                        <div class="rt-drawer-subtitle" id="rtDrawerDate">Memuat tanggal...</div>
                    </div>
                </div>
                <div class="rt-header-actions">
                    <button type="button" class="rt-btn-icon" onclick="window.openWidgetPopup()" title="Buka Jendela Mini Pop-out">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </button>
                    <button type="button" class="rt-btn-icon" id="rtBtnRefresh" onclick="window.refreshRealtimeWidget(true)" title="Perbarui Data">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    </button>
                    <button type="button" class="rt-btn-icon rt-btn-close" onclick="window.closeWidgetDrawer()" title="Tutup Widget">&times;</button>
                </div>
            </div>

            <!-- Digital Clock Strip -->
            <div class="rt-clock-strip">
                <div class="rt-clock-time">
                    <span id="rtClockHours">00</span>:<span id="rtClockMinutes">00</span>:<span id="rtClockSeconds">00</span>
                    <span class="rt-clock-tz">WIB</span>
                </div>
                <div class="rt-live-badge">
                    <span class="rt-live-dot"></span>
                    <span>LIVE</span>
                </div>
            </div>

            <!-- Tab Switcher -->
            <div class="rt-drawer-tabs">
                <button type="button" class="rt-tab active" id="rtTabJadwal" onclick="window.setWidgetTab('jadwal')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>Jadwal Hari Ini</span>
                    <span class="rt-badge-count" id="rtBadgeJadwal">0</span>
                </button>
                <button type="button" class="rt-tab" id="rtTabDeadlines" onclick="window.setWidgetTab('deadlines')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>DL Tugas</span>
                    <span class="rt-badge-count" id="rtBadgeDeadlines">0</span>
                </button>
            </div>

            <!-- Drawer Body -->
            <div class="rt-drawer-body">
                <div id="rtPaneJadwal" class="rt-tab-pane active">
                    <div id="rtJadwalList" class="rt-card-list">
                        <div class="rt-loading-state">Memuat jadwal...</div>
                    </div>
                </div>
                <div id="rtPaneDeadlines" class="rt-tab-pane">
                    <div id="rtDeadlinesList" class="rt-card-list">
                        <div class="rt-loading-state">Memuat deadline...</div>
                    </div>
                </div>
            </div>

            <!-- Drawer Footer -->
            <div class="rt-drawer-footer">
                <a href="${baseUrl}/widget" target="_blank" onclick="window.openWidgetPopup(event)" class="rt-footer-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    Buka Pop-up Terpisah
                </a>
                <span class="rt-auto-text">• Auto-sync 30s</span>
            </div>
        `;

        document.body.appendChild(widgetDrawerEl);
    }

    // Toggle Drawer Open / Close
    window.toggleRealtimeWidgetDrawer = function() {
        if (isWidgetOpen) {
            closeWidgetDrawer();
        } else {
            openWidgetDrawer();
        }
    };

    function openWidgetDrawer() {
        initWidgetDrawerDOM();
        if (widgetDrawerEl && widgetBackdropEl) {
            widgetDrawerEl.classList.add('active');
            widgetBackdropEl.classList.add('active');
            document.body.style.overflow = 'hidden';
            isWidgetOpen = true;
            fetchRealtimeData(false);
        }
    }

    window.closeWidgetDrawer = function() {
        if (widgetDrawerEl && widgetBackdropEl) {
            widgetDrawerEl.classList.remove('active');
            widgetBackdropEl.classList.remove('active');
            document.body.style.overflow = '';
            isWidgetOpen = false;
        }
    };

    // Open Standalone Popup Window
    window.openWidgetPopup = function(event) {
        if (event) event.preventDefault();
        const w = 460;
        const h = 720;
        const left = (screen.width - w) / 2;
        const top = (screen.height - h) / 2;
        window.open(
            baseUrl + '/widget',
            'TugasKuRealtimeWidget',
            `width=${w},height=${h},top=${top},left=${left},scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no`
        );
    };

    // Set Active Tab
    window.setWidgetTab = function(tab) {
        widgetActiveTab = tab;
        const tabJadwal = document.getElementById('rtTabJadwal');
        const tabDeadlines = document.getElementById('rtTabDeadlines');
        const paneJadwal = document.getElementById('rtPaneJadwal');
        const paneDeadlines = document.getElementById('rtPaneDeadlines');

        if (tab === 'jadwal') {
            if (tabJadwal) tabJadwal.classList.add('active');
            if (tabDeadlines) tabDeadlines.classList.remove('active');
            if (paneJadwal) paneJadwal.classList.add('active');
            if (paneDeadlines) paneDeadlines.classList.remove('active');
        } else {
            if (tabDeadlines) tabDeadlines.classList.add('active');
            if (tabJadwal) tabJadwal.classList.remove('active');
            if (paneDeadlines) paneDeadlines.classList.add('active');
            if (paneJadwal) paneJadwal.classList.remove('active');
        }
    };

    // Fetch API Data
    window.refreshRealtimeWidget = function(isManual) {
        fetchRealtimeData(isManual);
    };

    async function fetchRealtimeData(isManual = false) {
        const btnRef = document.getElementById('rtBtnRefresh');
        if (btnRef && isManual) btnRef.classList.add('spinning');

        try {
            const res = await fetch(baseUrl + '/api/widget.php', {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });

            if (res.status === 401) return;
            const data = await res.json();

            if (data.status === 'success') {
                localData = data;
                if (data.timestamp) {
                    timeOffset = (data.timestamp * 1000) - Date.now();
                }
                updateTopbarBadge(data);
                if (isWidgetOpen) {
                    renderDrawerUI(data);
                }
            }
        } catch (e) {
            console.error('Error fetching widget data:', e);
        } finally {
            if (btnRef && isManual) {
                setTimeout(() => btnRef.classList.remove('spinning'), 400);
            }
        }
    }

    // Update Topbar badge count
    function updateTopbarBadge(data) {
        const topbarBadge = document.getElementById('topbarWidgetBadge');
        if (topbarBadge && data && data.counts) {
            const total = (data.counts.total_jadwal_today || 0) + (data.counts.urgent_dl || 0);
            if (total > 0) {
                topbarBadge.textContent = total;
                topbarBadge.style.display = 'inline-flex';
            } else {
                topbarBadge.style.display = 'none';
            }
        }
    }

    // Render inside Drawer
    function renderDrawerUI(data) {
        const elDate = document.getElementById('rtDrawerDate');
        if (elDate) elDate.textContent = data.date_display || 'Hari Ini';

        const badgeJadwal = document.getElementById('rtBadgeJadwal');
        const badgeDeadlines = document.getElementById('rtBadgeDeadlines');
        if (badgeJadwal) badgeJadwal.textContent = data.counts.total_jadwal_today;
        if (badgeDeadlines) badgeDeadlines.textContent = data.counts.total_active_dl;

        // Render Jadwal
        const jList = document.getElementById('rtJadwalList');
        if (jList) {
            const items = data.jadwal || [];
            if (!items.length) {
                jList.innerHTML = `
                    <div class="rt-empty-state">
                        <div class="rt-empty-icon">☕</div>
                        <div class="rt-empty-title">Tidak Ada Jadwal Hari Ini</div>
                        <div class="rt-empty-sub">Bebas jadwal kuliah dan mandiri hari ini. Waktunya santai atau cicil tugas!</div>
                    </div>
                `;
            } else {
                let html = '';
                items.forEach(it => {
                    const isOngoing = it.status === 'ongoing';
                    const typeLabel = it.type === 'kuliah' ? 'Kuliah' : 'Mandiri';
                    html += `
                        <div class="rt-item-card card-${it.status}">
                            <div class="rt-item-header">
                                <span class="rt-badge badge-${it.status}">
                                    ${isOngoing ? '<span class="rt-live-dot" style="width:6px;height:6px;"></span>' : ''}
                                    ${typeLabel} • ${it.status_text}
                                </span>
                                <span class="rt-item-time">${esc(it.time_range)} WIB</span>
                            </div>
                            <div class="rt-item-title">${esc(it.title)}</div>
                            <div class="rt-item-sub">
                                <span>📍 ${esc(it.location)}</span>
                                ${it.info ? `<span>• ${esc(it.info)}</span>` : ''}
                            </div>
                            ${isOngoing && it.progress_pct > 0 ? `
                                <div class="rt-progress-bar-wrap">
                                    <div class="rt-progress-bar" style="width: ${it.progress_pct}%;"></div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
                jList.innerHTML = html;
            }
        }

        // Render Deadlines
        const dList = document.getElementById('rtDeadlinesList');
        if (dList) {
            const items = data.deadlines || [];
            if (!items.length) {
                dList.innerHTML = `
                    <div class="rt-empty-state">
                        <div class="rt-empty-icon">🎉</div>
                        <div class="rt-empty-title">Semua Tugas Beres!</div>
                        <div class="rt-empty-sub">Tidak ada deadline yang tertunda. Pertahankan konsistensimu!</div>
                    </div>
                `;
            } else {
                let html = '';
                items.forEach(task => {
                    const urgencyClass = 'badge-' + task.badge_class;
                    const typeLabel = task.task_type === 'kuliah' ? 'Kuliah' : 'Organisasi';
                    html += `
                        <div class="rt-item-card ${task.badge_class}" id="rt-task-${task.task_type}-${task.id}">
                            <div class="rt-item-header">
                                <span class="rt-badge ${urgencyClass}">
                                    ${typeLabel} • ${task.time_left_text}
                                </span>
                                <a href="${task.url}" class="rt-item-link" target="_blank" title="Buka Detail">
                                    Buka &rarr;
                                </a>
                            </div>
                            <div class="rt-task-row">
                                <input type="checkbox" class="rt-checkbox" 
                                    onchange="window.toggleDrawerTask('${task.task_type}', ${task.id}, this)"
                                    title="Tandai Selesai Langsung">
                                <div class="rt-task-content">
                                    <div class="rt-item-title">${esc(task.title)}</div>
                                    <div class="rt-item-sub">
                                        <span>${esc(task.source)}</span>
                                        <span>• ${esc(task.deadline_formatted)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                dList.innerHTML = html;
            }
        }
    }

    // Toggle Task Done
    window.toggleDrawerTask = async function(taskType, taskId, cb) {
        const card = document.getElementById(`rt-task-${taskType}-${taskId}`);
        if (card) {
            card.style.opacity = '0.4';
        }

        try {
            const fd = new FormData();
            fd.append('action', 'toggle_task');
            fd.append('task_type', taskType);
            fd.append('task_id', taskId);
            fd.append('status', 'selesai');

            const res = await fetch(baseUrl + '/api/widget.php', {
                method: 'POST',
                body: fd
            });
            const resp = await res.json();
            if (resp.status === 'success') {
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.transform = 'translateX(60px)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        fetchRealtimeData(false);
                    }, 300);
                }
            } else {
                alert(resp.message || 'Gagal mengubah status');
                if (card) {
                    card.style.opacity = '1';
                    cb.checked = false;
                }
            }
        } catch (e) {
            console.error(e);
            if (card) {
                card.style.opacity = '1';
                cb.checked = false;
            }
        }
    };

    // Live Clock Ticker
    function updateClock() {
        const now = new Date(Date.now() + timeOffset);
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');

        const elH = document.getElementById('rtClockHours');
        const elM = document.getElementById('rtClockMinutes');
        const elS = document.getElementById('rtClockSeconds');
        if (elH) elH.textContent = h;
        if (elM) elM.textContent = m;
        if (elS) elS.textContent = s;
    }

    setInterval(updateClock, 1000);

    // Initial fetch in background on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetchRealtimeData(false);
        // Auto-poll every 30 seconds
        setInterval(() => fetchRealtimeData(false), 30000);
    });

    // ESC to close drawer
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isWidgetOpen) {
            closeWidgetDrawer();
        }
    });

})();
