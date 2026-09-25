<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$userId = 0;
$token = trim($_GET['token'] ?? ($_POST['token'] ?? ($_SERVER['HTTP_X_WIDGET_TOKEN'] ?? '')));

if (!empty($token)) {
    $tokenUser = getUserByWidgetToken($token);
    if ($tokenUser) {
        $userId = (int)$tokenUser['id'];
    }
}

if (!$userId && isLoggedIn()) {
    $userId = (int)$_SESSION['user_id'];
}

if (!$userId) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Akses widget tidak sah. Silakan masuk atau gunakan tautan widget pribadi kamu.']);
    exit;
}

// Helper formatting human-friendly diff
function formatHumanTimeDiff($seconds) {
    if ($seconds < 60) {
        return "$seconds detik";
    } elseif ($seconds < 3600) {
        $m = floor($seconds / 60);
        return "$m menit";
    } elseif ($seconds < 86400) {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        return $m > 0 ? "$h jam $m mnt" : "$h jam";
    } else {
        $d = floor($seconds / 86400);
        $h = floor(($seconds % 86400) / 3600);
        return $h > 0 ? "$d hari $h jam" : "$d hari";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// POST: Quick Toggle Task Completion from Realtime Widget
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'toggle_task') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $taskType = $_POST['task_type'] ?? '';
        $newStatus = ($_POST['status'] ?? '') === 'selesai' ? 'selesai' : 'belum';

        if ($taskId <= 0 || !in_array($taskType, ['kuliah', 'organisasi'])) {
            echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid']);
            exit;
        }

        $table = ($taskType === 'kuliah') ? 'course_tasks' : 'org_tasks';
        $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param('sii', $newStatus, $taskId, $userId);
            $stmt->execute();
            echo json_encode([
                'status' => 'success',
                'message' => $newStatus === 'selesai' ? 'Tugas berhasil diselesaikan!' : 'Tugas dikembalikan ke aktif',
                'task_id' => $taskId,
                'task_type' => $taskType,
                'new_status' => $newStatus
            ]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui database']);
            exit;
        }
    }

    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenal']);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// GET: Fetch Realtime Data (Schedules & Deadlines)
// ─────────────────────────────────────────────────────────────────────────────
$hariMap = [
    'sunday' => 'minggu', 'monday' => 'senin', 'tuesday' => 'selasa', 
    'wednesday' => 'rabu', 'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu'
];
$dayEnglish = strtolower(date('l'));
$todayIndo = $hariMap[$dayEnglish] ?? 'senin';

$indoDaysFull = [
    'senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu',
    'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'
];
$indoMonthsFull = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$nowTs = time();
$todayDateStr = date('Y-m-d');
$nowFormattedDate = ($indoDaysFull[$todayIndo] ?? 'Hari Ini') . ', ' . (int)date('d') . ' ' . ($indoMonthsFull[(int)date('m')] ?? date('F')) . ' ' . date('Y');

// 1. Fetch Today's Courses
$courses = [];
$cStmt = $conn->prepare("SELECT * FROM courses WHERE user_id = ? AND hari = ? ORDER BY jam_mulai ASC");
if ($cStmt) {
    $cStmt->bind_param('is', $userId, $todayIndo);
    $cStmt->execute();
    $res = $cStmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['schedule_type'] = 'kuliah';
        $row['title'] = $row['nama_mk'];
        $row['detail_sub'] = ($row['dosen'] ? 'Dosen: ' . $row['dosen'] : '') . ($row['ruang'] ? ' • Ruang: ' . $row['ruang'] : '') . ($row['kelas'] ? ' (' . $row['kelas'] . ')' : '');
        $row['location'] = $row['ruang'] ?: 'Online / Belum ditentukan';
        $row['badge_color'] = '#6366f1';
        $courses[] = $row;
    }
}

// 2. Fetch Today's Independent Schedules
$schedules = [];
$sStmt = $conn->prepare("SELECT * FROM schedules WHERE user_id = ? AND hari = ? ORDER BY jam_mulai ASC");
if ($sStmt) {
    $sStmt->bind_param('is', $userId, $todayIndo);
    $sStmt->execute();
    $res = $sStmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['schedule_type'] = 'mandiri';
        $row['title'] = $row['judul'];
        $row['detail_sub'] = $row['deskripsi'] ?: 'Agenda mandiri';
        $row['location'] = $row['tempat'] ?: 'Pribadi / Fleksibel';
        $row['badge_color'] = $row['warna'] ?: '#10b981';
        $schedules[] = $row;
    }
}

// Merge & sort all today's items by jam_mulai
$todayAll = array_merge($courses, $schedules);
usort($todayAll, function($a, $b) {
    return strcmp($a['jam_mulai'], $b['jam_mulai']);
});

$parsedJadwal = [];
$ongoingCount = 0;
$upcomingCount = 0;
$passedCount = 0;

