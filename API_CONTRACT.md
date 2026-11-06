# API CONTRACT — SCP2 System (Backend REST API untuk Flutter)

Dokumen ini adalah kontrak resmi REST API **SCP2 System (School Control & Presence System)** yang dirancang khusus agar aplikasi mobile **Flutter** memiliki fitur, alur bisnis, hak akses (6 Role), dan struktur data yang **100% identik** dengan aplikasi Web (Blade).

---

## 1. Konvensi Umum & Konfigurasi Dasar

### Base URL
- **Local Development (Android Emulator):** `http://10.0.2.2:8000/api`
- **Local Development (iOS Simulator / Desktop / Web):** `http://127.0.0.1:8000/api`
- **Laragon Virtual Host:** `http://scp2-system.test/api`

### Standard Headers
Setiap request ke API **wajib** menyertakan header berikut:

| Header | Value | Keterangan |
|---|---|---|
| `Accept` | `application/json` | Wajib di semua request agar response selalu JSON |
| `Content-Type` | `application/json` atau `multipart/form-data` | Gunakan `multipart/form-data` saat upload file |
| `Authorization` | `Bearer <plainTextToken>` | Wajib untuk semua endpoint terproteksi (`auth:sanctum`) |

### Format Standar Response JSON
Seluruh endpoint menggunakan envelope JSON yang konsisten:

#### Response Sukses (`200 OK` / `201 Created`)
```json
{
  "status": "success",
  "message": "Pesan keberhasilan operasi.",
  "data": { ... }
}
```

#### Response Error Validasi (`422 Unprocessable Entity`)
```json
{
  "status": "error",
  "message": "Pesan error validasi pertama yang ditemukan.",
  "errors": {
    "nama_field": [
      "Detail pesan kesalahan validasi pada field tersebut."
    ]
  },
  "data": null
}
```

#### Response Error Autentikasi / Otorisasi (`401 Unauthorized` / `403 Forbidden` / `404 Not Found`)
```json
{
  "status": "error",
  "message": "Unauthenticated. Token tidak valid atau belum dikirim.",
  "data": null
}
```

> **Catatan Penting `must_change_password`:**
> Jika akun baru dibuat oleh WAKA (`must_change_password: true`), maka saat mengakses endpoint selain `/api/auth/profile`, `/api/auth/logout`, dan `/api/settings/account/*`, server akan mengembalikan HTTP `403`:
> ```json
> {
>   "status": "error",
>   "message": "Silakan ganti password sementara sebelum menggunakan sistem.",
>   "data": {
>     "must_change_password": true
>   }
> }
> ```
> Di Flutter, arahkan pengguna ke layar **Ganti Password** (`PUT /api/settings/account/password`) terlebih dahulu.

### Daftar 6 Role Pengguna (`role.name`)
1. `waka` — Wakil Kepala Sekolah Bidang Kurikulum
2. `guru` — Guru Mata Pelajaran
3. `wali_kelas` — Wali Kelas
4. `bk` — Guru Bimbingan Konseling (BK)
5. `siswa` — Peserta Didik / Siswa (Login ID wajib 10 digit NISN)
6. `orang_tua` — Orang Tua / Wali Siswa (Login ID berakhiran `_ortu`, contoh: `ahmad_fauzi_ortu`)

---

## 2. Daftar Lengkap Endpoint API

---

### A. MODUL AUTENTIKASI & PROFIL (SANCTUM BEARER TOKEN)

#### A.1. Health Check Server
- **URL:** `/api/health`
- **Method:** `GET`
- **Auth Required:** Tidak
- **Response `200 OK`:**
```json
{
  "status": "ok",
  "app": "Laravel",
  "active_round": 1,
  "mail_mailer": "smtp",
  "timestamp": "2026-10-31T08:00:00+07:00"
}
```

#### A.2. Login (Mendapatkan Bearer Token)
- **URL:** `/api/auth/login`
- **Method:** `POST`
- **Auth Required:** Tidak
- **Request Body (`application/json`):**
```json
{
  "login_key": "WAKA001",
  "password": "password123",
  "device_name": "flutter-android"
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `login_key` | `string` | Ya | Login ID / NIP / NISN / Username Ortu |
| `password` | `string` | Ya | Password akun |
| `device_name` | `string` | Tidak | Nama perangkat untuk label token Sanctum (default: `flutter-mobile`) |

- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Login berhasil.",
  "data": {
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789abcd12345678",
    "token_type": "Bearer",
    "id": 1,
    "name": "Bapak Waka",
    "login_key": "WAKA001",
    "email": "waka@school.test",
    "recovery_email": "waka.recovery@gmail.com",
    "role": "waka",
    "class": null,
    "must_change_password": false,
    "user": {
      "id": 1,
      "name": "Bapak Waka",
      "login_key": "WAKA001",
      "email": "waka@school.test",
      "recovery_email": "waka.recovery@gmail.com",
      "recovery_email_verified_at": "2026-10-31T08:00:00+07:00",
      "role_id": 1,
      "role": {
        "id": 1,
        "name": "waka",
        "display_name": "WAKA"
      },
      "role_name": "waka",
      "class_id": null,
      "school_class": null,
      "homeroom_classes": [],
      "students": [],
      "parents": [],
      "is_active": true,
      "must_change_password": false
    }
  }
}
```

