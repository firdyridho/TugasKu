# TugasKu

**Platform Manajemen Kegiatan & Akademik Mahasiswa Modern**

TugasKu adalah platform produktivitas all-in-one yang dirancang khusus untuk mahasiswa Indonesia. Satu antarmuka untuk mengelola jadwal kuliah, tugas organisasi, jadwal mandiri, dan kalender akademik -- tanpa iklan, tanpa ribet.

---

## Arsitektur Sistem

```mermaid
graph TB
    subgraph "Client Layer"
        A[Browser] --> B[Landing Page]
        A --> C[Auth - Login / Register]
        A --> D[Dashboard]
        A --> E[Kuliah]
        A --> F[Organisasi]
        A --> G[Jadwal]
        A --> H[Kalender]
        A --> I[AI Assistant]
    end

    subgraph "Server Layer - PHP"
        D --> J[config.php]
        E --> K[proses.php - CRUD Handler]
        F --> K
        G --> K
        I --> L[api/ai.php - NVIDIA NIM Proxy]
    end

    subgraph "Data Layer"
        K --> M[(MySQL - tugasku_db)]
        K --> N[uploads/ - File Storage]
        L --> O[NVIDIA Build API]
    end

    style A fill:#e0e7ff,stroke:#4f46e5,color:#1e1b4b
    style M fill:#fef3c7,stroke:#d97706,color:#78350f
    style O fill:#d1fae5,stroke:#059669,color:#064e3b
```

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP (Vanilla, tanpa framework) |
| Database | MySQL / MariaDB |
| Server | Apache via Laragon |
| Frontend | HTML5, CSS3, JavaScript Vanilla |
| AI | NVIDIA Build API (NIM) |
| Font | Plus Jakarta Sans + Newsreader |

---

## Struktur Database

```mermaid
erDiagram
    users {
        int id PK
        varchar nama
        varchar email UK
        varchar password
        varchar nim
        varchar jurusan
        year angkatan
        timestamp created_at
    }

    organisations {
        int id PK
        varchar nama
        text deskripsi
        enum kategori
        int user_id FK
        timestamp created_at
    }

    org_tasks {
        int id PK
        int org_id FK
        varchar judul
        text deskripsi
        datetime deadline
        varchar tempat_pengumpulan
        enum status
        int user_id FK
        timestamp created_at
    }

    courses {
        int id PK
        varchar nama_mk
        varchar dosen
        enum hari
        time jam_mulai
        time jam_selesai
        varchar ruang
        varchar kelas
        int user_id FK
        timestamp created_at
    }

    course_tasks {
        int id PK
        int course_id FK
        varchar judul
        text deskripsi
        datetime deadline
        varchar tempat_pengumpulan
        enum status
        int user_id FK
        timestamp created_at
    }

    schedules {
        int id PK
        varchar judul
        text deskripsi
        enum hari
        time jam_mulai
        time jam_selesai
        varchar tempat
        enum tipe
        varchar warna
        int user_id FK
        timestamp created_at
    }

    users ||--o{ organisations : "creates"
    users ||--o{ courses : "creates"
    users ||--o{ schedules : "creates"
    users ||--o{ org_tasks : "creates"
    users ||--o{ course_tasks : "creates"
    organisations ||--o{ org_tasks : "has"
    courses ||--o{ course_tasks : "has"
```

---

## Alur Autentikasi

```mermaid
sequenceDiagram
    actor User
    participant Browser
    participant Server
    participant DB

    User->>Browser: Akses halaman
    Browser->>Server: GET /auth/login.php
    Server-->>Browser: Tampilkan form login

    User->>Browser: Submit email + password
    Browser->>Server: POST proses.php (action=login)
    Server->>DB: SELECT user WHERE email = ?
    DB-->>Server: User record

    alt Password valid
        Server->>Server: password_verify()
        Server->>Server: session_start(), set $_SESSION
        Server-->>Browser: Redirect ke /dashboard/
    else Password invalid
        Server-->>Browser: Flash error + redirect
    end
```

---

## Fitur Utama

### Manajemen Kuliah
- Tambah, edit, hapus mata kuliah (nama, dosen, hari, jam, ruang, kelas)
- Dua mode tampilan: **Card View** dan **Timetable View** 7 hari
- Live search lintas mata kuliah dan nama dosen

### Manajemen Organisasi
- Kelola kegiatan UKM, Ormawa, Komunitas, atau kategori lainnya
- CRUD tugas per-organisasi dengan status: Belum, Progres, Selesai
- Badge urgensi deadline otomatis (Terlewat, Hari Ini, Besok, Aman)

### Jadwal Mandiri
- Buat jadwal belajar mandiri, hobi, atau kegiatan lain
- Color picker untuk pembedaan visual per kegiatan
- Tersusun per hari dalam seminggu

