<?php
require_once __DIR__ . '/config.php';
requireLogin();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/kuliah/');
}

$courseId   = (int)($_POST['course_id'] ?? 0);
$taskId     = !empty($_POST['course_task_id']) ? (int)$_POST['course_task_id'] : null;
$tipe       = in_array($_POST['tipe'] ?? '', ['soal', 'materi', 'jawaban']) ? $_POST['tipe'] : 'materi';
$judul      = sanitize($_POST['judul'] ?? '');
$modeUpload = sanitize($_POST['mode_upload'] ?? 'file'); // 'file' atau 'link'
$linkUrl    = trim($_POST['link_url'] ?? '');

$returnUrl  = '/kuliah/detail?id=' . $courseId . '&tab=' . ($tipe === 'materi' ? 'materi' : 'files');

// Validasi kepemilikan mata kuliah
$course = $conn->query("SELECT id FROM courses WHERE id = $courseId AND user_id = $userId")->fetch_assoc();
if (!$course) {
    flash('error', 'Mata kuliah tidak ditemukan.');
    redirect('/kuliah/');
}

if (empty($judul)) {
    flash('error', 'Judul materi / soal / jawaban wajib diisi.');
    redirect($returnUrl);
}

// ── KONDISI 1: PENGUMPULAN BERUPA TAUTAN / LINK ONLINE (Misal Video YouTube / Drive) ──
$hasFile = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;

if (!empty($linkUrl) && (!$hasFile || $modeUpload === 'link')) {
    // Normalisasi format URL jika belum diawali http:// atau https://
    if (!preg_match('~^(?:f|ht)tps?://~i', $linkUrl)) {
        $linkUrl = 'https://' . $linkUrl;
    }

    if (!filter_var($linkUrl, FILTER_VALIDATE_URL)) {
        flash('error', 'Format tautan / link URL tidak valid. Mohon periksa kembali.');
        redirect($returnUrl);
    }

    $parsedHost = strtolower(parse_url($linkUrl, PHP_URL_HOST) ?? '');
    $linkName = 'Tautan Eksternal';
    if (str_contains($parsedHost, 'youtube.com') || str_contains($parsedHost, 'youtu.be')) {
        $linkName = 'Video YouTube';
    } elseif (str_contains($parsedHost, 'drive.google.com')) {
        $linkName = 'Google Drive';
    } elseif (str_contains($parsedHost, 'loom.com')) {
        $linkName = 'Video Loom';
    } elseif (str_contains($parsedHost, 'docs.google.com')) {
        $linkName = 'Google Docs';
    } elseif (str_contains($parsedHost, 'canva.com')) {
        $linkName = 'Proyek Canva';
    } elseif (str_contains($parsedHost, 'figma.com')) {
        $linkName = 'Desain Figma';
    } elseif (str_contains($parsedHost, 'github.com')) {
        $linkName = 'Repositori GitHub';
    } elseif (!empty($parsedHost)) {
        $linkName = 'Link ' . $parsedHost;
    }

    // Pastikan kolom link_url tersedia
    $checkCol = @$conn->query("SHOW COLUMNS FROM uploads LIKE 'link_url'");
    if ($checkCol && $checkCol->num_rows === 0) {
        @$conn->query("ALTER TABLE uploads ADD COLUMN link_url TEXT AFTER path_file");
        @$conn->query("ALTER TABLE uploads MODIFY COLUMN nama_file VARCHAR(255) NULL");
        @$conn->query("ALTER TABLE uploads MODIFY COLUMN path_file VARCHAR(255) NULL");
        @$conn->query("ALTER TABLE uploads MODIFY COLUMN ukuran_file INT NULL DEFAULT 0");
        @$conn->query("ALTER TABLE uploads MODIFY COLUMN mime_type VARCHAR(100) NULL");
    }

    $zeroSize = 0;
    $mimeType = 'url';
    $emptyPath = '';

    $stmt = $conn->prepare('INSERT INTO uploads (course_task_id, course_id, user_id, tipe, judul, nama_file, path_file, link_url, ukuran_file, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('iiisssssis', $taskId, $courseId, $userId, $tipe, $judul, $linkName, $emptyPath, $linkUrl, $zeroSize, $mimeType);
        if ($stmt->execute()) {
            $typeLabels = ['soal' => 'Soal / Instruksi', 'materi' => 'Materi', 'jawaban' => 'Hasil / Jawaban Video'];
            flash('success', ($typeLabels[$tipe] ?? 'Tautan') . ' "' . htmlspecialchars($judul) . '" berhasil disimpan.');
        } else {
            flash('error', 'Gagal menyimpan tautan ke database.');
        }
    } else {
        // Fallback jika database belum update
        $stmt2 = $conn->prepare('INSERT INTO uploads (course_task_id, course_id, user_id, tipe, judul, nama_file, path_file, ukuran_file, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt2->bind_param('iiissssss', $taskId, $courseId, $userId, $tipe, $judul, $linkName, $linkUrl, $zeroSize, $mimeType);
        $stmt2->execute();
        flash('success', 'Tautan "' . htmlspecialchars($judul) . '" berhasil disimpan.');
    }

    redirect($returnUrl);
    exit;
}

// ── KONDISI 2: PENGUNGGAHAN FILE FISIK DOKUMEN ──
if (!$hasFile) {
    flash('error', 'Silakan pilih file untuk diunggah atau masukkan tautan/link URL video.');
    redirect($returnUrl);
}

$errCodes = [
    UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi batas server.',
    UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi batas form.',
    UPLOAD_ERR_PARTIAL    => 'File hanya terupload sebagian.',
    UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang dipilih.',
    UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara tidak tersedia.',
    UPLOAD_ERR_CANT_WRITE => 'Gagal menyimpan file.',
];
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    flash('error', $errCodes[$_FILES['file']['error']] ?? 'Terjadi kesalahan saat upload file.');
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
    'image/webp',
    'video/mp4',
    'video/quicktime',
    'application/zip',
    'application/x-zip-compressed',
    'text/plain',
];