#### A.3. Register Akun Mobile
- **URL:** `/api/auth/register`
- **Method:** `POST`
- **Auth Required:** Tidak
- **Request Body (`application/json`):**
```json
{
  "name": "Ahmad Fauzi",
  "login_key": "2026100099",
  "email": "ahmad.fauzi@school.test",
  "recovery_email": "ahmad.fauzi@gmail.com",
  "role": "siswa",
  "class_id": 1,
  "password": "PasswordBaru123!",
  "password_confirmation": "PasswordBaru123!",
  "device_name": "flutter-android"
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `name` | `string` | Ya | Nama lengkap pengguna |
| `login_key` | `string` | Ya | Unik. Jika `siswa`: wajib 10 digit angka. Jika `orang_tua`: wajib diakhiri `_ortu` |
| `email` | `string` | Tidak | Email akun (opsional) |
| `recovery_email` | `string` | Tidak | Email pemulihan untuk OTP |
| `role` | `string` | Tidak | `siswa` (default), `orang_tua`, `guru`, `wali_kelas`, `bk` |
| `class_id` | `integer` | Tidak | ID kelas jika role `siswa` |
| `password` | `string` | Ya | Minimal 12 karakter |
| `password_confirmation` | `string` | Ya | Harus sama dengan `password` |
| `device_name` | `string` | Tidak | Nama perangkat (default: `flutter-mobile`) |

- **Response `201 Created`:**
```json
{
  "status": "success",
  "message": "Registrasi berhasil.",
  "data": {
    "token": "2|xyz987654321...",
    "token_type": "Bearer",
    "user": {
      "id": 15,
      "name": "Ahmad Fauzi",
      "login_key": "2026100099",
      "role_name": "siswa",
      "class_id": 1,
      "is_active": true,
      "must_change_password": false
    }
  }
}
```

#### A.4. Get Profile (Data Pengguna yang Sedang Login)
- **URL:** `/api/auth/profile`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Profil pengguna berhasil diambil.",
  "data": {
    "id": 5,
    "name": "Ahmad Fauzi",
    "login_key": "2026100001",
    "email": "account-xxx@accounts.invalid",
    "recovery_email": "ahmad@gmail.com",
    "recovery_email_verified_at": "2026-10-31T08:00:00+07:00",
    "role_id": 5,
    "role": {
      "id": 5,
      "name": "siswa",
      "display_name": "Siswa"
    },
    "role_name": "siswa",
    "class_id": 1,
    "school_class": {
      "id": 1,
      "name": "10 RPL 1",
      "phase": "ganjil",
      "major_id": 1,
      "major": {
        "id": 1,
        "code": "RPL",
        "name": "Rekayasa Perangkat Lunak"
      },
      "homeroom_teacher": {
        "id": 3,
        "name": "Ibu Wali Kelas",
        "login_key": "WALI001"
      }
    },
    "homeroom_classes": [],
    "students": [],
    "parents": [
      {
        "id": 6,
        "name": "Budi Santoso",
        "login_key": "ahmad_fauzi_ortu",
        "recovery_email": "budi@gmail.com"
      }
    ],
    "is_active": true,
    "must_change_password": false
  }
}
```

