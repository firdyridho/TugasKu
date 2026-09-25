<?php
$pageTitle = 'Dashboard Utama';
require_once __DIR__ . '/../config.php';
requireLogin();

$userId = $_SESSION['user_id'];

// Hitung Statistik
$stats = [
    'org' => 0,
    'org_tasks_active' => 0,
    'org_tasks_done' => 0,
    'courses' => 0,
    'course_tasks_active' => 0,
    'course_tasks_done' => 0,
    'schedules' => 0,
];

$r = $conn->query("SELECT COUNT(*) as c FROM organisations WHERE user_id = $userId")->fetch_assoc();
$stats['org'] = (int)$r['c'];

$r = $conn->query("SELECT 
    SUM(CASE WHEN status != 'selesai' THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as done_count
    FROM org_tasks WHERE user_id = $userId")->fetch_assoc();
$stats['org_tasks_active'] = (int)($r['active_count'] ?? 0);
$stats['org_tasks_done'] = (int)($r['done_count'] ?? 0);

$r = $conn->query("SELECT COUNT(*) as c FROM courses WHERE user_id = $userId")->fetch_assoc();
$stats['courses'] = (int)$r['c'];

$r = $conn->query("SELECT 
    SUM(CASE WHEN status != 'selesai' THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as done_count
    FROM course_tasks WHERE user_id = $userId")->fetch_assoc();
$stats['course_tasks_active'] = (int)($r['active_count'] ?? 0);
$stats['course_tasks_done'] = (int)($r['done_count'] ?? 0);

$r = $conn->query("SELECT COUNT(*) as c FROM schedules WHERE user_id = $userId")->fetch_assoc();
$stats['schedules'] = (int)$r['c'];

$totalActiveTasks = $stats['org_tasks_active'] + $stats['course_tasks_active'];
$totalDoneTasks = $stats['org_tasks_done'] + $stats['course_tasks_done'];
$totalAllTasks = $totalActiveTasks + $totalDoneTasks;
$completionPercentage = $totalAllTasks > 0 ? round(($totalDoneTasks / $totalAllTasks) * 100) : 0;

// Deadline terdekat
$upcomingOrgTasks = $conn->query("
    SELECT ot.*, o.nama as source_name, 'org' as task_type, o.id as parent_id
    FROM org_tasks ot 
    JOIN organisations o ON ot.org_id = o.id 
    WHERE ot.user_id = $userId AND ot.status != 'selesai' AND ot.deadline IS NOT NULL 
    ORDER BY ot.deadline ASC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

$upcomingCourseTasks = $conn->query("
    SELECT ct.*, c.nama_mk as source_name, 'course' as task_type, c.id as parent_id
    FROM course_tasks ct 
    JOIN courses c ON ct.course_id = c.id 
    WHERE ct.user_id = $userId AND ct.status != 'selesai' AND ct.deadline IS NOT NULL 
    ORDER BY ct.deadline ASC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

$allUpcomingTasks = array_merge($upcomingOrgTasks, $upcomingCourseTasks);
usort($allUpcomingTasks, function($a, $b) {
    return strtotime($a['deadline']) - strtotime($b['deadline']);
});
$allUpcomingTasks = array_slice($allUpcomingTasks, 0, 5);

// Jadwal Hari Ini
$hariMap = [
    'sunday' => 'minggu', 'monday' => 'senin', 'tuesday' => 'selasa', 
    'wednesday' => 'rabu', 'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu'
];
$todayEnglish = strtolower(date('l'));
$todayIndo = $hariMap[$todayEnglish] ?? $todayEnglish;

$todayCourses = $conn->query("
    SELECT * FROM courses WHERE user_id = $userId AND hari = '$todayIndo' ORDER BY jam_mulai ASC
")->fetch_all(MYSQLI_ASSOC);

$todaySchedules = $conn->query("
    SELECT * FROM schedules WHERE user_id = $userId AND hari = '$todayIndo' ORDER BY jam_mulai ASC
")->fetch_all(MYSQLI_ASSOC);

// Penentuan Sapaan Waktu
$hour = (int)date('H');
if ($hour >= 4 && $hour < 11) {
    $greeting = 'Selamat Pagi';
} elseif ($hour >= 11 && $hour < 15) {
    $greeting = 'Selamat Siang';
} elseif ($hour >= 15 && $hour < 18) {
    $greeting = 'Selamat Sore';
} else {
    $greeting = 'Selamat Malam';
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="welcome-banner">
    <div class="welcome-text">
        <h2><?= $greeting ?>, <?= htmlspecialchars(explode(' ', $user['nama'] ?? 'Mahasiswa')[0]) ?></h2>
        <p>Kelola seluruh aktivitas perkuliahan, tugas aktif, dan organisasi Anda secara terpusat dan efisien.</p>
    </div>
    <div class="welcome-actions">
        <a href="<?= BASE_URL ?>/organisasi/" class="btn btn-secondary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Organisasi
        </a>
        <a href="<?= BASE_URL ?>/kuliah/" class="btn btn-secondary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Mata Kuliah
        </a>
        <a href="<?= BASE_URL ?>/jadwal/" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Jadwal Mandiri
        </a>
        <button type="button" onclick="if(window.toggleRealtimeWidgetDrawer) window.toggleRealtimeWidgetDrawer()" class="btn btn-secondary btn-sm" title="Buka Realtime Widget (Jadwal & Deadline Hari Ini)">
            <span class="live-pulse-dot" style="width: 7px; height: 7px;"></span>
            Live Widget
        </button>
    </div>
</div>

<!-- Mobile Quick Access & Logout Bar (Khusus Tampilan HP) -->
<style>
.mobile-quick-hub {
    display: none;
}
@media (max-width: 768px) {
    .mobile-quick-hub {
        display: block;
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 0.95rem 1rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    }
    .mq-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 0.75rem;
        margin-bottom: 0.75rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    }
    .mq-user {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }
    .mq-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--accent-gradient);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
    }
    .mq-user-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .mq-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.25;
    }
    .mq-status {
        font-size: 0.7rem;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mq-logout-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.8rem;
        border-radius: 9px;
        background: #fef2f2;
        border: 1px solid #fee2e2;
        color: #dc2626;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        flex-shrink: 0;
        transition: all 150ms ease;
    }
    .mq-logout-btn:active {
        background: #fee2e2;
        transform: scale(0.96);
    }
    .mq-chips-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }
    .mq-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.75rem;
        border-radius: 9px;
        background: var(--bg-hover);
        border: 1px solid var(--border);
        color: var(--text-secondary);
        font-size: 0.76rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 150ms ease;
        line-height: 1;
    }
    .mq-chip svg {
        color: var(--accent);
        flex-shrink: 0;
    }
    .mq-chip:active {
        background: rgba(79, 70, 229, 0.08);
        border-color: rgba(79, 70, 229, 0.3);
        color: var(--accent);
        transform: scale(0.96);
    }
    .mq-chip-btn {
        cursor: pointer;
        font-family: inherit;
        border: 1px solid var(--border);
    }
}
</style>

<div class="mobile-quick-hub" id="mobileQuickHub">
    <div class="mq-header">
        <div class="mq-user">
            <span class="mq-avatar"><?= strtoupper(substr($user['nama'] ?? 'U', 0, 1)) ?></span>
            <div class="mq-user-info">
                <span class="mq-name"><?= htmlspecialchars($user['nama'] ?? 'Mahasiswa') ?></span>
                <span class="mq-status"><?= htmlspecialchars($user['nim'] ?? ($user['jurusan'] ?? 'Akun Mahasiswa Aktif')) ?></span>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/auth/logout" class="mq-logout-btn" onclick="openLogoutModal(event)" title="Keluar dari Akun">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Keluar</span>
        </a>
    </div>

    <div class="mq-chips-grid">
        <button type="button" class="mq-chip mq-chip-btn" onclick="if(window.toggleRealtimeWidgetDrawer) window.toggleRealtimeWidgetDrawer()" style="background: rgba(99,102,241,0.1); border-color: rgba(99,102,241,0.3); color: var(--accent);">
            <span class="live-pulse-dot" style="width: 6px; height: 6px;"></span>
            <span>Live Widget</span>
        </button>
        <a href="<?= BASE_URL ?>/jadwal/" class="mq-chip">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Jadwal Mandiri</span>
        </a>
        <a href="<?= BASE_URL ?>/kalender/" class="mq-chip">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span>Kalender</span>
        </a>
        <a href="<?= BASE_URL ?>/ekspor" class="mq-chip">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Ekspor Data</span>
        </a>
        <a href="<?= BASE_URL ?>/setting/" class="mq-chip">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <span>Pengaturan</span>
        </a>
        <button type="button" class="mq-chip mq-chip-btn" onclick="openTugasKuTour(true)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>Panduan</span>
        </button>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon purple">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-info" style="flex: 1;">
            <h3><?= $stats['org'] ?></h3>
            <p>Organisasi & UKM</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div class="stat-info" style="flex: 1;">
            <h3><?= $totalActiveTasks ?></h3>
            <p>Tugas Belum Selesai</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <div class="stat-info" style="flex: 1;">
            <h3><?= $stats['courses'] ?></h3>
            <p>Mata Kuliah</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-info" style="flex: 1;">
            <h3><?= $completionPercentage ?>%</h3>
            <p><?= $totalDoneTasks ?> dari <?= $totalAllTasks ?> Tugas Tuntas</p>
            <div class="progress-container">
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: <?= $completionPercentage ?>%; background: var(--success);"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Jadwal Hari Ini -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Jadwal Hari Ini</h2>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 2px;"><?= ucfirst($todayIndo) ?>, <?= date('d M Y') ?></p>
            </div>
            <a href="<?= BASE_URL ?>/kalender/" class="btn btn-ghost btn-sm">Lihat Kalender</a>
        </div>
        <?php if (empty($todayCourses) && empty($todaySchedules)): ?>
            <div class="empty-state" style="padding: 2.5rem 1rem;">
                <div class="empty-icon" style="width: 48px; height: 48px; margin-bottom: 0.75rem;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <h3>Tidak Ada Jadwal Hari Ini</h3>
                <p>Tidak ada perkuliahan maupun jadwal mandiri terdaftar untuk hari <?= ucfirst($todayIndo) ?>.</p>
                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                    <a href="<?= BASE_URL ?>/kuliah/" class="btn btn-secondary btn-sm">Atur Kuliah</a>
                    <a href="<?= BASE_URL ?>/jadwal/" class="btn btn-secondary btn-sm">Tambah Jadwal</a>
                </div>
            </div>
        <?php else: ?>
            <div class="task-list">
                <?php foreach ($todayCourses as $c): ?>
                    <div class="task-item">
                        <div style="width: 4px; height: 38px; background: var(--info); border-radius: 2px; flex-shrink: 0;"></div>
                        <div class="task-content">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.2rem;">
                                <span class="badge badge-blue">Kuliah</span>
                                <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-secondary);"><?= substr($c['jam_mulai'], 0, 5) ?> - <?= substr($c['jam_selesai'], 0, 5) ?> WIB</span>
                            </div>
                            <h4><?= htmlspecialchars($c['nama_mk']) ?></h4>
                            <p><?= htmlspecialchars($c['dosen']) ?> &bull; Ruang <?= htmlspecialchars($c['ruang'] ?: 'TBA') ?></p>
                        </div>
                        <a href="<?= BASE_URL ?>/kuliah/detail?id=<?= $c['id'] ?>" class="btn btn-ghost btn-sm" title="Lihat Tugas MK">
                            Buka
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($todaySchedules as $s): ?>
                    <div class="task-item">
                        <div style="width: 4px; height: 38px; background: <?= htmlspecialchars($s['warna'] ?: '#6366f1') ?>; border-radius: 2px; flex-shrink: 0;"></div>
                        <div class="task-content">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.2rem;">
                                <span class="badge" style="background: <?= htmlspecialchars($s['warna']) ?>20; color: <?= htmlspecialchars($s['warna']) ?>;"><?= ucfirst($s['tipe']) ?></span>
                                <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-secondary);"><?= substr($s['jam_mulai'], 0, 5) ?> - <?= substr($s['jam_selesai'], 0, 5) ?> WIB</span>
                            </div>
                            <h4><?= htmlspecialchars($s['judul']) ?></h4>
                            <?php if ($s['tempat']): ?>
                                <p>Lokasi: <?= htmlspecialchars($s['tempat']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deadline Terdekat -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Deadline Tugas Terdekat</h2>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 2px;">Pantau tugas sebelum batas waktu berakhir</p>
            </div>
            <a href="<?= BASE_URL ?>/kalender/" class="btn btn-ghost btn-sm">Semua Deadline</a>
        </div>
        <?php if (empty($allUpcomingTasks)): ?>
            <div class="empty-state" style="padding: 2.5rem 1rem;">
                <div class="empty-icon" style="width: 48px; height: 48px; margin-bottom: 0.75rem;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3>Semua Tugas Telah Selesai</h3>
                <p>Bagus sekali! Tidak ada tugas yang memiliki batas waktu mendatang.</p>
            </div>
        <?php else: ?>
            <div class="task-list">
                <?php foreach ($allUpcomingTasks as $t): ?>
                    <?php
                    $isOrg = ($t['task_type'] === 'org');
                    $deadlineTime = strtotime($t['deadline']);
                    $now = time();
                    $diffSeconds = $deadlineTime - $now;
                    $diffDays = floor($diffSeconds / 86400);

                    if ($diffSeconds < 0) {
                        $urgencyClass = 'urgency-overdue';
                        $urgencyText = 'Terlewat';
                    } elseif ($diffDays == 0) {
                        $urgencyClass = 'urgency-urgent';
                        $urgencyText = 'Hari Ini (' . date('H:i', $deadlineTime) . ')';
                    } elseif ($diffDays == 1) {
                        $urgencyClass = 'urgency-soon';
                        $urgencyText = 'Besok (' . date('H:i', $deadlineTime) . ')';
                    } else {
                        $urgencyClass = 'urgency-safe';
                        $urgencyText = $diffDays . ' hari lagi';
                    }

                    $detailUrl = $isOrg ? (BASE_URL . '/organisasi/detail?id=' . $t['parent_id']) : (BASE_URL . '/kuliah/detail?id=' . $t['parent_id']);
                    ?>
                    <div class="task-item">
                        <div style="width: 4px; height: 38px; background: <?= $isOrg ? 'var(--accent)' : 'var(--info)' ?>; border-radius: 2px; flex-shrink: 0;"></div>
                        <div class="task-content">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.2rem;">
                                <span class="badge <?= $isOrg ? 'badge-purple' : 'badge-blue' ?>"><?= $isOrg ? 'Organisasi' : 'Kuliah' ?></span>
                                <span class="urgency-badge <?= $urgencyClass ?>"><?= $urgencyText ?></span>
                            </div>
                            <h4><?= htmlspecialchars($t['judul']) ?></h4>
                            <p><?= htmlspecialchars($t['source_name']) ?> <?= $t['tempat_pengumpulan'] ? ' &bull; Pengumpulan: ' . htmlspecialchars($t['tempat_pengumpulan']) : '' ?></p>
                        </div>
                        <a href="<?= $detailUrl ?>" class="btn btn-ghost btn-sm" title="Lihat Detail Tugas">
                            Periksa
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
