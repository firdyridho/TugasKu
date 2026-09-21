document.addEventListener('DOMContentLoaded', function() {
    // 1. Sidebar Toggle (Mobile)
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    const sidebarClose = document.getElementById('sidebarClose');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('open');
        });
    }

    if (sidebarClose && sidebar) {
        sidebarClose.addEventListener('click', function() {
            sidebar.classList.remove('open');
        });
    }

    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('open')) {
            if (!sidebar.contains(e.target) && e.target !== menuToggle) {
                sidebar.classList.remove('open');
            }
        }
    });

    // 2. Date Display
    const dateDisplay = document.getElementById('dateDisplay');
    if (dateDisplay) {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateDisplay.textContent = now.toLocaleDateString('id-ID', options);
    }

    // 3. Alerts auto-dismiss — branded alerts auto-remove after 5s
    function dismissAlert(el) {
        if (!el || !el.parentNode) return;
        el.style.opacity = '0';
        el.style.transform = 'translateY(-10px) scale(0.97)';
        el.style.transition = 'all 0.35s cubic-bezier(0.4,0,0.2,1)';
        setTimeout(function() {
            if (el.parentNode) el.remove();
        }, 350);
    }

    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        // Auto-dismiss after 5s (matching the CSS progress bar animation)
        const timer = setTimeout(function() { dismissAlert(alert); }, 5000);
        // Manual close via X button (already handled inline, but also here)
        const closeBtn = alert.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                clearTimeout(timer);
                dismissAlert(alert);
            });
        }
    });

    // 4. Setup Live Search on all search inputs
    setupLiveSearch();

    // 5. Setup Calendar Category Filtering
    setupCalendarFilter();

    // 6. Check First-Time User Onboarding Tour
    initOnboardingTour();
});

// Modal Helpers
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        const firstInput = modal.querySelector('input:not([type=hidden]), select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});

// Edit Population Functions
function editOrg(id, nama, kategori, deskripsi) {
    document.getElementById('edit_org_id').value = id;
    document.getElementById('edit_org_nama').value = nama;
    document.getElementById('edit_org_kategori').value = kategori;
    document.getElementById('edit_org_deskripsi').value = deskripsi;
    openModal('editOrgModal');
}

function editOrgTask(id, orgId, judul, deskripsi, deadline, tempat, status) {
    document.getElementById('edit_task_id').value = id;
    document.getElementById('edit_task_org_id').value = orgId;
    document.getElementById('edit_task_judul').value = judul;
    document.getElementById('edit_task_deskripsi').value = deskripsi;
    
    // Format deadline for datetime-local input (YYYY-MM-DDTHH:MM)
    if (deadline && deadline.length >= 16) {
        document.getElementById('edit_task_deadline').value = deadline.substring(0, 16).replace(' ', 'T');
    } else {
        document.getElementById('edit_task_deadline').value = '';
    }
    
    document.getElementById('edit_task_tempat').value = tempat || '';
    document.getElementById('edit_task_status').value = status || 'belum';
    openModal('editTaskModal');
}

function editCourse(id, namaMk, dosen, hari, jamMulai, jamSelesai, ruang, kelas) {
    document.getElementById('edit_course_id').value = id;
    document.getElementById('edit_course_nama_mk').value = namaMk;
    document.getElementById('edit_course_dosen').value = dosen;
    document.getElementById('edit_course_hari').value = hari;
    document.getElementById('edit_course_jam_mulai').value = jamMulai ? jamMulai.substring(0, 5) : '';
    document.getElementById('edit_course_jam_selesai').value = jamSelesai ? jamSelesai.substring(0, 5) : '';
    document.getElementById('edit_course_ruang').value = ruang || '';
    const kelasEl = document.getElementById('edit_course_kelas');
    if (kelasEl) kelasEl.value = kelas || '';
    openModal('editCourseModal');
}

function editCourseTask(id, courseId, judul, deskripsi, deadline, tempat, status) {
    document.getElementById('edit_course_task_id').value = id;
    document.getElementById('edit_course_task_course_id').value = courseId;
    document.getElementById('edit_course_task_judul').value = judul;
    document.getElementById('edit_course_task_deskripsi').value = deskripsi;
    
    if (deadline && deadline.length >= 16) {
        document.getElementById('edit_course_task_deadline').value = deadline.substring(0, 16).replace(' ', 'T');
    } else {
        document.getElementById('edit_course_task_deadline').value = '';
    }
    
    document.getElementById('edit_course_task_tempat').value = tempat || '';
    document.getElementById('edit_course_task_status').value = status || 'belum';
    openModal('editCourseTaskModal');
}

function editSchedule(id, judul, deskripsi, hari, jamMulai, jamSelesai, tempat, tipe, warna) {
    document.getElementById('edit_schedule_id').value = id;
    document.getElementById('edit_schedule_judul').value = judul;
    document.getElementById('edit_schedule_deskripsi').value = deskripsi;
    document.getElementById('edit_schedule_hari').value = hari;
    document.getElementById('edit_schedule_jam_mulai').value = jamMulai ? jamMulai.substring(0, 5) : '';
    document.getElementById('edit_schedule_jam_selesai').value = jamSelesai ? jamSelesai.substring(0, 5) : '';
    document.getElementById('edit_schedule_tempat').value = tempat || '';
    document.getElementById('edit_schedule_tipe').value = tipe || 'mandiri';
    document.getElementById('edit_schedule_warna').value = warna || '#6366f1';
    openModal('editScheduleModal');
}

// Live Search
function setupLiveSearch() {
    const searchInputs = document.querySelectorAll('[data-search-target]');
    searchInputs.forEach(function(input) {
        const targetSelector = input.getAttribute('data-search-target');
        const items = document.querySelectorAll(targetSelector);
        const emptyMsgId = input.getAttribute('data-search-empty');

        input.addEventListener('input', function() {
            const query = input.value.toLowerCase().trim();
            let visibleCount = 0;

            items.forEach(function(item) {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (emptyMsgId) {
                const emptyMsg = document.getElementById(emptyMsgId);
                if (emptyMsg) {
                    emptyMsg.style.display = (visibleCount === 0 && items.length > 0) ? 'block' : 'none';
                }
            }
        });
    });
}

// Calendar Category Filter
function setupCalendarFilter() {
    const filterBtns = document.querySelectorAll('.cal-filter-btn');
    if (filterBtns.length === 0) return;

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filterType = this.getAttribute('data-filter');
            const events = document.querySelectorAll('.calendar-event');

            events.forEach(ev => {
                if (filterType === 'all' || ev.classList.contains(filterType)) {
                    ev.style.display = '';
                } else {
                    ev.style.display = 'none';
                }
            });
        });
    });
}