#### A.5. Logout (Mencabut Bearer Token Saat Ini)
- **URL:** `/api/auth/logout`
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Logout berhasil. Token akses telah dihapus.",
  "data": null
}
```

---

### B. MODUL LUPA PASSWORD (OTP EMAIL PEMULIHAN)

#### B.1. Request Kirim OTP Reset Password
- **URL:** `/api/password/forgot`
- **Method:** `POST`
- **Auth Required:** Tidak
- **Request Body (`application/json`):**
```json
{
  "login_key": "2026100001",
  "email": "ahmad@gmail.com"
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `login_key` | `string` | Ya | Login ID akun |
| `email` | `string` | Ya | Email pemulihan (`recovery_email`) yang sudah terverifikasi |

- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Jika Login ID cocok dengan email pemulihan terverifikasi, kode OTP telah dikirim ke email tersebut.",
  "data": {
    "login_key": "2026100001",
    "email": "ahmad@gmail.com",
    "expires_in_minutes": 10,
    "otp_dispatched": true
  }
}
```

#### B.2. Verifikasi Kode OTP Reset Password
- **URL:** `/api/password/verify-otp`
- **Method:** `POST`
- **Auth Required:** Tidak
- **Request Body (`application/json`):**
```json
{
  "login_key": "2026100001",
  "otp": "123456"
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `login_key` | `string` | Ya | Login ID akun |
| `otp` | `string` | Ya | 6 digit angka OTP yang diterima di email |

- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "OTP valid. Silakan buat password baru.",
  "data": {
    "login_key": "2026100001",
    "verified": true
  }
}
```

#### B.3. Simpan Password Baru setelah OTP Terverifikasi
- **URL:** `/api/password/reset`
- **Method:** `POST`
- **Auth Required:** Tidak
- **Request Body (`application/json`):**
```json
{
  "login_key": "2026100001",
  "otp": "123456",
  "password": "PasswordBaruMinimal12!",
  "password_confirmation": "PasswordBaruMinimal12!"
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `login_key` | `string` | Ya | Login ID akun |
| `otp` | `string` | Tidak | 6 digit angka OTP (opsional jika sudah memanggil `/api/password/verify-otp`) |
| `password` | `string` | Ya | Password baru minimal 12 karakter |
| `password_confirmation` | `string` | Ya | Konfirmasi password baru |

- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Password berhasil diperbarui. Silakan login kembali.",
  "data": null
}
```

---

### C. MODUL PENGATURAN AKUN & VERIFIKASI EMAIL PEMULIHAN

#### C.1. Ambil Detail Pengaturan Akun
- **URL:** `/api/settings/account`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response `200 OK`:** Mengembalikan objek `UserResource` di dalam `data`.

#### C.2. Ganti Password Akun Sendiri
- **URL:** `/api/settings/account/password`
- **Method:** `PUT`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "current_password": "password123",
  "password": "PasswordBaruMinimal12!",
  "password_confirmation": "PasswordBaruMinimal12!"
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `current_password` | `string` | Ya | Password saat ini |
| `password` | `string` | Ya | Password baru (minimal 12 karakter, harus berbeda dari `current_password`) |
| `password_confirmation` | `string` | Ya | Harus sama dengan `password` |

- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Password berhasil diperbarui.",
  "data": {
    "id": 5,
    "name": "Ahmad Fauzi",
    "login_key": "2026100001",
    "must_change_password": false
  }
}
```

#### C.3. Kirim OTP Verifikasi Email Pemulihan (`recovery_email`)
- **URL:** `/api/settings/account/recovery-email`
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "recovery_email": "ahmad.pemulihan@gmail.com"
}
```
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Kode verifikasi dikirim ke email tersebut. Masukkan kode sebelum 10 menit.",
  "data": {
    "recovery_email": "ahmad.pemulihan@gmail.com",
    "expires_in_minutes": 10
  }
}
```

#### C.4. Verifikasi OTP Email Pemulihan
- **URL:** `/api/settings/account/recovery-email/verify`
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "otp": "654321"
}
```
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Email pemulihan berhasil diverifikasi dan dapat digunakan untuk menerima OTP reset password.",
  "data": {
    "id": 5,
    "recovery_email": "ahmad.pemulihan@gmail.com",
    "recovery_email_verified_at": "2026-10-31T08:15:00+07:00"
  }
}
```

---

### D. MODUL DASHBOARD (6 ROLE SCP)

#### D.1. Get Dashboard Data Sesuai Role Login
- **URL:** `/api/dashboard`
  *(Juga tersedia endpoint spesifik role: `/api/dashboard/waka`, `/api/dashboard/guru`, `/api/dashboard/wali-kelas`, `/api/dashboard/bk`, `/api/dashboard/siswa`, `/api/dashboard/orang-tua`)*
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Data dashboard berhasil diambil.",
  "data": {
    "user": {
      "id": 1,
      "name": "Bapak Waka",
      "login_key": "WAKA001",
      "role_name": "waka"
    },
    "role": "waka",
    "academic_year": "2026/2027",
    "active_round": 1,
    "schedule_locked": false,
    "unread_notifications_count": 0,
    "summary": {
      "majors_count": 3,
      "classes_count": 6,
      "subjects_count": 12,
      "rooms_count": 10,
      "active_users_count": 120,
      "active_round_schedules_count": 24,
      "today_journals_count": 5,
      "recent_audit_logs_count": 14
    }
  }
}
```

---

### E. MODUL MASTER DATA & IMPOR SISWA (KHUSUS ROLE `waka`)

#### E.1. Ambil Semua Master Data (Jurusan, Kelas, Mapel, Ruangan, Wali Kelas, Siswa, Orang Tua)
- **URL:** `/api/master-data`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Master data berhasil diambil.",
  "data": {
    "majors": [
      { "id": 1, "code": "RPL", "name": "Rekayasa Perangkat Lunak", "classes_count": 2 }
    ],
    "classes": [
      {
        "id": 1,
        "name": "10 RPL 1",
        "phase": "ganjil",
        "major_id": 1,
        "major": { "id": 1, "code": "RPL", "name": "Rekayasa Perangkat Lunak" },
        "homeroom_teacher_id": 3,
        "homeroom_teacher": { "id": 3, "name": "Ibu Wali", "login_key": "WALI001" },
        "students_count": 30,
        "schedules_count": 8
      }
    ],
    "subjects": [
      { "id": 1, "code": "PBO", "name": "Pemrograman Berorientasi Objek", "schedules_count": 4 }
    ],
    "rooms": [
      { "id": 1, "code": "LAB-RPL-1", "name": "Lab RPL 1", "capacity": 36, "rules": "Wajib lepas alas kaki" }
    ],
    "homeroom_teachers": [ ... ],
    "students": [ ... ],
    "parents": [ ... ]
  }
}
```

