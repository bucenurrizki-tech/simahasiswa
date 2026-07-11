CREATE DATABASE IF NOT EXISTS si_mahasiswa;
USE si_mahasiswa;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- password 'admin123' sudah di-hash dengan bcrypt
INSERT INTO users (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    prodi VARCHAR(50) NOT NULL,
    ipk DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- Tabel baru: mata kuliah & nilai (untuk IPS, IPK, transkrip)
-- ==========================================================

CREATE TABLE IF NOT EXISTS mata_kuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(15) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    sks TINYINT UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS nilai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    mata_kuliah_id INT NOT NULL,
    semester TINYINT UNSIGNED NOT NULL,
    nilai_huruf VARCHAR(2) NOT NULL,
    bobot DECIMAL(3,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mhs_matkul (mahasiswa_id, mata_kuliah_id),
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE
);

-- Contoh data mata kuliah
INSERT INTO mata_kuliah (kode, nama, sks) VALUES
('IF101', 'Algoritma dan Pemrograman', 3),
('IF102', 'Matematika Diskrit', 3),
('IF103', 'Pengantar Teknologi Informasi', 2),
('IF201', 'Struktur Data', 3),
('IF202', 'Basis Data', 3),
('IF203', 'Pemrograman Web', 3),
('IF301', 'Pemrograman Berorientasi Objek', 3),
('IF302', 'Rekayasa Perangkat Lunak', 3);
