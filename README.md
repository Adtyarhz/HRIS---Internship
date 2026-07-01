# 🏦 HRIS BPR Perdana — Human Resource Information System

> Sistem Informasi Sumber Daya Manusia berbasis web untuk BPR Perdana, dibangun menggunakan **Laravel 12** dengan antarmuka AdminLTE.

---

## 📋 Deskripsi

HRIS BPR Perdana adalah aplikasi manajemen sumber daya manusia yang dikembangkan sebagai bagian dari program **Kerja Praktik (Internship)**. Sistem ini dirancang untuk membantu divisi HR dalam mengelola seluruh siklus manajemen karyawan secara efisien dan terintegrasi.

---

## ✨ Fitur Utama

### 👥 Manajemen Karyawan
- Data lengkap karyawan (biodata, jabatan, divisi)
- Riwayat karir & proyeksi karir
- Riwayat pendidikan & pengalaman kerja
- Data keluarga & tanggungan
- Rekam kesehatan karyawan
- Permintaan perubahan data karyawan

### 📊 KPI & Penilaian
- Template KPI & indikator
- Periode penilaian KPI
- Penilaian KPI karyawan beserta scoring rules
- Laporan hasil penilaian

### 🧑‍💼 Rekrutmen
- Manajemen data pelamar (Applicant)
- Jadwal wawancara (Interview Schedule)
- Progress rekrutmen
- Proses onboarding dokumen

### ⏰ Lembur & Kehadiran
- Pengajuan lembur (Overtime Application)
- Riwayat & notifikasi lembur
- Approval workflow lembur

### 📜 Sertifikasi & Pelatihan
- Riwayat sertifikasi karyawan
- Riwayat pelatihan & materi pelatihan

### 🔔 Informasi & Komunikasi
- Pengumuman (Announcement)
- Polling karyawan
- Asuransi karyawan

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | AdminLTE 3, Blade Template, Vite |
| Styling | Tailwind CSS |
| Database | MySQL |
| Excel Import/Export | Maatwebsite Excel 3.1 |
| Activity Log | Spatie Laravel ActivityLog |
| Auth | Laravel Breeze / Built-in Auth |

---

## ⚙️ Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js >= 18 & NPM
- MySQL >= 8.0
- Laravel 12.x

---

## 🚀 Instalasi & Konfigurasi

### 1. Clone Repository

```bash
git clone https://github.com/Adtyarhz/HRIS---Internship.git
cd HRIS---Internship
```

### 2. Install Dependencies PHP

```bash
composer install
```

### 3. Install Dependencies Node

```bash
npm install
```

### 4. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` sesuai konfigurasi database lokal kamu:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris_perdana
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Migrasi & Seed Database

```bash
php artisan migrate --seed
```

### 6. Build Assets

```bash
npm run dev
```

### 7. Jalankan Aplikasi

```bash
php artisan serve
```

Akses aplikasi di: **http://localhost:8000**

---

## 📁 Struktur Direktori Utama

```
HRIS-Perdana/
├── app/
│   ├── Http/Controllers/   # Controller aplikasi
│   ├── Models/             # Eloquent Models
│   ├── Exports/            # Excel Exports
│   ├── Imports/            # Excel Imports
│   ├── Jobs/               # Queue Jobs
│   └── Notifications/      # Notifikasi sistem
├── database/
│   ├── migrations/         # Skema database
│   └── seeders/            # Data awal
├── resources/
│   └── views/              # Blade templates
├── routes/
│   └── web.php             # Routing aplikasi
└── public/                 # Asset publik
```

---

## 👨‍💻 Developer

Dikembangkan dalam rangka **Kerja Praktik** di **BPR Perdana**.

---

## 📄 Lisensi

Project ini bersifat **private** dan dikembangkan untuk keperluan internal BPR Perdana.