#### E.2. CRUD Jurusan (`majors`) — Role: `waka`
- **Tambah Jurusan:** `POST /api/master-data/majors`
  - Body: `{ "code": "TKJ", "name": "Teknik Komputer dan Jaringan" }`
- **Update Jurusan:** `PUT /api/master-data/majors/{major}`
  - Body: `{ "code": "TKJ", "name": "Teknik Komputer & Jaringan" }`
- **Hapus Jurusan:** `DELETE /api/master-data/majors/{major}`
  *(Mengembalikan `422` jika jurusan masih memiliki kelas)*

#### E.3. CRUD Kelas (`classes`) — Role: `waka`
- **Tambah Kelas:** `POST /api/master-data/classes`
  - Body:
  ```json
  {
    "name": "10 RPL 1",
    "major_id": 1,
    "phase": "ganjil",
    "homeroom_teacher_id": 3
  }
  ```
- **Update Kelas:** `PUT /api/master-data/classes/{class}`
  - Body: `{ "name": "10 RPL 1", "major_id": 1, "phase": "genap", "homeroom_teacher_id": 3 }`
- **Hapus Kelas:** `DELETE /api/master-data/classes/{class}`
- **Update Wali Kelas Saja:** `PUT /api/master-data/classes/{class}/homeroom-teacher`
  - Body: `{ "homeroom_teacher_id": 3 }` (boleh `null` untuk melepas wali kelas; 1 wali kelas maksimal 1 kelas)

#### E.4. CRUD Mata Pelajaran (`subjects`) — Role: `waka`
- **Tambah Mapel:** `POST /api/master-data/subjects`
  - Body: `{ "code": "MAT", "name": "Matematika" }`
- **Update Mapel:** `PUT /api/master-data/subjects/{subject}`
  - Body: `{ "code": "MAT", "name": "Matematika Wajib" }`
- **Hapus Mapel:** `DELETE /api/master-data/subjects/{subject}`

#### E.5. CRUD Ruangan (`rooms`) — Role: `waka`
- **Tambah Ruangan:** `POST /api/master-data/rooms`
  - Body: `{ "code": "LAB-1", "name": "Laboratorium 1", "capacity": 36, "rules": "Dilarang makan" }`
- **Update Ruangan:** `PUT /api/master-data/rooms/{room}`
  - Body: `{ "code": "LAB-1", "name": "Laboratorium Komputer 1", "capacity": 40, "rules": "Dilarang makan" }`
- **Hapus Ruangan:** `DELETE /api/master-data/rooms/{room}`

#### E.6. Penugasan Kelas & Orang Tua Siswa — Role: `waka`
- **URL:** `/api/master-data/students/{student}/assignment`
- **Method:** `PUT`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "class_id": 1,
  "parent_ids": [6, 7]
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `class_id` | `integer\|null` | Tidak | ID kelas siswa |
| `parent_ids` | `array<integer>` | Tidak | Array ID user dengan role `orang_tua` |

#### E.7. Download Template & Impor Siswa via Excel/CSV — Role: `waka`
- **Download Template:** `GET /api/master-data/students/import-template?format=xlsx` (atau `format=csv`)
- **Upload / Impor File Siswa & Orang Tua:** `POST /api/master-data/students/import`
  - **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
  - **Content-Type:** `multipart/form-data` atau `application/json` (jika menggunakan `roster_data` base64)
  - **Request Parameters:**
    - `roster` (`file`, `.xlsx` atau `.csv`, maks 5MB) **ATAU** `roster_data` (`string`, Data URL Base64) + `roster_name` (`string`)
    - `temporary_password` (`string`, opsional — jika dikosongkan otomatis menggunakan `password123` tanpa perlu validasi password rumit)
  - **Kolom Header Wajib di Excel/CSV:**
    `student_login_id,student_name,class_name,major_code,student_recovery_email,parent_name,parent_recovery_email`
  - **Response `201 Created`:**
  ```json
  {
    "status": "success",
    "message": "Berhasil mengimpor 2 data siswa dan orang tua (Password default: password123).",
    "data": {
      "imported_count": 2,
      "default_password": "password123",
      "items": [
        {
          "student_id": 10,
          "student_login_key": "2026100001",
          "student_name": "Ahmad Fauzi",
          "parent_id": 11,
          "parent_login_key": "ahmad_fauzi_ortu",
          "parent_name": "Budi Santoso"
        }
      ]
    }
  }
  ```

---

### F. MODUL MANAJEMEN AKUN PENGGUNA (KHUSUS ROLE `waka`)

