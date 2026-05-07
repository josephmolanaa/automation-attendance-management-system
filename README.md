<p align="center"><a href="https://your-domain.com" target="_blank"><h1>Sistem Manajemen Absensi Karyawan</h1></a></p>

## Tentang Sistem Ini
Sistem Manajemen Absensi ini adalah aplikasi web berbasis **Laravel** yang digunakan untuk mencatat jam kerja karyawan secara akurat. Aplikasi ini dikembangkan berdasarkan proyek open-source Attendance Management System (clone dari https://github.com/aliatayee/Attendance_Management_System), namun telah **dimodifikasi dan dikembangkan lebih lanjut** sesuai kebutuhan saya.

Fitur utama termasuk:
- Integrasi dengan **mesin absensi fingerprint Fingerspot** (menggunakan API/SDK Fingerspot.io untuk sinkronisasi realtime attlog, userinfo, dll.)
- Pencatatan absensi manual sebagai cadangan
- Manajemen karyawan, laporan kehadiran, cuti, lembur, dll.
- Dashboard admin dan user-friendly untuk karyawan

Sistem ini cocok untuk perusahaan, kantor, atau sekolah yang menggunakan perangkat **Fingerspot** (seperti seri Revo, Compact, atau model cloud-enabled).

## Teknologi Utama
- **Backend**: PHP, Laravel
- **Frontend**: HTML5, CSS, JavaScript, Bootstrap
- **Database**: MySQL
- **Integrasi Device**: Fingerspot API / SDK (developer.fingerspot.io) untuk pull attlog realtime via webhook atau polling

## Demo
<a href="https://web-production-44dd7.up.railway.app/">Lihat Demo</a>

### Kredensial Admin (default, segera ganti setelah install)
- Username: admin@ams.com
- Password: admin@ams.com (atau sesuai yang anda set di seeder)

## Cara Install & Setup
Ikuti langkah-langkah berikut untuk menjalankan proyek di lokal atau server:

1. Clone repository ini:

```
git clone https://github.com/josephmolanaa/automation-attendance-management-system.git

```
2. Masuk Ke Folder Proyek:

```
cd nama-repo-anda
``` 
3. Copy file `.env.example` menjadi `.env` dan sesuaikan konfigurasi database serta pengaturan Fingerspot (API key, mesin SN, webhook URL, dll.):
```
cp .env.example .env
```

4. Install dependencies PHP:
```
composer install
```

5. Install dependencies frontend:
```
npm install atau yarn install
```
6. Generate application key:
```
php artisan key:generate
```
7. jalankan migrasi database:
```
php artisan migrate
```
8. Jalankan seeder (untuk data dummy karyawan, admin, dll.);
```
php artisan db:seed
```
9. Jalankan server lokal:
```
php artisan serve
```
10.(Opsional) Compile asset frontend;
```
npm run dev atau npm run build untuk production
```


**Catatan khusus Fingerspot**:
- Daftarkan mesin absensi kamu di https://developer.fingerspot.io/
- Konfigurasi webhook untuk realtime attlog (kirim ke endpoint Laravel kamu, misal `/api/fingerspot/webhook`)
- Tambahkan cron job atau Laravel scheduler untuk polling attlog jika webhook tidak aktif.

## Screenshot
![Dashboard]
![Absensi]
![Laporan]
![Manajemen Karyawan]

## Persyaratan Sistem
- PHP ≥ 8.1
- Composer
- Node.js & NPM/Yarn
- MySQL / MariaDB
- Git
- Akses ke mesin Fingerspot (cloud atau LAN dengan API aktif)

## Penulis / Pengembang
👤 **Joseph**
- GitHub: [@josephmolanaa]([(https://github.com/josephmolanaa))  

Proyek ini dibangun berdasarkan dari [Ali Atayee](https://github.com/aliatayee) — terima kasih atas basis awalnya!

## Kontribusi
Kontribusi, saran, issue, dan fitur baru sangat diterima!  
Silakan buka [issues](../../issues/) atau pull request.

## Dukungan
Beri ⭐ jika proyek ini membantu kamu!

## Lisensi
[MIT License](LICENSE) — bebas digunakan, dimodifikasi, dan didistribusikan.

Terima kasih telah menggunakan sistem ini!
