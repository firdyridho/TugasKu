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
    header('Location: ' . BASE_URL . '/kuliah/detail.php?id=' . $courseId . '&tab=tugas');
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

function fileIcon($mime) {
    if (str_contains($mime, 'pdf')) return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
    if (str_contains($mime, 'image')) return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
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
                <a href="<?= BASE_URL ?>/proses.php?action=update_status&type=course&id=<?= $task['id'] ?>&status=<?= $nextStatus ?>&return=<?= urlencode('/kuliah/detail.php?id=' . $courseId . '&tab=tugas') ?>"
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
                        <button class="btn btn-ghost btn-sm" onclick="editCourseTask(<?= $task['id'] ?>, <?= $courseId ?>, '<?= htmlspecialchars(addslashes($task['judul'])) ?>', '<?= htmlspecialchars(addslashes($task['deskripsi'] ?? '')) ?>', '<?= $task['deadline'] ?? '' ?>', '<?= htmlspecialchars(addslashes($task['tempat_pengumpulan'] ?? '')) ?>', '<?= $task['status'] ?>')">Edit</button>
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
        <?php foreach ($materi as $f): ?>
            <div class="upload-file-card">
                <div class="upload-file-icon type-materi"><?= fileIcon($f['mime_type']) ?></div>
                <div class="upload-file-info">
                    <div class="upload-file-title"><?= htmlspecialchars($f['judul']) ?></div>
                    <div class="upload-file-meta">
                        <span><?= htmlspecialchars($f['nama_file']) ?></span>
                        <span><?= formatFileSize($f['ukuran_file']) ?></span>
                        <span><?= date('d M Y', strtotime($f['created_at'])) ?></span>
                    </div>
                </div>
                <div class="upload-file-actions">
                    <a href="<?= BASE_URL ?>/download.php?id=<?= $f['id'] ?>" class="btn btn-secondary btn-sm" title="Download">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Unduh
                    </a>
                    <a href="<?= BASE_URL ?>/proses.php?action=delete_upload&id=<?= $f['id'] ?>&course_id=<?= $courseId ?>&tab=materi" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus file ini?')">Hapus</a>
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
                <p style="font-size: 0.84rem; color: var(--text-secondary);">File soal dari dosen</p>
            </div>
            <button class="btn btn-secondary btn-sm" onclick="openUploadModal('soal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Upload Soal
            </button>
        </div>
        <?php if (empty($soal)): ?>
            <div class="upload-empty-mini">Belum ada soal diupload. Klik "Upload Soal" untuk menambahkan.</div>
        <?php else: ?>
            <div class="upload-list">
                <?php foreach ($soal as $f): ?>
                    <div class="upload-list-item">
                        <div class="upload-list-icon type-soal"><?= fileIcon($f['mime_type']) ?></div>
                        <div class="upload-list-info">
                            <div class="upload-list-title"><?= htmlspecialchars($f['judul']) ?></div>
                            <div class="upload-list-meta"><?= formatFileSize($f['ukuran_file']) ?> &bull; <?= date('d M Y', strtotime($f['created_at'])) ?></div>
                        </div>
                        <div style="display: flex; gap: 0.25rem; flex-shrink: 0;">
                            <a href="<?= BASE_URL ?>/download.php?id=<?= $f['id'] ?>" class="btn btn-ghost btn-sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            </a>
                            <a href="<?= BASE_URL ?>/proses.php?action=delete_upload&id=<?= $f['id'] ?>&course_id=<?= $courseId ?>&tab=files" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus?')">
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
                <p style="font-size: 0.84rem; color: var(--text-secondary);">File yang sudah kamu kumpulkan</p>
            </div>
            <button class="btn btn-primary btn-sm" onclick="openUploadModal('jawaban')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Upload Jawaban
            </button>
        </div>
        <?php if (empty($jawaban)): ?>
            <div class="upload-empty-mini">Belum ada jawaban diupload. Klik "Upload Jawaban" untuk menyimpan hasil kerja kamu.</div>
        <?php else: ?>
            <div class="upload-list">
                <?php foreach ($jawaban as $f): ?>
                    <div class="upload-list-item">
                        <div class="upload-list-icon type-jawaban"><?= fileIcon($f['mime_type']) ?></div>
                        <div class="upload-list-info">
                            <div class="upload-list-title"><?= htmlspecialchars($f['judul']) ?></div>
                            <div class="upload-list-meta"><?= formatFileSize($f['ukuran_file']) ?> &bull; <?= date('d M Y H:i', strtotime($f['created_at'])) ?></div>
                        </div>
                        <div style="display: flex; gap: 0.25rem; flex-shrink: 0;">
                            <a href="<?= BASE_URL ?>/download.php?id=<?= $f['id'] ?>" class="btn btn-ghost btn-sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            </a>
                            <a href="<?= BASE_URL ?>/proses.php?action=delete_upload&id=<?= $f['id'] ?>&course_id=<?= $courseId ?>&tab=files" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus?')">
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
    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.35rem;">Semua Riwayat Upload</h3>
    <p style="font-size: 0.88rem; color: var(--text-secondary);">Seluruh file yang pernah kamu upload untuk mata kuliah ini, dari yang terbaru.</p>
</div>

<?php if (empty($allUploads)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h3>Belum Ada Riwayat Upload</h3>
        <p>Upload materi, soal, atau jawaban di tab yang tersedia untuk mulai membangun arsip kuliah kamu.</p>
    </div>
<?php else: ?>
    <div class="upload-history-list">
        <?php foreach ($allUploads as $f): 
            $typeColors = ['soal' => 'badge-yellow', 'materi' => 'badge-blue', 'jawaban' => 'badge-green'];
            $typeLabels = ['soal' => 'Soal', 'materi' => 'Materi', 'jawaban' => 'Jawaban'];
            $tabMap = ['soal' => 'files', 'materi' => 'materi', 'jawaban' => 'files'];
        ?>
            <div class="upload-history-item">
                <div class="upload-history-icon"><?= fileIcon($f['mime_type']) ?></div>
                <div class="upload-history-info">
                    <div class="upload-history-title"><?= htmlspecialchars($f['judul']) ?></div>
                    <div class="upload-history-meta">
                        <span class="badge <?= $typeColors[$f['tipe']] ?>" style="font-size: 0.7rem;"><?= $typeLabels[$f['tipe']] ?></span>
                        <span><?= htmlspecialchars($f['nama_file']) ?></span>
                        <span><?= formatFileSize($f['ukuran_file']) ?></span>
                        <span><?= date('d M Y, H:i', strtotime($f['created_at'])) ?></span>
                    </div>
                </div>
                <div class="upload-history-actions">
                    <a href="<?= BASE_URL ?>/download.php?id=<?= $f['id'] ?>" class="btn btn-secondary btn-sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Unduh
                    </a>
                    <a href="<?= BASE_URL ?>/proses.php?action=delete_upload&id=<?= $f['id'] ?>&course_id=<?= $courseId ?>&tab=riwayat" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus file ini dari riwayat?')">Hapus</a>
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
        <form method="POST" action="<?= BASE_URL ?>/proses.php">
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
        <form method="POST" action="<?= BASE_URL ?>/proses.php">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_course_task">
                <input type="hidden" name="id" id="edit_course_task_id">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <div class="form-group">
                    <label for="edit_course_task_judul">Nama / Judul Tugas</label>
                    <input type="text" id="edit_course_task_judul" name="judul" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_course_task_deskripsi">Deskripsi &amp; Instruksi</label>
                    <textarea id="edit_course_task_deskripsi" name="deskripsi" class="form-control"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_course_task_deadline">Batas Waktu</label>
                        <input type="datetime-local" id="edit_course_task_deadline" name="deadline" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_course_task_tempat">Tempat Pengumpulan</label>
                        <input type="text" id="edit_course_task_tempat" name="tempat" class="form-control">
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
        <form method="POST" action="<?= BASE_URL ?>/upload.php" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <input type="hidden" name="tipe" value="materi">
                <div class="form-group">
                    <label for="materi_judul">Nama / Judul Materi</label>
                    <input type="text" id="materi_judul" name="judul" class="form-control" placeholder="Contoh: Slide Pertemuan 3 - Array dan Pointer" required>
                </div>
                <div class="form-group">
                    <label for="materi_file">Pilih File</label>
                    <div class="file-upload-zone" id="materiUploadZone">
                        <input type="file" id="materi_file" name="file" class="file-upload-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.txt" required>
                        <div class="file-upload-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p>Klik atau drag &amp; drop file di sini</p>
                            <span>PDF, DOC, PPT, XLS, JPG, PNG, ZIP — maks. 15 MB</span>
                        </div>
                        <div class="file-upload-selected" id="materiSelected" style="display:none;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('uploadMateriModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Upload Materi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Upload Soal/Jawaban (dinamis) -->
<div class="modal-overlay" id="uploadFileModal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="uploadFileModalTitle">Upload File</h2>
            <button class="modal-close" onclick="closeModal('uploadFileModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/upload.php" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <input type="hidden" name="tipe" id="uploadFileTipe" value="soal">
                <div class="form-group">
                    <label for="file_judul">Nama / Judul File</label>
                    <input type="text" id="file_judul" name="judul" class="form-control" placeholder="Contoh: Soal UTS Pemrograman Web 2024" required>
                </div>
                <div class="form-group">
                    <label for="upload_file">Pilih File</label>
                    <div class="file-upload-zone" id="fileUploadZone">
                        <input type="file" id="upload_file" name="file" class="file-upload-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.txt" required>
                        <div class="file-upload-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p>Klik atau drag &amp; drop file di sini</p>
                            <span>PDF, DOC, PPT, XLS, JPG, PNG, ZIP — maks. 15 MB</span>
                        </div>
                        <div class="file-upload-selected" id="fileSelected" style="display:none;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('uploadFileModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Upload File</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCourseTask(id, courseId, judul, deskripsi, deadline, tempat, status) {
    document.getElementById('edit_course_task_id').value = id;
    document.getElementById('edit_course_task_judul').value = judul;
    document.getElementById('edit_course_task_deskripsi').value = deskripsi;
    if (deadline) {
        document.getElementById('edit_course_task_deadline').value = deadline.replace(' ', 'T').substring(0, 16);
    }
    document.getElementById('edit_course_task_tempat').value = tempat;
    document.getElementById('edit_course_task_status').value = status;
    openModal('editCourseTaskModal');
}

function openUploadModal(tipe) {
    document.getElementById('uploadFileTipe').value = tipe;
    const titles = { soal: 'Upload Soal Tugas / Kuis', jawaban: 'Upload Hasil Pengerjaan / Jawaban' };
    document.getElementById('uploadFileModalTitle').textContent = titles[tipe] || 'Upload File';
    openModal('uploadFileModal');
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