### Kalender Akademik
Tiga mode tampilan dalam satu halaman:

```mermaid
graph LR
    A[Kalender] --> B[Bulan]
    A --> C[Minggu]
    A --> D[Agenda]

    B --> E[Grid bulanan dengan titik event]
    C --> F[Time-slot grid 06:00 - 22:00]
    D --> G[Daftar kronologis per bulan]

    style A fill:#4f46e5,stroke:#3730a3,color:#fff
    style B fill:#e0e7ff,stroke:#4f46e5,color:#1e1b4b
    style C fill:#e0e7ff,stroke:#4f46e5,color:#1e1b4b
    style D fill:#e0e7ff,stroke:#4f46e5,color:#1e1b4b
```

- Filter kategori: Kuliah, Tugas Kuliah, Organisasi, Jadwal Mandiri
- Navigasi bulan/minggu sebelumnya dan berikutnya
- Klik hari pada mode bulan untuk melihat detail event

### Ekspor Data (CSV)
Unduh seluruh data dalam format CSV dengan encoding UTF-8 BOM (kompatibel Google Sheets & Excel). Lima pilihan filter:
1. Semua Data
2. Tugas Organisasi
3. Tugas Kuliah
4. Jadwal Kuliah
5. Jadwal Mandiri

### Asisten AI
- Widget chat floating di pojok kanan bawah
- Powered by NVIDIA Build API dengan model fallback
- Quick suggestion: Prioritas Deadline, Manajemen Waktu, Draft Email Dosen, Tips Ujian
- Riwayat percakapan (6 turn terakhir)

### Fitur Tambahan
- **Onboarding Tour**: Walkthrough 5-langkah untuk pengguna baru
- **Status Sistem**: Monitor uptime dan latensi database secara realtime
- **Kebijakan Privasi**: Dokumentasi lengkap transparansi data
- **Changelog**: Riwayat rilis dan pembaruan fitur
- **Mobile Responsive**: Bottom navigation untuk smartphone
- **Flash Messages**: Notifikasi auto-dismiss dengan progress bar
- **Modal System**: Keyboard (Escape) dan overlay-click dismiss

---

## Instalasi

### Prasyarat
- PHP 7.4 atau lebih tinggi
- MySQL 5.7+ / MariaDB 10.3+
- Apache dengan mod `rewrite` aktif
- Laragon (opsional, untuk development lokal)

### Langkah Setup

```bash
# 1. Clone repository
git clone https://github.com/firdyridho/TugasKu.git

# 2. Pindahkan ke web server directory
# Untuk Laragon: C:\laragon\www\TugasKu

# 3. Import database
mysql -u root -p < database.sql

# 4. Konfigurasi koneksi database di config.php
# Sesuaikan DB_HOST, DB_NAME, DB_USER, DB_PASS

# 5. Pastikan folder uploads/ memiliki .htaccess
# (sudah termasuk dalam repository)

# 6. Buka di browser
# http://localhost/TugasKu/
```

### Konfigurasi NVIDIA AI (Opsional)

Untuk mengaktifkan fitur Asisten AI, tambahkan API key NVIDIA Build di `api/ai.php`:

```php
$apiKey = 'nvapi-your-api-key-here';
```

---

## Tampilan Antar Muka

```
Landing Page          Dashboard
+------------------+  +------------------+
|    Hero CTA      |  |   Stats Cards    |
|  Feature Bento   |  |  Today Schedule  |
|  Live Preview    |  |  Deadlines       |
+------------------+  +------------------+

Timetable View      Kalender
+------------------+  +------------------+
|  Mon Tue Wed ...  |  |  Month / Week /  |
|  Course Blocks    |  |  Agenda View     |
|  Today Highlight  |  |  Event Dots      |
+------------------+  +------------------+
```

---

## versi

| Versi | Tanggal | Ringkasan |
|---|---|---|
| 2.4 | 21 September 2026 | Ekspor CSV, Pengaturan Akun, Timetable Redesign, Onboarding Tour |
| 2.3 | 12 Agustus 2026 | Navigasi Kalender, Mobile Bottom Navigation |
| 2.2 | 28 Juli 2026 | Deadline Reminder, Dashboard Statistik |
| 2.0 | 10 Mei 2026 | Arsitektur baru, Manajemen Organisasi + Kuliah terpadu |

---

## Lisensi

Hak Cipta Dilindungi. Dikembangkan oleh **Firdy Ridho** – 2026.

---

<div align="center">

**TugasKu** -- Satu Platform, Semua Kegiatan Mahasiswa.

Dibuat dengan vanilla PHP, tanpa framework, tanpa dependency manager. Murni kode tangan.

</div>
