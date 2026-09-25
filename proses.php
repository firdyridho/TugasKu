<?php
require_once __DIR__ . '/config.php';
requireLogin();

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? ($_GET['action'] ?? '');

switch ($action) {
    case 'add_org':
        $nama = sanitize($_POST['nama'] ?? '');
        $kategori = sanitize($_POST['kategori'] ?? 'ukm');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $stmt = $conn->prepare('INSERT INTO organisations (nama, kategori, deskripsi, user_id) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sssi', $nama, $kategori, $deskripsi, $userId);
        if ($stmt->execute()) {
            flash('success', 'Organisasi berhasil dibuat.');
        } else {
            flash('error', 'Gagal membuat organisasi.');
        }
        redirect('/organisasi/');
        break;

    case 'edit_org':
        $id = (int)($_POST['id'] ?? 0);
        $nama = sanitize($_POST['nama'] ?? '');
        $kategori = sanitize($_POST['kategori'] ?? 'ukm');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $stmt = $conn->prepare('UPDATE organisations SET nama = ?, kategori = ?, deskripsi = ? WHERE id = ? AND user_id = ?');
        $stmt->bind_param('sssii', $nama, $kategori, $deskripsi, $id, $userId);
        if ($stmt->execute()) {
            flash('success', 'Organisasi berhasil diperbarui.');
        } else {
            flash('error', 'Gagal memperbarui organisasi.');
        }
        redirect('/organisasi/');
        break;

    case 'add_org_task':
        $orgId = (int)($_POST['org_id'] ?? 0);
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
        $tempat = sanitize($_POST['tempat'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['belum', 'progres', 'selesai']) ? $_POST['status'] : 'belum';
        
        $stmt = $conn->prepare('INSERT INTO org_tasks (org_id, judul, deskripsi, deadline, tempat_pengumpulan, status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssssi', $orgId, $judul, $deskripsi, $deadline, $tempat, $status, $userId);
        if ($stmt->execute()) {
            flash('success', 'Tugas organisasi berhasil ditambahkan.');
        } else {
            flash('error', 'Gagal menambahkan tugas.');
        }
        redirect('/organisasi/detail?id=' . $orgId);
        break;

    case 'edit_org_task':
        $taskId = (int)($_POST['id'] ?? 0);
        $orgId = (int)($_POST['org_id'] ?? 0);
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
        $tempat = sanitize($_POST['tempat'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['belum', 'progres', 'selesai']) ? $_POST['status'] : 'belum';

        $stmt = $conn->prepare('UPDATE org_tasks SET judul = ?, deskripsi = ?, deadline = ?, tempat_pengumpulan = ?, status = ? WHERE id = ? AND user_id = ?');
        $stmt->bind_param('sssssii', $judul, $deskripsi, $deadline, $tempat, $status, $taskId, $userId);
        if ($stmt->execute()) {
            flash('success', 'Tugas organisasi berhasil diperbarui.');
        } else {
            flash('error', 'Gagal memperbarui tugas.');
        }
        redirect('/organisasi/detail?id=' . $orgId);
        break;

    case 'add_course':
        $namaMk = sanitize($_POST['nama_mk'] ?? '');
        $dosen = sanitize($_POST['dosen'] ?? '');
        $hari = sanitize($_POST['hari'] ?? 'senin');
        $jamMulai = sanitize($_POST['jam_mulai'] ?? '08:00');
        $jamSelesai = sanitize($_POST['jam_selesai'] ?? '09:40');
        $ruang = sanitize($_POST['ruang'] ?? '');
        $kelas = sanitize($_POST['kelas'] ?? '');
        
        $stmt = $conn->prepare('INSERT INTO courses (nama_mk, dosen, hari, jam_mulai, jam_selesai, ruang, kelas, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssssi', $namaMk, $dosen, $hari, $jamMulai, $jamSelesai, $ruang, $kelas, $userId);
        if ($stmt->execute()) {
            flash('success', 'Mata kuliah berhasil ditambahkan.');
        } else {
            flash('error', 'Gagal menambahkan mata kuliah.');
        }
        redirect('/kuliah/');
        break;

    case 'edit_course':
        $id = (int)($_POST['id'] ?? 0);
        $namaMk = sanitize($_POST['nama_mk'] ?? '');
        $dosen = sanitize($_POST['dosen'] ?? '');
        $hari = sanitize($_POST['hari'] ?? 'senin');
        $jamMulai = sanitize($_POST['jam_mulai'] ?? '08:00');
        $jamSelesai = sanitize($_POST['jam_selesai'] ?? '09:40');
        $ruang = sanitize($_POST['ruang'] ?? '');
        $kelas = sanitize($_POST['kelas'] ?? '');

        $stmt = $conn->prepare('UPDATE courses SET nama_mk = ?, dosen = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, ruang = ?, kelas = ? WHERE id = ? AND user_id = ?');
        $stmt->bind_param('sssssssii', $namaMk, $dosen, $hari, $jamMulai, $jamSelesai, $ruang, $kelas, $id, $userId);
        if ($stmt->execute()) {
            flash('success', 'Mata kuliah berhasil diperbarui.');
        } else {
            flash('error', 'Gagal memperbarui mata kuliah.');
        }
        redirect('/kuliah/');
        break;

    case 'delete_upload':
        $uploadId = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
        $courseId = (int)($_GET['course_id'] ?? ($_POST['course_id'] ?? 0));
        $tab = sanitize($_GET['tab'] ?? 'materi');

        $upload = $conn->query("SELECT * FROM uploads WHERE id = $uploadId AND user_id = $userId")->fetch_assoc();
        if ($upload) {
            $filePath = __DIR__ . '/uploads/' . $upload['path_file'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $conn->query("DELETE FROM uploads WHERE id = $uploadId AND user_id = $userId");
            flash('success', 'File berhasil dihapus.');
        } else {
            flash('error', 'File tidak ditemukan atau akses ditolak.');
        }
        redirect('/kuliah/detail?id=' . $courseId . '&tab=' . $tab);
        break;

    case 'add_course_task':
        $courseId = (int)($_POST['course_id'] ?? 0);
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
        $tempat = sanitize($_POST['tempat'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['belum', 'progres', 'selesai']) ? $_POST['status'] : 'belum';

        $stmt = $conn->prepare('INSERT INTO course_tasks (course_id, judul, deskripsi, deadline, tempat_pengumpulan, status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssssi', $courseId, $judul, $deskripsi, $deadline, $tempat, $status, $userId);
        if ($stmt->execute()) {
            flash('success', 'Tugas kuliah berhasil ditambahkan.');
        } else {
            flash('error', 'Gagal menambahkan tugas kuliah.');
        }
        redirect('/kuliah/detail?id=' . $courseId);
        break;

    case 'edit_course_task':
        $taskId = (int)($_POST['id'] ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
        $tempat = sanitize($_POST['tempat'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['belum', 'progres', 'selesai']) ? $_POST['status'] : 'belum';

        $stmt = $conn->prepare('UPDATE course_tasks SET judul = ?, deskripsi = ?, deadline = ?, tempat_pengumpulan = ?, status = ? WHERE id = ? AND user_id = ?');
        $stmt->bind_param('sssssii', $judul, $deskripsi, $deadline, $tempat, $status, $taskId, $userId);
        if ($stmt->execute()) {
            flash('success', 'Tugas kuliah berhasil diperbarui.');
        } else {
            flash('error', 'Gagal memperbarui tugas kuliah.');
        }
        redirect('/kuliah/detail?id=' . $courseId . '&tab=tugas');
        break;

    case 'add_schedule':
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $hari = sanitize($_POST['hari'] ?? 'senin');
        $jamMulai = sanitize($_POST['jam_mulai'] ?? '08:00');
        $jamSelesai = sanitize($_POST['jam_selesai'] ?? '09:00');
        $tempat = sanitize($_POST['tempat'] ?? '');
        $tipe = sanitize($_POST['tipe'] ?? 'mandiri');
        $warna = sanitize($_POST['warna'] ?? '#6366f1');

        $stmt = $conn->prepare('INSERT INTO schedules (judul, deskripsi, hari, jam_mulai, jam_selesai, tempat, tipe, warna, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssssssi', $judul, $deskripsi, $hari, $jamMulai, $jamSelesai, $tempat, $tipe, $warna, $userId);
        if ($stmt->execute()) {
            flash('success', 'Jadwal berhasil ditambahkan.');
        } else {
            flash('error', 'Gagal menambahkan jadwal.');
        }
        redirect('/jadwal/');
        break;

    case 'edit_schedule':
        $id = (int)($_POST['id'] ?? 0);
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $hari = sanitize($_POST['hari'] ?? 'senin');
        $jamMulai = sanitize($_POST['jam_mulai'] ?? '08:00');
        $jamSelesai = sanitize($_POST['jam_selesai'] ?? '09:00');
        $tempat = sanitize($_POST['tempat'] ?? '');
        $tipe = sanitize($_POST['tipe'] ?? 'mandiri');
        $warna = sanitize($_POST['warna'] ?? '#6366f1');

        $stmt = $conn->prepare('UPDATE schedules SET judul = ?, deskripsi = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, tempat = ?, tipe = ?, warna = ? WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ssssssssii', $judul, $deskripsi, $hari, $jamMulai, $jamSelesai, $tempat, $tipe, $warna, $id, $userId);
        if ($stmt->execute()) {
            flash('success', 'Jadwal berhasil diperbarui.');
        } else {
            flash('error', 'Gagal memperbarui jadwal.');
        }
        redirect('/jadwal/');
        break;

    case 'update_status':
        $type = $_GET['type'] ?? ($_POST['type'] ?? '');
        $taskId = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
        $newStatus = $_GET['status'] ?? ($_POST['status'] ?? '');
        $returnUrl = $_GET['return'] ?? ($_POST['return'] ?? '/dashboard/');

        if (!in_array($newStatus, ['belum', 'progres', 'selesai'])) {
            $newStatus = 'belum';
        }

        if ($type === 'org') {
            $stmt = $conn->prepare("UPDATE org_tasks SET status = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param('sii', $newStatus, $taskId, $userId);
            $stmt->execute();
            flash('success', 'Status tugas organisasi berhasil diubah.');
        } elseif ($type === 'course') {
            $stmt = $conn->prepare("UPDATE course_tasks SET status = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param('sii', $newStatus, $taskId, $userId);
            $stmt->execute();
            flash('success', 'Status tugas kuliah berhasil diubah.');
        }
        
        redirect($returnUrl);
        break;

    case 'update_profile':
        $nama    = trim($_POST['nama'] ?? '');
        $nim     = trim($_POST['nim'] ?? '');
        $jurusan = trim($_POST['jurusan'] ?? '');
        $angkatan = (int)($_POST['angkatan'] ?? date('Y'));

        if (empty($nama)) {
            flash('error', 'Nama lengkap tidak boleh kosong.');
            redirect('/setting/');
            break;
        }

        $stmt = $conn->prepare('UPDATE users SET nama = ?, nim = ?, jurusan = ?, angkatan = ? WHERE id = ?');
        $stmt->bind_param('sssii', $nama, $nim, $jurusan, $angkatan, $userId);
        if ($stmt->execute()) {
            flash('success', 'Profil berhasil diperbarui.');
        } else {
            flash('error', 'Gagal memperbarui profil.');
        }
        redirect('/setting/');
        break;

    case 'change_password':
        $pwOld     = $_POST['pw_old'] ?? '';
        $pwNew     = $_POST['pw_new'] ?? '';
        $pwConfirm = $_POST['pw_confirm'] ?? '';

        // Get current password
        $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row || !password_verify($pwOld, $row['password'])) {
            flash('error', 'Password lama tidak sesuai. Silakan periksa kembali.');
            redirect('/setting/#password');
            break;
        }

        if (strlen($pwNew) < 8) {
            flash('error', 'Password baru minimal 8 karakter.');
            redirect('/setting/#password');
            break;
        }

        if ($pwNew !== $pwConfirm) {
            flash('error', 'Konfirmasi password baru tidak cocok.');
            redirect('/setting/#password');
            break;
        }

        $hash = password_hash($pwNew, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $userId);
        if ($stmt->execute()) {
            flash('success', 'Password berhasil diubah. Silakan login kembali jika diminta.');
        } else {
            flash('error', 'Gagal mengubah password.');
        }
        redirect('/setting/');
        break;

    case 'reset_widget_token':
        $newToken = bin2hex(random_bytes(16));
        $stmt = $conn->prepare('UPDATE users SET widget_token = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('si', $newToken, $userId);
            $stmt->execute();
            flash('success', 'Token widget berhasil diperbarui. Tautan widget baru Anda siap digunakan.');
        } else {
            flash('error', 'Gagal memperbarui token widget.');
        }
        redirect('/setting/?tab=widget');
        break;

    default:
        redirect('/dashboard/');
}
