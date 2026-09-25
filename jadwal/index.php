<?php
$pageTitle = 'Jadwal Mandiri & Kegiatan';
require_once __DIR__ . '/../config.php';
requireLogin();

$userId = $_SESSION['user_id'];

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM schedules WHERE id = $id AND user_id = $userId");
    flash('success', 'Jadwal kegiatan berhasil dihapus.');
    header('Location: ' . BASE_URL . '/jadwal/');
    exit;
}

$schedules = $conn->query("
    SELECT * FROM schedules 
    WHERE user_id = $userId 
    ORDER BY FIELD(hari, 'senin','selasa','rabu','kamis','jumat','sabtu','minggu'), jam_mulai ASC
")->fetch_all(MYSQLI_ASSOC);

$hariOrder = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
$grouped = [];
foreach ($schedules as $s) {
    $grouped[$s['hari']][] = $s;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Jadwal Mandiri & Kegiatan</h1>
        <p>Atur jadwal belajar pribadi, kegiatan non-akademik, olahraga, atau rutinitas harian Anda.</p>
    </div>
    <div class="header-actions">
        <?php if (!empty($schedules)): ?>
            <div class="search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="search-input" placeholder="Cari kegiatan mandiri..." data-search-target=".data-card" data-search-empty="searchScheduleEmpty">
            </div>
        <?php endif; ?>
        <button class="btn btn-primary" onclick="openModal('addScheduleModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Jadwal
        </button>
    </div>
</div>

<?php if (empty($schedules)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h3>Belum Ada Jadwal Mandiri</h3>
        <p>Buat jadwal kegiatan pribadi seperti jam belajar mandiri, diskusi kelompok, olahraga, atau kegiatan lainnya.</p>
        <button class="btn btn-primary" onclick="openModal('addScheduleModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Jadwal Pertama
        </button>
    </div>
<?php else: ?>
    <div id="searchScheduleEmpty" style="display: none; text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
        Tidak ada jadwal yang sesuai dengan kata kunci pencarian.
    </div>
    <?php foreach ($hariOrder as $hari): ?>
        <?php if (!empty($grouped[$hari])): ?>
            <div style="margin-bottom: 2.25rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--success);"></div>
                    <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.04em;">
                        <?= ucfirst($hari) ?>
                    </h2>
                    <span class="badge badge-gray" style="font-size: 0.72rem;"><?= count($grouped[$hari]) ?> aktivitas</span>
                </div>
                <div class="data-grid">
                    <?php foreach ($grouped[$hari] as $s): ?>
                        <?php
                        $color = htmlspecialchars($s['warna'] ?: '#6366f1');
                        ?>
                        <div class="data-card">
                            <div class="data-card-header">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 10px; height: 10px; border-radius: 3px; background: <?= $color ?>;"></div>
                                    <span class="badge" style="background: <?= $color ?>18; color: <?= $color ?>; border: 1px solid <?= $color ?>30;">
                                        <?= ucfirst($s['tipe']) ?>
                                    </span>
                                </div>
                                <div style="display: flex; gap: 0.25rem;">
                                    <button class="btn btn-ghost btn-sm" data-schedule="<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>" onclick="openEditScheduleModal(this)" title="Edit Jadwal">
                                        Edit
                                    </button>
                                    <a href="?delete=<?= $s['id'] ?>" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus jadwal mandiri ini?')" title="Hapus Jadwal">
                                        Hapus
                                    </a>
                                </div>
                            </div>
                            <h3><?= htmlspecialchars($s['judul']) ?></h3>
                            <p><?= htmlspecialchars($s['deskripsi'] ?: 'Tidak ada catatan.') ?></p>
                            <div class="data-card-meta">
                                <span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <?= substr($s['jam_mulai'], 0, 5) ?> - <?= substr($s['jam_selesai'], 0, 5) ?> WIB
                                </span>
                                <?php if ($s['tempat']): ?>
                                    <span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <?= htmlspecialchars($s['tempat']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Modal Tambah Jadwal -->
<div class="modal-overlay" id="addScheduleModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Tambah Jadwal Mandiri</h2>
            <button class="modal-close" onclick="closeModal('addScheduleModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/proses">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_schedule">
                <div class="form-group">
                    <label for="judul">Nama Kegiatan / Agenda</label>
                    <input type="text" id="judul" name="judul" class="form-control" placeholder="Contoh: Belajar Pemrograman Web Lanjutan" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Singkat</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" placeholder="Target belajar, modul yang dipelajari, dsb..."></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="hari">Hari Kegiatan</label>
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
                        <label for="tipe">Jenis Kegiatan</label>
                        <select id="tipe" name="tipe" class="form-control">
                            <option value="mandiri">Belajar Mandiri</option>
                            <option value="kegiatan">Kegiatan / Hobi</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="jam_mulai">Jam Mulai</label>
                        <input type="time" id="jam_mulai" name="jam_mulai" class="form-control" required value="08:00">
                    </div>
                    <div class="form-group">
                        <label for="jam_selesai">Jam Selesai</label>
                        <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" required value="09:00">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="tempat">Lokasi / Tempat</label>
                        <input type="text" id="tempat" name="tempat" class="form-control" placeholder="Contoh: Perpustakaan Pusat, Kamar">
                    </div>
                    <div class="form-group">
                        <label for="warna">Warna Penanda</label>
                        <input type="color" id="warna" name="warna" class="form-control" value="#6366f1" style="height: 42px; padding: 4px; cursor: pointer;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addScheduleModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Jadwal -->
<div class="modal-overlay" id="editScheduleModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Jadwal Mandiri</h2>
            <button class="modal-close" onclick="closeModal('editScheduleModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/proses">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_schedule">
                <input type="hidden" name="id" id="edit_schedule_id">
                <div class="form-group">
                    <label for="edit_schedule_judul">Nama Kegiatan / Agenda</label>
                    <input type="text" id="edit_schedule_judul" name="judul" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_schedule_deskripsi">Deskripsi Singkat</label>
                    <textarea id="edit_schedule_deskripsi" name="deskripsi" class="form-control"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_schedule_hari">Hari Kegiatan</label>
                        <select id="edit_schedule_hari" name="hari" class="form-control" required>
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
                        <label for="edit_schedule_tipe">Jenis Kegiatan</label>
                        <select id="edit_schedule_tipe" name="tipe" class="form-control">
                            <option value="mandiri">Belajar Mandiri</option>
                            <option value="kegiatan">Kegiatan / Hobi</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_schedule_jam_mulai">Jam Mulai</label>
                        <input type="time" id="edit_schedule_jam_mulai" name="jam_mulai" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_schedule_jam_selesai">Jam Selesai</label>
                        <input type="time" id="edit_schedule_jam_selesai" name="jam_selesai" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_schedule_tempat">Lokasi / Tempat</label>
                        <input type="text" id="edit_schedule_tempat" name="tempat" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_schedule_warna">Warna Penanda</label>
                        <input type="color" id="edit_schedule_warna" name="warna" class="form-control" style="height: 42px; padding: 4px; cursor: pointer;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editScheduleModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
