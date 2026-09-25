<?php
$pageTitle = 'Detail Tugas Organisasi';
require_once __DIR__ . '/../config.php';
requireLogin();

$userId = $_SESSION['user_id'];
$orgId = (int)($_GET['id'] ?? 0);

$org = $conn->query("SELECT * FROM organisations WHERE id = $orgId AND user_id = $userId")->fetch_assoc();
if (!$org) {
    flash('error', 'Organisasi tidak ditemukan.');
    redirect('/organisasi/');
}

// Handle Delete Task
if (isset($_GET['delete_task'])) {
    $taskId = (int)$_GET['delete_task'];
    $conn->query("DELETE FROM org_tasks WHERE id = $taskId AND user_id = $userId");
    flash('success', 'Tugas organisasi berhasil dihapus.');
    header('Location: ' . BASE_URL . '/organisasi/detail?id=' . $orgId);
    exit;
}

// Filter Status
$filter = $_GET['filter'] ?? 'all';
$whereFilter = '';
if ($filter === 'active') {
    $whereFilter = "AND ot.status != 'selesai'";
} elseif ($filter === 'belum') {
    $whereFilter = "AND ot.status = 'belum'";
} elseif ($filter === 'progres') {
    $whereFilter = "AND ot.status = 'progres'";
} elseif ($filter === 'done') {
    $whereFilter = "AND ot.status = 'selesai'";
}

$tasks = $conn->query("
    SELECT ot.* FROM org_tasks ot 
    WHERE ot.org_id = $orgId AND ot.user_id = $userId $whereFilter 
    ORDER BY 
        CASE ot.status WHEN 'progres' THEN 0 WHEN 'belum' THEN 1 ELSE 2 END,
        ot.deadline ASC
")->fetch_all(MYSQLI_ASSOC);

// Counts per status
$counts = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END) as belum,
        SUM(CASE WHEN status = 'progres' THEN 1 ELSE 0 END) as progres,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
    FROM org_tasks 
    WHERE org_id = $orgId AND user_id = $userId
")->fetch_assoc();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>/organisasi/" class="btn btn-ghost btn-sm" style="margin-bottom: 0.5rem; padding-left: 0; color: var(--text-muted);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali ke Daftar Organisasi
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h1><?= htmlspecialchars($org['nama']) ?></h1>
            <span class="badge badge-purple"><?= ucfirst($org['kategori']) ?></span>
        </div>
        <p><?= htmlspecialchars($org['deskripsi'] ?: 'Tidak ada deskripsi tambahan.') ?></p>
    </div>
    <div class="header-actions">
        <button class="btn btn-primary" onclick="openModal('addTaskModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tugas
        </button>
    </div>
</div>

<div class="filter-bar">
    <div class="tabs">
        <a href="?id=<?= $orgId ?>&filter=all" class="tab <?= $filter === 'all' ? 'active' : '' ?>">
            Semua (<?= (int)$counts['total'] ?>)
        </a>
        <a href="?id=<?= $orgId ?>&filter=belum" class="tab <?= $filter === 'belum' ? 'active' : '' ?>">
            Belum Mulai (<?= (int)$counts['belum'] ?>)
        </a>
        <a href="?id=<?= $orgId ?>&filter=progres" class="tab <?= $filter === 'progres' ? 'active' : '' ?>">
            Sedang Dikerjakan (<?= (int)$counts['progres'] ?>)
        </a>
        <a href="?id=<?= $orgId ?>&filter=done" class="tab <?= $filter === 'done' ? 'active' : '' ?>">
            Selesai (<?= (int)$counts['selesai'] ?>)
        </a>
    </div>
    <div class="search-box">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" class="search-input" placeholder="Cari tugas organisasi..." data-search-target=".task-item" data-search-empty="searchTasksEmpty">
    </div>
</div>

<div id="searchTasksEmpty" style="display: none; text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
    Tidak ada tugas yang sesuai dengan pencarian.
</div>

