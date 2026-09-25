<?php
$pageTitle = 'Detail Mata Kuliah';
require_once __DIR__ . '/../config.php';
requireLogin();

$userId   = $_SESSION['user_id'];
$courseId = (int)($_GET['id'] ?? 0);
$activeTab = $_GET['tab'] ?? 'tugas'; // tugas | materi | files | riwayat

$course = $conn->query("SELECT * FROM courses WHERE id = $courseId AND user_id = $userId")->fetch_assoc();
if (!$course) {
    flash('error', 'Mata kuliah tidak ditemukan.');
    redirect('/kuliah/');
}

if (isset($_GET['delete_task'])) {
    $taskId = (int)$_GET['delete_task'];
    $conn->query("DELETE FROM course_tasks WHERE id = $taskId AND user_id = $userId");
    flash('success', 'Tugas kuliah berhasil dihapus.');
    header('Location: ' . BASE_URL . '/kuliah/detail?id=' . $courseId . '&tab=tugas');
    exit;
}

$filter = $_GET['filter'] ?? 'all';
$whereFilter = '';
if ($filter === 'active') {
    $whereFilter = "AND ct.status != 'selesai'";
} elseif ($filter === 'belum') {
    $whereFilter = "AND ct.status = 'belum'";
} elseif ($filter === 'progres') {
    $whereFilter = "AND ct.status = 'progres'";
} elseif ($filter === 'done') {
    $whereFilter = "AND ct.status = 'selesai'";
}

$tasks = $conn->query("
    SELECT ct.* FROM course_tasks ct 
    WHERE ct.course_id = $courseId AND ct.user_id = $userId $whereFilter 
    ORDER BY 
        CASE ct.status WHEN 'progres' THEN 0 WHEN 'belum' THEN 1 ELSE 2 END,
        ct.deadline ASC
")->fetch_all(MYSQLI_ASSOC);

$counts = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END) as belum,
        SUM(CASE WHEN status = 'progres' THEN 1 ELSE 0 END) as progres,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
    FROM course_tasks 
    WHERE course_id = $courseId AND user_id = $userId
")->fetch_assoc();

