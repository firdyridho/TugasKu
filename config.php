<?php
if (session_status() === PHP_SESSION_NONE) {
    // If remember me cookie is present or user opted in, extend session cookie lifetime
    if (!empty($_COOKIE['tugasku_remember'])) {
        ini_set('session.gc_maxlifetime', 30 * 86400);
        session_set_cookie_params([
            'lifetime' => 30 * 86400,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    session_start();
}
date_default_timezone_set('Asia/Jakarta');

// Versioning for static asset cache-busting
if (!defined('APP_VERSION')) define('APP_VERSION', '2.6.6');

// Load local/hosting configuration if present (e.g. on live web server)
if (file_exists(__DIR__ . '/config_local.php')) {
    require_once __DIR__ . '/config_local.php';
}

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'sql101.infinityfree.com');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'if0_42968898');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'FOF3RwgiSuq');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'if0_42968898_tugasku');

if (!defined('BASE_URL')) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/TugasKu') === 0) {
        define('BASE_URL', '/TugasKu');
    } else {
        define('BASE_URL', '');
    }
}

try {
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }

    $conn->set_charset('utf8mb4');
    @$conn->query("SET time_zone = '+07:00'");

    // ── Auto Migrations (Safely executed) ────────────────────────────────
    $checkKelas = @$conn->query("SHOW COLUMNS FROM courses LIKE 'kelas'");
    if ($checkKelas && $checkKelas->num_rows === 0) {
        @$conn->query("ALTER TABLE courses ADD COLUMN kelas VARCHAR(10) AFTER ruang");
    }

    $checkLink = @$conn->query("SHOW COLUMNS FROM uploads LIKE 'link_url'");
    if ($checkLink && $checkLink->num_rows === 0) {
        @$conn->query("ALTER TABLE uploads ADD COLUMN link_url TEXT AFTER path_file");
        @$conn->query("ALTER TABLE uploads MODIFY COLUMN nama_file VARCHAR(255) NULL");
        @$conn->query("ALTER TABLE uploads MODIFY COLUMN path_file VARCHAR(255) NULL");
        @$conn->query("ALTER TABLE uploads MODIFY COLUMN ukuran_file INT NULL DEFAULT 0");
        @$conn->query("ALTER TABLE uploads MODIFY COLUMN mime_type VARCHAR(100) NULL");
    }

    // Auto-create remember_tokens table for persistent "Tetap Login"
    @$conn->query("CREATE TABLE IF NOT EXISTS remember_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_token (token_hash)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Auto-login from Remember Me cookie if session is not active
    if (!isset($_SESSION['user_id']) && !empty($_COOKIE['tugasku_remember'])) {
        $rememberParts = explode(':', $_COOKIE['tugasku_remember'], 2);
        if (count($rememberParts) === 2) {
            $cUserId = (int)$rememberParts[0];
            $cToken = $rememberParts[1];
            $cTokenHash = hash('sha256', $cToken);

            $stmtRemember = $conn->prepare("SELECT user_id FROM remember_tokens WHERE user_id = ? AND token_hash = ? AND expires_at > NOW() LIMIT 1");
            if ($stmtRemember) {
                $stmtRemember->bind_param('is', $cUserId, $cTokenHash);
                $stmtRemember->execute();
                $tokenRow = $stmtRemember->get_result()->fetch_assoc();
                if ($tokenRow) {
                    $_SESSION['user_id'] = (int)$tokenRow['user_id'];
                } else {
                    setcookie('tugasku_remember', '', time() - 3600, '/');
                }
            }
        }
    }
} catch (Throwable $e) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Koneksi Database Gagal - TugasKu</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 1.5rem; box-sizing: border-box; }
            .err-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 2rem; max-width: 540px; width: 100%; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
            h2 { color: #dc2626; margin-top: 0; font-size: 1.3rem; display: flex; align-items: center; gap: 0.5rem; }
            p { font-size: 0.92rem; line-height: 1.6; color: #475569; }
            .err-code { background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; font-family: monospace; font-size: 0.85rem; word-break: break-all; margin: 1rem 0; }
            .tips { background: #f1f5f9; padding: 1rem; border-radius: 8px; font-size: 0.85rem; line-height: 1.6; }
            .tips ol { margin: 0.5rem 0 0; padding-left: 1.25rem; }
            .btn { display: inline-block; margin-top: 1.25rem; background: #2563eb; color: #fff; padding: 0.65rem 1.25rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
        </style>
    </head>
    <body>
        <div class="err-card">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Koneksi Database Gagal
            </h2>
            <p>Aplikasi tidak dapat terhubung ke database MySQL. Jika Anda baru mendeploy ke hosting, periksa pengaturan database berikut:</p>
            <div class="err-code"><?= htmlspecialchars($e->getMessage()) ?></div>
            <div class="tips">
                <strong>Panduan Periksa Hosting (InfinityFree / cPanel):</strong>
                <ol>
                    <li>Buka <strong>Control Panel</strong> hosting Anda &gt; menu <strong>MySQL Databases</strong>.</li>
                    <li>Pastikan <strong>MySQL Hostname</strong> (contoh: <code>sql101.infinityfree.com</code> atau nomor server akun Anda) sudah tepat.</li>
                    <li>Pastikan <strong>Username</strong>, <strong>Password</strong>, dan <strong>Database Name</strong> sesuai.</li>
                    <li>Buat file <code>config_local.php</code> di hosting atau ubah kredensial di <code>config.php</code>.</li>
                    <li>Pastikan tabel sudah diimport via <strong>phpMyAdmin</strong> menggunakan file <code>database.sql</code>.</li>
                </ol>
            </div>
            <a href="javascript:location.reload()" class="btn">Coba Muat Ulang</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}
// ─────────────────────────────────────────────────────────────────────

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }
}

function getUser()
{
    global $conn;
    if (!isLoggedIn()) return null;
    $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function redirect($path)
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function sanitize($data)
{
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

function flash($key, $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}