#### F.1. Daftar Akun Pengguna
- **URL:** `/api/accounts?role=guru&search=budi&page=1`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Query Params (Opsional):** `role` (`waka`/`bk`/`wali_kelas`/`guru`/`siswa`/`orang_tua`), `search` (`string`), `page` (`int`), `per_page` (`int`)
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Daftar akun pengguna berhasil diambil.",
  "data": {
    "selected_role": "guru",
    "users": [ ... ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 25,
      "total": 4
    },
    "roles": [ ... ],
    "classes": [ ... ]
  }
}
```

#### F.2. Buat Akun Pengguna Baru
- **URL:** `/api/accounts`
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "name": "Bapak Guru Baru",
  "login_key": "GURU005",
  "email": "guru005@school.test",
  "recovery_email": "guru005@gmail.com",
  "role": "guru",
  "password": "PasswordSementara123!",
  "password_confirmation": "PasswordSementara123!"
}
```
- **Response `201 Created`:** Mengembalikan `UserResource` dengan `must_change_password: true`.

#### F.3. Update Akun Pengguna / Aktif-Nonaktifkan Akun
- **URL:** `/api/accounts/{user}`
- **Method:** `PUT`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "name": "Bapak Guru Baru",
  "email": "guru005@school.test",
  "recovery_email": "guru005@gmail.com",
  "is_active": true,
  "password": null,
  "password_confirmation": null
}
```

---

### G. MODUL JADWAL MENGAJAR, SIKLUS PUTARAN & LOCK SCHEDULE

#### G.1. Ambil Daftar Jadwal Mengajar
- **URL:** `/api/schedules?round=1&day=Senin`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Perilaku Otomatis Berdasarkan Role:**
  - `waka`: Melihat seluruh jadwal sekolah pada putaran tersebut.
  - `guru`: Melihat jadwal mengajar miliknya sendiri.
  - `wali_kelas`: Melihat jadwal kelas binaannya.
  - `siswa`: Melihat jadwal kelasnya sendiri.
  - `orang_tua`: Melihat jadwal kelas anak-anaknya.
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Daftar jadwal berhasil diambil.",
  "active_round": 1,
  "round": 1,
  "count": 1,
  "is_locked": false,
  "data": [
    {
      "id": 1,
      "teacher_id": 2,
      "class_id": 1,
      "subject_id": 1,
      "room_id": 1,
      "day": "Senin",
      "start_time": "07:00",
      "end_time": "08:30",
      "round": 1,
      "teacher": { "id": 2, "name": "Pak Guru", "login_key": "GURU001" },
      "class": {
        "id": 1,
        "name": "10 RPL 1",
        "major_id": 1,
        "major": { "id": 1, "code": "RPL", "name": "Rekayasa Perangkat Lunak" }
      },
      "subject": { "id": 1, "code": "PBO", "name": "Pemrograman Berorientasi Objek" },
      "room": { "id": 1, "code": "LAB-1", "name": "Lab RPL 1", "capacity": 36 }
    }
  ],
  "meta": {
    "active_round": 1,
    "is_locked": false,
    "classes": [ ... ],
    "subjects": [ ... ],
    "rooms": [ ... ],
    "reserved_schedules": [ ... ]
  }
}
```

#### G.2. Input Jadwal Mengajar Baru (Multi-Baris & Anti-Bentrok) — Role: `guru`
- **URL:** `/api/schedules`
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "schedules": [
    {
      "class_id": 1,
      "subject_id": 1,
      "room_id": 1,
      "day": "Senin",
      "start_time": "07:00",
      "end_time": "08:30",
      "round": 1
    },
    {
      "class_id": 2,
      "subject_id": 1,
      "room_id": 1,
      "day": "Selasa",
      "start_time": "08:30",
      "end_time": "10:00",
      "round": 1
    }
  ]
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `schedules` | `array` | Ya | Minimal 1 baris, maksimal 60 baris jadwal |
| `schedules.*.class_id` | `integer` | Ya | ID kelas (`classes.id`) |
| `schedules.*.subject_id` | `integer` | Ya | ID mata pelajaran (`subjects.id`) |
| `schedules.*.room_id` | `integer` | Ya | ID ruangan (`rooms.id`) |
| `schedules.*.day` | `string` | Ya | `Senin`, `Selasa`, `Rabu`, `Kamis`, `Jumat`, atau `Sabtu` |
| `schedules.*.start_time` | `string` | Ya | Format `HH:mm` (contoh `07:00`) |
| `schedules.*.end_time` | `string` | Ya | Format `HH:mm`, harus setelah `start_time` |
| `schedules.*.round` | `integer` | Ya | `1` atau `2` |

- **Validasi Bisnis Otomatis:**
  1. Menolak (`422`) jika jadwal sedang dikunci oleh WAKA (`is_locked: true`).
  2. Menolak (`422`) jika antar baris input terdapat bentrok waktu pada hari & putaran yang sama.
  3. Menolak (`422`) jika guru, kelas, atau ruangan sudah terpakai pada hari, irisan jam, dan putaran yang sama di database.

