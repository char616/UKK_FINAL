# Dokumentasi REST API - School Class Progress (SCP2)

Dokumentasi ini menjelaskan seluruh endpoint REST API (JSON) pada sistem **School Class Progress (SCP2)** serta panduan cara mengujinya menggunakan **Postman**, **Thunder Client (VS Code)**, maupun **Terminal / PowerShell**.

---

## 1. Informasi Dasar (Base URL & Headers)

- **Base URL Lokal:** `http://127.0.0.1:8000/api` (atau `http://localhost:8000/api`)
- **Headers Wajib untuk Semua Request `POST`:**
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Koleksi Postman Siap Pakai:**
  Anda dapat langsung mengimpor file [`SCP2_API_Postman_Collection.json`](./SCP2_API_Postman_Collection.json) ke aplikasi **Postman** atau **Thunder Client**.

---

## 2. Daftar Endpoint API

### 2.1. Cek Status Server & Konfigurasi (`GET /api/health`)

Memeriksa apakah server API aktif, putaran blok jadwal yang sedang berjalan, dan driver mailer yang aktif.

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/health`
- **Contoh Response (`200 OK`):**
  ```json
  {
    "status": "ok",
    "app": "SCP2 System",
    "active_round": 1,
    "mail_mailer": "failover",
    "timestamp": "2026-10-31T11:45:00+07:00"
  }
  ```

---

### 2.2. Login Pengguna (`POST /api/auth/login`)

Memvalidasi `login_key` (misalnya `WAKA001`, `GURU_1`, `WALI_10_RPL_1`, `BK_1`, NISN 10 digit untuk Siswa, atau `nama_siswa_ortu` untuk Orang Tua) beserta `password` (default: `password123`).

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/auth/login`
- **Request Body (JSON):**
  ```json
  {
    "login_key": "WAKA001",
    "password": "password123"
  }
  ```
- **Contoh Response (`200 OK`):**
  ```json
  {
    "status": "success",
    "message": "Login berhasil.",
    "data": {
      "id": 1,
      "name": "Bapak Waka Kurikulum",
      "login_key": "WAKA001",
      "email": "waka@school.test",
      "recovery_email": null,
      "role": "waka",
      "class": null
    }
  }
  ```

---

### 2.3. Test Kirim Email OTP Reset Password (`POST /api/mail/test-otp`)

Menguji pengiriman email kode OTP 6 digit menggunakan Mailable `App\Mail\OtpResetPassword`.

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/mail/test-otp`
- **Request Body (JSON):**
  ```json
  {
    "email": "target.pengujian@gmail.com",
    "login_key": "WAKA001",
    "name": "Bapak Waka Kurikulum"
  }
  ```
- **Contoh Response (`200 OK`):**
  ```json
  {
    "status": "success",
    "message": "Email OTP berhasil dikirim ke target.pengujian@gmail.com",
    "data": {
      "recipient_email": "target.pengujian@gmail.com",
      "recipient_name": "Bapak Waka Kurikulum",
      "otp_preview": "482910",
      "expires_in_minutes": 10,
      "mailer": "failover"
    }
  }
  ```

---

### 2.4. Test Kirim Email Notifikasi Kehadiran Siswa (`POST /api/mail/test-attendance`)

Menguji pengiriman email notifikasi ketidakhadiran siswa (`Izin`, `Sakit`, `Alpa`, atau `Hadir`) menggunakan Mailable `App\Mail\StudentAttendanceNotification`.

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/mail/test-attendance`
- **Request Body (JSON):**
  ```json
  {
    "email": "orangtua.siswa@gmail.com",
    "recipient_name": "Budi Santoso (Orang Tua)",
    "student_name": "Ahmad Fauzi",
    "class_name": "10 RPL 1",
    "subject_name": "Pemrograman Berorientasi Objek",
    "date": "2026-10-31",
    "status": "Sakit"
  }
  ```
