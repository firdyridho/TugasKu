CREATE DATABASE IF NOT EXISTS tugasku_db;
USE tugasku_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nim VARCHAR(20),
    jurusan VARCHAR(100),
    angkatan YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE organisations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    kategori ENUM('ukm','ormawa','komunitas','lainnya') DEFAULT 'ukm',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE org_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    deadline DATETIME,
    tempat_pengumpulan VARCHAR(200),
    status ENUM('belum','progres','selesai') DEFAULT 'belum',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_mk VARCHAR(150) NOT NULL,
    dosen VARCHAR(100),
    hari ENUM('senin','selasa','rabu','kamis','jumat','sabtu','minggu'),
    jam_mulai TIME,
    jam_selesai TIME,
    ruang VARCHAR(50),
    kelas VARCHAR(10),
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Migration: add kelas to existing installations
-- ALTER TABLE courses ADD COLUMN kelas VARCHAR(10) AFTER ruang;


CREATE TABLE course_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    deadline DATETIME,
    tempat_pengumpulan VARCHAR(200),
    status ENUM('belum','progres','selesai') DEFAULT 'belum',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    hari ENUM('senin','selasa','rabu','kamis','jumat','sabtu','minggu'),
    jam_mulai TIME,
    jam_selesai TIME,
    tempat VARCHAR(200),
    tipe ENUM('mandiri','kegiatan','lainnya') DEFAULT 'mandiri',
    warna VARCHAR(7) DEFAULT '#6366f1',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
