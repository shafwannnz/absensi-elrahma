# 🎓 Sistem Informasi Absensi Mahasiswa — STIKOM El Rahma

Aplikasi web presensi mahasiswa berbasis **Pengenalan Wajah (Face Recognition AI)** dan **Validasi Radius Lokasi (Geolocation GPS)** secara *real-time*. Dibangun dengan framework **Laravel 12**, arsitektur antarmuka modern bernuansa **Neobrutalism**, serta dukungan **Progressive Web App (PWA)**.

---

## 🌟 Fitur Utama Sistem

### 1. 🌐 Landing Page & Dashboard Publik
- **Informasi Publik Terbuka**: Pengunjung web disajikan informasi sistem, cara kerja, dan status jadwal presensi aktif sebelum diarahkan masuk.
- **Statistik & Tren Kehadiran**: Grafik interaktif rekap kehadiran 7 hari terakhir serta persentase kehadiran hari ini.
- **Informasi Jadwal & Titik Kampus**: Menampilkan batas radius absensi, jam buka-tutup presensi masuk, serta jam kepulangan secara transparan.

### 2. 👨‍🎓 Portal Mahasiswa (Student)
- **Autentikasi NIM**: Login cepat dan aman khusus mahasiswa berbasis NIM dan kata sandi.
- **Pendaftaran Wajah (Face Enrollment)**:
  - Deteksi dan ekstraksi fitur biometrik wajah (128-dimensional descriptor) langsung melalui kamera browser via `face-api.js`.
  - Wajah disimpan dalam bentuk vektor enkripsi untuk verifikasi presensi berikutnya.
- **Presensi Masuk (Check-In)**:
  - Validasi multi-lapisan: Pencocokan descriptor biometrik wajah dan jarak radius GPS terhadap titik kampus.
  - Pencatatan status otomatis: **Tepat Waktu** atau **Terlambat** sesuai aturan jam operasional.
  - Pengambilan foto selfie bukti kehadiran secara otomatis.
- **Presensi Pulang (Check-Out)**:
  - Mahasiswa yang sudah check-in dapat melakukan check-out pada rentang jam pulang yang ditentukan.
  - Mengambil foto selfie pulang serta merekam titik koordinat kepulangan.
- **Riwayat Absensi Mandiri**:
  - Rekapitulasi jumlah absensi, kehadiran tepat waktu, dan keterlambatan.
  - Tabel catatan historis lengkap dengan jam masuk, jam pulang, status, serta jarak lokasi absensi.

### 3. 👨‍💼 Panel Administrator (Admin)
- **Dashboard Ringkasan Real-Time**:
  - Kartu metrik: Total absensi, mahasiswa hadir hari ini, mahasiswa terlambat, jumlah yang sudah pulang, dan mahasiswa belum absen.
  - Grafik batang kehadiran 7 hari terakhir.
  - Tabel absensi terbaru dilengkapi hover dan modal preview foto selfie masuk & pulang.
- **Manajemen Data Mahasiswa**:
  - Tambah, edit, dan kelola status akun mahasiswa (Aktif / Nonaktif).
  - Status registrasi wajah mahasiswa dengan fitur **Reset Data Wajah** jika mahasiswa perlu mendaftarkan ulang wajahnya.
- **Laporan & Ekspor Data Presensi**:
  - Filter komprehensif berdasarkan rentang tanggal, status kehadiran (Hadir/Terlambat), serta pencarian Nama/NIM.
  - Inspeksi foto selfie masuk dan keluar mahasiswa secara aman.
  - Fitur **Ekspor Laporan ke CSV/Excel** untuk rekapitulasi akademik.
- **Pengaturan Lokasi & Aturan Jam Presensi**:
  - Penentuan titik koordinat kampus (Latitude & Longitude) interaktif dengan peta **Leaflet**.
  - Pengaturan radius toleransi jarak (dalam meter).
  - Penjadwalan jam buka, toleransi keterlambatan, jam tutup masuk, serta jam buka-tutup presensi pulang.

### 4. 🛡️ Keamanan & Manajemen Sesi (Security)
- **Session Inactivity Timeout (5 Menit)**:
  - Sistem otomatis memutuskan sesi (logout) jika pengguna tidak melakukan aktivitas apa pun selama 5 menit berturut-turut untuk mencegah penyalahgunaan akun di perangkat umum.
- **Anti-Back Cache Protection (`no-cache`)**:
  - Header HTTP `no-store, no-cache, must-revalidate` diimplementasikan sehingga tombol *Back* pada browser tidak akan menampilkan kembali formulir login atau data halaman yang sudah logout.
- **Anti-Spoofing & Validasi Sisi Server**:
  - Koordinat GPS dan descriptor wajah diverifikasi ulang di backend (`AttendanceService`) untuk mencegah manipulasi data dari client.

---

## 🛠️ Tumpukan Teknologi (Tech Stack)

- **Backend**: PHP 8.4+, [Laravel 12](https://laravel.com)
- **Frontend / Styling**: [Tailwind CSS v4](https://tailwindcss.com) (Neobrutalism Design System), Blade Templating
- **AI / Computer Vision**: [face-api.js](https://github.com/vladmandic/face-api) (TensorFlow.js models)
- **Pemetaan (Maps)**: [Leaflet.js](https://leafletjs.com) & OpenStreetMap
- **Database**: MySQL / MariaDB
- **Build Tool**: [Vite](https://vite.dev)
- **Mobile Support**: PWA (Manifest & Service Worker)

---

## 🚀 Panduan Instalasi Lokal

### 1. Klon Repositori & Masuk Direktori
```bash
git clone <url-repository>
cd absensi-elrahma
```

### 2. Pasang Dependensi
```bash
composer install
npm install
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin file konfigurasi dan buat application key:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database MySQL Anda di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_elrahma
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi & Seeder Database
```bash
php artisan migrate --seed
```

### 5. Kompilasi Aset Frontend
```bash
npm run build
# atau untuk mode pengembangan:
npm run dev
```

### 6. Jalankan Server Lokal
```bash
php artisan serve
```
Akses sistem melalui browser di `http://127.0.0.1:8000`.

---

## 📋 Hak Akses Default (Development)

- **Admin**:
  - Email: `admin@elrahma.ac.id` (atau sesuai seeder)
- **Mahasiswa**:
  - Login menggunakan **NIM** yang didaftarkan oleh administrator.

---

## 📄 Lisensi
Sistem Informasi Absensi STIKOM El Rahma dikembangkan untuk kebutuhan internal akademik dan operasional presensi kampus.