#### G.3. Ubah Putaran Jadwal Aktif (Putaran 1 / Putaran 2) — Role: `waka`
- **URL:** `/api/schedules/active-round`
- **Method:** `PUT`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "active_round": 2
}
```
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Putaran jadwal aktif berhasil diperbarui menjadi Putaran 2.",
  "data": {
    "active_round": 2
  }
}
```

#### G.4. Kunci / Buka Kunci Jadwal Mengajar (Toggle Lock) — Role: `waka`
- **URL:** `/api/schedules/toggle-lock`
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Jadwal pelajaran berhasil dikunci.",
  "data": {
    "is_locked": true,
    "locked_by": 1,
    "locked_at": "2026-10-31T09:00:00+07:00"
  }
}
```

---

### H. MODUL PRESENSI PER JAM PELAJARAN

#### H.1. Daftar Riwayat Presensi
- **URL:** `/api/attendances?date=2026-10-31&student_id=5&status=Sakit`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Role yang Diizinkan:** `bk`, `wali_kelas`, `guru`, `siswa`, `orang_tua`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Daftar presensi berhasil diambil.",
  "count": 1,
  "active_round": 1,
  "data": [
    {
      "id": 10,
      "schedule_id": 1,
      "student_id": 5,
      "date": "2026-10-31",
      "status": "Hadir",
      "notes": null,
      "recorded_by": 2,
      "student": { "id": 5, "name": "Ahmad Fauzi", "login_key": "2026100001", "class_id": 1 },
      "schedule": {
        "id": 1,
        "day": "Senin",
        "start_time": "07:00",
        "end_time": "08:30",
        "round": 1,
        "class": { "id": 1, "name": "10 RPL 1" },
        "subject": { "id": 1, "code": "PBO", "name": "Pemrograman Berorientasi Objek" },
        "teacher": { "id": 2, "name": "Pak Guru" }
      }
    }
  ],
  "teacher_schedules": [ ... ]
}
```

#### H.2. Ambil Daftar Siswa pada Jadwal untuk Pengisian Presensi — Role: `guru`
- **URL:** `/api/attendances/schedules/{schedule}` *(Alias: `/api/attendances/create/{schedule}`)*
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Detail jadwal dan daftar siswa presensi berhasil diambil.",
  "data": {
    "schedule": {
      "id": 1,
      "day": "Senin",
      "start_time": "07:00",
      "end_time": "08:30",
      "round": 1
    },
    "date": "2026-10-31",
    "students": [
      {
        "student": {
          "id": 5,
          "name": "Ahmad Fauzi",
          "login_key": "2026100001",
          "class_id": 1
        },
        "current_status": "Hadir",
        "notes": null
      }
    ]
  }
}
```

#### H.3. Simpan Presensi Siswa per Jam Pelajaran — Role: `guru`
- **URL:** `/api/attendances/schedules/{schedule}` *(Alias: `/api/attendances/store/{schedule}`)*
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "attendances": {
    "5": "Hadir",
    "8": "Sakit",
    "9": "Alpa"
  }
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `attendances` | `object/map` | Ya | Key = `student_id`, Value = `Hadir`, `Izin`, `Sakit`, atau `Alpa` |

- **Efek Otomatis:**
  - Menyimpan/memperbarui presensi (`upsert`) untuk tanggal hari ini.
  - Jika status bukan `Hadir` (`Izin`, `Sakit`, `Alpa`), sistem otomatis mengirim **Notifikasi In-App** ke siswa dan mengirim **Email Notifikasi Kehadiran (`StudentAttendanceNotification`)** ke siswa dan orang tua yang terhubung.

---

### I. MODUL PENGAJUAN IZIN / SAKIT & PERSETUJUAN WALI KELAS

#### I.1. Daftar Pengajuan Izin & Sakit
- **URL:** `/api/leaves?status=Pending`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Role yang Diizinkan:** `siswa`, `orang_tua`, `wali_kelas`, `waka`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Daftar pengajuan izin berhasil diambil.",
  "count": 1,
  "data": [
    {
      "id": 1,
      "student_id": 5,
      "type": "Sakit",
      "start_date": "2026-10-31",
      "end_date": "2026-11-01",
      "reason": "Demam tinggi disertai surat dokter",
      "attachment": "leave_attachments/leave_abc.jpg",
      "has_attachment": true,
      "attachment_download_url": "http://127.0.0.1:8000/api/leaves/1/attachment",
      "status": "Pending",
      "processed_by": null,
      "processed_at": null,
      "student": {
        "id": 5,
        "name": "Ahmad Fauzi",
        "login_key": "2026100001",
        "class_id": 1,
        "class_name": "10 RPL 1"
      }
    }
  ]
}
```

