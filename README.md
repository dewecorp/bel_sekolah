# 🔔 Bel Sekolah Digital

Aplikasi **Bel Sekolah Digital** — sistem pengatur jadwal bel sekolah otomatis berbasis **PHP Native + MySQL**. Modern, responsif, cocok untuk SD, SMP, maupun SMA.

---

## ✨ Fitur

| Fitur | Keterangan |
|---|---|
| 📊 **Dashboard** | Jam digital realtime, tanggal & hari, jadwal bel berikutnya, countdown, status sistem, jadwal hari ini, tombol "Bunyikan Bel Sekarang" |
| 📅 **Jadwal Bel** | Tambah / ubah / hapus / aktif-nonaktifkan jadwal per hari (Senin–Minggu), cegah bentrok |
| 🔔 **Jenis Bel** | 6 kategori: Bel Masuk, Pergantian, Istirahat, Masuk Setelah Istirahat, Pulang, Khusus |
| 🎵 **Audio Bel** | Upload MP3/WAV/OGG, preview suara, atur volume & durasi, file disimpan aman |
| 🤖 **Mode Otomatis** | Cek jadwal realtime, bunyikan bel tepat waktu, anti dobel bunyi (dedupe), tampil notifikasi popup |
| 📆 **Hari Libur** | Kelola tanggal libur, bel otomatis dimatikan pada tanggal tersebut |
| 🛡️ **Login Admin** | Autentikasi session + password hashed (bcrypt), halaman admin terlindungi |
| ⚙️ **Pengaturan** | Nama sekolah, alamat, zona waktu, format jam 12/24, volume, durasi bel, ubah password |
| 📋 **Riwayat Bel** | Histori tanggal-jam-nama-jenis-mode, filter per tanggal, hapus per item / bersihkan |

---

## 🛠️ Teknologi

- **PHP 8+** (native, modular, tanpa framework)
- **MySQL** + **PDO** (prepared statement, anti SQL injection)
- **CSS**: custom modern responsive design
- **JavaScript**: vanilla (HTML5 Audio API, fetch API)
- **Router** custom → Controller@Method

---

## 📂 Struktur Folder

```
bel_sekolah/
├── config/
│   └── database.php          # Kredensial MySQL
├── core/
│   ├── App.php               # Bootstrap, helper, settings cache
│   ├── Database.php          # Koneksi PDO
│   ├── Router.php            # Router URL → Controller@Method
│   ├── Controller.php        # Base controller (view, json, redirect)
│   └── Auth.php              # Session auth, bcrypt
├── models/
│   ├── BaseModel.php         # CRUD generik
│   ├── User.php  Schedule.php  BellType.php
│   ├── Audio.php  Holiday.php  BellHistory.php  Settings.php
├── controllers/
│   ├── AuthController.php     # login/logout
│   ├── DashboardController.php# halaman publik & admin
│   ├── JadwalController.php   # CRUD jadwal (dengan deteksi bentrok)
│   ├── BelTypeController.php  # CRUD jenis bel
│   ├── AudioController.php    # upload/kelola audio
│   ├── HolidayController.php  # CRUD hari libur
│   ├── RiwayatController.php  # riwayat bel
│   ├── SettingsController.php # pengaturan sekolah
│   ├── BellController.php     # API realtime bel (today/check/log/manual)
│   ├── InstallController.php  # installer database
│   └── FileController.php     # streaming audio (dukung range request)
├── views/
│   ├── layouts/  (admin, public, auth, install)
│   ├── auth/login.php
│   ├── dashboard/ (public, admin)
│   ├── jadwal/  bel-types/  audio/  holidays/  riwayat/  settings/
│   └── install.php
├── public/                   # DOCUMENT ROOT
│   ├── index.php             # Front controller (semua request masuk sini)
│   ├── .htaccess             # Rewrite → index.php + blokir file sensitif
│   ├── css/app.css
│   └── js/  (app, dashboard, admin-dashboard, jadwal, bel, audio, holidays, riwayat, settings)
├── storage/audio/            # File audio hasil upload (dilindungi)
├── sql/
│   ├── schema.sql            # Skema database
│   └── seed.sql              # Data contoh (jadwal, jenis bel, hari libur, admin)
└── .htaccess                 # Proteksi folder internal
```

