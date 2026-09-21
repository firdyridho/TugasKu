<?php
$pageTitle = 'Manajemen Organisasi';
require_once __DIR__ . '/../config.php';
requireLogin();

$userId = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM organisations WHERE id = $id AND user_id = $userId");
    flash('success', 'Organisasi berhasil dihapus.');
    header('Location: ' . BASE_URL . '/organisasi/');
    exit;
}

$organisations = $conn->query("
    SELECT o.*, 
        (SELECT COUNT(*) FROM org_tasks WHERE org_id = o.id AND status != 'selesai') as pending_tasks,
        (SELECT COUNT(*) FROM org_tasks WHERE org_id = o.id) as total_tasks
    FROM organisations o 
    WHERE o.user_id = $userId 
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Organisasi & UKM</h1>
        <p>Kelola semua organisasi kampus, kepanitiaan, serta tugas dan kegiatannya.</p>
    </div>
    <div class="header-actions">
        <?php if (!empty($organisations)): ?>
            <div class="search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="search-input" placeholder="Cari organisasi..." data-search-target=".data-card" data-search-empty="searchEmptyMsg">
            </div>
        <?php endif; ?>
        <button class="btn btn-primary" onclick="openModal('addOrgModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Organisasi
        </button>
    </div>
</div>

<?php if (empty($organisations)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h3>Belum Ada Organisasi Terdaftar</h3>
        <p>Mulai tambahkan organisasi, UKM, atau kepanitiaan yang Anda ikuti untuk mengelola pembagian tugas secara rapi.</p>
        <button class="btn btn-primary" onclick="openModal('addOrgModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Organisasi Pertama
        </button>
    </div>
<?php else: ?>
    <div id="searchEmptyMsg" style="display: none; text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
        Tidak ada organisasi yang cocok dengan kata kunci pencarian.
    </div>
    <div class="data-grid">
        <?php foreach ($organisations as $org): ?>
            <?php
            $catLabel = ucfirst($org['kategori']);
            $catBadgeClass = 'badge-purple';
            if ($org['kategori'] === 'ormawa') $catBadgeClass = 'badge-blue';
            elseif ($org['kategori'] === 'komunitas') $catBadgeClass = 'badge-green';
            elseif ($org['kategori'] === 'lainnya') $catBadgeClass = 'badge-gray';
            ?>
            <div class="data-card">
                <div class="data-card-header">
                    <span class="badge <?= $catBadgeClass ?>"><?= $catLabel ?></span>
                    <div style="display: flex; gap: 0.25rem;">
                        <button class="btn btn-ghost btn-sm" onclick="editOrg(<?= $org['id'] ?>, '<?= htmlspecialchars(addslashes($org['nama'])) ?>', '<?= htmlspecialchars(addslashes($org['kategori'])) ?>', '<?= htmlspecialchars(addslashes($org['deskripsi'] ?? '')) ?>')" title="Edit Organisasi">
                            Edit
                        </button>
                        <a href="?delete=<?= $org['id'] ?>" class="btn btn-danger-ghost btn-sm" onclick="return confirm('Hapus organisasi ini beserta seluruh tugasnya?')" title="Hapus Organisasi">
                            Hapus
                        </a>
                    </div>
                </div>
                <h3><?= htmlspecialchars($org['nama']) ?></h3>
                <p><?= htmlspecialchars($org['deskripsi'] ?: 'Tidak ada deskripsi.') ?></p>
                <div class="data-card-meta">
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <?= $org['pending_tasks'] ?> tugas aktif (<?= $org['total_tasks'] ?> total)
                    </span>
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                        <?= date('d M Y', strtotime($org['created_at'])) ?>
                    </span>
                </div>
                <div class="data-card-actions">
                    <a href="<?= BASE_URL ?>/organisasi/detail.php?id=<?= $org['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
                        Buka Daftar Tugas
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Tambah Organisasi -->
<div class="modal-overlay" id="addOrgModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Buat Organisasi Baru</h2>
            <button class="modal-close" onclick="closeModal('addOrgModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/proses.php">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_org">
                <div class="form-group">
                    <label for="nama">Nama Organisasi / UKM</label>
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Contoh: BEM Fakultas Ilmu Komputer" required>
                </div>
                <div class="form-group">
                    <label for="kategori">Kategori Organisasi</label>
                    <select id="kategori" name="kategori" class="form-control" required>
                        <option value="ukm">UKM (Unit Kegiatan Mahasiswa)</option>
                        <option value="ormawa">Ormawa (Organisasi Mahasiswa)</option>
                        <option value="komunitas">Komunitas / Club</option>
                        <option value="lainnya">Lainnya / Kepanitiaan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Singkat</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" placeholder="Tuliskan divisi, peran, atau tujuan organisasi ini..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addOrgModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Organisasi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Organisasi -->
<div class="modal-overlay" id="editOrgModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Organisasi</h2>
            <button class="modal-close" onclick="closeModal('editOrgModal')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/proses.php">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_org">
                <input type="hidden" name="id" id="edit_org_id">
                <div class="form-group">
                    <label for="edit_org_nama">Nama Organisasi / UKM</label>
                    <input type="text" id="edit_org_nama" name="nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_org_kategori">Kategori Organisasi</label>
                    <select id="edit_org_kategori" name="kategori" class="form-control" required>
                        <option value="ukm">UKM (Unit Kegiatan Mahasiswa)</option>
                        <option value="ormawa">Ormawa (Organisasi Mahasiswa)</option>
                        <option value="komunitas">Komunitas / Club</option>
                        <option value="lainnya">Lainnya / Kepanitiaan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_org_deskripsi">Deskripsi Singkat</label>
                    <textarea id="edit_org_deskripsi" name="deskripsi" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editOrgModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
