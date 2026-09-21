<?php
$pageTitle = 'Kalender Aktivitas & Deadline';
require_once __DIR__ . '/../config.php';
requireLogin();

$userId = $_SESSION['user_id'];
$month  = (int)($_GET['month'] ?? date('m'));
$year   = (int)($_GET['year']  ?? date('Y'));
$mode   = in_array($_GET['mode'] ?? '', ['bulan','minggu','agenda']) ? $_GET['mode'] : 'bulan';
$weekOffset = (int)($_GET['week_offset'] ?? 0); // 0 = current week, -1 = last week, etc.

if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1;  $year++; }

$monthNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$dayNames   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$dayShort   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

$firstDay       = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth    = (int)date('t', $firstDay);
$startDay       = (int)date('w', $firstDay);

$prevMonth = $month === 1 ? 12 : $month - 1;
$prevYear  = $month === 1 ? $year - 1 : $year;
$nextMonth = $month === 12 ? 1 : $month + 1;
$nextYear  = $month === 12 ? $year + 1 : $year;

$currentDayReal   = (int)date('j');
$currentMonthReal = (int)date('m');
$currentYearReal  = (int)date('Y');

$hariMap = [
    'Sunday' => 'minggu','Monday' => 'senin','Tuesday' => 'selasa',
    'Wednesday' => 'rabu','Thursday' => 'kamis','Friday' => 'jumat','Saturday' => 'sabtu'
];

// Fetch all events for the month
$events = [];

