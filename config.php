<?php
session_start();

// Load local/hosting configuration if present (e.g. on live web server)
if (file_exists(__DIR__ . '/config_local.php')) {
    require_once __DIR__ . '/config_local.php';
}

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'tugasku_db');

if (!defined('BASE_URL')) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/TugasKu') === 0) {
        define('BASE_URL', '/TugasKu');
    } else {
        define('BASE_URL', '');
    }
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// ── Auto Migrations ──────────────────────────────────────────────────
// Add kelas column to courses if it doesn't exist
$checkKelas = $conn->query("SHOW COLUMNS FROM courses LIKE 'kelas'");
if ($checkKelas && $checkKelas->num_rows === 0) {
    $conn->query("ALTER TABLE courses ADD COLUMN kelas VARCHAR(10) AFTER ruang");
}
// ─────────────────────────────────────────────────────────────────────

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }
}

function getUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}
