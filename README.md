# Attendance App - Aplikasi Absensi Berbasis Web

Sistem absensi karyawan berbasis web yang lengkap dengan fitur clock-in/clock-out, webcam selfie, geolocation, dan laporan. Dibangun menggunakan HTML, CSS, JavaScript, PHP, dan MySQL.

## 🚀 Fitur Utama

### Autentikasi
- ✅ Login untuk karyawan dan admin
- ✅ Register untuk pendaftaran karyawan baru
- ✅ Logout dengan session management
- ✅ Password hashing menggunakan `password_hash()` PHP

### Dashboard Karyawan
- ✅ Tampilan profil karyawan
- ✅ Tombol Clock-in (absen masuk)
- ✅ Tombol Clock-out (absen pulang)
- ✅ Riwayat absensi pribadi
- ✅ Status kehadiran hari ini
- ✅ Statistik kehadiran bulanan

### Dashboard Admin
- ✅ Melihat semua data karyawan
- ✅ Melihat semua data absensi
- ✅ Tambah, edit, hapus karyawan
- ✅ Filter absensi berdasarkan tanggal, karyawan, status
- ✅ Statistik kehadiran real-time

### Fitur Clock-in/Clock-out
- ✅ Capture foto selfie menggunakan webcam (JavaScript)
- ✅ Capture geolocation (latitude, longitude) saat absen
- ✅ Validasi waktu absen (tidak bisa double clock-in)
- ✅ Timestamp otomatis
- ✅ Status otomatis (present/late berdasarkan jam masuk)

### Laporan Kehadiran
- ✅ Rekap absensi per karyawan
- ✅ Rekap absensi per periode
- ✅ Export ke PDF
- ✅ Export ke Excel
- ✅ Export ke CSV
- ✅ Filter berdasarkan tanggal, karyawan, dan departemen

### UI/UX
- ✅ Responsive design (mobile-friendly)
- ✅ Bootstrap 5
- ✅ Clean dan professional UI
- ✅ Loading indicators
- ✅ SweetAlert2 untuk notifications
- ✅ DataTables untuk tabel interaktif

## 📋 Requirement

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)
- Browser modern dengan support untuk:
  - Webcam API
  - Geolocation API
  - LocalStorage

## 🔧 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/rahmatsyahdz/attendance-app.git
cd attendance-app
```

### 2. Setup Database

```bash
# Login ke MySQL
mysql -u root -p

# Import database
mysql -u root -p < database/attendance_db.sql

# Atau melalui phpMyAdmin:
# - Buat database baru bernama 'attendance_db'
# - Import file database/attendance_db.sql
```

### 3. Konfigurasi Database

Edit file `config/database.php` sesuai dengan konfigurasi MySQL Anda:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'attendance_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sesuaikan dengan password MySQL Anda
```

### 4. Setup Permissions

```bash
# Berikan permission untuk folder uploads
chmod -R 777 uploads/
chmod -R 777 exports/
```

### 5. Akses Aplikasi

Buka browser dan akses:
```
http://localhost/attendance-app/
```

## 👥 Default User Accounts

### Admin Account
- Email: `admin@attendance.com`
- Password: `admin123`

### Employee Account
- Email: `john@attendance.com`
- Password: `employee123`

**⚠️ PENTING**: Segera ubah password default setelah instalasi pertama!

## 📁 Struktur Folder

