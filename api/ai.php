<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// Ensure user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Sesi berakhir atau Anda belum login. Silakan login terlebih dahulu.'
    ]);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Metode permintaan tidak diizinkan.'
    ]);
    exit;
}

// Read JSON input
$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);

$userMessage = trim($inputData['message'] ?? '');
if ($userMessage === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Pesan tidak boleh kosong.'
    ]);
    exit;
}

$history = $inputData['history'] ?? [];
if (!is_array($history)) {
    $history = [];
}

// ==========================================================================
// 1. FETCH LIVE DATABASE CONTEXT FOR LOGGED-IN USER
// ==========================================================================
$currentUser = getUser();
$userId = (int)($currentUser['id'] ?? 0);
$userName = $currentUser['nama'] ?? 'Mahasiswa';
$userNim = $currentUser['nim'] ?? '';
$userJurusan = $currentUser['jurusan'] ?? '';
$userAngkatan = $currentUser['angkatan'] ?? '';

// A. Active Course Tasks
$courseTasks = [];
if ($userId > 0) {
    $stmtCT = $conn->prepare("
        SELECT ct.id, ct.judul, ct.deskripsi, ct.deadline, ct.tempat_pengumpulan, ct.status, c.nama_mk, c.dosen
        FROM course_tasks ct
        JOIN courses c ON ct.course_id = c.id
        WHERE ct.user_id = ?
        ORDER BY CASE WHEN ct.status = 'belum' THEN 1 WHEN ct.status = 'progres' THEN 2 ELSE 3 END,
                 ct.deadline ASC
        LIMIT 15
    ");
    if ($stmtCT) {
        $stmtCT->bind_param('i', $userId);
        $stmtCT->execute();
        $resCT = $stmtCT->get_result();
        while ($row = $resCT->fetch_assoc()) {
            $courseTasks[] = $row;
        }
    }
}

// B. Active Organisation Tasks
$orgTasks = [];
if ($userId > 0) {
    $stmtOT = $conn->prepare("
        SELECT ot.id, ot.judul, ot.deskripsi, ot.deadline, ot.tempat_pengumpulan, ot.status, o.nama AS nama_org
        FROM org_tasks ot
        JOIN organisations o ON ot.org_id = o.id
        WHERE ot.user_id = ?
        ORDER BY CASE WHEN ot.status = 'belum' THEN 1 WHEN ot.status = 'progres' THEN 2 ELSE 3 END,
                 ot.deadline ASC
        LIMIT 15
    ");
    if ($stmtOT) {
        $stmtOT->bind_param('i', $userId);
        $stmtOT->execute();
        $resOT = $stmtOT->get_result();
        while ($row = $resOT->fetch_assoc()) {
            $orgTasks[] = $row;
        }
    }
}

// C. User Courses & Weekly Schedule
$courses = [];
if ($userId > 0) {
    $stmtC = $conn->prepare("
        SELECT id, nama_mk, dosen, hari, jam_mulai, jam_selesai, ruang, kelas
        FROM courses
        WHERE user_id = ?
        ORDER BY FIELD(hari, 'senin','selasa','rabu','kamis','jumat','sabtu','minggu'), jam_mulai ASC
    ");
    if ($stmtC) {
        $stmtC->bind_param('i', $userId);
        $stmtC->execute();
        $resC = $stmtC->get_result();
        while ($row = $resC->fetch_assoc()) {
            $courses[] = $row;
        }
    }
}

// D. User Organisations
$orgs = [];
if ($userId > 0) {
    $stmtO = $conn->prepare("SELECT id, nama, kategori, deskripsi FROM organisations WHERE user_id = ?");
    if ($stmtO) {
        $stmtO->bind_param('i', $userId);
        $stmtO->execute();
        $resO = $stmtO->get_result();
        while ($row = $resO->fetch_assoc()) {
            $orgs[] = $row;
        }
    }
}

// E. User Personal Schedules
$schedules = [];
if ($userId > 0) {
    $stmtS = $conn->prepare("
        SELECT id, judul, hari, jam_mulai, jam_selesai, tempat, tipe
        FROM schedules
        WHERE user_id = ?
        ORDER BY FIELD(hari, 'senin','selasa','rabu','kamis','jumat','sabtu','minggu'), jam_mulai ASC
    ");
    if ($stmtS) {
        $stmtS->bind_param('i', $userId);
        $stmtS->execute();
        $resS = $stmtS->get_result();
        while ($row = $resS->fetch_assoc()) {
            $schedules[] = $row;
        }
    }
}

// ==========================================================================
// 2. CONSTRUCT STRUCTURED CONTEXT FOR THE AI MODEL
// ==========================================================================
$dayNamesIndo = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];
$currentDayIndo = $dayNamesIndo[date('l')] ?? date('l');
$currentDateFormatted = $currentDayIndo . ', ' . date('d F Y H:i') . ' WIB';

$dbSummary = "WAKTU SISTEM SAAT INI: {$currentDateFormatted}\n\n";

$dbSummary .= "=== DATA PENGGUNA ===\n";
$dbSummary .= "- Nama: {$userName}\n";
$dbSummary .= "- NIM: " . ($userNim ?: '-') . "\n";
$dbSummary .= "- Jurusan: " . ($userJurusan ?: '-') . "\n";
$dbSummary .= "- Angkatan: " . ($userAngkatan ?: '-') . "\n\n";

$dbSummary .= "=== MATA KULIAH & JADWAL KULIAH (" . count($courses) . " MK) ===\n";
if (empty($courses)) {
    $dbSummary .= "Belum ada mata kuliah yang didaftarkan di TugasKu.\n";
} else {
    foreach ($courses as $c) {
        $jam = substr($c['jam_mulai'], 0, 5) . ' - ' . substr($c['jam_selesai'], 0, 5) . ' WIB';
        $dbSummary .= "- " . $c['nama_mk'] 
            . " | Dosen: " . ($c['dosen'] ?: '-') 
            . " | Hari: " . ucfirst($c['hari']) . " (" . $jam . ")" 
            . " | Ruang: " . ($c['ruang'] ?: '-') 
            . ($c['kelas'] ? " [Kelas " . $c['kelas'] . "]" : "") . "\n";
    }
}
$dbSummary .= "\n";

$dbSummary .= "=== TUGAS KULIAH (" . count($courseTasks) . " TUGAS AKTIF) ===\n";
if (empty($courseTasks)) {
    $dbSummary .= "Tidak ada tugas kuliah aktif di database saat ini.\n";
} else {
    foreach ($courseTasks as $t) {
        $dl = $t['deadline'] ? date('d M Y H:i', strtotime($t['deadline'])) : 'Tanpa deadline';
        $dbSummary .= "- [" . strtoupper($t['status']) . "] " . $t['judul'] 
            . " (MK: " . $t['nama_mk'] . ")"
            . " | Deadline: " . $dl
            . ($t['tempat_pengumpulan'] ? " | Kumpul di: " . $t['tempat_pengumpulan'] : "")
            . ($t['deskripsi'] ? " | Info: " . substr(strip_tags($t['deskripsi']), 0, 80) : "") . "\n";
    }
}
$dbSummary .= "\n";

$dbSummary .= "=== ORGANISASI & TUGAS ORGANISASI ===\n";
if (empty($orgs)) {
    $dbSummary .= "Pengguna belum bergabung di organisasi TugasKu.\n";
} else {
    $orgNames = array_map(function($o) { return $o['nama'] . ' (' . strtoupper($o['kategori']) . ')'; }, $orgs);
    $dbSummary .= "Organisasi Diikuti: " . implode(', ', $orgNames) . "\n";

    if (!empty($orgTasks)) {
        $dbSummary .= "Tugas/Agenda Organisasi:\n";
        foreach ($orgTasks as $ot) {
            $dl = $ot['deadline'] ? date('d M Y H:i', strtotime($ot['deadline'])) : 'Tanpa deadline';
            $dbSummary .= "- [" . strtoupper($ot['status']) . "] " . $ot['judul'] 
                . " (Org: " . $ot['nama_org'] . ")"
                . " | Deadline: " . $dl . "\n";
        }
    } else {
        $dbSummary .= "Tidak ada tugas organisasi aktif saat ini.\n";
    }
}
$dbSummary .= "\n";

if (!empty($schedules)) {
    $dbSummary .= "=== JADWAL KEGIATAN MANDIRI ===\n";
    foreach ($schedules as $s) {
        $dbSummary .= "- " . $s['judul'] . " | " . ucfirst($s['hari']) . " " . substr($s['jam_mulai'], 0, 5) . " - " . substr($s['jam_selesai'], 0, 5) . " | Tempat: " . ($s['tempat'] ?: '-') . "\n";
    }
    $dbSummary .= "\n";
}

// ==========================================================================
// 3. SYSTEM PROMPT WITH DATABASE AWARENESS
// ==========================================================================
$systemPrompt = "Kamu adalah TugasKu AI, asisten virtual akademik dan organisasi pribadi untuk mahasiswa bernama {$userName} di platform TugasKu.\n\n"
    . "Kamu TERHUBUNG LANGSUNG SECARA REALTIME ke database TugasKu milik pengguna. "
    . "Berikut adalah data lengkap dan terkini tentang mata kuliah, tugas, deadline, jadwal, dan organisasi pengguna:\n\n"
    . $dbSummary . "\n\n"
    . "PANDUAN & ATURAN MENJAWAB:\n"
    . "1. Gunakan data di atas untuk menjawab pertanyaan spesifik pengguna (seperti tugas terdekat, jadwal hari ini, prioritas deadline, proker organisasi, dll).\n"
    . "2. Sebutkan nama tugas, mata kuliah, dosen, atau deadline secara akurat dan spesifik sesuai data database di atas.\n"
    . "3. Jika pengguna bertanya hal yang belum ada di database (misal belum ada tugas), beri tahu dengan santun dan berikan panduan umum.\n"
    . "4. Format jawabanmu dengan SANGAT BERSIH, JELAS, RAPI, dan LANGSUNG PADA INTINYA (tanpa basa-basi bertele-tele). Gunakan poin-poin tebal (bullet atau penomoran) dan paragraf pendek.\n"
    . "5. DILARANG KERAS menyertakan tag pemikiran internal seperti <think> atau penalaran mentah. Berikan langsung hasil jawaban akhir.";

$messages = [
    ['role' => 'system', 'content' => $systemPrompt]
];

// Keep last 6 conversation turns for context
$recentHistory = array_slice($history, -6);
foreach ($recentHistory as $msg) {
    if (isset($msg['role'], $msg['content']) && in_array($msg['role'], ['user', 'assistant'])) {
        $messages[] = [
            'role' => $msg['role'],
            'content' => (string)$msg['content']
        ];
    }
}

// Append current user message
$messages[] = [
    'role' => 'user',
    'content' => $userMessage
];

// Active verified NVIDIA NIM Models
$apiKey = "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";
$primaryModel = "meta/llama-3.2-11b-vision-instruct";
$fallbackModel = "openai/gpt-oss-20b";
$endpoint = "https://integrate.api.nvidia.com/v1/chat/completions";

function callNvidiaModel($endpoint, $apiKey, $model, $messages, $timeout = 25) {
    $postPayload = [
        'model' => $model,
        'messages' => $messages,
        'temperature' => 0.6,
        'top_p' => 0.9,
        'max_tokens' => 1800
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($postPayload),
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'response' => $result,
        'error' => $error
    ];
}

// Function to provide database-aware academic fallback if cloud API is unreachable
function generateAcademicFallbackWithDb($msg, $name, $jurusan, $courses, $courseTasks, $orgTasks, $orgs) {
    $lower = strtolower($msg);
    $salutation = "Halo **{$name}**" . ($jurusan ? " ({$jurusan})" : "") . "! ";

    // Query: Tugas terdekat / Deadline / Prioritas
    if (strpos($lower, 'tugas') !== false || strpos($lower, 'deadline') !== false || strpos($lower, 'prioritas') !== false) {
        $reply = $salutation . "Berikut status tugas dan deadline terdekat yang tercatat di database TugasKu Anda:\n\n";

        if (!empty($courseTasks)) {
            $reply .= "### Tugas Kuliah Terdekat:\n";
            foreach (array_slice($courseTasks, 0, 5) as $t) {
                $dl = $t['deadline'] ? date('d M Y H:i', strtotime($t['deadline'])) : 'Tanpa deadline';
                $reply .= "- **{$t['judul']}** ({$t['nama_mk']}) — Deadline: `{$dl}` [Status: " . strtoupper($t['status']) . "]\n";
            }
            $reply .= "\n";
        }

        if (!empty($orgTasks)) {
            $reply .= "### Tugas/Agenda Organisasi:\n";
            foreach (array_slice($orgTasks, 0, 5) as $ot) {
                $dl = $ot['deadline'] ? date('d M Y H:i', strtotime($ot['deadline'])) : 'Tanpa deadline';
                $reply .= "- **{$ot['judul']}** ({$ot['nama_org']}) — Deadline: `{$dl}`\n";
            }
            $reply .= "\n";
        }

        if (empty($courseTasks) && empty($orgTasks)) {
            $reply .= "Saat ini **belum ada tugas aktif** yang tersimpan di TugasKu Anda. Anda dapat menambahkan tugas baru di menu **Kuliah** atau **Organisasi**.\n\n";
        } else {
            $reply .= "> **Tips Prioritas:** Kerjakan tugas dengan tenggat terdekat terlebih dahulu. Gunakan tombol centang di halaman detail untuk memperbarui progres tugas Anda.";
        }

        return $reply;
    }

    // Query: Jadwal / Mata Kuliah / Hari
    if (strpos($lower, 'jadwal') !== false || strpos($lower, 'kuliah') !== false || strpos($lower, 'mata kuliah') !== false) {
        $reply = $salutation . "Berikut daftar mata kuliah dan jadwal mingguan Anda di TugasKu:\n\n";
        if (!empty($courses)) {
            $reply .= "| Hari | Jam | Mata Kuliah | Ruang | Dosen |\n";
            $reply .= "|---|---|---|---|---|\n";
            foreach ($courses as $c) {
                $jam = substr($c['jam_mulai'], 0, 5) . '-' . substr($c['jam_selesai'], 0, 5);
                $reply .= "| " . ucfirst($c['hari']) . " | " . $jam . " | " . $c['nama_mk'] . " | " . ($c['ruang'] ?: '-') . " | " . ($c['dosen'] ?: '-') . " |\n";
            }
        } else {
            $reply .= "Anda belum menambahkan jadwal mata kuliah. Silakan tambahkan di menu **Kuliah**.\n";
        }
        return $reply;
    }

    // Query: Dosen / Izin / Chat
    if (strpos($lower, 'dosen') !== false || strpos($lower, 'izin') !== false || strpos($lower, 'email') !== false || strpos($lower, 'wa') !== false) {
        return $salutation . "Berikut draf pesan sopan yang dapat Anda gunakan:\n\n"
            . "> *\"Selamat pagi/siang Bapak/Ibu [Nama Dosen], mohon maaf mengganggu waktunya. "
            . "Perkenalkan saya {$name}, mahasiswa {$jurusan}. "
            . "Terkait perkuliahan pada hari ini, saya bermaksud menyampaikan izin tidak dapat hadir dikarenakan [Alasan singkat, misal: sakit/keperluan resmi]. "
            . "Saya berkomitmen untuk tetap mengejar materi dan mengumpulkan tugas sesuai ketentuan. Terima kasih banyak atas pengertian Bapak/Ibu.\"*\n\n"
            . "**Tips Komunikasi:** Kirim pada jam kerja (08.00 - 16.30 WIB), gunakan bahasa baku, dan hindari menyingkat kata (*yg*, *dgn*, *sy*).";
    }

    // Query: Organisasi
    if (strpos($lower, 'organisasi') !== false) {
        $reply = $salutation . "Berikut data organisasi yang Anda ikuti:\n\n";
        if (!empty($orgs)) {
            foreach ($orgs as $o) {
                $reply .= "- **" . $o['nama'] . "** (" . strtoupper($o['kategori']) . ")\n";
            }
            if (!empty($orgTasks)) {
                $reply .= "\n**Agenda/Tugas Organisasi Aktif:**\n";
                foreach ($orgTasks as $ot) {
                    $dl = $ot['deadline'] ? date('d M Y H:i', strtotime($ot['deadline'])) : 'Tanpa deadline';
                    $reply .= "- {$ot['judul']} ({$ot['nama_org']}) — Deadline: `{$dl}`\n";
                }
            }
        } else {
            $reply .= "Anda belum menambahkan data organisasi di menu **Organisasi**.\n";
        }
        return $reply;
    }

    return $salutation . "Saya terhubung langsung dengan database TugasKu Anda.\n\n"
        . "Anda dapat bertanya hal-hal seperti:\n"
        . "- *\"Apa tugas kuliah dan deadline terdekat saya?\"*\n"
        . "- *\"Kapan jadwal kuliah saya minggu ini?\"*\n"
        . "- *\"Buatkan draf pesan izin ke dosen.\"*\n"
        . "- *\"Bagaimana membagi waktu kuliah dan organisasi?\"*\n\n"
        . "Silakan ajukan pertanyaan Anda!";
}

// Try primary verified model first
$apiResult = callNvidiaModel($endpoint, $apiKey, $primaryModel, $messages, 25);
$activeModel = $primaryModel;

// If primary model failed, try secondary fallback model
if ($apiResult['code'] !== 200 || empty($apiResult['response'])) {
    $fallbackResult = callNvidiaModel($endpoint, $apiKey, $fallbackModel, $messages, 25);
    if ($fallbackResult['code'] === 200 && !empty($fallbackResult['response'])) {
        $apiResult = $fallbackResult;
        $activeModel = $fallbackModel;
    }
}

// Evaluate response
if ($apiResult['code'] !== 200) {
    // API failed, use DB-aware fallback engine so user experience is always rich and personalized
    $fallbackReply = generateAcademicFallbackWithDb($userMessage, $userName, $userJurusan, $courses, $courseTasks, $orgTasks, $orgs);
    echo json_encode([
        'success' => true,
        'reply' => $fallbackReply,
        'model' => 'tugasku-db-assistant',
        'is_db_connected' => true
    ]);
    exit;
}

$decoded = json_decode($apiResult['response'], true);
$replyText = $decoded['choices'][0]['message']['content'] ?? '';

// Hapus tag pemikiran internal (<think>...</think>) jika ada
$replyText = preg_replace('/<think>[\s\S]*?<\/think>/i', '', $replyText);
$replyText = trim($replyText);

if (empty($replyText)) {
    $replyText = generateAcademicFallbackWithDb($userMessage, $userName, $userJurusan, $courses, $courseTasks, $orgTasks, $orgs);
}

echo json_encode([
    'success' => true,
    'reply' => $replyText,
    'model' => $activeModel,
    'is_db_connected' => true
]);
