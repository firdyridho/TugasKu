<?php
require_once __DIR__ . '/config.php';
requireLogin();

$userId     = $_SESSION['user_id'];
$uploadId   = (int)($_GET['id'] ?? 0);

if (!$uploadId) {
    flash('error', 'File tidak ditemukan.');
    redirect('/kuliah/');
}

// Only allow download of files belonging to this user
$upload = $conn->query("SELECT * FROM uploads WHERE id = $uploadId AND user_id = $userId")->fetch_assoc();

if (!$upload) {
    flash('error', 'Akses ditolak atau file tidak ditemukan.');
    redirect('/kuliah/');
}

$filePath = __DIR__ . '/uploads/' . $upload['path_file'];

if (!file_exists($filePath)) {
    flash('error', 'File fisik tidak ditemukan di server.');
    redirect('/kuliah/detail.php?id=' . $upload['course_id']);
}

// Deliver file to browser
$mime     = $upload['mime_type'] ?: 'application/octet-stream';
$fileName = $upload['nama_file'];

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . addslashes($fileName) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
ob_clean();
flush();
readfile($filePath);
exit;
