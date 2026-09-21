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

// Fetch user profile for customized answers
$currentUser = getUser();
$userName = $currentUser['nama'] ?? 'Mahasiswa';
$userJurusan = $currentUser['jurusan'] ?? '';

// Build message thread
$systemPrompt = "Kamu adalah TugasKu AI, asisten virtual cerdas mahasiswa Indonesia dalam platform TugasKu. "
    . "Nama pengguna adalah {$userName}" . ($userJurusan ? " dari jurusan {$userJurusan}" : "") . ". "
    . "Tugas utamamu adalah membantu mahasiswa mengelola waktu, menyusun prioritas tugas kuliah, merencanakan proker organisasi, "
    . "merangkum materi perkuliahan, menyusun draf email sopan ke dosen, dan memberikan strategi belajar efektif. "
    . "Gunakan gaya bahasa Indonesia yang profesional, jelas, ramah, dan solutif. "
    . "Hindari penggunaan emoji berlebihan, gunakan poin-poin terstruktur jika memuat langkah-langkah.";

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

// NVIDIA Build Configuration
$apiKey = "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";
$primaryModel = "google/diffusiongemma-26b-a4b-it";
$fallbackModel = "meta/llama-3.1-8b-instruct";
$endpoint = "https://integrate.api.nvidia.com/v1/chat/completions";

function callNvidiaModel($endpoint, $apiKey, $model, $messages) {
    $postPayload = [
        'model' => $model,
        'messages' => $messages,
        'temperature' => 0.8,
        'top_p' => 0.95,
        'max_tokens' => 2048
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
        CURLOPT_TIMEOUT => 40,
        CURLOPT_CONNECTTIMEOUT => 15,
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

// Try primary model first
$apiResult = callNvidiaModel($endpoint, $apiKey, $primaryModel, $messages);
$activeModel = $primaryModel;

// If primary model failed (e.g. 404, 500, or empty), try fallback model
if ($apiResult['code'] !== 200 || empty($apiResult['response'])) {
    $fallbackResult = callNvidiaModel($endpoint, $apiKey, $fallbackModel, $messages);
    if ($fallbackResult['code'] === 200 && !empty($fallbackResult['response'])) {
        $apiResult = $fallbackResult;
        $activeModel = $fallbackModel;
    }
}

// Evaluate response
if ($apiResult['code'] !== 200) {
    http_response_code(502);
    $errMsg = 'Layanan AI NVIDIA sementara sedang sibuk atau tidak merespons.';
    if (!empty($apiResult['response'])) {
        $decodedErr = json_decode($apiResult['response'], true);
        if (isset($decodedErr['error']['message'])) {
            $errMsg = $decodedErr['error']['message'];
        } elseif (isset($decodedErr['detail'])) {
            $errMsg = $decodedErr['detail'];
        }
    } elseif (!empty($apiResult['error'])) {
        $errMsg .= ' (' . $apiResult['error'] . ')';
    }

    echo json_encode([
        'success' => false,
        'error' => $errMsg,
        'code' => $apiResult['code']
    ]);
    exit;
}

$decoded = json_decode($apiResult['response'], true);
$replyText = $decoded['choices'][0]['message']['content'] ?? '';

// If content is empty but reasoning exists
if (trim($replyText) === '') {
    $reasoning = $decoded['choices'][0]['message']['reasoning_content'] ?? '';
    if (!empty($reasoning)) {
        $replyText = $reasoning;
    } else {
        $replyText = 'Tidak ada jawaban yang dihasilkan oleh model.';
    }
}

echo json_encode([
    'success' => true,
    'reply' => trim($replyText),
    'model' => $activeModel
]);