#### I.2. Data Form Pengajuan Izin (Daftar Anak untuk Orang Tua / Data Kelas Siswa)
- **URL:** `/api/leaves/create`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Role yang Diizinkan:** `siswa`, `orang_tua`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Data formulir pengajuan izin berhasil diambil.",
  "data": {
    "allowed_types": ["Izin", "Sakit"],
    "students": [
      {
        "id": 5,
        "name": "Ahmad Fauzi",
        "login_key": "2026100001",
        "class_id": 1
      }
    ]
  }
}
```

#### I.3. Buat Pengajuan Izin / Sakit Baru — Role: `siswa` atau `orang_tua`
- **URL:** `/api/leaves`
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Content-Type:** `multipart/form-data` (jika upload file `attachment`) atau `application/json` (jika menggunakan `attachment_data` base64 atau tanpa lampiran)
- **Request Body:**
```json
{
  "student_id": 5,
  "type": "Sakit",
  "start_date": "2026-10-31",
  "end_date": "2026-10-31",
  "reason": "Istirahat sakit sesuai surat keterangan dokter",
  "attachment_data": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=",
  "attachment_name": "surat-dokter.png"
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `student_id` | `integer` | Kondisional | Wajib jika `orang_tua` memiliki > 1 anak. Otomatis terisi jika `siswa` atau `orang_tua` dengan 1 anak |
| `type` | `string` | Ya | `Izin` atau `Sakit` |
| `start_date` | `string (date)` | Ya | Tanggal mulai (`YYYY-MM-DD`) |
| `end_date` | `string (date)` | Ya | Tanggal selesai (`YYYY-MM-DD`, `>= start_date`) |
| `reason` | `string` | Ya | Alasan pengajuan (maksimal 500 karakter) |
| `attachment` | `file` | Tidak | File bukti (`jpg`, `jpeg`, `png`, `pdf`, maks 5MB) |
| `attachment_data` | `string` | Tidak | Alternatif upload via Base64 Data URL |

- **Response `201 Created`:** Mengembalikan `LeaveRequestResource` dengan status `Pending` dan mengirim notifikasi ke Wali Kelas.

#### I.4. Setujui / Tolak Pengajuan Izin — Role: `wali_kelas`
- **URL:** `/api/leaves/{leaveRequest}/status`
- **Method:** `POST` *(atau `PUT`)*
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "status": "Approved"
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `status` | `string` | Ya | `Approved` (Disetujui) atau `Rejected` (Ditolak) |

- **Efek Otomatis:**
  - Jika `Approved`, sistem secara otomatis menyinkronkan/mengisi tabel `attendances` siswa tersebut pada seluruh jadwal kelas di rentang tanggal izin (`start_date` s/d `end_date`) pada putaran aktif dengan status `Izin`/`Sakit` beserta catatan alasannya.
  - Mengirim notifikasi ke Siswa dan seluruh Orang Tua siswa tersebut.

#### I.5. Unduh Lampiran Bukti Izin
- **URL:** `/api/leaves/{leaveRequest}/attachment`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`
- **Role yang Diizinkan:** `waka`, `wali_kelas` (kelas binaan), `siswa` (milik sendiri), `orang_tua` (milik anaknya)

---

### J. MODUL JURNAL MENGAJAR GURU & PENGINGAT WAKA

#### J.1. Daftar Jurnal Mengajar
- **URL:** `/api/journals?date=2026-10-31`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Role yang Diizinkan:** `guru`, `waka`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Daftar jurnal mengajar berhasil diambil.",
  "count": 1,
  "journal_date": "2026-10-31",
  "active_round": 1,
  "data": [
    {
      "id": 1,
      "teacher_id": 2,
      "schedule_id": 1,
      "date": "2026-10-31",
      "materi": "Konsep Inheritance dan Polymorphism",
      "activity_summary": "Praktikum pembuatan class turunan di Java",
      "summary": "Praktikum pembuatan class turunan di Java",
      "photo_proof": "journal_attachments/proof1.jpg",
      "has_attachment": true,
      "attachment_download_url": "http://127.0.0.1:8000/api/journals/1/attachment",
      "teacher": { "id": 2, "name": "Pak Guru", "login_key": "GURU001" },
      "schedule": {
        "id": 1,
        "day": "Senin",
        "start_time": "07:00",
        "end_time": "08:30",
        "round": 1,
        "class": { "id": 1, "name": "10 RPL 1" },
        "subject": { "id": 1, "code": "PBO", "name": "Pemrograman Berorientasi Objek" }
      }
    }
  ],
  "teacher_schedules": [ ... ],
  "schedules_without_journal": [ ... ]
}
```

#### J.2. Simpan Jurnal Mengajar Baru — Role: `guru`
- **URL:** `/api/journals`
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Content-Type:** `multipart/form-data` atau `application/json`
- **Request Body:**
```json
{
  "schedule_id": 1,
  "date": "2026-10-31",
  "materi": "Enkapsulasi & Access Modifier",
  "summary": "Diskusi studi kasus dan latihan koding di laboratorium.",
  "attachment_data": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII="
}
```
| Parameter | Tipe Data | Wajib | Keterangan |
|---|---|---|---|
| `schedule_id` | `integer` | Ya | ID jadwal mengajar milik guru pada putaran aktif |
| `date` | `string (date)` | Ya | Tanggal pelaksanaan KBM (`YYYY-MM-DD`) |
| `materi` | `string` | Ya | Pokok bahasan / materi (maks 255 karakter) |
| `summary` | `string` | Ya | Ringkasan kegiatan KBM |
| `attachment` | `file` | Tidak | Foto dokumentasi / PDF bukti KBM (`jpg`, `png`, `pdf`, maks 5MB) |
| `attachment_data` | `string` | Tidak | Alternatif unggah bukti via Base64 Data URL |

#### J.3. Perbarui Jurnal Mengajar — Role: `guru`
- **URL:** `/api/journals/{journal}`
- **Method:** `PUT` *(atau `POST` untuk kemudahan multipart di Flutter)*
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body:** Sama seperti `POST /api/journals` (`date`, `materi`, `summary`, `attachment` / `attachment_data`).

#### J.4. Kirim Pengingat Jurnal Mengajar yang Belum Diisi — Role: `waka`
- **URL:** `/api/journals/reminders`
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body (`application/json`):**
```json
{
  "date": "2026-10-31"
}
```
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Pengingat terkirim ke 2 guru. 2 guru masih memiliki jurnal yang perlu dilengkapi.",
  "data": {
    "date": "2026-10-31",
    "sent_count": 2,
    "teachers_missing_count": 2
  }
}
```

#### J.5. Unduh Lampiran Bukti Jurnal
- **URL:** `/api/journals/{journal}/attachment`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`
- **Role yang Diizinkan:** `waka`, `guru` (pemilik jurnal)

