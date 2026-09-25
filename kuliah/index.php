<?php
$pageTitle = 'Jadwal Mata Kuliah';
require_once __DIR__ . '/../config.php';
requireLogin();

$userId = $_SESSION['user_id'];
$viewMode = $_GET['view'] ?? 'card'; // 'card' or 'timetable'

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM courses WHERE id = $id AND user_id = $userId");
    flash('success', 'Mata kuliah berhasil dihapus.');
    header('Location: ' . BASE_URL . '/kuliah/');
    exit;
}

$courses = $conn->query("
    SELECT c.*, 
        (SELECT COUNT(*) FROM course_tasks WHERE course_id = c.id AND status != 'selesai') as pending_tasks,
        (SELECT COUNT(*) FROM course_tasks WHERE course_id = c.id) as total_tasks
    FROM courses c 
    WHERE c.user_id = $userId 
    ORDER BY FIELD(c.hari, 'senin','selasa','rabu','kamis','jumat','sabtu','minggu'), c.jam_mulai ASC
")->fetch_all(MYSQLI_ASSOC);

$hariOrder = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
$hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'];
$grouped = [];
foreach ($courses as $c) {
    $grouped[$c['hari']][] = $c;
}

// Auto-color palette for courses in timetable
$palette = ['#2563eb','#7c3aed','#059669','#d97706','#dc2626','#0891b2','#be185d','#65a30d'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Jadwal Mata Kuliah</h1>
        <p>Atur jadwal perkuliahan, dosen, kelas, ruangan, serta tugas akademik semester ini.</p>
    </div>
    <div class="header-actions">
        <!-- View Toggle -->
        <div class="view-toggle-group">
            <a href="?view=card" class="view-toggle-btn <?= $viewMode === 'card' ? 'active' : '' ?>" title="Tampilan Kartu">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            </a>
            <a href="?view=timetable" class="view-toggle-btn <?= $viewMode === 'timetable' ? 'active' : '' ?>" title="Tampilan Jadwal Mingguan">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </a>
        </div>
        <?php if (!empty($courses)): ?>
            <div class="search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="search-input" placeholder="Cari mata kuliah atau dosen..." data-search-target=".data-card" data-search-empty="searchCourseEmpty">
            </div>
        <?php endif; ?>
        <button class="btn btn-primary" onclick="openModal('addCourseModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Mata Kuliah
        </button>
    </div>
</div>

<?php if (empty($courses)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <h3>Belum Ada Jadwal Mata Kuliah</h3>
        <p>Tambahkan mata kuliah semester ini agar jadwal harian dan tugas dari dosen tercatat dengan jelas.</p>
        <button class="btn btn-primary" onclick="openModal('addCourseModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Mata Kuliah Pertama
        </button>
    </div>

<?php elseif ($viewMode === 'timetable'): ?>
    <!-- ===== TIMETABLE VIEW ===== -->
    <div class="timetable-wrapper">
        <div class="tt-rows">
            <?php foreach ($hariOrder as $hari):
                $isToday = strtolower(date('l')) === match($hari) {
                    'senin' => 'monday', 'selasa' => 'tuesday', 'rabu' => 'wednesday',
                    'kamis' => 'thursday', 'jumat' => 'friday', 'sabtu' => 'saturday', 'minggu' => 'sunday', default => ''
                };
                $hasCourses = !empty($grouped[$hari]);
            ?>
                <?php if ($hasCourses): ?>
                    <div class="tt-row <?= $isToday ? 'tt-row-today' : '' ?>">
                        <div class="tt-row-label">
                            <span class="tt-row-day"><?= $hariLabel[$hari] ?></span>
                            <?php if ($isToday): ?><span class="tt-row-today-badge">Hari ini</span><?php endif; ?>
                        </div>
                        <div class="tt-row-courses">
                            <?php foreach ($grouped[$hari] as $cIdx => $course): 
                                $color = $palette[$cIdx % count($palette)];
                            ?>
                                <a href="<?= BASE_URL ?>/kuliah/detail?id=<?= $course['id'] ?>" class="tt-course-block" style="--tt-color: <?= $color ?>;">
                                    <div class="tt-course-time">
                                        <?= substr($course['jam_mulai'], 0, 5) ?> – <?= substr($course['jam_selesai'], 0, 5) ?>
                                    </div>
                                    <div class="tt-course-name"><?= htmlspecialchars($course['nama_mk']) ?></div>
                                    <div class="tt-course-meta">
                                        <?php if ($course['dosen']): ?><span><?= htmlspecialchars($course['dosen']) ?></span><?php endif; ?>
                                        <?php if ($course['ruang']): ?><span>Ruang <?= htmlspecialchars($course['ruang']) ?></span><?php endif; ?>
                                        <?php if ($course['kelas']): ?><span>Kelas <?= htmlspecialchars($course['kelas']) ?></span><?php endif; ?>
                                    </div>
                                    <?php if ($course['pending_tasks'] > 0): ?>
                                        <div class="tt-course-badge"><?= $course['pending_tasks'] ?> tugas aktif</div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tt-row" style="opacity: 0.5;">
                        <div class="tt-row-label">
                            <span class="tt-row-day" style="color: var(--text-muted);"><?= $hariLabel[$hari] ?></span>
                        </div>
                        <div class="tt-row-courses" style="align-items: center;">
                            <span style="font-size: 0.82rem; color: var(--text-muted); font-style: italic;">Tidak ada jadwal kuliah</span>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <p style="margin-top: 0.85rem; font-size: 0.82rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.4rem;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Klik pada blok mata kuliah untuk melihat detail & tugas.
    </p>

<?php else: ?>
    <!-- ===== CARD VIEW ===== -->
    <div id="searchCourseEmpty" style="display: none; text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
        Tidak ada mata kuliah yang cocok dengan kata kunci pencarian.
    </div>
    <?php foreach ($hariOrder as $hari): ?>
        <?php if (!empty($grouped[$hari])): ?>
            <div style="margin-bottom: 2.25rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--accent);"></div>
                    <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.04em;">
                        <?= ucfirst($hari) ?>
                    </h2>
                    <span class="badge badge-gray" style="font-size: 0.72rem;"><?= count($grouped[$hari]) ?> kelas</span>
                </div>
                <div class="data-grid">
                    <?php foreach ($grouped[$hari] as $course): ?>
                        <div class="data-card">
                            <div class="data-card-header">
                                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                    <span class="badge badge-blue">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <?= substr($course['jam_mulai'], 0, 5) ?> - <?= substr($course['jam_selesai'], 0, 5) ?> WIB
                                    </span>
                                    <?php if ($course['kelas']): ?>
                                        <span class="badge badge-purple">Kelas <?= htmlspecialchars($course['kelas']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; gap: 0.25rem;">
                                    <button class="btn btn-ghost btn-sm" onclick="editCourse(<?= $course['id'] ?>, '<?= htmlspecialchars(addslashes($course['nama_mk'])) ?>', '<?= htmlspecialchars(addslashes($course['dosen'] ?? '')) ?>', '<?= $course['hari'] ?>', '<?= $course['jam_mulai'] ?>', '<?= $course['jam_selesai'] ?>', '<?= htmlspecialchars(addslashes($course['ruang'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($course['kelas'] ?? '')) ?>')" title="Edit Mata Kuliah">
                                        Edit
                                    </button>
                                    <a href="?delete=<?= $course['id'] ?>" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus mata kuliah ini beserta seluruh tugasnya?')" title="Hapus Kuliah">
                                        Hapus
                                    </a>
                                </div>
                            </div>
                            <h3><?= htmlspecialchars($course['nama_mk']) ?></h3>
                            <p style="color: var(--text-secondary); margin-bottom: 0.75rem;">
                                <?= htmlspecialchars($course['dosen'] ?: 'Dosen belum ditentukan') ?>
                            </p>
                            <div class="data-card-meta">
                                <span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    Ruang <?= htmlspecialchars($course['ruang'] ?: 'TBA') ?>
                                </span>
                                <?php if ($course['kelas']): ?>
                                <span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    Kelas <?= htmlspecialchars($course['kelas']) ?>
                                </span>
                                <?php endif; ?>
                                <span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    <?= $course['pending_tasks'] ?> tugas aktif (<?= $course['total_tasks'] ?> total)
                                </span>
                            </div>
                            <div class="data-card-actions">
                                <a href="<?= BASE_URL ?>/kuliah/detail?id=<?= $course['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
                                    Kelola Tugas & Materi
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Modal Tambah Mata Kuliah -->
<div class="modal-overlay" id="addCourseModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Tambah Mata Kuliah</h2>
            <button class="modal-close" onclick="closeModal('addCourseModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/proses">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_course">
                <div class="form-group">
                    <label for="nama_mk">Nama Mata Kuliah</label>
                    <input type="text" id="nama_mk" name="nama_mk" class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                </div>
                <div class="form-group">
                    <label for="dosen">Dosen Pengampu</label>
                    <input type="text" id="dosen" name="dosen" class="form-control" placeholder="Contoh: Dr. Ir. Ahmad, M.T.">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="hari">Hari Perkuliahan</label>
                        <select id="hari" name="hari" class="form-control" required>
                            <option value="senin">Senin</option>
                            <option value="selasa">Selasa</option>
                            <option value="rabu">Rabu</option>
                            <option value="kamis">Kamis</option>
                            <option value="jumat">Jumat</option>
                            <option value="sabtu">Sabtu</option>
                            <option value="minggu">Minggu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="kelas">Kelas</label>
                        <input type="text" id="kelas" name="kelas" class="form-control" placeholder="Contoh: A, B, C, atau IK-2A">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ruang">Nomor Ruangan</label>
                        <input type="text" id="ruang" name="ruang" class="form-control" placeholder="Contoh: Gedung B-204">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="jam_mulai">Jam Mulai</label>
                        <input type="time" id="jam_mulai" name="jam_mulai" class="form-control" required value="08:00">
                    </div>
                    <div class="form-group">
                        <label for="jam_selesai">Jam Selesai</label>
                        <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" required value="09:40">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCourseModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Mata Kuliah</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Mata Kuliah -->
<div class="modal-overlay" id="editCourseModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Mata Kuliah</h2>
            <button class="modal-close" onclick="closeModal('editCourseModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/proses">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_course">
                <input type="hidden" name="id" id="edit_course_id">
                <div class="form-group">
                    <label for="edit_course_nama_mk">Nama Mata Kuliah</label>
                    <input type="text" id="edit_course_nama_mk" name="nama_mk" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_course_dosen">Dosen Pengampu</label>
                    <input type="text" id="edit_course_dosen" name="dosen" class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_course_hari">Hari Perkuliahan</label>
                        <select id="edit_course_hari" name="hari" class="form-control" required>
                            <option value="senin">Senin</option>
                            <option value="selasa">Selasa</option>
                            <option value="rabu">Rabu</option>
                            <option value="kamis">Kamis</option>
                            <option value="jumat">Jumat</option>
                            <option value="sabtu">Sabtu</option>
                            <option value="minggu">Minggu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_course_kelas">Kelas</label>
                        <input type="text" id="edit_course_kelas" name="kelas" class="form-control" placeholder="Contoh: A, B, IK-2A">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_course_ruang">Nomor Ruangan</label>
                    <input type="text" id="edit_course_ruang" name="ruang" class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_course_jam_mulai">Jam Mulai</label>
                        <input type="time" id="edit_course_jam_mulai" name="jam_mulai" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_course_jam_selesai">Jam Selesai</label>
                        <input type="time" id="edit_course_jam_selesai" name="jam_selesai" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editCourseModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCourse(id, nama, dosen, hari, jamMulai, jamSelesai, ruang, kelas) {
    document.getElementById('edit_course_id').value = id;
    document.getElementById('edit_course_nama_mk').value = nama;
    document.getElementById('edit_course_dosen').value = dosen;
    document.getElementById('edit_course_hari').value = hari;
    document.getElementById('edit_course_jam_mulai').value = jamMulai.substring(0,5);
    document.getElementById('edit_course_jam_selesai').value = jamSelesai.substring(0,5);
    document.getElementById('edit_course_ruang').value = ruang;
    document.getElementById('edit_course_kelas').value = kelas || '';
    openModal('editCourseModal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
