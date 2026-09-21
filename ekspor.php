<?php
$pageTitle = "Ekspor Data";
require_once __DIR__ . "/config.php";
requireLogin();
$userId = $_SESSION["user_id"];
$user   = getUser();

if (isset($_GET["download"])) {
    $filter = $_GET["filter"] ?? "all";
    $bom = "\xEF\xBB\xBF";
    $rows = [];
    $rows[] = ["Kategori","Judul","Sumber / Mata Kuliah","Hari / Deadline","Jam","Tempat / Ruang","Status","Keterangan"];

    if ($filter === "all" || $filter === "org") {
        $res = $conn->query("SELECT ot.judul,ot.deskripsi,ot.deadline,ot.tempat_pengumpulan,ot.status,o.nama as org_nama FROM org_tasks ot JOIN organisations o ON ot.org_id=o.id WHERE ot.user_id=$userId ORDER BY ot.deadline ASC");
        if ($res) while ($r = $res->fetch_assoc()) {
            $rows[] = ["Tugas Organisasi",$r["judul"],$r["org_nama"],$r["deadline"]?date("d/m/Y",strtotime($r["deadline"])):"-",$r["deadline"]?date("H:i",strtotime($r["deadline"])):"-",$r["tempat_pengumpulan"]?:"-",ucfirst($r["status"]),$r["deskripsi"]?:"-"];
        }
    }
    if ($filter === "all" || $filter === "tugas") {
        $res = $conn->query("SELECT ct.judul,ct.deskripsi,ct.deadline,ct.tempat_pengumpulan,ct.status,c.nama_mk FROM course_tasks ct JOIN courses c ON ct.course_id=c.id WHERE ct.user_id=$userId ORDER BY ct.deadline ASC");
        if ($res) while ($r = $res->fetch_assoc()) {
            $rows[] = ["Tugas Kuliah",$r["judul"],$r["nama_mk"],$r["deadline"]?date("d/m/Y",strtotime($r["deadline"])):"-",$r["deadline"]?date("H:i",strtotime($r["deadline"])):"-",$r["tempat_pengumpulan"]?:"-",ucfirst($r["status"]),$r["deskripsi"]?:"-"];
        }
    }
    if ($filter === "all" || $filter === "kuliah") {
        $res = $conn->query("SELECT * FROM courses WHERE user_id=$userId ORDER BY FIELD(hari,\"senin\",\"selasa\",\"rabu\",\"kamis\",\"jumat\",\"sabtu\",\"minggu\"),jam_mulai");
        if ($res) while ($r = $res->fetch_assoc()) {
            $rows[] = ["Jadwal Kuliah",$r["nama_mk"],$r["dosen"]?:"-",ucfirst($r["hari"]),substr($r["jam_mulai"],0,5)." - ".substr($r["jam_selesai"],0,5),"Ruang ".($r["ruang"]?:"TBA"),"Aktif","Kelas ".($r["kelas"]?:"-")];
        }
    }
    if ($filter === "all" || $filter === "mandiri") {
        $res = $conn->query("SELECT * FROM schedules WHERE user_id=$userId ORDER BY FIELD(hari,\"senin\",\"selasa\",\"rabu\",\"kamis\",\"jumat\",\"sabtu\",\"minggu\"),jam_mulai");
        if ($res) while ($r = $res->fetch_assoc()) {
            $rows[] = ["Jadwal Mandiri",$r["judul"],ucfirst($r["tipe"]),ucfirst($r["hari"]),substr($r["jam_mulai"],0,5)." - ".substr($r["jam_selesai"],0,5),$r["tempat"]?:"-","Aktif",$r["deskripsi"]?:"-"];
        }
    }
    $labels = ["all"=>"Semua-Data","org"=>"Tugas-Organisasi","tugas"=>"Tugas-Kuliah","kuliah"=>"Jadwal-Kuliah","mandiri"=>"Jadwal-Mandiri"];
    $filename = "TugasKu-".($labels[$filter]??"Data")."-".date("Ymd").".csv";
    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Cache-Control: no-cache");
    $out = fopen("php://output","w");
    echo $bom;
    foreach ($rows as $row) fputcsv($out,$row);
    fclose($out);
    exit;
}