- **Contoh Response (`200 OK`):**
  ```json
  {
    "status": "success",
    "message": "Email notifikasi kehadiran berhasil dikirim ke orangtua.siswa@gmail.com",
    "data": {
      "recipient_email": "orangtua.siswa@gmail.com",
      "recipient_name": "Budi Santoso (Orang Tua)",
      "student_name": "Ahmad Fauzi",
      "class_name": "10 RPL 1",
      "subject_name": "Pemrograman Berorientasi Objek",
      "date": "2026-10-31",
      "attendance_status": "Sakit",
      "mailer": "failover"
    }
  }
  ```

---

### 2.5. Daftar Master Data Kelas & Mata Pelajaran (`GET /api/master-data`)

Mengambil daftar seluruh kelas beserta jurusan dan wali kelasnya, serta daftar mata pelajaran.

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/master-data`

---

### 2.6. Daftar Jadwal Pelajaran (`GET /api/schedules`)

Mengambil daftar jadwal pelajaran pada putaran aktif (atau filter dengan query `?round=1` / `?round=2`).

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/schedules?round=1`

---

### 2.7. Daftar Presensi Siswa (`GET /api/attendances`)

Mengambil daftar riwayat presensi siswa (dapat difilter dengan `?date=2026-10-31` atau `?student_id=5`).

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/attendances`

---

### 2.8. Daftar & Pengajuan Izin / Sakit (`GET /api/leaves` & `POST /api/leaves`)

#### A. Ambil Daftar Pengajuan Izin (`GET /api/leaves`)
- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/leaves`

#### B. Ajukan Izin Baru oleh Siswa atau Orang Tua (`POST /api/leaves`)
- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/leaves`
- **Request Body (JSON):**
  ```json
  {
    "actor_login_key": "2026100001",
    "type": "Sakit",
    "start_date": "2026-10-31",
    "end_date": "2026-11-01",
    "reason": "Demam tinggi dan istirahat sesuai surat keterangan dokter"
  }
  ```
  *(Jika diajukan oleh **Orang Tua**, isi `"actor_login_key": "ahmad_fauzi_ortu"` dan opsional `"student_id": 15`).*

---

### 2.9. Daftar Jurnal Mengajar (`GET /api/journals`)

Mengambil daftar jurnal mengajar harian yang telah diinput oleh guru.

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/journals`

---

## 3. Cara Cepat Test API

### Cara 1: Menggunakan Postman / Thunder Client (Paling Mudah)
1. Pastikan server Laravel menyala:
   ```powershell
   php artisan serve
   ```
2. Buka aplikasi **Postman** (atau ekstensi **Thunder Client** di VS Code).
3. Klik tombol **Import** -> Pilih file [`SCP2_API_Postman_Collection.json`](./SCP2_API_Postman_Collection.json) di folder proyek ini.
4. Pilih salah satu request (misalnya **Test Kirim Email OTP** atau **Test Kirim Email Kehadiran**), lalu klik **Send**.

### Cara 2: Menggunakan PowerShell (Langsung dari Terminal)
Jalankan perintah berikut di terminal PowerShell Anda saat `php artisan serve` aktif:

```powershell
# 1. Cek Health API
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/health" -Method GET

# 2. Test Kirim Email OTP Reset Password
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/mail/test-otp" -Method POST -ContentType "application/json" -Body '{"email":"siswa@contoh.sch.id","login_key":"WAKA001","name":"Bapak Waka"}'

# 3. Test Kirim Email Notifikasi Kehadiran Siswa
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/mail/test-attendance" -Method POST -ContentType "application/json" -Body '{"email":"ortu@contoh.sch.id","recipient_name":"Budi Santoso","student_name":"Ahmad Fauzi","class_name":"10 RPL 1","subject_name":"PBO","status":"Sakit"}'
```

### Cara 3: Menjalankan Pengujian Otomatis API (PHPUnit)
Tanpa perlu menyalakan browser atau Postman, Anda bisa langsung mengetes seluruh endpoint API sekaligus dari terminal:
```powershell
php vendor/phpunit/phpunit/phpunit tests/Feature/ScpApiTest.php
```

