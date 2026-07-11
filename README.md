# 📘 SIMahasiswa — Sistem Informasi Mahasiswa

Aplikasi web manajemen data mahasiswa berbasis **PHP OOP** dan **MySQL**, dengan fitur nilai mata kuliah, **IPS (Indeks Prestasi Semester)**, **IPK (Indeks Prestasi Kumulatif)**, dan **Transkrip Nilai**.

## ✨ Fitur

- 🔐 Login admin (password di-hash dengan bcrypt) + proteksi CSRF
- 📋 CRUD data mahasiswa (tambah, lihat, edit, hapus) + upload foto
- 🔍 Pencarian, filter prodi/IPK, dan pagination
- 📚 Kelola mata kuliah (kode, nama, SKS)
- 📝 Input nilai per mahasiswa (nilai huruf A s.d. E)
- 📈 **IPS** — dihitung otomatis per semester: `Σ(bobot × SKS) / Σ(SKS)`
- ⭐ **IPK** — dihitung otomatis kumulatif dari seluruh semester
- 🎓 **Transkrip nilai** per semester, lengkap dengan mutu, total SKS, dan tombol cetak
- 📊 Dashboard statistik dengan grafik (Chart.js)
- 📎 Ekspor data mahasiswa ke CSV

## 🏗️ Arsitektur OOP

```
simahasiswa/
├── classes/
│   ├── Database.php    # Singleton koneksi PDO
│   ├── Auth.php        # Login, logout, session
│   ├── Mahasiswa.php   # Model CRUD mahasiswa + statistik
│   └── Nilai.php       # Model nilai, IPS, IPK, transkrip
├── config/
│   └── init.php        # Bootstrap + autoloader (spl_autoload_register)
├── includes/           # Header, footer, CSRF, helper upload
├── sql/setup.sql       # Skema database + data contoh
└── *.php               # Halaman (controller + view)
```

## 🗄️ Skema Nilai

| Nilai | Bobot | | Nilai | Bobot |
|-------|-------|-|-------|-------|
| A     | 4.00  | | C+    | 2.50  |
| A-    | 3.75  | | C     | 2.00  |
| B+    | 3.50  | | D     | 1.00  |
| B     | 3.00  | | E     | 0.00  |
| B-    | 2.75  | |       |       |

Setiap kali nilai ditambah/dihapus, kolom `mahasiswa.ipk` diperbarui otomatis oleh `Nilai::updateIPK()`.

## 🐳 Menjalankan dengan Docker (tanpa XAMPP)

Cukup punya [Docker Desktop](https://www.docker.com/products/docker-desktop/), lalu:

```bash
docker compose up -d --build
```

Tunggu ±30 detik saat pertama kali (MySQL menyiapkan database), lalu buka:

| Layanan     | URL                     | Login                  |
|-------------|-------------------------|------------------------|
| Aplikasi    | http://localhost:8080   | admin / admin123       |
| phpMyAdmin  | http://localhost:8081   | root / root            |

Database otomatis terisi **30 mahasiswa contoh + nilai acak** dari `sql/seed.sql`.

Perintah berguna:

```bash
docker compose down        # hentikan
docker compose down -v     # hentikan + hapus data database (reset dari awal)
docker compose logs app    # lihat log aplikasi
```

## 🚀 Cara Menjalankan (XAMPP)

1. Jalankan XAMPP/Laragon (Apache + MySQL)
2. Salin folder proyek ke `htdocs/`
3. Import `sql/setup.sql` lewat phpMyAdmin (atau `mysql -u root < sql/setup.sql`)
4. (Opsional) Import juga `sql/seed.sql` untuk mengisi 30 data mahasiswa contoh
5. Buka `http://localhost/simahasiswa`
6. Login: username `admin`, password `admin123`

> Kredensial database bisa disesuaikan di `classes/Database.php`.

## 🛠️ Teknologi

- PHP 8+ (OOP: class, singleton, type hints, autoloading)
- MySQL (PDO + prepared statements)
- Chart.js untuk visualisasi