$allowedExts = ['pdf','doc','docx','ppt','pptx','xls','xlsx','jpg','jpeg','png','gif','webp','mp4','mov','zip','txt'];
$maxSize = 25 * 1024 * 1024; // 25 MB

$file     = $_FILES['file'];
$origName = basename($file['name']);
$ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
$size     = $file['size'];
$tmpPath  = $file['tmp_name'];

// Check real MIME
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$realMime = finfo_file($finfo, $tmpPath);
finfo_close($finfo);

if (!in_array($ext, $allowedExts)) {
    flash('error', 'Tipe file tidak diizinkan. Gunakan PDF, DOCX, PPTX, XLS, JPG, PNG, MP4, atau ZIP.');
    redirect($returnUrl);
}

if (!in_array($realMime, $allowedMimes)) {
    flash('error', 'Format file tidak valid atau tidak aman.');
    redirect($returnUrl);
}

if ($size > $maxSize) {
    flash('error', 'Ukuran file melebihi batas maksimum 25 MB.');
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
    flash('error', 'Gagal memindahkan file ke server.');
    redirect($returnUrl);
}

// Save to DB (dukung link_url jika ada)
$checkCol = @$conn->query("SHOW COLUMNS FROM uploads LIKE 'link_url'");
if ($checkCol && $checkCol->num_rows > 0) {
    $stmt = $conn->prepare('INSERT INTO uploads (course_task_id, course_id, user_id, tipe, judul, nama_file, path_file, link_url, ukuran_file, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iiisssssis', $taskId, $courseId, $userId, $tipe, $judul, $origName, $uniqueName, $linkUrl, $size, $realMime);
} else {
    $stmt = $conn->prepare('INSERT INTO uploads (course_task_id, course_id, user_id, tipe, judul, nama_file, path_file, ukuran_file, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iiissssss', $taskId, $courseId, $userId, $tipe, $judul, $origName, $uniqueName, $size, $realMime);
}

if ($stmt && $stmt->execute()) {
    $typeLabels = ['soal' => 'Soal', 'materi' => 'Materi', 'jawaban' => 'Jawaban'];
    flash('success', ($typeLabels[$tipe] ?? 'File') . ' "' . htmlspecialchars($judul) . '" berhasil diunggah.');
} else {
    @unlink($destPath);
    flash('error', 'Gagal menyimpan data berkas ke database.');
}

redirect($returnUrl);