$orgTasks = $conn->query("
    SELECT ot.judul, ot.deadline, ot.status, o.nama as source, 'org' as type
    FROM org_tasks ot 
    JOIN organisations o ON ot.org_id = o.id
    WHERE ot.user_id = $userId AND ot.deadline IS NOT NULL
    AND MONTH(ot.deadline) = $month AND YEAR(ot.deadline) = $year
")->fetch_all(MYSQLI_ASSOC);

foreach ($orgTasks as $e) {
    $day = (int)date('j', strtotime($e['deadline']));
    $e['time'] = date('H:i', strtotime($e['deadline']));
    $events[$day][] = $e;
}

$courseTasks = $conn->query("
    SELECT ct.judul, ct.deadline, ct.status, c.nama_mk as source, 'tugas' as type
    FROM course_tasks ct 
    JOIN courses c ON ct.course_id = c.id
    WHERE ct.user_id = $userId AND ct.deadline IS NOT NULL
    AND MONTH(ct.deadline) = $month AND YEAR(ct.deadline) = $year
")->fetch_all(MYSQLI_ASSOC);

foreach ($courseTasks as $e) {
    $day = (int)date('j', strtotime($e['deadline']));
    $e['time'] = date('H:i', strtotime($e['deadline']));
    $events[$day][] = $e;
}

$courses = $conn->query("SELECT * FROM courses WHERE user_id = $userId")->fetch_all(MYSQLI_ASSOC);
foreach ($courses as $c) {
    for ($dayNum = 1; $dayNum <= $daysInMonth; $dayNum++) {
        $dateObj = mktime(0, 0, 0, $month, $dayNum, $year);
        $wDay = date('l', $dateObj);
        if (($hariMap[$wDay] ?? '') === $c['hari']) {
            $events[$dayNum][] = [
                'judul'  => $c['nama_mk'],
                'deadline' => date('Y-m-d', $dateObj),
                'source' => ($c['dosen'] ? $c['dosen'] . ' | ' : '') . 'Ruang ' . ($c['ruang'] ?: 'TBA'),
                'type'   => 'kuliah',
                'time'   => substr($c['jam_mulai'], 0, 5) . ' - ' . substr($c['jam_selesai'], 0, 5),
            ];
        }
    }
}

$schedules = $conn->query("SELECT * FROM schedules WHERE user_id = $userId")->fetch_all(MYSQLI_ASSOC);
foreach ($schedules as $s) {
    for ($dayNum = 1; $dayNum <= $daysInMonth; $dayNum++) {
        $dateObj = mktime(0, 0, 0, $month, $dayNum, $year);
        $wDay = date('l', $dateObj);
        if (($hariMap[$wDay] ?? '') === $s['hari']) {
            $events[$dayNum][] = [
                'judul'  => $s['judul'],
                'deadline' => date('Y-m-d', $dateObj),
                'source' => $s['tempat'] ? 'Lokasi: ' . $s['tempat'] : ucfirst($s['tipe']),
                'type'   => 'jadwal',
                'time'   => substr($s['jam_mulai'], 0, 5) . ' - ' . substr($s['jam_selesai'], 0, 5),
            ];
        }
    }
}

// For week mode: calculate week based on offset from today
$todayTs   = mktime(0, 0, 0, $currentMonthReal, $currentDayReal, $currentYearReal);
$dayOfWeek = (int)date('w', $todayTs); // 0=Sun
$weekStart = $todayTs - ($dayOfWeek * 86400) + ($weekOffset * 7 * 86400);
$weekEnd   = $weekStart + (6 * 86400);

// Fetch week events separately (across potentially multiple months)
$weekEvents = [];
$weekDays  = [];
for ($i = 0; $i < 7; $i++) {
    $ts = $weekStart + ($i * 86400);
    $weekDays[] = [
        'ts'    => $ts,
        'date'  => (int)date('j', $ts),
        'month' => (int)date('m', $ts),
        'year'  => (int)date('Y', $ts),
        'day'   => $dayShort[date('w', $ts)],
        'fullDate' => date('Y-m-d', $ts),
        'isToday' => date('Y-m-d', $ts) === date('Y-m-d'),
    ];
}

// Fetch week events from DB (could span multiple months)
$weekStartDate = date('Y-m-d', $weekStart);
$weekEndDate   = date('Y-m-d', $weekEnd);

// Build week events array
foreach ($weekDays as $wd) {
    $wdKey = $wd['fullDate'];
    $weekEvents[$wdKey] = [];

    // Org tasks deadline
    $res = $conn->query("
        SELECT ot.judul, ot.deadline, ot.status, o.nama as source, 'org' as type
        FROM org_tasks ot JOIN organisations o ON ot.org_id = o.id
        WHERE ot.user_id = $userId AND DATE(ot.deadline) = '{$wdKey}'
    ");
    if ($res) foreach ($res->fetch_all(MYSQLI_ASSOC) as $e) {
        $e['time'] = date('H:i', strtotime($e['deadline']));
        $weekEvents[$wdKey][] = $e;
    }

    // Course tasks deadline
    $res = $conn->query("
        SELECT ct.judul, ct.deadline, ct.status, c.nama_mk as source, 'tugas' as type
        FROM course_tasks ct JOIN courses c ON ct.course_id = c.id
        WHERE ct.user_id = $userId AND DATE(ct.deadline) = '{$wdKey}'
    ");
    if ($res) foreach ($res->fetch_all(MYSQLI_ASSOC) as $e) {
        $e['time'] = date('H:i', strtotime($e['deadline']));
        $weekEvents[$wdKey][] = $e;
    }

    // Courses (recurring by day of week)
    $wDay = date('l', strtotime($wdKey));
    $hariVal = $hariMap[$wDay] ?? '';
    foreach ($courses as $c) {
        if ($c['hari'] === $hariVal) {
            $weekEvents[$wdKey][] = [
                'judul'  => $c['nama_mk'],
                'source' => ($c['dosen'] ? $c['dosen'] . ' | ' : '') . 'Ruang ' . ($c['ruang'] ?: 'TBA'),
                'type'   => 'kuliah',
                'time'   => substr($c['jam_mulai'], 0, 5) . ' - ' . substr($c['jam_selesai'], 0, 5),
            ];
        }
    }

    // Schedules (recurring by day of week)
    foreach ($schedules as $s) {
        if ($s['hari'] === $hariVal) {
            $weekEvents[$wdKey][] = [
                'judul'  => $s['judul'],
                'source' => $s['tempat'] ? 'Lokasi: ' . $s['tempat'] : ucfirst($s['tipe']),
                'type'   => 'jadwal',
                'time'   => substr($s['jam_mulai'], 0, 5) . ' - ' . substr($s['jam_selesai'], 0, 5),
            ];
        }
    }
}

// Week navigation params
$prevWeekOffset = $weekOffset - 1;
$nextWeekOffset = $weekOffset + 1;
$weekStartMonth = (int)date('m', $weekStart);
$weekStartYear  = (int)date('Y', $weekStart);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Kalender Aktivitas</h1>
        <p>Lihat semua agenda, jadwal kuliah, tugas, dan kegiatan dalam satu tampilan.</p>
    </div>
    <div class="header-actions">
        <div class="cal-mode-bar">
            <a href="?month=<?= $month ?>&year=<?= $year ?>&mode=bulan" class="cal-mode-btn <?= $mode === 'bulan' ? 'active' : '' ?>">Bulan</a>
            <a href="?month=<?= $month ?>&year=<?= $year ?>&mode=minggu" class="cal-mode-btn <?= $mode === 'minggu' ? 'active' : '' ?>">Minggu</a>
            <a href="?month=<?= $month ?>&year=<?= $year ?>&mode=agenda" class="cal-mode-btn <?= $mode === 'agenda' ? 'active' : '' ?>">Agenda</a>
        </div>
        <a href="?month=<?= $currentMonthReal ?>&year=<?= $currentYearReal ?>&mode=<?= $mode ?>" class="btn btn-secondary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Hari Ini
        </a>
    </div>
</div>

<!-- Filter Kategori -->
<div class="filter-bar" style="margin-bottom: 1.5rem;">
    <div class="tabs">
        <button type="button" class="tab cal-filter-btn active" data-filter="all">Semua Agenda</button>
        <button type="button" class="tab cal-filter-btn" data-filter="kuliah">Kuliah</button>
        <button type="button" class="tab cal-filter-btn" data-filter="tugas">Tugas Kuliah</button>
        <button type="button" class="tab cal-filter-btn" data-filter="org">Organisasi</button>
        <button type="button" class="tab cal-filter-btn" data-filter="jadwal">Jadwal Mandiri</button>
    </div>
</div>

<?php if ($mode === 'bulan'): ?>
<!-- ===== MODE: BULAN ===== -->
<div class="calendar-wrapper">
    <div class="calendar-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h2><?= $monthNames[$month] ?> <?= $year ?></h2>
        </div>
        <div class="calendar-nav">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>&mode=bulan" class="btn btn-secondary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Sebelumnya
            </a>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>&mode=bulan" class="btn btn-secondary btn-sm">
                Selanjutnya
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </div>

    <div class="calendar-weekdays">
        <?php foreach ($dayNames as $d): ?>
            <div class="calendar-weekday"><?= substr($d, 0, 3) ?></div>
        <?php endforeach; ?>
    </div>

    <div class="calendar-days">
        <?php
        $prevMonthDays = (int)date('t', mktime(0, 0, 0, $month - 1, 1, $year));
        for ($i = 0; $i < $startDay; $i++):
            $d = $prevMonthDays - $startDay + 1 + $i;
        ?>
            <div class="calendar-day other-month">
                <div class="calendar-date"><?= $d ?></div>
            </div>
        <?php endfor; ?>

        <?php for ($day = 1; $day <= $daysInMonth; $day++):
            $isToday = ($day == $currentDayReal && $month == $currentMonthReal && $year == $currentYearReal);
            $dayEvents = $events[$day] ?? [];
            $eventsJson = htmlspecialchars(json_encode($dayEvents), ENT_QUOTES, 'UTF-8');
            $dateLabel = $day . ' ' . $monthNames[$month] . ' ' . $year;
        ?>
            <div class="calendar-day <?= $isToday ? 'today' : '' ?>"
                 onclick="showDayDetails('<?= $dateLabel ?>', '<?= $eventsJson ?>')"
                 title="<?= count($dayEvents) ?> agenda - Klik untuk detail">
                <div class="calendar-date"><?= $day ?></div>
                <?php if (!empty($dayEvents)): ?>
                    <?php foreach (array_slice($dayEvents, 0, 3) as $ev): ?>
                        <div class="calendar-event <?= $ev['type'] ?>" data-type="<?= $ev['type'] ?>">
                            <?= htmlspecialchars($ev['judul']) ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($dayEvents) > 3): ?>
                        <div class="calendar-event" style="background: var(--bg-hover); color: var(--text-muted); font-size: 0.7rem;">
                            +<?= count($dayEvents) - 3 ?> lainnya
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endfor; ?>

        <?php
        $totalCells = $startDay + $daysInMonth;
        $remaining = $totalCells % 7 == 0 ? 0 : 7 - ($totalCells % 7);
        for ($i = 1; $i <= $remaining; $i++):
        ?>
            <div class="calendar-day other-month">
                <div class="calendar-date"><?= $i ?></div>
            </div>
        <?php endfor; ?>
    </div>
</div>

<!-- Legenda -->
<div style="margin-top: 1.5rem; display: flex; gap: 1.5rem; flex-wrap: wrap; background: var(--bg-secondary); padding: 0.85rem 1.5rem; border-radius: var(--radius); border: 1px solid var(--border);">
    <?php foreach ([
        ['color' => 'var(--accent)', 'label' => 'Tugas Organisasi'],
        ['color' => 'var(--info)', 'label' => 'Jadwal Kuliah'],
        ['color' => 'var(--warning)', 'label' => 'Tugas Mata Kuliah'],
        ['color' => 'var(--success)', 'label' => 'Jadwal Mandiri'],
    ] as $l): ?>
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; font-weight: 500; color: var(--text-secondary);">
            <div style="width: 10px; height: 10px; border-radius: 2px; background: <?= $l['color'] ?>; flex-shrink: 0;"></div>
            <?= $l['label'] ?>
        </div>
    <?php endforeach; ?>
</div>

<?php elseif ($mode === 'minggu'): ?>
<!-- ===== MODE: MINGGU ===== -->
<div class="cal-week-nav">
    <a href="?mode=minggu&week_offset=<?= $prevWeekOffset ?>" class="btn btn-secondary btn-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Minggu Lalu
    </a>
    <div class="cal-week-nav-info">
        <?= date('d M', $weekDays[0]['ts']) ?> – <?= date('d M Y', $weekDays[6]['ts']) ?>
        <?php if ($weekOffset === 0): ?>
            <span class="badge badge-blue" style="margin-left: 0.5rem; font-size: 0.7rem;">Minggu Ini</span>
        <?php endif; ?>
    </div>
    <a href="?mode=minggu&week_offset=<?= $nextWeekOffset ?>" class="btn btn-secondary btn-sm">
        Minggu Depan
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </a>
</div>
<div class="cal-week-wrapper">
    <div class="cal-week-header">
        <div style="padding: 0.85rem 0.65rem; font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-align: center;">Jam</div>
        <?php foreach ($weekDays as $wd): ?>
            <div class="cal-week-col-header <?= $wd['isToday'] ? 'today-col' : '' ?>">
                <span><?= $wd['day'] ?></span>
                <span class="cal-week-col-date"><?= $wd['date'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="cal-week-body">
        <?php for ($hour = 6; $hour <= 22; $hour++): ?>
            <div class="cal-week-row">
                <div class="cal-week-time"><?= sprintf('%02d:00', $hour) ?></div>
                <?php foreach ($weekDays as $wd):
                    $dayEventsW = $weekEvents[$wd['fullDate']] ?? [];
                    $hourEvents = array_filter($dayEventsW, function($e) use ($hour) {
                        if (!isset($e['time']) || empty($e['time'])) return false;
                        $evHour = (int)explode(':', $e['time'])[0];
                        return $evHour === $hour;
                    });
                ?>
                    <div class="cal-week-cell <?= $wd['isToday'] ? 'today-col' : '' ?>">
                        <?php foreach ($hourEvents as $ev): ?>
                            <div class="cal-week-event calendar-event <?= $ev['type'] ?>" data-type="<?= $ev['type'] ?>" title="<?= htmlspecialchars($ev['judul']) ?>">
                                <?= htmlspecialchars(mb_substr($ev['judul'], 0, 20)) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>


<?php elseif ($mode === 'agenda'): ?>
<!-- ===== MODE: AGENDA ===== -->
<?php
// Collect all days with events
$agendaDays = [];
for ($day = 1; $day <= $daysInMonth; $day++) {
    if (!empty($events[$day])) {
        $ts = mktime(0, 0, 0, $month, $day, $year);
        $agendaDays[$day] = [
            'ts'     => $ts,
            'dayName' => $dayNames[(int)date('w', $ts)],
            'events' => $events[$day],
        ];
    }
}

if (empty($agendaDays)):
?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <h3>Tidak Ada Agenda di <?= $monthNames[$month] ?> <?= $year ?></h3>
        <p>Tambahkan tugas, jadwal kuliah, atau kegiatan organisasi untuk mengisi kalender kamu.</p>
    </div>
<?php else: ?>
    <div class="cal-agenda-wrapper">
        <?php foreach ($agendaDays as $day => $info):
            $isToday = ($day == $currentDayReal && $month == $currentMonthReal && $year == $currentYearReal);
            $typeColors = ['org' => '#6366f1', 'kuliah' => '#0284c7', 'tugas' => '#d97706', 'jadwal' => '#10b981'];
        ?>
            <div class="cal-agenda-day">
                <div class="cal-agenda-day-header">
                    <div class="cal-agenda-date-num" style="<?= $isToday ? 'color: var(--accent);' : '' ?>"><?= $day ?></div>
                    <div class="cal-agenda-date-info">
                        <span class="cal-agenda-day-name"><?= $info['dayName'] ?></span>
                        <span class="cal-agenda-month"><?= $monthNames[$month] ?> <?= $year ?></span>
                    </div>
                    <?php if ($isToday): ?>
                        <span class="cal-agenda-today-badge">HARI INI</span>
                    <?php endif; ?>
                    <span class="badge badge-gray" style="margin-left: auto;"><?= count($info['events']) ?> agenda</span>
                </div>
                <div class="cal-agenda-events">
                    <?php
                    // Sort by time
                    usort($info['events'], fn($a, $b) => strcmp($a['time'] ?? '', $b['time'] ?? ''));
                    foreach ($info['events'] as $ev):
                        $color = $typeColors[$ev['type']] ?? '#64748b';
                    ?>
                        <div class="cal-agenda-event" data-type="<?= $ev['type'] ?>">
                            <div class="cal-agenda-event-dot" style="background: <?= $color ?>;"></div>
                            <div class="cal-agenda-event-time"><?= htmlspecialchars($ev['time'] ?? '') ?></div>
                            <div class="cal-agenda-event-info">
                                <div class="cal-agenda-event-title"><?= htmlspecialchars($ev['judul']) ?></div>
                                <div class="cal-agenda-event-source"><?= htmlspecialchars($ev['source'] ?? '') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div style="display: flex; justify-content: space-between; margin-top: 1.25rem;">
        <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>&mode=agenda" class="btn btn-secondary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            <?= $monthNames[$prevMonth] ?> <?= $prevYear ?>
        </a>
        <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>&mode=agenda" class="btn btn-secondary btn-sm">
            <?= $monthNames[$nextMonth] ?> <?= $nextYear ?>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </div>
<?php endif; ?>
<?php endif; ?>

<!-- Modal Detail Hari -->
<div class="modal-overlay" id="dayDetailModal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="dayDetailTitle">Agenda Hari Ini</h2>
            <button class="modal-close" onclick="closeModal('dayDetailModal')">&times;</button>
        </div>
        <div class="modal-body" id="dayDetailList"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('dayDetailModal')">Tutup</button>
        </div>
    </div>
</div>

<script>
// Calendar filter
document.querySelectorAll('.cal-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.cal-filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.calendar-event, .cal-week-event, .cal-agenda-event').forEach(ev => {
            if (filter === 'all') {
                ev.style.display = '';
            } else {
                const type = ev.dataset.type || ev.closest('[data-type]')?.dataset.type;
                ev.style.display = (ev.dataset.type === filter || ev.closest('[data-type]')?.dataset.type === filter) ? '' : 'none';
            }
        });
        // For agenda: hide entire day if no visible events
        document.querySelectorAll('.cal-agenda-day').forEach(dayEl => {
            const visible = [...dayEl.querySelectorAll('.cal-agenda-event')].some(e => e.style.display !== 'none');
            dayEl.style.display = visible || filter === 'all' ? '' : 'none';
        });
    });
});