// Fetch uploads by type
$materi  = $conn->query("SELECT * FROM uploads WHERE course_id = $courseId AND user_id = $userId AND tipe = 'materi' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$soal    = $conn->query("SELECT * FROM uploads WHERE course_id = $courseId AND user_id = $userId AND tipe = 'soal' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$jawaban = $conn->query("SELECT * FROM uploads WHERE course_id = $courseId AND user_id = $userId AND tipe = 'jawaban' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$allUploads = $conn->query("SELECT * FROM uploads WHERE course_id = $courseId AND user_id = $userId ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';

function formatFileSize($bytes) {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return number_format($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

function fileIcon($mime, $linkUrl = '') {
    $mime = $mime ?? '';
    $linkUrl = strtolower($linkUrl ?? '');
    if (!empty($linkUrl) || $mime === 'url') {
        if (str_contains($linkUrl, 'youtube.com') || str_contains($linkUrl, 'youtu.be')) {
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg>';
        }
        if (str_contains($linkUrl, 'drive.google.com')) {
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M12 2L2 19h20L12 2z"/><line x1="2" y1="19" x2="12" y2="2"/></svg>';
        }
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';
    }
    if (str_contains($mime, 'pdf')) return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
    if (str_contains($mime, 'image')) return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
    if (str_contains($mime, 'video')) return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>';
    if (str_contains($mime, 'powerpoint') || str_contains($mime, 'presentation')) return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';
    if (str_contains($mime, 'zip')) return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>';
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';
}
?>

<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>/kuliah/" class="btn btn-ghost btn-sm" style="margin-bottom: 0.5rem; padding-left: 0; color: var(--text-muted);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali ke Jadwal Kuliah
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h1><?= htmlspecialchars($course['nama_mk']) ?></h1>
            <span class="badge badge-blue"><?= ucfirst($course['hari']) ?></span>
            <span class="badge badge-gray"><?= substr($course['jam_mulai'], 0, 5) ?> - <?= substr($course['jam_selesai'], 0, 5) ?> WIB</span>
            <?php if ($course['kelas']): ?>
                <span class="badge badge-purple">Kelas <?= htmlspecialchars($course['kelas']) ?></span>
            <?php endif; ?>
        </div>
        <p style="margin-top: 0.35rem;">
            <?= htmlspecialchars($course['dosen'] ?: 'Dosen belum ditentukan') ?> 
            &bull; Ruang <?= htmlspecialchars($course['ruang'] ?: 'TBA') ?>
        </p>
    </div>
    <div class="header-actions">
        <button class="btn btn-primary" onclick="openModal('addCourseTaskModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tugas
        </button>
    </div>
</div>

<!-- Tab Navigation -->
<div class="detail-tabs" style="margin-bottom: 2rem;">
    <a href="?id=<?= $courseId ?>&tab=tugas" class="detail-tab <?= $activeTab === 'tugas' ? 'active' : '' ?>">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Tugas <span class="detail-tab-count"><?= (int)$counts['total'] ?></span>
    </a>
    <a href="?id=<?= $courseId ?>&tab=materi" class="detail-tab <?= $activeTab === 'materi' ? 'active' : '' ?>">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        Materi <span class="detail-tab-count"><?= count($materi) ?></span>
    </a>
    <a href="?id=<?= $courseId ?>&tab=files" class="detail-tab <?= $activeTab === 'files' ? 'active' : '' ?>">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Soal &amp; Jawaban <span class="detail-tab-count"><?= count($soal) + count($jawaban) ?></span>
    </a>
    <a href="?id=<?= $courseId ?>&tab=riwayat" class="detail-tab <?= $activeTab === 'riwayat' ? 'active' : '' ?>">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Semua Riwayat <span class="detail-tab-count"><?= count($allUploads) ?></span>
    </a>
</div>

<!-- ===== TAB: TUGAS ===== -->
<?php if ($activeTab === 'tugas'): ?>
<div class="filter-bar">
    <div class="tabs">
        <a href="?id=<?= $courseId ?>&tab=tugas&filter=all" class="tab <?= $filter === 'all' ? 'active' : '' ?>">
            Semua (<?= (int)$counts['total'] ?>)
        </a>
        <a href="?id=<?= $courseId ?>&tab=tugas&filter=belum" class="tab <?= $filter === 'belum' ? 'active' : '' ?>">
            Belum (<?= (int)$counts['belum'] ?>)
        </a>
        <a href="?id=<?= $courseId ?>&tab=tugas&filter=progres" class="tab <?= $filter === 'progres' ? 'active' : '' ?>">
            Dikerjakan (<?= (int)$counts['progres'] ?>)
        </a>
        <a href="?id=<?= $courseId ?>&tab=tugas&filter=done" class="tab <?= $filter === 'done' ? 'active' : '' ?>">
            Selesai (<?= (int)$counts['selesai'] ?>)
        </a>
    </div>
    <div class="search-box">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" class="search-input" placeholder="Cari tugas..." data-search-target=".task-item" data-search-empty="searchEmpty">
    </div>
</div>
<div id="searchEmpty" style="display: none; text-align: center; padding: 3rem 1rem; color: var(--text-muted);">Tidak ada tugas yang sesuai pencarian.</div>

<?php if (empty($tasks)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <h3>Belum Ada Tugas</h3>
        <p>Catat pekerjaan rumah, laporan, kuis, atau tugas proyek dari dosen pengampu.</p>
        <button class="btn btn-primary" onclick="openModal('addCourseTaskModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tugas Sekarang
        </button>
    </div>
<?php else: ?>
    <div class="task-list">
        <?php foreach ($tasks as $task):
            $isDone   = ($task['status'] === 'selesai');
            $isProgres = ($task['status'] === 'progres');
            $urgencyClass = 'urgency-safe'; $urgencyLabel = '';
            if ($task['deadline']) {
                $deadlineTs = strtotime($task['deadline']);
                $diff = $deadlineTs - time();
                $diffDays = floor($diff / 86400);
                if ($isDone) { $urgencyClass = 'urgency-safe'; $urgencyLabel = 'Selesai'; }
                elseif ($diff < 0) { $urgencyClass = 'urgency-overdue'; $urgencyLabel = 'Terlewat'; }
                elseif ($diffDays == 0) { $urgencyClass = 'urgency-urgent'; $urgencyLabel = 'Hari Ini (' . date('H:i', $deadlineTs) . ')'; }
                elseif ($diffDays == 1) { $urgencyClass = 'urgency-soon'; $urgencyLabel = 'Besok'; }
                else { $urgencyClass = 'urgency-safe'; $urgencyLabel = $diffDays . ' hari lagi'; }
            }
            $statusLabel = 'Belum Mulai'; $statusClass = 'status-belum'; $nextStatus = 'progres';
            if ($task['status'] === 'progres') { $statusLabel = 'Dikerjakan'; $statusClass = 'status-progres'; $nextStatus = 'selesai'; }
            elseif ($task['status'] === 'selesai') { $statusLabel = 'Selesai'; $statusClass = 'status-selesai'; $nextStatus = 'belum'; }
        ?>
            <div class="task-item">
                <a href="<?= BASE_URL ?>/proses?action=update_status&type=course&id=<?= $task['id'] ?>&status=<?= $nextStatus ?>&return=<?= urlencode('/kuliah/detail?id=' . $courseId . '&tab=tugas') ?>"
                   class="status-pill <?= $statusClass ?>" title="Klik untuk ubah status">
                    <?php if ($task['status'] === 'selesai'): ?>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    <?php elseif ($task['status'] === 'progres'): ?>
                        <span style="width:6px;height:6px;border-radius:50%;background:#0284c7;display:inline-block;"></span>
                    <?php else: ?>
                        <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;display:inline-block;"></span>
                    <?php endif; ?>
                    <?= $statusLabel ?>
                </a>

                <div class="task-content <?= $isDone ? 'task-item-done' : '' ?>">
                    <h4><?= htmlspecialchars($task['judul']) ?></h4>
                    <?php if ($task['deskripsi']): ?>
                        <p><?= htmlspecialchars($task['deskripsi']) ?></p>
                    <?php endif; ?>
                </div>

                <div style="display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap;">
                    <?php if ($task['deadline']): ?>
                        <span class="task-deadline">
                            <span class="urgency-badge <?= $urgencyClass ?>"><?= $urgencyLabel ?></span>
                            <span><?= date('d M Y, H:i', strtotime($task['deadline'])) ?></span>
                        </span>
                    <?php endif; ?>
                    <?php if ($task['tempat_pengumpulan']): ?>
                        <span class="badge badge-gray" title="Tempat Pengumpulan">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars($task['tempat_pengumpulan']) ?>
                        </span>
                    <?php endif; ?>
                    <div style="display: flex; gap: 0.25rem;">
                        <button type="button" class="btn btn-ghost btn-sm" 
                                data-task="<?= htmlspecialchars(json_encode($task), ENT_QUOTES, 'UTF-8') ?>" 
                                onclick="openEditCourseTaskModal(this)" title="Edit Tugas">
                            Edit
                        </button>
                        <a href="?id=<?= $courseId ?>&delete_task=<?= $task['id'] ?>&tab=tugas" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus tugas ini?')">Hapus</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ===== TAB: MATERI ===== -->
<?php elseif ($activeTab === 'materi'): ?>
<div class="upload-tab-header">
    <div>
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.3rem;">Materi Kuliah</h3>
        <p style="font-size: 0.88rem; color: var(--text-secondary);">Upload slide, bahan ajar, modul, atau referensi untuk mata kuliah ini.</p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openModal('uploadMateriModal')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload Materi
    </button>
</div>

<?php if (empty($materi)): ?>
    <div class="empty-state" style="margin-top: 1.5rem;">
        <div class="empty-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <h3>Belum Ada Materi</h3>
        <p>Upload slide PPT, modul PDF, atau bahan ajar lainnya dari dosen.</p>
        <button class="btn btn-primary" onclick="openModal('uploadMateriModal')">Upload Materi Pertama</button>
    </div>
<?php else: ?>
    <div class="upload-file-grid">
        <?php foreach ($materi as $f): 
            $isUrl = !empty($f['link_url']) || ($f['mime_type'] === 'url');
            $targetLink = $isUrl ? ($f['link_url'] ?: $f['path_file']) : (BASE_URL . '/download?id=' . $f['id']);
        ?>
            <div class="upload-file-card">
                <div class="upload-file-icon type-materi"><?= fileIcon($f['mime_type'], $f['link_url'] ?? '') ?></div>
                <div class="upload-file-info">
                    <div class="upload-file-title"><?= htmlspecialchars($f['judul']) ?></div>
                    <div class="upload-file-meta">
                        <span><?= htmlspecialchars($f['nama_file'] ?: ($isUrl ? 'Tautan Online' : 'Berkas')) ?></span>
                        <span><?= $isUrl ? 'Tautan / Link' : formatFileSize($f['ukuran_file']) ?></span>
                        <span><?= date('d M Y', strtotime($f['created_at'])) ?></span>
                    </div>
                    <?php if ($isUrl): ?>
                        <div style="font-size: 0.78rem; color: var(--accent); margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 280px;">
                            <a href="<?= htmlspecialchars($targetLink) ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">
                                <?= htmlspecialchars($targetLink) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="upload-file-actions">
                    <?php if ($isUrl): ?>
                        <a href="<?= htmlspecialchars($targetLink) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm" title="Buka Tautan Materi">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            Buka Link
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/download?id=<?= $f['id'] ?>" class="btn btn-secondary btn-sm" title="Download">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Unduh
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/proses?action=delete_upload&id=<?= $f['id'] ?>&course_id=<?= $courseId ?>&tab=materi" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus berkas/tautan ini?')">Hapus</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ===== TAB: SOAL & JAWABAN ===== -->
<?php elseif ($activeTab === 'files'): ?>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">

    <!-- Soal Column -->
    <div>
        <div class="upload-tab-header" style="margin-bottom: 1rem;">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">Soal Tugas / Kuis</h3>
                <p style="font-size: 0.84rem; color: var(--text-secondary);">Berkas soal atau tautan video/instruksi</p>
            </div>
            <button class="btn btn-secondary btn-sm" onclick="openUploadModal('soal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Upload Soal / Link
            </button>
        </div>
        <?php if (empty($soal)): ?>
            <div class="upload-empty-mini">Belum ada soal diupload. Klik "Upload Soal / Link" untuk menambahkan.</div>
        <?php else: ?>
            <div class="upload-list">
                <?php foreach ($soal as $f): 
                    $isUrl = !empty($f['link_url']) || ($f['mime_type'] === 'url');
                    $targetLink = $isUrl ? ($f['link_url'] ?: $f['path_file']) : (BASE_URL . '/download?id=' . $f['id']);
                ?>
                    <div class="upload-list-item">
                        <div class="upload-list-icon type-soal"><?= fileIcon($f['mime_type'], $f['link_url'] ?? '') ?></div>
                        <div class="upload-list-info" style="min-width: 0;">
                            <div class="upload-list-title"><?= htmlspecialchars($f['judul']) ?></div>
                            <div class="upload-list-meta">
                                <?php if ($isUrl): ?>
                                    <span class="badge badge-purple" style="font-size: 0.68rem; padding: 1px 6px;"><?= htmlspecialchars($f['nama_file'] ?: 'Tautan') ?></span>
                                <?php else: ?>
                                    <?= formatFileSize($f['ukuran_file']) ?>
                                <?php endif; ?>
                                &bull; <?= date('d M Y', strtotime($f['created_at'])) ?>
                            </div>
                            <?php if ($isUrl): ?>
                                <div style="font-size: 0.74rem; color: var(--accent); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">
                                    <a href="<?= htmlspecialchars($targetLink) ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">
                                        <?= htmlspecialchars($targetLink) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 0.25rem; flex-shrink: 0; align-items: center;">
                            <?php if ($isUrl): ?>
                                <a href="<?= htmlspecialchars($targetLink) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-ghost btn-sm" title="Buka Tautan">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/download?id=<?= $f['id'] ?>" class="btn btn-ghost btn-sm" title="Unduh File">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/proses?action=delete_upload&id=<?= $f['id'] ?>&course_id=<?= $courseId ?>&tab=files" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus?')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Jawaban Column -->
    <div>
        <div class="upload-tab-header" style="margin-bottom: 1rem;">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">Hasil Pengerjaan / Jawaban</h3>
                <p style="font-size: 0.84rem; color: var(--text-secondary);">File atau tautan video/jawaban yang kamu kumpulkan</p>
            </div>
            <button class="btn btn-primary btn-sm" onclick="openUploadModal('jawaban')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Upload Jawaban / Video Link
            </button>
        </div>
        <?php if (empty($jawaban)): ?>
            <div class="upload-empty-mini">Belum ada jawaban diupload. Klik "Upload Jawaban / Video Link" untuk menyimpan hasil kerja atau link video kamu.</div>
        <?php else: ?>
            <div class="upload-list">
                <?php foreach ($jawaban as $f): 
                    $isUrl = !empty($f['link_url']) || ($f['mime_type'] === 'url');
                    $targetLink = $isUrl ? ($f['link_url'] ?: $f['path_file']) : (BASE_URL . '/download?id=' . $f['id']);
                ?>
                    <div class="upload-list-item">
                        <div class="upload-list-icon type-jawaban"><?= fileIcon($f['mime_type'], $f['link_url'] ?? '') ?></div>
                        <div class="upload-list-info" style="min-width: 0;">
                            <div class="upload-list-title"><?= htmlspecialchars($f['judul']) ?></div>
                            <div class="upload-list-meta">
                                <?php if ($isUrl): ?>
                                    <span class="badge badge-green" style="font-size: 0.68rem; padding: 1px 6px;"><?= htmlspecialchars($f['nama_file'] ?: 'Tautan Video') ?></span>
                                <?php else: ?>
                                    <?= formatFileSize($f['ukuran_file']) ?>
                                <?php endif; ?>
                                &bull; <?= date('d M Y H:i', strtotime($f['created_at'])) ?>
                            </div>
                            <?php if ($isUrl): ?>
                                <div style="font-size: 0.74rem; color: var(--accent); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">
                                    <a href="<?= htmlspecialchars($targetLink) ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">
                                        <?= htmlspecialchars($targetLink) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 0.25rem; flex-shrink: 0; align-items: center;">
                            <?php if ($isUrl): ?>
                                <a href="<?= htmlspecialchars($targetLink) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-ghost btn-sm" title="Buka Tautan Video / Jawaban">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/download?id=<?= $f['id'] ?>" class="btn btn-ghost btn-sm" title="Unduh File">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/proses?action=delete_upload&id=<?= $f['id'] ?>&course_id=<?= $courseId ?>&tab=files" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus?')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== TAB: RIWAYAT ===== -->
<?php elseif ($activeTab === 'riwayat'): ?>
<div style="margin-bottom: 1.5rem;">
    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.35rem;">Semua Riwayat Upload &amp; Tautan</h3>
    <p style="font-size: 0.88rem; color: var(--text-secondary);">Seluruh file dan tautan video yang pernah kamu cantumkan untuk mata kuliah ini.</p>
</div>

<?php if (empty($allUploads)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h3>Belum Ada Riwayat Upload</h3>
        <p>Upload materi, soal, atau tautan jawaban di tab yang tersedia untuk mulai membangun arsip kuliah kamu.</p>
    </div>
<?php else: ?>
    <div class="upload-history-list">
        <?php foreach ($allUploads as $f): 
            $typeColors = ['soal' => 'badge-yellow', 'materi' => 'badge-blue', 'jawaban' => 'badge-green'];
            $typeLabels = ['soal' => 'Soal', 'materi' => 'Materi', 'jawaban' => 'Jawaban'];
            $tabMap = ['soal' => 'files', 'materi' => 'materi', 'jawaban' => 'files'];
            $isUrl = !empty($f['link_url']) || ($f['mime_type'] === 'url');
            $targetLink = $isUrl ? ($f['link_url'] ?: $f['path_file']) : (BASE_URL . '/download?id=' . $f['id']);
        ?>
            <div class="upload-history-item">
                <div class="upload-history-icon"><?= fileIcon($f['mime_type'], $f['link_url'] ?? '') ?></div>
                <div class="upload-history-info">
                    <div class="upload-history-title"><?= htmlspecialchars($f['judul']) ?></div>
                    <div class="upload-history-meta">
                        <span class="badge <?= $typeColors[$f['tipe']] ?>" style="font-size: 0.7rem;"><?= $typeLabels[$f['tipe']] ?></span>
                        <span><?= htmlspecialchars($f['nama_file'] ?: ($isUrl ? 'Tautan Online' : 'Berkas')) ?></span>
                        <span><?= $isUrl ? 'Tautan / Link' : formatFileSize($f['ukuran_file']) ?></span>
                        <span><?= date('d M Y, H:i', strtotime($f['created_at'])) ?></span>
                    </div>
                    <?php if ($isUrl): ?>
                        <div style="font-size: 0.78rem; color: var(--accent); margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 320px;">
                            <a href="<?= htmlspecialchars($targetLink) ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">
                                <?= htmlspecialchars($targetLink) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="upload-history-actions">
                    <?php if ($isUrl): ?>
                        <a href="<?= htmlspecialchars($targetLink) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm" title="Buka Tautan">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            Buka
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/download?id=<?= $f['id'] ?>" class="btn btn-secondary btn-sm" title="Unduh File">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Unduh
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/proses?action=delete_upload&id=<?= $f['id'] ?>&course_id=<?= $courseId ?>&tab=riwayat" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus file ini dari riwayat?')">Hapus</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php endif; ?>

<!-- ===== MODALS ===== -->

<!-- Modal Tambah Tugas -->
<div class="modal-overlay" id="addCourseTaskModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Tambah Tugas Kuliah</h2>
            <button class="modal-close" onclick="closeModal('addCourseTaskModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/proses">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_course_task">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <div class="form-group">
                    <label for="judul">Nama / Judul Tugas</label>
                    <input type="text" id="judul" name="judul" class="form-control" placeholder="Contoh: Laporan Praktikum 2 - Algoritma Sorting" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi &amp; Instruksi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" placeholder="Format pengumpulan, bab materi, link rujukan..."></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="deadline">Batas Waktu (Deadline)</label>
                        <input type="datetime-local" id="deadline" name="deadline" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="tempat">Tempat / Portal Pengumpulan</label>
                        <input type="text" id="tempat" name="tempat" class="form-control" placeholder="LMS, Email Dosen, dll.">
                    </div>
                </div>
                <div class="form-group">
                    <label for="status">Status Pengerjaan</label>
                    <select id="status" name="status" class="form-control">
                        <option value="belum">Belum Mulai</option>
                        <option value="progres">Sedang Dikerjakan</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCourseTaskModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Tugas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Tugas -->
<div class="modal-overlay" id="editCourseTaskModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Tugas Kuliah</h2>
            <button class="modal-close" onclick="closeModal('editCourseTaskModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/proses">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_course_task">
                <input type="hidden" name="id" id="edit_course_task_id">
                <input type="hidden" name="course_id" id="edit_course_task_course_id" value="<?= $courseId ?>">
                <div class="form-group">
                    <label for="edit_course_task_judul">Nama / Judul Tugas</label>
                    <input type="text" id="edit_course_task_judul" name="judul" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_course_task_deskripsi">Deskripsi &amp; Instruksi</label>
                    <textarea id="edit_course_task_deskripsi" name="deskripsi" class="form-control" rows="4"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_course_task_deadline">Batas Waktu</label>
                        <input type="datetime-local" id="edit_course_task_deadline" name="deadline" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_course_task_tempat">Tempat / Tautan Pengumpulan</label>
                        <input type="text" id="edit_course_task_tempat" name="tempat" class="form-control" placeholder="LMS, Link Drive, Email Dosen...">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_course_task_status">Status</label>
                    <select id="edit_course_task_status" name="status" class="form-control">
                        <option value="belum">Belum Mulai</option>
                        <option value="progres">Sedang Dikerjakan</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editCourseTaskModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Upload Materi -->
<div class="modal-overlay" id="uploadMateriModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Upload Materi Kuliah</h2>
            <button class="modal-close" onclick="closeModal('uploadMateriModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/upload" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <input type="hidden" name="tipe" value="materi">
                <input type="hidden" name="mode_upload" id="uploadMateriModeInput" value="file">

                <div class="form-group">
                    <label>Metode Materi</label>
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <button type="button" class="btn btn-sm btn-primary btn-mode-file active" onclick="switchUploadMode('uploadMateriModal', 'file')" style="flex: 1; border-radius: 8px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Unggah File
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary btn-mode-link" onclick="switchUploadMode('uploadMateriModal', 'link')" style="flex: 1; border-radius: 8px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            Tautan / Link Materi
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="materi_judul">Nama / Judul Materi</label>
                    <input type="text" id="materi_judul" name="judul" class="form-control" placeholder="Contoh: Slide Pertemuan 3 - Array dan Pointer" required>
                </div>

                <!-- Bagian 1: File -->
                <div class="upload-section-file">
                    <div class="form-group">
                        <label for="materi_file">Pilih Berkas File</label>
                        <div class="file-upload-zone" id="materiUploadZone">
                            <input type="file" id="materi_file" name="file" class="file-upload-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.txt,.mp4">
                            <div class="file-upload-placeholder">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <p>Klik atau drag &amp; drop file di sini</p>
                                <span>PDF, DOCX, PPTX, XLS, Gambar, ZIP — maks. 25 MB</span>
                            </div>
                            <div class="file-upload-selected" id="materiSelected" style="display:none;"></div>
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Tautan Link / Video -->
                <div class="upload-section-link" style="display: none;">
                    <div class="form-group">
                        <label for="materi_link_url">Tautan / Link Online (YouTube, Google Drive, SlideShare, dll.)</label>
                        <div style="position: relative;">
                            <input type="url" id="materi_link_url" name="link_url" class="form-control" placeholder="https://youtube.com/watch?v=... atau https://drive.google.com/..." style="padding-left: 2.5rem;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </div>
                        <span style="display: block; font-size: 0.78rem; color: var(--text-muted); margin-top: 0.4rem;">
                            💡 Tidak perlu upload file fisik. Tautan akan langsung bisa dibuka di tab baru.
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('uploadMateriModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Materi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Upload Soal/Jawaban (dinamis) -->
<div class="modal-overlay" id="uploadFileModal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="uploadFileModalTitle">Upload Berkas / Tautan Tugas</h2>
            <button class="modal-close" onclick="closeModal('uploadFileModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/upload" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <input type="hidden" name="tipe" id="uploadFileTipe" value="soal">
                <input type="hidden" name="mode_upload" id="uploadFileModeInput" value="file">

                <div class="form-group">
                    <label>Metode Pengumpulan / Berkas</label>
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <button type="button" class="btn btn-sm btn-primary btn-mode-file active" onclick="switchUploadMode('uploadFileModal', 'file')" style="flex: 1; border-radius: 8px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Unggah File
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary btn-mode-link" onclick="switchUploadMode('uploadFileModal', 'link')" style="flex: 1; border-radius: 8px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            Tautan / Link Video &amp; Jawaban
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="file_judul">Nama / Judul Berkas</label>
                    <input type="text" id="file_judul" name="judul" class="form-control" placeholder="Contoh: Jawaban Video Praktikum / Soal UTS" required>
                </div>

                <!-- Bagian 1: File Fisik -->
                <div class="upload-section-file">
                    <div class="form-group">
                        <label for="upload_file">Pilih Berkas</label>
                        <div class="file-upload-zone" id="fileUploadZone">
                            <input type="file" id="upload_file" name="file" class="file-upload-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.txt,.mp4,.mov">
                            <div class="file-upload-placeholder">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <p>Klik atau drag &amp; drop file di sini</p>
                                <span>PDF, DOCX, PPTX, XLS, Gambar, MP4, ZIP — maks. 25 MB</span>
                            </div>
                            <div class="file-upload-selected" id="fileSelected" style="display:none;"></div>
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Tautan Link / Video -->
                <div class="upload-section-link" style="display: none;">
                    <div class="form-group">
                        <label for="upload_link_url">Tautan / Link Video atau Jawaban Online</label>
                        <div style="position: relative;">
                            <input type="url" id="upload_link_url" name="link_url" class="form-control" placeholder="https://youtube.com/watch?v=... atau https://drive.google.com/..." style="padding-left: 2.5rem;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </div>
                        <div style="margin-top: 0.5rem; font-size: 0.78rem; color: var(--text-secondary); line-height: 1.45; background: var(--bg-secondary); padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border);">
                            💡 <strong>Sangat cocok untuk:</strong> Video presentasi YouTube, Google Drive, Loom, Google Docs, Canva, Figma, atau website tugas tanpa perlu mengunggah berkas.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('uploadFileModal')">Batal</button>
                <button type="submit" class="btn btn-primary" id="uploadFileSubmitBtn">Simpan Berkas</button>
            </div>
        </form>
    </div>
</div>

<script>
// Fungsi Buka Modal Edit Tugas Kuliah (Aman dari kutip, newline, karakter khusus)
function openEditCourseTaskModal(btn) {
    try {
        const data = JSON.parse(btn.getAttribute('data-task'));
        document.getElementById('edit_course_task_id').value = data.id;
        const cidEl = document.getElementById('edit_course_task_course_id');
        if (cidEl) cidEl.value = data.course_id;
        document.getElementById('edit_course_task_judul').value = data.judul || '';
        document.getElementById('edit_course_task_deskripsi').value = data.deskripsi || '';
        if (data.deadline && data.deadline.length >= 16) {
            document.getElementById('edit_course_task_deadline').value = data.deadline.substring(0, 16).replace(' ', 'T');
        } else {
            document.getElementById('edit_course_task_deadline').value = '';
        }
        document.getElementById('edit_course_task_tempat').value = data.tempat_pengumpulan || '';
        document.getElementById('edit_course_task_status').value = data.status || 'belum';
        openModal('editCourseTaskModal');
    } catch (e) {
        console.error('Gagal membuka modal edit tugas kuliah', e);
    }
}

// Buka Modal Upload File / Link Tautan
function openUploadModal(tipe) {
    document.getElementById('uploadFileTipe').value = tipe;
    const titles = { 
        soal: 'Upload Soal / Tautan Tugas', 
        jawaban: 'Upload Hasil Jawaban / Tautan Video' 
    };
    document.getElementById('uploadFileModalTitle').textContent = titles[tipe] || 'Upload Berkas / Tautan';
    switchUploadMode('uploadFileModal', 'file');
    openModal('uploadFileModal');
}

// Switcher Mode Upload antara "File Fisik" dan "Tautan / Link Video"
function switchUploadMode(modalId, mode) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    const modeInput = modal.querySelector('input[name="mode_upload"]');
    const fileSec = modal.querySelector('.upload-section-file');
    const linkSec = modal.querySelector('.upload-section-link');
    const fileInput = modal.querySelector('input[type="file"]');
    const linkInput = modal.querySelector('input[name="link_url"]');
    const btnFile = modal.querySelector('.btn-mode-file');
    const btnLink = modal.querySelector('.btn-mode-link');
    const submitBtn = modal.querySelector('button[type="submit"]');

    if (mode === 'link') {
        if (modeInput) modeInput.value = 'link';
        if (fileSec) fileSec.style.display = 'none';
        if (linkSec) linkSec.style.display = 'block';
        if (fileInput) fileInput.required = false;
        if (linkInput) {
            linkInput.required = true;
            setTimeout(() => linkInput.focus(), 100);
        }
        if (btnFile) {
            btnFile.classList.remove('btn-primary', 'active');
            btnFile.classList.add('btn-secondary');
        }
        if (btnLink) {
            btnLink.classList.remove('btn-secondary');
            btnLink.classList.add('btn-primary', 'active');
        }
        if (submitBtn) submitBtn.textContent = 'Simpan Tautan Link';
    } else {
        if (modeInput) modeInput.value = 'file';
        if (fileSec) fileSec.style.display = 'block';
        if (linkSec) linkSec.style.display = 'none';
        if (fileInput) fileInput.required = true;
        if (linkInput) {
            linkInput.required = false;
        }
        if (btnFile) {
            btnFile.classList.remove('btn-secondary');
            btnFile.classList.add('btn-primary', 'active');
        }
        if (btnLink) {
            btnLink.classList.remove('btn-primary', 'active');
            btnLink.classList.add('btn-secondary');
        }
        if (submitBtn) submitBtn.textContent = 'Upload File';
    }
}

// File upload zone interactivity
function setupFileZone(inputId, selectedId, zoneId) {
    const input = document.getElementById(inputId);
    const selected = document.getElementById(selectedId);
    const zone = document.getElementById(zoneId);
    if (!input || !zone) return;
    
    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const f = this.files[0];
            const kb = f.size < 1048576 ? (f.size/1024).toFixed(1) + ' KB' : (f.size/1048576).toFixed(1) + ' MB';
            selected.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><span>${f.name}</span><small>${kb}</small>`;
            selected.style.display = 'flex';
            zone.querySelector('.file-upload-placeholder').style.display = 'none';
        }
    });
    
    ['dragover','dragenter'].forEach(e => zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.add('drag-over'); }));
    ['dragleave','drop'].forEach(e => zone.addEventListener(e, ev => { zone.classList.remove('drag-over'); }));
    zone.addEventListener('drop', function(ev) {
        ev.preventDefault();
        if (ev.dataTransfer.files && ev.dataTransfer.files[0]) {
            input.files = ev.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
}

setupFileZone('materi_file', 'materiSelected', 'materiUploadZone');
setupFileZone('upload_file', 'fileSelected', 'fileUploadZone');
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
