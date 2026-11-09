<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data - School Class Progress (SCP2)</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <!-- SIDEBAR -->
    @include('layouts.sidebar', ['user' => $user])

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <div class="content-wrapper">

            <header class="page-header">
                <div>
                    <div class="page-eyebrow">
                        <i class="bi bi-folder2-open"></i>
                        Waka Kurikulum Panel
                    </div>
                    <h1 class="page-title">Manajemen Data Sekolah</h1>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('accounts.index') }}" class="btn-primary-custom">
                        <i class="bi bi-people-fill me-1"></i>
                        <span>Buka Manajemen Pengguna</span>
                    </a>
                    <div class="academic-badge">
                        <i class="bi bi-calendar3"></i>
                        <span>Tahun Ajaran 2026/2027</span>
                    </div>
                </div>
            </header>

            <!-- Banner Utama -->
            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-database-fill-gear"></i>
                            MANAJEMEN DATA & PENGGUNA
                        </div>

                        <h2 class="hero-title">
                            Pusat Manajemen Data Sekolah
                        </h2>

                        <p class="hero-description">
                            Kelola data pokok peserta didik melalui fasilitas impor massal Excel/CSV, serta kelola seluruh akun pengguna (Waka, Guru, Wali Kelas, Guru BK, Siswa, dan Orang Tua) secara terpadu.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                </div>
            </section>

            @if(session('success'))
                <div class="alert-scp-success">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-scp-danger">
                    <i class="bi bi-exclamation-circle-fill fs-5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- 1. Bagian Impor Massal Siswa & Ortu -->
            <section class="content-card mb-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-light text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 10px;">Fitur 1</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-semibold" style="font-size: 10px;">Impor Massal</span>
                        </div>
                        <h2 class="fw-bold text-dark mb-2" style="font-size: 16px;">Impor Siswa dan Orang Tua dari Excel / CSV</h2>
                        <p class="text-muted mb-3" style="font-size: 12px; line-height: 1.6;">
                            Unduh template Excel (<code>.xlsx</code>) atau CSV (<code>.csv</code>), isi satu baris per siswa, lalu unggah kembali. Akun orang tua dibuat otomatis terpisah per anak dengan Login ID dari nama siswa berakhiran <code>_ortu</code>.
                        </p>
                        <ul class="text-muted mb-3 ps-3" style="font-size: 12px; line-height: 1.6;">
                            <li class="mb-1">Pastikan nama kelas di file sesuai dengan data rombel sekolah (misalnya: <code>X RPL 1</code>, <code>XI TKJ 2</code>).</li>
                            <li class="mb-1">Tidak perlu mengisi password saat impor — semua akun baru otomatis siap digunakan (password default: <code>password123</code>) tanpa paksaan ganti password saat login pertama.</li>
                            <li class="mb-1">Email pemulihan bersifat opsional (boleh dikosongkan).</li>
                            <li>File divalidasi seluruhnya terlebih dahulu. Jika ada kesalahan baris, tidak ada data yang disimpan sebagian.</li>
                        </ul>
                        <details class="mt-2 text-dark" style="font-size: 12px;">
                            <summary class="fw-semibold text-primary" style="cursor: pointer;">Arti kolom template Excel / CSV</summary>
                            <ul class="text-muted mt-2 ps-3" style="line-height: 1.6;">
                                <li>Wajib: <code>student_login_id</code> (NISN 10 digit), <code>student_name</code>, <code>class_name</code>, <code>major_code</code>, <code>parent_name</code>.</li>
                                <li>Opsional: <code>student_recovery_email</code> dan <code>parent_recovery_email</code>.</li>
                                <li>Satu siswa memiliki satu akun orang tua; Login ID akun itu dibuat otomatis dari nama siswa.</li>
                            </ul>
                        </details>
                    </div>
                    <div class="col-lg-5">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <a href="{{ route('master-data.students.import-template', ['format' => 'xlsx']) }}" class="btn-primary-custom flex-grow-1 justify-content-center py-2">
                                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Unduh Template Excel (.xlsx)
                                </a>
                                <a href="{{ route('master-data.students.import-template') }}" class="btn-outline-primary-custom flex-grow-1 justify-content-center py-2">
                                    <i class="bi bi-filetype-csv me-1"></i> Template CSV
                                </a>
                            </div>
                            <form action="{{ route('master-data.students.import') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-3" data-roster-upload-form>
                                @csrf
                                <div>
                                    <label for="student-roster" class="form-label fw-bold text-dark" style="font-size: 12px;">Unggah File Data Siswa (<code>.xlsx</code> / <code>.csv</code>)</label>
                                    <input id="student-roster" type="file" name="roster" class="form-control form-control-sm" accept=".xlsx,.csv,.txt,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" required data-roster-file-input>
                                    <input type="hidden" name="roster_data" data-roster-file-data>
                                    <input type="hidden" name="roster_name" data-roster-file-name>
                                    <div class="form-text text-muted" style="font-size: 11px;" data-roster-file-status>Mendukung format Microsoft Excel (<code>.xlsx</code>) atau <code>.csv</code>. Maksimal 5 MB.</div>
                                </div>
                                <button type="submit" class="btn-primary-custom w-100" data-roster-submit-btn>
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Impor Data Siswa & Orang Tua
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. Bagian Manajemen Pengguna (Kelola Akun) -->
            <section class="content-card">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-primary border border-primary-subtle px-2 py-0.5 fw-semibold" style="font-size: 10px;">Fitur 2</span>
                                <h2 class="section-title mb-0">Manajemen Pengguna</h2>
                            </div>
                            <p class="section-description mb-0">Kelola akun dan hak akses Guru, Wali Kelas, Guru BK, Siswa, dan Orang Tua.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('accounts.index') }}" class="btn-primary-custom">
                            <i class="bi bi-gear-wide-connected me-1"></i>
                            <span>Buka Halaman Kelola Akun</span>
                        </a>
                    </div>
                </div>

                <div class="row g-3 my-2">
                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 rounded-3 border bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold" style="font-size: 11px;">Akun Siswa</span>
                                <i class="bi bi-mortarboard text-primary fs-5"></i>
                            </div>
                            <div class="fs-4 fw-bold text-dark">{{ $students->count() }}</div>
                            <div class="text-muted small" style="font-size: 10px;">Terdaftar di rombel kelas</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 rounded-3 border bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold" style="font-size: 11px;">Akun Wali Kelas</span>
                                <i class="bi bi-person-badge text-success fs-5"></i>
                            </div>
                            <div class="fs-4 fw-bold text-dark">{{ $homeroomTeachers->count() }}</div>
                            <div class="text-muted small" style="font-size: 10px;">Login: Nama Kelas (cth: X RPL 1)</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 rounded-3 border bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold" style="font-size: 11px;">Akun Guru BK</span>
                                <i class="bi bi-heart-pulse text-danger fs-5"></i>
                            </div>
                            <div class="fs-4 fw-bold text-dark">6</div>
                            <div class="text-muted small" style="font-size: 10px;">Login: BK_1 s/d BK_6</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 rounded-3 border bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold" style="font-size: 11px;">Akun Orang Tua</span>
                                <i class="bi bi-people text-warning fs-5"></i>
                            </div>
                            <div class="fs-4 fw-bold text-dark">{{ $parents->count() }}</div>
                            <div class="text-muted small" style="font-size: 10px;">Terhubung dengan data anak</div>
                        </div>
                    </div>
                </div>

                <div class="p-3 mt-3 rounded-3 border bg-white">
                    <h4 class="fw-bold text-dark mb-2" style="font-size: 13px;">
                        <i class="bi bi-info-circle text-primary me-1"></i> Standarisasi Akun Pengguna SCP2
                    </h4>
                    <div class="row g-3 text-muted" style="font-size: 12px; line-height: 1.6;">
                        <div class="col-md-6">
                            <ul class="mb-0 ps-3">
                                <li><strong>Guru Bimbingan Konseling (BK):</strong> Menggunakan 6 akun tetap dengan Login Key <code>BK_1</code> sampai <code>BK_6</code>.</li>
                                <li><strong>Wali Kelas:</strong> Menggunakan Login Key sesuai nama kelas binaan (contoh: <code>X RPL 1</code>, <code>XI TKJ 2</code>, dll) dengan pencocokan nama yang fleksibel.</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0 ps-3">
                                <li><strong>Siswa:</strong> Login menggunakan NISN atau Login ID unik hasil impor data siswa.</li>
                                <li><strong>Orang Tua:</strong> Login menggunakan akun terpisah yang otomatis terhubung ke data putra/putrinya.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-roster-upload-form]').forEach(function (form) {
            const fileInput = form.querySelector('[data-roster-file-input]');
            const dataInput = form.querySelector('[data-roster-file-data]');
            const nameInput = form.querySelector('[data-roster-file-name]');
            const statusText = form.querySelector('[data-roster-file-status]');
            const submitBtn = form.querySelector('[data-roster-submit-btn]');

            if (!fileInput || !dataInput || !nameInput) return;

            fileInput.addEventListener('change', function () {
                const file = fileInput.files && fileInput.files[0];
                if (!file) {
                    dataInput.value = '';
                    nameInput.value = '';
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file maksimal 5 MB.');
                    fileInput.value = '';
                    dataInput.value = '';
                    nameInput.value = '';
                    return;
                }

                nameInput.value = file.name;
                if (submitBtn) submitBtn.disabled = true;
                if (statusText) statusText.textContent = 'Memproses file ' + file.name + '...';

                const reader = new FileReader();
                reader.onload = function (e) {
                    dataInput.value = e.target.result || '';
                    if (submitBtn) submitBtn.disabled = false;
                    if (statusText) statusText.textContent = 'File siap diimpor: ' + file.name;
                };
                reader.onerror = function () {
                    if (submitBtn) submitBtn.disabled = false;
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
</body>
</html>