---

### K. MODUL LAPORAN REKAPITULASI PRESENSI (ROLE: `waka`, `bk`, `wali_kelas`)

#### K.1. Ambil Rekapitulasi & Statistik Laporan Presensi
- **URL:** `/api/reports/attendance?date_from=2026-10-01&date_to=2026-10-31&class_id=1&page=1`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Rekapitulasi laporan presensi berhasil diambil.",
  "data": {
    "filters": {
      "date_from": "2026-10-01",
      "date_to": "2026-10-31",
      "class_id": 1
    },
    "summary": {
      "Hadir": 120,
      "Izin": 4,
      "Sakit": 3,
      "Alpa": 1
    },
    "classes": [
      { "id": 1, "name": "10 RPL 1" }
    ],
    "attendances": [ ... ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 25,
      "total": 128
    }
  }
}
```

#### K.2. Ekspor Laporan Presensi ke CSV
- **URL:** `/api/reports/attendance/export?date_from=2026-10-01&date_to=2026-10-31&class_id=1`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`
- **Response `200 OK`:** File stream `text/csv; charset=UTF-8` (`rekap-presensi-YYYY-MM-DD-sampai-YYYY-MM-DD.csv`).

---

### L. MODUL NOTIFIKASI PENGGUNA & AUDIT LOG

#### L.1. Ambil Daftar Notifikasi Pengguna
- **URL:** `/api/notifications?mark_as_read=1&page=1`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Role yang Diizinkan:** `guru`, `wali_kelas`, `bk`, `siswa`, `orang_tua`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Daftar notifikasi berhasil diambil.",
  "data": {
    "notifications": [
      {
        "id": 1,
        "user_id": 5,
        "title": "Status Izin Diperbarui",
        "message": "Pengajuan Sakit Anda telah Disetujui (Approved)",
        "is_read": true,
        "created_at": "2026-10-31T09:30:00+07:00"
      }
    ],
    "unread_count": 0,
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 25,
      "total": 1
    }
  }
}
```

#### L.2. Tandai Semua Notifikasi Sudah Dibaca
- **URL:** `/api/notifications/read-all`
- **Method:** `POST`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Semua notifikasi berhasil ditandai telah dibaca.",
  "data": {
    "marked_read_count": 3
  }
}
```

#### L.3. Ambil Riwayat Audit Log Sistem — Role: `waka`
- **URL:** `/api/audit-logs?action=LOGIN_API&page=1`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Riwayat audit log berhasil diambil.",
  "data": {
    "logs": [
      {
        "id": 100,
        "user_id": 1,
        "action": "LOGIN_API",
        "description": "Pengguna berhasil login melalui REST API (flutter-android).",
        "user": {
          "id": 1,
          "name": "Bapak Waka",
          "login_key": "WAKA001",
          "role": "waka"
        },
        "created_at": "2026-10-31T08:00:00+07:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 50,
      "total": 75
    }
  }
}
```

---

### M. ENDPOINT UTILITAS PENGUJIAN EMAIL (DEVELOPMENT / QA)

1. **`POST /api/mail/test-otp`**
   - Body: `{ "email": "target@gmail.com", "login_key": "WAKA001", "name": "Bapak Waka" }`
2. **`POST /api/mail/test-attendance`**
   - Body: `{ "email": "target@gmail.com", "recipient_name": "Budi Santoso", "student_name": "Ahmad Fauzi", "class_name": "10 RPL 1", "subject_name": "PBO", "status": "Sakit" }`