require_once __DIR__ . "/includes/header.php";
$countOrg    = $conn->query("SELECT COUNT(*) as n FROM org_tasks WHERE user_id=$userId")->fetch_assoc()["n"];
$countTugas  = $conn->query("SELECT COUNT(*) as n FROM course_tasks WHERE user_id=$userId")->fetch_assoc()["n"];
$countKuliah = $conn->query("SELECT COUNT(*) as n FROM courses WHERE user_id=$userId")->fetch_assoc()["n"];
$countMandiri= $conn->query("SELECT COUNT(*) as n FROM schedules WHERE user_id=$userId")->fetch_assoc()["n"];
$total = $countOrg + $countTugas + $countKuliah + $countMandiri;
?>
<div class="page-header"><div><h1>Ekspor Data</h1><p>Unduh data ke CSV - langsung bisa dibuka di Google Sheets atau Excel.</p></div></div>
<div style="background:var(--accent-dim);border:1px solid rgba(79,70,229,0.2);border-radius:var(--radius-lg);padding:1rem 1.5rem;margin-bottom:2rem;display:flex;align-items:flex-start;gap:0.85rem;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div><p style="font-size:0.88rem;font-weight:600;color:var(--accent);margin-bottom:0.2rem;">Cara Membuka di Google Sheets</p><p style="font-size:0.82rem;color:var(--text-secondary);line-height:1.55;">Buka <strong>sheets.google.com</strong> &rarr; Buat spreadsheet baru &rarr; Menu <strong>File &rarr; Import</strong> &rarr; Pilih file CSV &rarr; Pilih <em>"Comma"</em> sebagai separator.</p></div>
</div>
<div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:2rem;">
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);"><h3 style="font-size:1rem;font-weight:700;">Pilih Data yang Ingin Diekspor</h3><p style="font-size:0.82rem;color:var(--text-muted);margin-top:0.2rem;">Klik salah satu opsi untuk langsung mengunduh file CSV.</p></div>
    <div style="padding:1.5rem;"><div class="export-grid">
        <a href="?download=1&filter=all" class="export-option"><div class="export-option-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></div><div class="export-option-title">Semua Data</div><div class="export-option-desc">Semua tugas, jadwal kuliah, dan jadwal mandiri</div></a>
        <a href="?download=1&filter=org" class="export-option"><div class="export-option-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><div class="export-option-title">Tugas Organisasi</div><div class="export-option-desc">Semua tugas dari seluruh organisasi</div></a>
        <a href="?download=1&filter=tugas" class="export-option"><div class="export-option-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div><div class="export-option-title">Tugas Kuliah</div><div class="export-option-desc">Semua tugas dari mata kuliah</div></a>
        <a href="?download=1&filter=kuliah" class="export-option"><div class="export-option-icon" style="background:linear-gradient(135deg,#0284c7,#0ea5e9)"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div><div class="export-option-title">Jadwal Kuliah</div><div class="export-option-desc">Daftar mata kuliah dengan jadwal & ruangan</div></a>
        <a href="?download=1&filter=mandiri" class="export-option"><div class="export-option-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><div class="export-option-title">Jadwal Mandiri</div><div class="export-option-desc">Kegiatan & rutinitas harian pribadi</div></a>
    </div></div>
</div>
<div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);"><h3 style="font-size:1rem;font-weight:700;">Ringkasan Data Kamu</h3></div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);">
        <?php foreach([["Tugas Organisasi",$countOrg,"var(--accent)"],["Tugas Kuliah",$countTugas,"var(--warning)"],["Jadwal Kuliah",$countKuliah,"var(--info)"],["Jadwal Mandiri",$countMandiri,"var(--success)"]] as $s): ?>
        <div style="padding:1.25rem 1.5rem;border-right:1px solid var(--border);"><div style="font-size:1.75rem;font-weight:800;color:<?=$s[2]?>;line-height:1"><?=$s[1]?></div><div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.3rem"><?=$s[0]?></div></div>
        <?php endforeach; ?>
    </div>
    <div style="padding:1rem 1.5rem;background:var(--bg-primary);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:0.85rem;color:var(--text-secondary)">Total <strong><?=$total?></strong> data siap diekspor</span>
        <a href="?download=1&filter=all" class="btn btn-primary btn-sm"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Ekspor Semua (<?=$total?> data)</a>
    </div>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
