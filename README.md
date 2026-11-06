<<<<<<< HEAD
# School Class Progress (SCP)

SCP adalah aplikasi berbasic Laravel untuk mengelola data sekolah, jadwal pelajaran, presensi, pengajuan izin, jurnal mengajar, notifikasi, dan rekap kehadiran.

Rekap presensi dapat difilter berdasarkan rentang tanggal dan kelas, menampilkan rincian catatan, serta diunduh sebagai CSV. Wali Kelas hanya dapat melihat kelas yang ditugaskan kepadanya.
Kotak masuk notifikasi menampilkan 25 pesan per halaman dan hanya memuat milik pengguna yang sedang login.

## Fitur dan alur kerja

- Enam role: WAKA, BK, Wali Kelas, Guru, Siswa, dan Orang Tua/Wali; setiap role memiliki dashboard dan batas akses masing-masing.
- WAKA mengelola akun, jurusan, kelas, wali kelas, penempatan siswa dan orang tua, mata pelajaran, serta ruangan.
- WAKA dapat mengimpor hingga 500 siswa sekaligus melalui template CSV UTF-8 yang bisa diedit di Excel. Pemisah kolom koma atau titik koma diterima. Satu baris memuat satu siswa; akun orang tua dibuat per siswa dengan Login ID dari nama anak dan akhiran `_ortu`. Seluruh file divalidasi sebelum satu transaksi menyimpan akun dan relasi. Akun baru memakai password sementara yang dimasukkan di formulir impor; siswa tanpa email menerima alamat internal `.invalid` yang tidak dapat menerima pesan. Password awal yang sama dipakai untuk seluruh akun baru di batch dan wajib diganti oleh setiap pengguna saat login pertama.
- Target jumlah akun aktif: 1 WAKA, 6 BK, 10 Guru sementara, dan 6 Wali Kelas. Target Wali Kelas untuk kelas 10, 11, dan 12 RPL 1 serta RPL 2; satu akun Wali Kelas hanya boleh ditugaskan ke satu kelas. Sistem menampilkan progres target di Kelola Akun tetapi tidak membuat akun sekolah fiktif.
- Pengguna wajib mengganti password sementara pada login pertama dan dapat menggantinya lagi lewat Pengaturan Akun. Email pemulihan harus didaftarkan dan diverifikasi melalui OTP setelah login; lupa password meminta Login ID serta email terverifikasi yang cocok. Email yang tidak diverifikasi tidak dapat menerima OTP reset.
- Login ID siswa harus berupa NISN 10 digit. Login ID Orang Tua dibuat per anak dengan format nama anak yang memakai garis bawah dan akhiran `_ortu`. Akun WAKA kedua ditolak; target aktif BK, Guru, dan Wali Kelas ditampilkan di Kelola Akun.
- Guru dapat mengirim beberapa jadwal sekaligus. Jadwal langsung tercatat dan terlihat oleh WAKA; WAKA dapat mengunci atau membuka pengiriman jadwal berikutnya. Sistem menolak bentrok guru, kelas, atau ruangan. Saat ini belum ada status persetujuan terpisah per jadwal.
- Guru mengisi presensi per jadwal dan tanggal. Catatan yang dikirim ulang memperbarui catatan harian yang sama, bukan membuat duplikat.
- Siswa mengajukan izin/sakit; Wali Kelas yang ditugaskan menyetujui atau menolak. Bukti disimpan privat.
- Guru membuat jurnal pada jadwalnya; bukti jurnal disimpan privat dan unduhannya dibatasi.
- BK, WAKA, dan Wali Kelas dapat melihat laporan presensi sesuai batas akses; laporan bisa diekspor sebagai CSV.
- Notifikasi dan audit log mencatat tindakan penting dan pesan per pengguna.
- Pemulihan password menggunakan OTP melalui email. Pengiriman email nyata memerlukan SMTP yang dikonfigurasi.

## Relasi data utama

- `users.role_id` mengarah ke `roles.id`; login ID dan email pengguna unik.
- `users.class_id` menghubungkan siswa ke `classes`; `classes.major_id` menghubungkan kelas ke `majors`, dan `classes.homeroom_teacher_id` menunjuk akun Wali Kelas.
- `parent_student` menghubungkan Orang Tua dan Siswa dalam relasi many-to-many.
- `schedules` menghubungkan guru (`users`), kelas, mata pelajaran, dan ruangan.
- `attendances` menghubungkan jadwal, siswa, dan pencatat; kombinasi jadwal-siswa-tanggal unik.
- `leave_requests` menghubungkan siswa dan pemroses; `teaching_journals` menghubungkan guru dan jadwal.
- Notifikasi, audit log, OTP reset password, sesi, serta pengunci jadwal terhubung ke akun atau data terkait.

Foreign key dan indeks menjaga konsistensi data utama. Data sekolah tetap harus dilengkapi WAKA; khususnya setiap siswa perlu ditempatkan ke kelas dan dihubungkan ke akun Orang Tua yang benar.

Tabel `cycles` sudah ada di skema, tetapi pengelolaan semester/siklus akademik belum terhubung ke alur aplikasi. Putaran jadwal saat ini diisi langsung pada jadwal.

## Menjalankan aplikasi

Persyaratan: PHP dan Composer sesuai versi pada `composer.json`, Node.js/npm, serta MySQL atau database lain yang didukung Laravel.

1. Salin `.env.example` menjadi `.env`, lalu isi koneksi database.
2. Buat application key untuk instalasi baru:

   ```bash
   php artisan key:generate
   ```

