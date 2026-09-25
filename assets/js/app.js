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
        mobileSelector: '.welcome-banner',
        tag: 'Langkah 1 dari 6',
        title: 'Pusat Komando & Aksi Cepat',
        desc: 'Selamat datang di TugasKu! Banner ini adalah pusat kendali perkuliahan dan organisasi Anda. Sapaan dinamis menyesuaikan waktu, dilengkapi ringkasan status harian serta tombol aksi instan untuk mencatat agenda baru tanpa ribet berpindah halaman.',
        features: [
            'Tombol Aksi Cepat: Tambah Proker Organisasi, Mata Kuliah, dan Jadwal Mandiri',
            'Sapaan Waktu Otomatis & Identitas Mahasiswa Terintegrasi'
        ],
        tip: 'Klik salah satu tombol aksi cepat di banner kapan pun dosen mengumumkan tugas baru atau ketua divisi menetapkan proker!',
        icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>`
    },
    {
        selector: '.stats-grid',
        mobileSelector: '.stats-grid',
        tag: 'Langkah 2 dari 6',
        title: 'Metrik Real-Time & Progres Tuntas',
        desc: 'Empat kartu ringkasan ini memantau performa akademik dan keaktifan organisasi Anda secara akurat. Memuat jumlah organisasi aktif, tugas yang belum diselesaikan, total mata kuliah semester ini, serta persentase produktivitas mingguan Anda.',
        features: [
            'Indikator Persentase Tuntas: Otomatis naik saat tugas diselesaikan',
            'Sinkronisasi Otomatis: Terhubung langsung dengan database tugas divisi & kuliah'
        ],
        tip: 'Jaga persentase tuntas di atas 80% setiap pekan agar tidak terjadi penumpukan tugas menjelang pekan UTS/UAS.',
        icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`
    },
    {
        selector: '.dashboard-grid',
        mobileSelector: '.dashboard-grid',
        tag: 'Langkah 3 dari 6',
        title: 'Jadwal Hari Ini & Deteksi Deadline Darurat',
        desc: 'Sistem TugasKu otomatis mendeteksi hari ini (Senin - Minggu) dan menampilkan mata kuliah yang harus dihadiri beserta ruangan dan jam mulainya. Kolom sebelah kanan mengurutkan deadline terdekat berdasarkan tingkat kedaruratan waktu.',
        features: [
            'Badge Urgensi Cerdas: Merah (Mendesak / <24 jam), Kuning (Mendekati), Hijau (Aman)',
            'Centang Selesai Instan: Selesaikan tugas langsung dari kartu dashboard utama'
        ],
        tip: 'Tugas berlabel merah wajib didahulukan hari ini agar Anda tidak terlambat mengumpulkan ke dosen pengampu.',
        icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`
    },
    {
        selector: '#sidebar .sidebar-nav',
        mobileSelector: '#mobileBottomNav',
        tag: 'Langkah 4 dari 6',
        title: 'Pemisahan Ruang: Kuliah vs Organisasi',
        desc: 'TugasKu memisahkan secara tegas ruang kerja Organisasi Mahasiswa (BEM, HIMA, UKM) dari Ruang Akademik Kuliah. Masing-masing memiliki manajemen divisi, anggota, berkas rapat, silabus, dan daftar tugas tersendiri tanpa bercampur aduk.',
        features: [
            'Ruang Organisasi: Kelola divisi, proker, dan anggaran kas kepengurusan',
            'Ruang Kuliah: Arsip modul materi, jadwal kelas, dan tugas per mata kuliah'
        ],
        tip: 'Gunakan navigasi ini untuk berpindah ruang secara instan baik di laptop maupun melalui bilah menu bawah di HP.',
        icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`
    },
    {
        selector: '.content-wrapper',
        mobileSelector: '.content-wrapper',
        tag: 'Langkah 5 dari 6',
        title: 'Kalender Terpadu & Ekspor Data Sheets',
        desc: 'Semua deadline tugas, kelas kuliah, dan proker dipetakan ke dalam satu Kalender Akademik interaktif. Anda juga dapat mengekspor rekapitulasi data lengkap ke format CSV yang kompatibel 100% dengan Google Sheets dan Microsoft Excel.',
        features: [
            'Kalender Terpadu: Klik tanggal mana saja untuk melihat detail seluruh agenda hari itu',
            'Ekspor CSV / Sheets: Cocok untuk backup data atau bahan LPJ divisi organisasi'
        ],
        tip: 'Menu Ekspor Data di sidebar/pengaturan dapat diakses kapan saja untuk mengunduh rekap tugas semester.',
        icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`
    },
    {
        selector: '#aiFloatingBtn',
        mobileSelector: '#aiFloatingBtn',
        tag: 'Langkah 6 dari 6',
        title: 'Asisten AI TugasKu (NVIDIA NIM)',
        desc: 'Butuh bantuan menyusun prioritas tugas, membuat draf email sopan ke dosen, strategi belajar UAS, atau pembagian proker divisi? Asisten AI siap membantu Anda 24/7 dengan respon cerdas, cepat, dan berbobot akademis.',
        features: [
            'Didukung model AI resmi NVIDIA NIM dengan pemrosesan kilat',
            'Format jawaban rapi terstruktur dengan tombol Salin Cepat',
            'Dilengkapi indikator berpikir neural wave yang futuristik'
        ],
        tip: 'Klik tombol TugasKu AI di kanan bawah kapan pun Anda butuh inspirasi atau bantuan akademis!',
        icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>`
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

function getTourTargetElement(step) {
    if (!step) return null;
    const isMobile = window.innerWidth <= 768;
    if (isMobile && step.mobileSelector) {
        const el = document.querySelector(step.mobileSelector);
        if (el && el.offsetParent !== null) return el;
    }
    return document.querySelector(step.selector);
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
        const targetEl = getTourTargetElement(step);
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
    
    // Rich content: description + feature bullets
    if (descEl) {
        let contentHtml = `<div class="spotlight-desc-text">${escapeHtml(step.desc)}</div>`;
        if (step.features && step.features.length > 0) {
            contentHtml += `<ul class="spotlight-features-list">`;
            step.features.forEach(feat => {
                contentHtml += `<li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg><span>${escapeHtml(feat)}</span></li>`;
            });
            contentHtml += `</ul>`;
        }
        descEl.innerHTML = contentHtml;
    }

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
    const targetEl = getTourTargetElement(step);
    positionSpotlight(targetEl);
}

/**
 * Smart Collision-Free Spotlight Positioning
 * Guarantees popover NEVER covers the spotlighted element
 */
function positionSpotlight(targetEl) {
    const box = document.getElementById('spotlightBox');
    const popover = document.getElementById('spotlightPopover');
    if (!box || !popover) return;

    const isMobile = window.innerWidth <= 768;

    if (!targetEl || targetEl.offsetParent === null) {
        // Fallback center of screen
        box.style.display = 'none';
        popover.className = 'spotlight-popover pos-center';
        popover.style.top = '50%';
        popover.style.left = '50%';
        popover.style.bottom = 'auto';
        popover.style.right = 'auto';
        popover.style.transform = 'translate(-50%, -50%)';
        return;
    }

    const pad = isMobile ? 6 : 10;
    const viewW = window.innerWidth;
    const viewH = window.innerHeight;

    // Mobile Viewport Logic: Dock at bottom as bottom-card, scroll target to upper screen area
    if (isMobile) {
        // Smooth scroll target so it sits in upper half with clear breathing space
        const currentRect = targetEl.getBoundingClientRect();
        const idealTop = 75; // safe margin below topbar
        const scrollDelta = currentRect.top - idealTop;
        window.scrollBy({ top: scrollDelta, behavior: 'smooth' });

        setTimeout(() => {
            const rect = targetEl.getBoundingClientRect();
            box.style.display = 'block';
            box.style.top = Math.max(0, rect.top - pad) + 'px';
            box.style.left = Math.max(4, rect.left - pad) + 'px';
            box.style.width = Math.min(viewW - 8, rect.width + pad * 2) + 'px';
            box.style.height = (rect.height + pad * 2) + 'px';

            const compStyle = window.getComputedStyle(targetEl);
            box.style.borderRadius = compStyle.borderRadius || '12px';

            // Dock popover at bottom sheet with safe margin above bottom nav
            popover.className = 'spotlight-popover pos-mobile-bottom';
            popover.style.top = 'auto';
            popover.style.bottom = '76px';
            popover.style.left = '12px';
            popover.style.right = '12px';
            popover.style.width = 'auto';
            popover.style.maxWidth = '460px';
            popover.style.margin = '0 auto';
            popover.style.transform = 'none';
        }, 220);
        return;
    }

    // Desktop Viewport Logic: Calculate smart vertical and horizontal clearance
    // Pre-calculate whether placement below or above is better
    const targetCenterY = targetEl.offsetTop + targetEl.offsetHeight / 2;
    const estimatedPopH = 310;
    const popW = Math.min(410, viewW - 32);

    // Scroll so element has sufficient room
    const elemRect = targetEl.getBoundingClientRect();
    if (elemRect.top < 90 || elemRect.bottom > viewH - 120) {
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    setTimeout(() => {
        const rect = targetEl.getBoundingClientRect();
        box.style.display = 'block';
        box.style.top = Math.max(0, rect.top - pad) + 'px';
        box.style.left = Math.max(0, rect.left - pad) + 'px';
        box.style.width = (rect.width + pad * 2) + 'px';
        box.style.height = (rect.height + pad * 2) + 'px';

        const compStyle = window.getComputedStyle(targetEl);
        box.style.borderRadius = compStyle.borderRadius || '14px';

        const actualPopH = popover.offsetHeight || estimatedPopH;
        const spaceBelow = viewH - rect.bottom;
        const spaceAbove = rect.top;
        const spaceRight = viewW - rect.right;
        const spaceLeft = rect.left;

        let finalTop = 0;
        let finalLeft = 0;
        let placementClass = 'pos-below';

        // 1. Check if it fits comfortably BELOW target
        if (spaceBelow >= actualPopH + 18) {
            finalTop = rect.bottom + 14;
            finalLeft = rect.left + (rect.width / 2) - (popW / 2);
            placementClass = 'pos-below';
        }
        // 2. Else check if it fits comfortably ABOVE target
        else if (spaceAbove >= actualPopH + 18) {
            finalTop = rect.top - actualPopH - 14;
            finalLeft = rect.left + (rect.width / 2) - (popW / 2);
            placementClass = 'pos-above';
        }
        // 3. Else check side placement (e.g. for sidebar or tall cards)
        else if (spaceRight >= popW + 24) {
            finalTop = Math.max(16, Math.min(rect.top, viewH - actualPopH - 16));
            finalLeft = rect.right + 16;
            placementClass = 'pos-right';
        }
        else if (spaceLeft >= popW + 24) {
            finalTop = Math.max(16, Math.min(rect.top, viewH - actualPopH - 16));
            finalLeft = rect.left - popW - 16;
            placementClass = 'pos-left';
        }
        // 4. Fallback: Place at bottom right floating dock without obscuring main content
        else {
            finalTop = Math.max(16, viewH - actualPopH - 24);
            finalLeft = Math.max(16, viewW - popW - 24);
            placementClass = 'pos-floating';
        }

        // Clamp horizontal boundaries safely inside viewport
        if (finalLeft < 16) finalLeft = 16;
        if (finalLeft + popW > viewW - 16) finalLeft = viewW - popW - 16;

        popover.className = 'spotlight-popover ' + placementClass;
        popover.style.top = finalTop + 'px';
        popover.style.left = finalLeft + 'px';
        popover.style.bottom = 'auto';
        popover.style.right = 'auto';
        popover.style.width = popW + 'px';
        popover.style.transform = 'none';
    }, 200);
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


