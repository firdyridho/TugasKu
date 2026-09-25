<?php
require_once __DIR__ . '/../config.php';

$token = trim($_GET['token'] ?? '');
$user = null;

if (!empty($token)) {
    $user = getUserByWidgetToken($token);
}

if (!$user && isLoggedIn()) {
    $user = getUser();
    $token = getUserWidgetToken($_SESSION['user_id']);
}

if (!$user) {
    http_response_code(401);
    die("Akses tidak sah. Silakan login atau sertakan token.");
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$fullWidgetUrl = $protocol . $host . BASE_URL . '/widget?token=' . urlencode($token);

$batContent = "@echo off\r\n";
$batContent .= ":: ==========================================================\r\n";
$batContent .= ":: TugasKu Realtime Widget Launcher (Windows Standalone Mode)\r\n";
$batContent .= ":: ==========================================================\r\n";
$batContent .= "title TugasKu Widget\r\n";
$batContent .= "echo Membuka TugasKu Widget Realtime...\r\n";
$batContent .= "\r\n";
$batContent .= ":: Coba buka via Microsoft Edge App Mode\r\n";
$batContent .= "start \"\" msedge.exe --app=\"" . $fullWidgetUrl . "\" --window-size=450,720\r\n";
$batContent .= "if %ERRORLEVEL% EQU 0 goto end\r\n";
$batContent .= "\r\n";
$batContent .= ":: Fallback coba buka via Google Chrome App Mode\r\n";
$batContent .= "start \"\" chrome.exe --app=\"" . $fullWidgetUrl . "\" --window-size=450,720\r\n";
$batContent .= "if %ERRORLEVEL% EQU 0 goto end\r\n";
$batContent .= "\r\n";
$batContent .= ":: Fallback browser bawaan\r\n";
$batContent .= "start \"\" \"" . $fullWidgetUrl . "\"\r\n";
$batContent .= "\r\n";
$batContent .= ":end\r\n";
$batContent .= "exit\r\n";

header('Content-Type: application/bat');
header('Content-Disposition: attachment; filename="TugasKu-Widget.bat"');
header('Content-Length: ' . strlen($batContent));
header('Cache-Control: no-cache, no-store, must-revalidate');

echo $batContent;
exit;
