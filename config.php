<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tugasku_db');
define('BASE_URL', '/TugasKu');

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
        header('Location: ' . BASE_URL . '/auth/login.php');
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