// Interactive Day Detail Modal for Calendar
function showDayDetails(dateLabel, eventsJson) {
    const modal = document.getElementById('dayDetailModal');
    if (!modal) return;

    const titleEl = document.getElementById('dayDetailTitle');
    const listEl = document.getElementById('dayDetailList');
    if (titleEl) titleEl.textContent = 'Agenda: ' + dateLabel;

    let events = [];
    try {
        events = JSON.parse(eventsJson || '[]');
    } catch (e) {
        events = [];
    }

    if (listEl) {
        listEl.innerHTML = '';
        if (events.length === 0) {
            listEl.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 2rem 0;">Tidak ada agenda di tanggal ini.</div>';
        } else {
            events.forEach(ev => {
                const item = document.createElement('div');
                item.className = 'task-item';
                item.style.marginBottom = '0.5rem';

                let color = 'var(--accent)';
                let typeLabel = 'Organisasi';
                if (ev.type === 'kuliah') {
                    color = 'var(--info)';
                    typeLabel = 'Kuliah';
                } else if (ev.type === 'tugas') {
                    color = 'var(--warning)';
                    typeLabel = 'Tugas Kuliah';
                } else if (ev.type === 'jadwal') {
                    color = 'var(--success)';
                    typeLabel = 'Jadwal Mandiri';
                }

                item.innerHTML = `
                    <div style="width: 4px; height: 38px; background: ${color}; border-radius: 2px; flex-shrink: 0;"></div>
                    <div class="task-content">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="badge" style="background: ${color}20; color: ${color}; font-size: 0.7rem;">${typeLabel}</span>
                            ${ev.time ? `<span style="font-size: 0.78rem; color: var(--text-muted);">${ev.time}</span>` : ''}
                        </div>
                        <h4 style="margin-top: 0.25rem;">${escapeHtml(ev.judul)}</h4>
                        ${ev.source ? `<p style="font-size: 0.8rem; color: var(--text-secondary);">${escapeHtml(ev.source)}</p>` : ''}
                    </div>
                `;
                listEl.appendChild(item);
            });
        }
    }

    openModal('dayDetailModal');
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

// ==========================================
// 6. ON-PAGE DOM SPOTLIGHT TOUR ("DI KOTAKIN LANGSUNG DI HALAMAN")
// ==========================================
const spotlightTourSteps = [
    {
        selector: '.welcome-banner',
        tag: 'Langkah 1 dari 5',
        title: 'Pusat Komando Produktivitas',
        desc: 'Selamat datang di TugasKu! Di sini Anda dapat memantau tugas kuliah, agenda organisasi, jadwal harian, dan tenggat waktu terdekat secara terpusat dan rapi.',
        tip: 'Gunakan tombol aksi cepat di sebelah kanan banner untuk langsung menambahkan proker organisasi, mata kuliah, atau jadwal mandiri baru.',
        icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>`
    },
    {
        selector: '.stats-grid',
        tag: 'Langkah 2 dari 5',
        title: 'Statistik & Indikator Progres',
        desc: 'Kartu ringkasan ini memantau total mata kuliah aktif, organisasi yang Anda ikuti, tugas yang menunggu penyelesaian, serta persentase penyelesaian tugas mingguan Anda secara akurat.',
        tip: 'Tingkat persentase tuntas akan otomatis bertambah setiap kali Anda menyelesaikan tugas perkuliahan atau divisi.',
        icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`
    },
    {
        selector: '.dashboard-grid',
        tag: 'Langkah 3 dari 5',
        title: 'Jadwal Hari Ini & Deadline Terdekat',
        desc: 'Sistem secara otomatis mendeteksi hari ini dan menampilkan daftar kelas yang harus dihadiri, serta mengurutkan deadline tugas berdasarkan tingkat kedaruratan.',
        tip: 'Label urgensi berwarna merah menandakan tugas yang mendekati tenggat waktu atau harus dikumpulkan hari ini.',
        icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`
    },
    {
        selector: '#sidebar .sidebar-nav',
        tag: 'Langkah 4 dari 5',
        title: 'Pemisahan Organisasi & Kampus',
        desc: 'Menu samping memisahkan ruang khusus Organisasi (BEM, Himpunan, UKM), Kuliah, Jadwal Mandiri, Kalender, dan Ekspor Data Google Sheets agar tidak tercampur aduk.',
        tip: 'Gunakan fitur Ekspor Data untuk mengunduh laporan tugas rapi dalam format CSV yang kompatibel langsung dengan Google Sheets dan Excel.',
        icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`
    },
    {
        selector: '#aiFloatingBtn',
        tag: 'Langkah 5 dari 5',
        title: 'Asisten AI TugasKu (NVIDIA NIM)',
        desc: 'Butuh bantuan menyusun prioritas tugas, strategi belajar UAS, draf email sopan izin tidak masuk ke dosen, atau pembagian proker? Klik tombol TugasKu AI di kanan bawah kapan saja!',
        tip: 'Didukung oleh model AI NVIDIA yang cerdas, cepat, dan siap mendampingi studi Anda 24/7.',
        icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>`
    }
];

let currentSpotlightIndex = 0;
let isTourActive = false;

function initOnboardingTour() {
    const mask = document.getElementById('spotlightMask');
    if (!mask) return;

    // Check if user already completed the tour
    const completed = localStorage.getItem('tugasku_spotlight_completed');
    if (!completed) {
        setTimeout(function() {
            startSpotlightTour(false);
        }, 800);
    }
}

function startSpotlightTour(force = false) {
    const mask = document.getElementById('spotlightMask');
    if (!mask) return;

    isTourActive = true;
    currentSpotlightIndex = 0;
    mask.style.display = 'block';
    renderSpotlightStep(0);

    // Reposition on window resize or scroll
    window.addEventListener('resize', handleTourReposition);
    window.addEventListener('scroll', handleTourReposition, { passive: true });
}

function closeSpotlightTour(markCompleted = true) {
    const mask = document.getElementById('spotlightMask');
    if (!mask) return;

    mask.style.display = 'none';
    isTourActive = false;
    window.removeEventListener('resize', handleTourReposition);
    window.removeEventListener('scroll', handleTourReposition);

    if (markCompleted) {
        localStorage.setItem('tugasku_spotlight_completed', 'true');
    }
}

function handleTourReposition() {
    if (!isTourActive) return;
    const step = spotlightTourSteps[currentSpotlightIndex];
    if (step) {
        const targetEl = document.querySelector(step.selector);
        positionSpotlight(targetEl);
    }
}

function renderSpotlightStep(index) {
    if (index < 0 || index >= spotlightTourSteps.length) return;
    currentSpotlightIndex = index;
    const step = spotlightTourSteps[index];

    const tagEl = document.getElementById('spotlightStepTag');
    const titleEl = document.getElementById('spotlightTitle');
    const descEl = document.getElementById('spotlightDesc');
    const tipTextEl = document.getElementById('spotlightTipText');
    const iconEl = document.getElementById('spotlightIcon');
    const btnPrev = document.getElementById('spotlightBtnPrev');
    const btnNext = document.getElementById('spotlightBtnNext');
    const nextLabel = document.getElementById('spotlightNextLabel');
    const dotsEl = document.getElementById('spotlightDots');

    if (tagEl) tagEl.textContent = step.tag;
    if (titleEl) titleEl.textContent = step.title;
    if (descEl) descEl.textContent = step.desc;
    if (tipTextEl) tipTextEl.textContent = step.tip;
    if (iconEl) iconEl.innerHTML = step.icon;

    if (btnPrev) {
        btnPrev.style.display = index === 0 ? 'none' : 'inline-block';
    }

    if (nextLabel) {
        if (index === spotlightTourSteps.length - 1) {
            nextLabel.textContent = 'Mulai Eksplorasi';
            if (btnNext) btnNext.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
        } else {
            nextLabel.textContent = 'Lanjut';
            if (btnNext) btnNext.style.background = '';
        }
    }

    // Dots
    if (dotsEl) {
        dotsEl.innerHTML = '';
        spotlightTourSteps.forEach((s, i) => {
            const dot = document.createElement('span');
            dot.className = 'spotlight-dot' + (i === index ? ' active' : '');
            dot.addEventListener('click', () => renderSpotlightStep(i));
            dotsEl.appendChild(dot);
        });
    }

    // Find and box target DOM element
    const targetEl = document.querySelector(step.selector);
    positionSpotlight(targetEl);
}

function positionSpotlight(targetEl) {
    const box = document.getElementById('spotlightBox');
    const popover = document.getElementById('spotlightPopover');
    if (!box || !popover) return;

    if (!targetEl) {
        // Fallback center of screen
        box.style.display = 'none';
        popover.style.top = '50%';
        popover.style.left = '50%';
        popover.style.transform = 'translate(-50%, -50%)';
        return;
    }

    // Scroll element into view smoothly
    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });

    setTimeout(() => {
        const rect = targetEl.getBoundingClientRect();
        const pad = 8;

        box.style.display = 'block';
        box.style.top = Math.max(0, rect.top - pad) + 'px';
        box.style.left = Math.max(0, rect.left - pad) + 'px';
        box.style.width = (rect.width + pad * 2) + 'px';
        box.style.height = (rect.height + pad * 2) + 'px';

        const compStyle = window.getComputedStyle(targetEl);
        box.style.borderRadius = compStyle.borderRadius || '12px';

        // Position popover
        const popW = Math.min(360, window.innerWidth - 28);
        const popH = 260;
        const viewW = window.innerWidth;
        const viewH = window.innerHeight;

        let top = rect.bottom + 14;
        let left = rect.left + (rect.width / 2) - (popW / 2);

        // If bottom overflows screen, show above target
        if (top + popH > viewH) {
            top = Math.max(16, rect.top - popH - 14);
        }

        // Clamp horizontal
        if (left < 14) left = 14;
        if (left + popW > viewW - 14) left = viewW - popW - 14;

        popover.style.transform = 'none';
        popover.style.top = top + 'px';
        popover.style.left = left + 'px';
    }, 180);
}

function nextSpotlightStep() {
    if (currentSpotlightIndex < spotlightTourSteps.length - 1) {
        renderSpotlightStep(currentSpotlightIndex + 1);
    } else {
        closeSpotlightTour(true);
    }
}

function prevSpotlightStep() {
    if (currentSpotlightIndex > 0) {
        renderSpotlightStep(currentSpotlightIndex - 1);
    }
}

// Backward compatibility alias for any caller
window.openTugasKuTour = startSpotlightTour;
window.closeTourModal = closeSpotlightTour;
window.nextTourStep = nextSpotlightStep;
window.prevTourStep = prevSpotlightStep;

// New Spotlight exposes
window.startSpotlightTour = startSpotlightTour;
window.closeSpotlightTour = closeSpotlightTour;
window.nextSpotlightStep = nextSpotlightStep;
window.prevSpotlightStep = prevSpotlightStep;