---

## 🚀 Instalasi & Menjalankan

### Prasyarat
- **PHP 8+** (aktifkan ekstensi `pdo_mysql`)
- **MySQL** (Laragon: user `root`, tanpa password — default)

### Langkah
1. Letakkan project di folder web server (mis. `D:\laragon\www\bel_sekolah`).
2. Pastikan file `config/database.php` sesuai kredensial MySQL Anda.
3. **Laragon → Apache → Restart**.
4. Buka `http://bel_sekolah.test` (atau `http://localhost/bel_sekolah`):
   - Jika database belum ada → otomatis diarahkan ke halaman **Instalasi**.
   - Klik **"Jalankan Instalasi"** → database, tabel, jadwal contoh, dan akun admin dibuat otomatis.
5. Selesai. Masuk ke panel admin.

> **Catatan**: Jika Apache memakai DocumentRoot di folder lain, sesuaikan
> `public/` sebagai DocumentRoot (vhost), atau akses lewat `http://host/bel_sekolah/public`.
> Jalankan ulang installer dengan menghapus tabel/database `bel_sekolah`.

### Akun Default Admin

| | |
|---|---|
| **Username** | `admin` |
| **Password** | `admin123` |

> ⚠️ Segera ganti password setelah instalasi (menu **Pengaturan → Ubah Password**).

---

## 🧭 Alur Auto-Bell

1. Halaman dashboard (publik & admin) menjalankan **fetch `/api/bell/check`** setiap **5 detik**.
2. Server membandingkan waktu sekarang dengan jadwal aktif hari itu (dan mengecek hari libur + status sistem).
3. Jika ada jadwal yang **waktu-nya sama** → audio diputar otomatis.
4. **Anti dobel bunyi**: dedupe key `tanggal|waktu+idJadwal` disimpan di variabel + `localStorage`.
5. Riwayat dicatat otomatis ke tabel `bell_history` (mode: otomatis/manual, status: berhasil/gagal).
6. Suara bel: audio yang di-upload per jenis bel, atau **file default** `storage/audio/bell-default.wav`.

---

## 🔗 Referensi Endpoint Utama

| Method | URL | Fungsi |
|---|---|---|
| GET | `/api/bell/today` | Jadwal bel hari ini + settings |
| GET | `/api/bell/check` | Cek jadwal yang match sekarang (auto-bell) |
| POST | `/api/bell/log` | Catat riwayat bel otomatis |
| POST | `/api/bell/manual` | Catat riwayat bel manual |
| GET | `/api/bell/audio` | Audio default untuk tombol manual |
| GET | `/storage/audio/{file}` | Streaming file audio (dukung range) |
| POST | `/admin/jadwal`, `/admin/bel`, `/admin/libur`, `/admin/pengaturan` | CRUD (PUT/DELETE via `_method`) |

---

## 🧪 Validasi & Keamanan

- ✅ Validasi form (format waktu HH:MM, tanggal YYYY-MM-DD, ukuran file, tipe file)
- ✅ Deteksi **bentrok jadwal** (hari + jam sama)
- ✅ Semua query memakai **prepared statement PDO**
- ✅ Password **bcrypt** + session regenerate saat login
- ✅ **CSRF token** pada form
- ✅ Proteksi folder internal (`config/`, `core/`, `models/`, `sql/`, `.htaccess`)
- ✅ Upload audio dibatasi format & ukuran (10MB), nama file diacak
- ✅ Konfirmasi sebelum hapus data (dialog)