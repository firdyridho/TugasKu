<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus token remember me dari database dan hapus cookie jika ada
if (!empty($_COOKIE['tugasku_remember'])) {
    $parts = explode(':', $_COOKIE['tugasku_remember'], 2);
    if (count($parts) === 2) {
        $cUserId = (int)$parts[0];
        $cTokenHash = hash('sha256', $parts[1]);
        if (isset($conn) && $conn instanceof mysqli) {
            $stmtDel = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ? AND token_hash = ?");
            if ($stmtDel) {
                $stmtDel->bind_param('is', $cUserId, $cTokenHash);
                $stmtDel->execute();
            }
        }
    }
    setcookie('tugasku_remember', '', time() - 3600, '/');
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

$redirectUrl = (defined('BASE_URL') ? BASE_URL : '') . '/auth/login?status=logged_out';
header('Location: ' . $redirectUrl);
exit;