function showDayDetails(dateLabel, eventsJson) {
    const events = JSON.parse(eventsJson);
    document.getElementById('dayDetailTitle').textContent = dateLabel;
    const list = document.getElementById('dayDetailList');
    
    if (!events || events.length === 0) {
        list.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:0.92rem;">Tidak ada agenda pada hari ini.</div>';
    } else {
        const typeLabels = { org: 'Tugas Organisasi', kuliah: 'Jadwal Kuliah', tugas: 'Tugas Kuliah', jadwal: 'Jadwal Mandiri' };
        const typeColors = { org: '#6366f1', kuliah: '#0284c7', tugas: '#d97706', jadwal: '#10b981' };
        const typeBgClass = { org: 'badge-purple', kuliah: 'badge-blue', tugas: 'badge-yellow', jadwal: 'badge-green' };
        
        list.innerHTML = events.map(ev => `
            <div style="display:flex;align-items:flex-start;gap:0.85rem;padding:0.85rem 0;border-bottom:1px solid var(--border);">
                <div style="width:3px;border-radius:99px;background:${typeColors[ev.type] || '#64748b'};align-self:stretch;flex-shrink:0;"></div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:0.9rem;font-weight:600;color:var(--text-primary);margin-bottom:0.25rem;">${ev.judul}</div>
                    <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                        <span class="badge ${typeBgClass[ev.type] || 'badge-gray'}" style="font-size:0.7rem;">${typeLabels[ev.type] || ev.type}</span>
                        ${ev.time ? `<span style="font-size:0.78rem;color:var(--text-muted);">${ev.time}</span>` : ''}
                        ${ev.source ? `<span style="font-size:0.78rem;color:var(--text-muted);">${ev.source}</span>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
    }
    openModal('dayDetailModal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