3. Pasang dependency dan jalankan migrasi:

   ```bash
   composer install
   php artisan migrate
   php artisan scp:create-initial-waka
   npm install
   npm run build
   php artisan serve
   ```

   Command bootstrap meminta data WAKA dan password secara interaktif; password tidak dimasukkan sebagai argumen terminal. Command ini juga menyiapkan enam role sistem dan menolak membuat WAKA kedua. Jangan jalankan `php artisan db:seed` pada produksi karena seeder membuat akun demo dengan password bersama. Untuk pengembangan lokal saja, jalankan seeder setelah membuat database lokal yang aman.

   Migrasi menambahkan kolom relasi siswa-kelas, siswa-orang tua, dan wali kelas. Jangan gunakan `migrate:fresh` pada database yang berisi data yang ingin dipertahankan.
   Migrasi integritas role menolak pemasangan foreign key bila ada pengguna yang menunjuk role yang tidak tersedia; perbaiki penugasan role tersebut sebelum mengulang migrasi.
   Migrasi presensi menolak berjalan jika ada catatan ganda untuk siswa, jadwal, dan tanggal yang sama; tinjau dan selesaikan data tersebut secara manual sebelum mengulang migrasi. Migrasi tidak menghapus catatan presensi.
   Jika aplikasi sudah pernah menerima bukti izin atau jurnal yang tersimpan di disk publik, jalankan sekali setelah migrasi untuk memindahkan file yang ada ke penyimpanan privat:

   ```bash
   php artisan scp:move-leave-attachments-private
   php artisan scp:move-journal-attachments-private
   ```

   File bukti izin dan jurnal baru langsung disimpan privat. Unduh bukti lama dan baru harus melalui halaman masing-masing setelah pengguna lolos pemeriksaan akses.

## Akun demo

Seeder menyediakan akun demo untuk role WAKA, BK, Wali Kelas, Guru, Siswa, dan Orang Tua. Login menggunakan `login_key` yang didefinisikan di `database/seeders/DatabaseSeeder.php`; password awal semua akun demo adalah `password123` dan wajib diganti pada login pertama. Jangan gunakan akun demo di lingkungan produksi.

Setelah login sebagai WAKA:

1. Buka **Kelola Akun** untuk membuat akun guru, wali kelas, siswa, dan orang tua. Email akun boleh dikosongkan; sistem akan membuat alamat internal yang tidak dapat menerima email. Login ID dan role akun tidak dapat diubah setelah dibuat; WAKA dapat menonaktifkan akun atau memberi password sementara baru.
2. Buka **Master Data Sekolah**.
3. Buat data jurusan dan kelas, lalu gunakan **Impor massal** untuk mengunduh template dan mengisi satu baris per siswa. Setiap anak mendapat akun Orang Tua tersendiri dengan Login ID otomatis `nama_anak_ortu`; email pemulihan boleh kosong.
4. Isi password sementara minimal 12 karakter di formulir impor dan unggah CSV UTF-8. Kredensial sementara yang sama dipakai bagi akun baru pada batch tersebut; bagikan secara pribadi. Setiap pengguna wajib menggantinya sendiri saat login pertama, lalu dapat mengganti password kapan saja lewat **Pengaturan Akun**. Akun yang sudah ada dipakai kembali tanpa mengganti password; kelas siswa diperbarui dan relasi orang tua ditambahkan tanpa duplikasi.
5. Tetapkan Wali Kelas untuk setiap kelas.
6. Jika ada kesalahan validasi, perbaiki baris yang dilaporkan dan unggah lagi; tidak ada sebagian data yang disimpan.
7. Baru setelah penempatan kelas dan wali lengkap, pengajuan izin akan dikirim ke Wali Kelas yang menangani kelas siswa.

Kolom wajib template adalah `student_login_id` (NISN 10 digit), `student_name`, `class_name`, `major_code`, dan `parent_name`. Kolom `student_recovery_email` serta `parent_recovery_email` opsional dan belum dianggap terverifikasi sampai pemilik akun memverifikasikannya. Login orang tua dibuat sebagai slug nama anak ditambah `_ortu`, sehingga setiap anak memiliki akun orang tua tersendiri. Gunakan kode jurusan serta nama kelas yang sudah terdaftar.

Penugasan tersebut juga membatasi data yang dapat dilihat Wali Kelas dan Orang Tua serta menentukan siswa yang muncul pada form presensi Guru.

## Email OTP

Atur variabel `MAIL_*` di `.env` ke SMTP provider yang benar agar OTP verifikasi email dan reset password dapat dikirim. Umumnya yang diperlukan adalah konfigurasi SMTP dan password aplikasi/provider, bukan API key terpisah; ikuti dokumentasi provider email yang dipakai. Konfigurasi contoh pada `.env.example` bukan kredensial SMTP aktif. Jangan menyimpan password atau kredensial provider di repository.

Email pemulihan diatur dari **Pengaturan Akun** setelah password sementara diganti. OTP verifikasi berlaku 10 menit dan email baru dapat dipakai untuk reset hanya setelah kode benar. Form lupa password meminta Login ID dan email terverifikasi yang terdaftar; respons tetap sama bila akun tidak cocok agar tidak membocorkan keberadaan akun.

Login dibatasi 5 percobaan per menit per identitas/IP dan 300 permintaan per menit per IP. Permintaan OTP reset dibatasi 3 kali per jam per identitas/IP serta 60 kali per jam per IP; verifikasi OTP dibatasi 5 kali per menit per akun/IP. Pengiriman OTP email akun dibatasi 3 kali per jam per akun dan 20 kali per jam per IP; verifikasi email dibatasi 5 kali per menit per akun. Jika aplikasi berjalan di beberapa instance, gunakan cache bersama agar batas berlaku konsisten.

## Pemeriksaan

Jalankan pengujian aplikasi dengan:

```bash
php artisan test
```
=======
# scpprogress
>>>>>>> origin/main
