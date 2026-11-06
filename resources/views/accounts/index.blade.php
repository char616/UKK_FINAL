<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun Pengguna - SCP System</title>
    
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
                        <i class="bi bi-person-gear"></i>
                        Manajemen Pengguna
                    </div>
                    <h1 class="page-title">Kelola Akun Pengguna</h1>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('master-data.index') }}" class="btn-outline-primary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali ke Master Data</span>
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
                            <i class="bi bi-person-badge-fill"></i>
                            ADMINISTRASI AKUN
                        </div>

                        <h2 class="hero-title">
                            Pusat Pengelolaan Akun Sekolah
                        </h2>

                        <p class="hero-description">
                            Buat akun baru untuk seluruh role civitas akademika, kelola hak akses login, dan atur status keaktifan akun pengguna secara terintegrasi.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </section>

            <!-- Ringkasan Target Akun Aktif -->
            <div class="alert alert-info py-2.5 px-3 mb-4 small d-flex flex-wrap align-items-center justify-content-between gap-2 border-0 rounded-3" style="background: #eff6ff; color: #174ea6;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill fs-6"></i>
                    <span><strong>Target akun aktif:</strong> 1 WAKA, Guru, Wali Kelas, BK, Siswa, dan Orang Tua.</span>
                </div>
                <span class="badge bg-white text-primary border border-primary-subtle px-2.5 py-1">Kelas: 10, 11, 12 RPL 1 dan 2</span>
            </div>

            <!-- Notifikasi -->
            @if (session('success'))
                <div class="alert-scp-success">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-scp-danger">
                    <i class="bi bi-exclamation-circle-fill fs-5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Section: Buat Akun Sekolah -->
            <section class="content-card mb-4">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div>
                        <h2 class="section-title">Buat Akun Sekolah Baru</h2>
                        <p class="section-description">
                            Buat akun untuk setiap role. Siswa menggunakan NISN (10 digit). Login akun orang tua menggunakan nama siswa berakhiran <code>_ortu</code>.
                        </p>
                    </div>
                </div>

                <form action="{{ route('accounts.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label for="new-name" class="form-label">Nama Lengkap</label>
                        <input id="new-name" name="name" class="form-control" value="{{ old('name') }}" maxlength="255" required placeholder="Nama lengkap pengguna...">
                    </div>
                    <div class="col-md-6">
                        <label for="new-login-key" class="form-label">Login ID <span class="text-muted fw-normal">(NISN / nama_anak_ortu / username)</span></label>
                        <input id="new-login-key" name="login_key" class="form-control" value="{{ old('login_key') }}" maxlength="255" required placeholder="Login ID unik...">
                    </div>
                    <div class="col-md-6">
                        <label for="new-email" class="form-label">Email Akun (Opsional)</label>
                        <input id="new-email" type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="255" placeholder="email@sekolah.sch.id">
                        <div class="form-text text-muted mt-1 small">Jika kosong, sistem membuat alamat internal otomatis.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="new-recovery-email" class="form-label">Email Pemulihan (Opsional)</label>
                        <input id="new-recovery-email" type="email" name="recovery_email" class="form-control" value="{{ old('recovery_email') }}" maxlength="255" placeholder="email_pemulihan@gmail.com">
                    </div>
                    <div class="col-md-4">
                        <label for="new-role" class="form-label">Hak Akses (Role)</label>
                        <select id="new-role" name="role" class="form-select" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="new-password" class="form-label">Password Sementara <span class="text-muted fw-normal">(Min. 12 Karakter)</span></label>
                        <input id="new-password" type="password" name="password" class="form-control" minlength="12" autocomplete="new-password" required placeholder="Minimal 12 karakter...">
                    </div>
                    <div class="col-md-4">
                        <label for="new-password-confirmation" class="form-label">Konfirmasi Password Sementara</label>
                        <input id="new-password-confirmation" type="password" name="password_confirmation" class="form-control" minlength="12" autocomplete="new-password" required placeholder="Ulangi password...">
                    </div>
                    <div class="col-12 pt-2">
                        <button type="submit" class="btn-primary-custom px-4">
                            <i class="bi bi-person-check-fill"></i>
                            <span>Buat Akun Pengguna</span>
                        </button>
                    </div>
                </form>
            </section>

            <!-- Section: Akun Terdaftar -->
            <section class="content-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Daftar Akun Pengguna Terdaftar</h3>
                        <p class="section-description">Kelola kredensial dan pembaruan profil pengguna.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" style="min-width: 860px;">
                        <thead>
                            <tr>
                                <th width="20%">Pengguna</th>
                                <th width="16%">Role / Login ID</th>
                                <th width="18%">Status & Pemulihan</th>
                                <th width="46%">Perbarui Data Akun</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $account)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-person-circle fs-4 text-primary opacity-75"></i>
                                            <div class="text-break">
                                                <strong class="text-dark">{{ $account->name }}</strong><br>
                                                <span class="text-muted small">{{ $account->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border border-primary-subtle fw-bold px-2 py-1 mb-1">
                                            {{ $account->role?->display_name ?? 'Role tidak tersedia' }}
                                        </span><br>
                                        <code class="text-dark bg-light px-2 py-0.5 rounded border small">{{ $account->login_key }}</code>
                                    </td>
                                    <td>
                                        <span class="text-muted small d-block text-break">{{ $account->recovery_email ?: 'Email pemulihan belum diatur' }}</span>
                                        <div class="mt-1 d-flex gap-1 flex-wrap">
                                            @if($account->recovery_email_verified_at)
                                                <span class="scp-badge scp-badge-success" style="font-size: 9px; padding: 2px 6px;">Terverifikasi</span>
                                            @else
                                                <span class="scp-badge scp-badge-warning" style="font-size: 9px; padding: 2px 6px;">Belum Verifikasi</span>
                                            @endif

                                            @if($account->is_active)
                                                <span class="scp-badge scp-badge-info" style="font-size: 9px; padding: 2px 6px;">Aktif</span>
                                            @else
                                                <span class="scp-badge scp-badge-danger" style="font-size: 9px; padding: 2px 6px;">Nonaktif</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <form action="{{ route('accounts.update', $account) }}" method="POST" class="row g-2">
                                            @csrf
                                            @method('PUT')
                                            <div class="col-sm-6">
                                                <label class="form-label text-muted mb-1 small" for="name-{{ $account->id }}">Nama</label>
                                                <input id="name-{{ $account->id }}" name="name" class="form-control form-control-sm" value="{{ $account->name }}" maxlength="255" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label text-muted mb-1 small" for="email-{{ $account->id }}">Email Akun</label>
                                                <input id="email-{{ $account->id }}" type="email" name="email" class="form-control form-control-sm" value="{{ $account->email }}" maxlength="255" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label text-muted mb-1 small" for="recovery-{{ $account->id }}">Email Pemulihan</label>
                                                <input id="recovery-{{ $account->id }}" type="email" name="recovery_email" class="form-control form-control-sm" value="{{ $account->recovery_email }}" maxlength="255">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label text-muted mb-1 small" for="active-{{ $account->id }}">Status</label>
                                                <select id="active-{{ $account->id }}" name="is_active" class="form-select form-select-sm" required>
                                                    <option value="1" @selected($account->is_active)>Aktif</option>
                                                    <option value="0" @selected(! $account->is_active)>Nonaktif</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label text-muted mb-1 small" for="password-{{ $account->id }}">Password Baru (Opsional)</label>
                                                <input id="password-{{ $account->id }}" type="password" name="password" class="form-control form-control-sm" minlength="12" autocomplete="new-password">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label text-muted mb-1 small" for="password-confirmation-{{ $account->id }}">Ulangi Password</label>
                                                <input id="password-confirmation-{{ $account->id }}" type="password" name="password_confirmation" class="form-control form-control-sm" minlength="12" autocomplete="new-password">
                                            </div>
                                            <div class="col-12 pt-1 text-end">
                                                <button type="submit" class="btn-outline-primary-custom btn-sm">
                                                    <i class="bi bi-save"></i>
                                                    <span>Simpan Perubahan</span>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">Belum ada akun pengguna terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $users->links() }}
                </div>
            </section>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
