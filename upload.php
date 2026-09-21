<?php
require_once __DIR__ . '/config.php';
requireLogin();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/kuliah/');
}

$courseId   = (int)($_POST['course_id'] ?? 0);
$taskId     = !empty($_POST['course_task_id']) ? (int)$_POST['course_task_id'] : null;
$tipe       = in_array($_POST['tipe'] ?? '', ['soal','materi','jawaban']) ? $_POST['tipe'] : 'materi';
$judul      = sanitize($_POST['judul'] ?? '');
$returnUrl  = '/kuliah/detail.php?id=' . $courseId . '&tab=' . ($tipe === 'materi' ? 'materi' : 'files');

// Validate course ownership
$course = $conn->query("SELECT id FROM courses WHERE id = $courseId AND user_id = $userId")->fetch_assoc();
if (!$course) {
    flash('error', 'Mata kuliah tidak ditemukan.');
    redirect('/kuliah/');
}

if (empty($judul)) {
    flash('error', 'Judul file wajib diisi.');
    redirect($returnUrl);
}

// File upload handling
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errCodes = [
        UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi batas server.',
        UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi batas form.',
        UPLOAD_ERR_PARTIAL    => 'File hanya terupload sebagian.',
        UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang dipilih.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara tidak tersedia.',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menyimpan file.',
    ];
    $errCode = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    flash('error', $errCodes[$errCode] ?? 'Terjadi kesalahan saat upload file.');
    redirect($returnUrl);
}

$allowedMimes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/zip',
    'application/x-zip-compressed',
    'text/plain',
];

$allowedExts = ['pdf','doc','docx','ppt','pptx','xls','xlsx','jpg','jpeg','png','gif','zip','txt'];

$maxSize = 15 * 1024 * 1024; // 15 MB

$file     = $_FILES['file'];
$origName = basename($file['name']);
$ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
$size     = $file['size'];
$tmpPath  = $file['tmp_name'];

// Check real MIME via finfo
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$realMime = finfo_file($finfo, $tmpPath);
finfo_close($finfo);

if (!in_array($ext, $allowedExts)) {
    flash('error', 'Tipe file tidak diizinkan. Gunakan: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG, ZIP, TXT.');
    redirect($returnUrl);
}

if (!in_array($realMime, $allowedMimes)) {
    flash('error', 'Format file tidak valid atau tidak aman.');
    redirect($returnUrl);
}

if ($size > $maxSize) {
    flash('error', 'Ukuran file melebihi batas maksimum 15 MB.');
    redirect($returnUrl);
}

// Build safe filename
$safeBase    = preg_replace('/[^a-z0-9_\-]/i', '_', pathinfo($origName, PATHINFO_FILENAME));
$uniqueName  = date('Ymd_His') . '_' . $userId . '_' . substr($safeBase, 0, 40) . '.' . $ext;
$uploadDir   = __DIR__ . '/uploads/';
$destPath    = $uploadDir . $uniqueName;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!move_uploaded_file($tmpPath, $destPath)) {
    flash('error', 'Gagal memindahkan file ke server. Hubungi administrator.');
    redirect($returnUrl);
}

// Save to DB
$stmt = $conn->prepare('INSERT INTO uploads (course_task_id, course_id, user_id, tipe, judul, nama_file, path_file, ukuran_file, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('iiissssss', $taskId, $courseId, $userId, $tipe, $judul, $origName, $uniqueName, $size, $realMime);
if ($stmt->execute()) {
    $typeLabels = ['soal' => 'Soal', 'materi' => 'Materi', 'jawaban' => 'Jawaban'];
    flash('success', ($typeLabels[$tipe] ?? 'File') . ' "' . htmlspecialchars($judul) . '" berhasil diupload.');
} else {
    // Delete uploaded file on DB error
    @unlink($destPath);
    flash('error', 'Gagal menyimpan informasi file ke database.');
}

redirect($returnUrl);