```
attendance-app/
├── index.php                 # Landing page / Login
├── register.php              # Halaman register
├── logout.php                # Logout handler
├── config/
│   └── database.php          # Koneksi database
├── admin/
│   ├── dashboard.php         # Dashboard admin
│   ├── employees.php         # Kelola karyawan
│   ├── attendance.php        # Lihat semua absensi
│   ├── reports.php           # Laporan & filter
│   ├── export_pdf.php        # Export PDF
│   ├── export_excel.php      # Export Excel
│   └── export_csv.php        # Export CSV
├── employee/
│   ├── dashboard.php         # Dashboard karyawan
│   ├── attendance.php        # Clock-in/out
│   ├── history.php           # Riwayat absensi
│   └── profile.php           # Profil karyawan
├── includes/
│   ├── header.php            # Header template
│   ├── footer.php            # Footer template
│   └── functions.php         # Helper functions
├── assets/
│   ├── css/
│   │   └── style.css         # Custom styles
│   ├── js/
│   │   ├── app.js            # Main JavaScript
│   │   ├── camera.js         # Webcam handler
│   │   └── geolocation.js    # Geolocation handler
│   └── img/                  # Images & icons
├── uploads/
│   └── selfies/              # Foto selfie absensi
├── exports/                  # Folder untuk file export
├── database/
│   └── attendance_db.sql     # SQL schema
└── README.md                 # Dokumentasi
```

## 🗄️ Database Schema

### Tabel `users`
- id, name, email, password, role (admin/employee), department_id, phone, photo, created_at, updated_at

### Tabel `attendance`
- id, user_id, date, clock_in, clock_out, clock_in_photo, clock_out_photo, clock_in_location, clock_out_location, status (present/late/absent), notes, created_at

### Tabel `departments`
- id, name, description, created_at

## 💻 Teknologi Stack

- **Frontend**: HTML5, CSS3, JavaScript ES6, Bootstrap 5
- **Backend**: PHP 8.x (PDO untuk database)
- **Database**: MySQL
- **Libraries**: 
  - Bootstrap 5 (UI Framework)
  - Font Awesome (Icons)
  - SweetAlert2 (Notifications)
  - DataTables (Table dengan pagination & search)
  - jQuery (DOM manipulation)

## 🔐 Fitur Keamanan

- ✅ Password hashing dengan bcrypt
- ✅ Prepared statements untuk mencegah SQL injection
- ✅ Session management yang aman
- ✅ XSS protection dengan htmlspecialchars
- ✅ CSRF protection (dapat ditambahkan)
- ✅ Input validation (client-side dan server-side)

## 📱 Browser Support

- Chrome/Edge (Recommended)
- Firefox
- Safari
- Opera

## 🎯 Cara Penggunaan

### Untuk Karyawan:

1. **Login**: Masuk dengan email dan password
2. **Clock-in**: 
   - Klik menu "Absensi"
   - Aktifkan kamera dan ambil foto selfie
   - Izinkan akses lokasi
   - Klik "Clock-in Sekarang"
3. **Clock-out**: 
   - Klik menu "Absensi"
   - Ambil foto selfie clock-out
   - Klik "Clock-out Sekarang"
4. **Lihat Riwayat**: Menu "Riwayat" untuk melihat history absensi
5. **Edit Profil**: Menu "Profil" untuk update data pribadi

### Untuk Admin:

1. **Login**: Masuk sebagai admin
2. **Kelola Karyawan**: 
   - Menu "Karyawan" untuk tambah/edit/hapus karyawan
3. **Lihat Absensi**: 
   - Menu "Absensi" untuk melihat semua data absensi
   - Filter berdasarkan tanggal, karyawan, status
4. **Generate Laporan**:
   - Menu "Laporan"
   - Pilih filter periode, karyawan, departemen
   - Export ke PDF/Excel/CSV

## 🛠️ Troubleshooting

### Kamera tidak berfungsi:
- Pastikan browser mendukung WebRTC
- Izinkan akses kamera di browser
- Gunakan HTTPS untuk production

### Lokasi tidak terdeteksi:
- Izinkan akses lokasi di browser
- Pastikan GPS aktif di perangkat

### Database connection error:
- Periksa konfigurasi di `config/database.php`
- Pastikan MySQL service berjalan
- Periksa username dan password MySQL

## 📝 License

MIT License - Silakan gunakan untuk keperluan apapun.

## 👨‍💻 Developer

Developed by rahmatsyahdz

## 📧 Support

Jika ada pertanyaan atau masalah, silakan buat issue di repository ini.

---

**Happy Coding! 🚀**