<?php if (empty($tasks)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <h3>Belum Ada Tugas di Kategori Ini</h3>
        <p>Tambahkan tugas atau agenda kepengurusan untuk organisasi ini agar seluruh tenggat waktu terkontrol.</p>
        <button class="btn btn-primary" onclick="openModal('addTaskModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tugas Sekarang
        </button>
    </div>
<?php else: ?>
    <div class="task-list">
        <?php foreach ($tasks as $task): ?>
            <?php
            $isDone = ($task['status'] === 'selesai');
            $isProgres = ($task['status'] === 'progres');
            
            // Perhitungan Urgensi Deadline
            $urgencyClass = 'urgency-safe';
            $urgencyLabel = '';
            if ($task['deadline']) {
                $deadlineTs = strtotime($task['deadline']);
                $diff = $deadlineTs - time();
                $diffDays = floor($diff / 86400);

                if ($isDone) {
                    $urgencyClass = 'urgency-safe';
                    $urgencyLabel = 'Selesai';
                } elseif ($diff < 0) {
                    $urgencyClass = 'urgency-overdue';
                    $urgencyLabel = 'Terlewat';
                } elseif ($diffDays == 0) {
                    $urgencyClass = 'urgency-urgent';
                    $urgencyLabel = 'Hari Ini (' . date('H:i', $deadlineTs) . ')';
                } elseif ($diffDays == 1) {
                    $urgencyClass = 'urgency-soon';
                    $urgencyLabel = 'Besok (' . date('H:i', $deadlineTs) . ')';
                } else {
                    $urgencyClass = 'urgency-safe';
                    $urgencyLabel = $diffDays . ' hari lagi';
                }
            }

            // Status Badge & Tombol Siklus
            $statusLabel = 'Belum Mulai';
            $statusClass = 'status-belum';
            $nextStatus = 'progres';
            if ($task['status'] === 'progres') {
                $statusLabel = 'Sedang Dikerjakan';
                $statusClass = 'status-progres';
                $nextStatus = 'selesai';
            } elseif ($task['status'] === 'selesai') {
                $statusLabel = 'Selesai';
                $statusClass = 'status-selesai';
                $nextStatus = 'belum';
            }
            ?>
            <div class="task-item">
                <!-- Status Quick Cycle -->
                <a href="<?= BASE_URL ?>/proses?action=update_status&type=org&id=<?= $task['id'] ?>&status=<?= $nextStatus ?>&return=<?= urlencode('/organisasi/detail?id=' . $orgId) ?>" 
                   class="status-pill <?= $statusClass ?>" 
                   title="Klik untuk ubah status ke: <?= ucfirst($nextStatus) ?>">
                    <?php if ($task['status'] === 'selesai'): ?>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    <?php elseif ($task['status'] === 'progres'): ?>
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #0284c7; display: inline-block;"></span>
                    <?php else: ?>
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #94a3b8; display: inline-block;"></span>
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
                        <button class="btn btn-ghost btn-sm" onclick="editOrgTask(<?= $task['id'] ?>, <?= $orgId ?>, '<?= htmlspecialchars(addslashes($task['judul'])) ?>', '<?= htmlspecialchars(addslashes($task['deskripsi'] ?? '')) ?>', '<?= $task['deadline'] ?? '' ?>', '<?= htmlspecialchars(addslashes($task['tempat_pengumpulan'] ?? '')) ?>', '<?= $task['status'] ?>')" title="Edit Tugas">
                            Edit
                        </button>
                        <a href="?id=<?= $orgId ?>&delete_task=<?= $task['id'] ?>" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus tugas ini?')" title="Hapus Tugas">
                            Hapus
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Tambah Tugas -->
<div class="modal-overlay" id="addTaskModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Tambah Tugas Organisasi</h2>
            <button class="modal-close" onclick="closeModal('addTaskModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/proses">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_org_task">
                <input type="hidden" name="org_id" value="<?= $orgId ?>">
                <div class="form-group">
                    <label for="judul">Nama / Judul Tugas</label>
                    <input type="text" id="judul" name="judul" class="form-control" placeholder="Contoh: Buat Proposal Kegiatan Dies Natalis" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi & Rincian</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" placeholder="Rincian yang harus dikerjakan, pembagian tim, dsb..."></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="deadline">Batas Waktu (Deadline)</label>
                        <input type="datetime-local" id="deadline" name="deadline" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="tempat">Tempat / Tautan Pengumpulan</label>
                        <input type="text" id="tempat" name="tempat" class="form-control" placeholder="Contoh: Google Drive, Ruang BEM">
                    </div>
                </div>
                <div class="form-group">
                    <label for="status">Status Pengerjaan Awal</label>
                    <select id="status" name="status" class="form-control">
                        <option value="belum">Belum Mulai</option>
                        <option value="progres">Sedang Dikerjakan</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addTaskModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Tugas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Tugas -->
<div class="modal-overlay" id="editTaskModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Tugas Organisasi</h2>
            <button class="modal-close" onclick="closeModal('editTaskModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/proses">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_org_task">
                <input type="hidden" name="id" id="edit_task_id">
                <input type="hidden" name="org_id" id="edit_task_org_id" value="<?= $orgId ?>">
                <div class="form-group">
                    <label for="edit_task_judul">Nama / Judul Tugas</label>
                    <input type="text" id="edit_task_judul" name="judul" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_task_deskripsi">Deskripsi & Rincian</label>
                    <textarea id="edit_task_deskripsi" name="deskripsi" class="form-control"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_task_deadline">Batas Waktu (Deadline)</label>
                        <input type="datetime-local" id="edit_task_deadline" name="deadline" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_task_tempat">Tempat / Tautan Pengumpulan</label>
                        <input type="text" id="edit_task_tempat" name="tempat" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_task_status">Status Pengerjaan</label>
                    <select id="edit_task_status" name="status" class="form-control">
                        <option value="belum">Belum Mulai</option>
                        <option value="progres">Sedang Dikerjakan</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editTaskModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