foreach ($todayAll as $item) {
    $startTs = strtotime($todayDateStr . ' ' . $item['jam_mulai']);
    $endTs = !empty($item['jam_selesai']) ? strtotime($todayDateStr . ' ' . $item['jam_selesai']) : ($startTs + 3600);
    if ($endTs <= $startTs) $endTs = $startTs + 3600;

    $status = 'upcoming';
    $statusText = '';
    $progressPct = 0;

    if ($nowTs >= $startTs && $nowTs <= $endTs) {
        $status = 'ongoing';
        $ongoingCount++;
        $totalDur = max(1, $endTs - $startTs);
        $elapsed = $nowTs - $startTs;
        $progressPct = min(100, max(0, round(($elapsed / $totalDur) * 100)));
        $leftSec = $endTs - $nowTs;
        $statusText = 'Berlangsung • Sisa ' . formatHumanTimeDiff($leftSec);
    } elseif ($nowTs < $startTs) {
        $status = 'upcoming';
        $upcomingCount++;
        $untilSec = $startTs - $nowTs;
        $statusText = 'Mulai dlm ' . formatHumanTimeDiff($untilSec);
    } else {
        $status = 'passed';
        $passedCount++;
        $statusText = 'Selesai';
    }

    $parsedJadwal[] = [
        'id' => (int)$item['id'],
        'type' => $item['schedule_type'],
        'title' => $item['title'],
        'jam_mulai' => substr($item['jam_mulai'], 0, 5),
        'jam_selesai' => substr($item['jam_selesai'] ?? '', 0, 5),
        'time_range' => substr($item['jam_mulai'], 0, 5) . ' - ' . substr($item['jam_selesai'] ?? '', 0, 5),
        'location' => $item['location'],
        'info' => $item['detail_sub'],
        'color' => $item['badge_color'] ?? '#6366f1',
        'status' => $status,
        'status_text' => $statusText,
        'progress_pct' => $progressPct
    ];
}

// 3. Fetch Active Task Deadlines (Kuliah + Organisasi)
$deadlines = [];

// Course tasks
$cTasks = $conn->query("
    SELECT ct.id, ct.judul, ct.deadline, ct.tempat_pengumpulan, ct.status, ct.course_id, c.nama_mk as source_name, 'kuliah' as task_type
    FROM course_tasks ct
    JOIN courses c ON ct.course_id = c.id
    WHERE ct.user_id = $userId AND ct.status != 'selesai' AND ct.deadline IS NOT NULL
    ORDER BY ct.deadline ASC LIMIT 25
");
if ($cTasks) {
    while ($r = $cTasks->fetch_assoc()) $deadlines[] = $r;
}

// Organization tasks
$oTasks = $conn->query("
    SELECT ot.id, ot.judul, ot.deadline, ot.tempat_pengumpulan, ot.status, ot.org_id, o.nama as source_name, 'organisasi' as task_type
    FROM org_tasks ot
    JOIN organisations o ON ot.org_id = o.id
    WHERE ot.user_id = $userId AND ot.status != 'selesai' AND ot.deadline IS NOT NULL
    ORDER BY ot.deadline ASC LIMIT 25
");
if ($oTasks) {
    while ($r = $oTasks->fetch_assoc()) $deadlines[] = $r;
}

// Sort deadlines by date ascending
usort($deadlines, function($a, $b) {
    return strtotime($a['deadline']) - strtotime($b['deadline']);
});

$parsedDeadlines = [];
$urgentDlCount = 0;

foreach ($deadlines as $dl) {
    $dlTs = strtotime($dl['deadline']);
    $diffSec = $dlTs - $nowTs;
    $dlDateOnly = date('Y-m-d', $dlTs);

    $urgency = 'normal';
    $timeText = '';
    $badgeClass = '';

    if ($diffSec < 0) {
        $urgency = 'overdue';
        $timeText = 'Terlewat ' . formatHumanTimeDiff(abs($diffSec)) . ' lalu';
        $badgeClass = 'dl-overdue';
        $urgentDlCount++;
    } elseif ($dlDateOnly === $todayDateStr) {
        $urgency = 'today';
        $timeText = 'Hari Ini (' . date('H:i', $dlTs) . ' WIB) • Sisa ' . formatHumanTimeDiff($diffSec);
        $badgeClass = 'dl-today';
        $urgentDlCount++;
    } elseif ($diffSec <= 86400 * 2) {
        $urgency = 'tomorrow';
        $timeText = 'Besok (' . date('H:i', $dlTs) . ' WIB)';
        $badgeClass = 'dl-urgent';
        $urgentDlCount++;
    } else {
        $urgency = 'normal';
        $days = ceil($diffSec / 86400);
        $timeText = $days . ' hari lagi (' . date('d M, H:i', $dlTs) . ')';
        $badgeClass = 'dl-normal';
    }

    $detailUrl = ($dl['task_type'] === 'kuliah') 
        ? BASE_URL . '/kuliah/detail?id=' . $dl['course_id'] . '&tab=tugas'
        : BASE_URL . '/organisasi/detail?id=' . $dl['org_id'] . '&tab=tugas';

    $parsedDeadlines[] = [
        'id' => (int)$dl['id'],
        'task_type' => $dl['task_type'],
        'title' => $dl['judul'],
        'source' => $dl['source_name'],
        'deadline_raw' => $dl['deadline'],
        'deadline_formatted' => date('d M Y, H:i', $dlTs) . ' WIB',
        'urgency' => $urgency,
        'badge_class' => $badgeClass,
        'time_left_text' => $timeText,
        'status' => $dl['status'],
        'url' => $detailUrl
    ];
}

echo json_encode([
    'status' => 'success',
    'timestamp' => $nowTs,
    'server_time' => date('H:i:s'),
    'date_display' => $nowFormattedDate,
    'today_name' => $indoDaysFull[$todayIndo] ?? 'Hari Ini',
    'counts' => [
        'total_jadwal_today' => count($parsedJadwal),
        'ongoing_jadwal' => $ongoingCount,
        'upcoming_jadwal' => $upcomingCount,
        'passed_jadwal' => $passedCount,
        'total_active_dl' => count($parsedDeadlines),
        'urgent_dl' => $urgentDlCount
    ],
    'jadwal' => $parsedJadwal,
    'deadlines' => $parsedDeadlines
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
