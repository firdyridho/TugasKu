<?php
/**
 * Contoh Konfigurasi Server Hosting (Live Production)
 * 
 * CARA PAKAI:
 * 1. Di File Manager cPanel / hosting Anda, salin file ini menjadi 'config_local.php'.
 * 2. Ubah kredensial database di bawah sesuai database MySQL hosting Anda.
 * 3. File 'config_local.php' ini otomatis diabaikan oleh Git (.gitignore) sehingga aman dan tidak akan tertimpa saat push GitHub Actions.
 */

// define('DB_HOST', 'localhost');
// define('DB_USER', 'nama_user_db_hosting');
// define('DB_PASS', 'password_db_hosting');
// define('DB_NAME', 'nama_db_hosting');

// Jika website dipasang di root domain (misal https://namadomain.com), biarkan BASE_URL kosong:
// define('BASE_URL', '');
