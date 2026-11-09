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
                        <span>Kembali ke Manajemen Data</span>
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
                            ADMINISTRASI PENGGUNA
                        </div>

                        <h2 class="hero-title">
                            Pusat Pengelolaan Akun Pengguna
                        </h2>

                        <p class="hero-description">
                            Kelola pendaftaran akun pengguna baru, pengaturan peran (role), serta pembaruan informasi kredensial bagi Guru, Wali Kelas, Guru BK, Siswa, dan Orang Tua secara terpusat.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </section>

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

            <!-- Section: Buat Akun Pengguna Baru -->
            <section class="content-card mb-4">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div>
                        <h2 class="section-title">Buat Akun Pengguna Baru</h2>
                        <p class="section-description">
                            Lengkapi formulir di bawah ini untuk mendaftarkan akun pengguna baru sesuai peran di lingkungan sekolah. Akun Siswa menggunakan nomor induk (NIS/NISN), sedangkan akun Orang Tua menggunakan format <code>nama_siswa_ortu</code> atau <code>ortu_[NIS]</code>.
                        </p>
                    </div>
                </div>

                <form action="{{ route('accounts.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label for="new-name" class="form-label">Nama Lengkap Pengguna <span class="text-danger">*</span></label>
                        <input id="new-name" name="name" class="form-control" value="{{ old('name') }}" maxlength="255" required placeholder="Masukkan nama lengkap pengguna">
                    </div>
                    <div class="col-md-4">
                        <label for="new-login-key" class="form-label">ID Login (Username) <span class="text-danger">*</span></label>
                        <input id="new-login-key" name="login_key" class="form-control" value="{{ old('login_key') }}" maxlength="255" required placeholder="Contoh: NIP, NISN, atau nama_siswa_ortu">
                    </div>
                    <div class="col-md-4">
                        <label for="new-role" class="form-label">Peran Pengguna (Role) <span class="text-danger">*</span></label>
                        <select id="new-role" name="role" class="form-select" required>
                            <option value="">-- Pilih Peran Pengguna --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="new-email" class="form-label">Alamat Email Akun <span class="text-muted fw-normal">(Opsional)</span></label>
                        <input id="new-email" type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="255" placeholder="Contoh: pengguna@smkn1bantul.sch.id">
                        <div class="form-text text-muted mt-1 small">Apabila dikosongkan, sistem akan membuat alamat email internal secara otomatis.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="new-recovery-email" class="form-label">Alamat Email Pemulihan <span class="text-muted fw-normal">(Opsional)</span></label>
                        <input id="new-recovery-email" type="email" name="recovery_email" class="form-control" value="{{ old('recovery_email') }}" maxlength="255" placeholder="Contoh: email.aktif@gmail.com">
                        <div class="form-text text-muted mt-1 small">Digunakan untuk pengiriman kode OTP saat pemulihan kata sandi.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="new-password" class="form-label">Kata Sandi Awal <span class="text-danger">*</span></label>
                        <input id="new-password" type="password" name="password" class="form-control" minlength="6" autocomplete="new-password" required placeholder="Minimal 6 karakter">
                    </div>
                    <div class="col-md-6">
                        <label for="new-password-confirmation" class="form-label">Konfirmasi Kata Sandi Awal <span class="text-danger">*</span></label>
                        <input id="new-password-confirmation" type="password" name="password_confirmation" class="form-control" minlength="6" autocomplete="new-password" required placeholder="Ketik ulang kata sandi awal">
                    </div>
                    <div class="col-12 pt-2 d-flex justify-content-end border-top mt-3">
                        <button type="submit" class="btn-primary-custom px-4">
                            <i class="bi bi-person-check-fill"></i>
                            <span>Buat Akun Pengguna Baru</span>
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
                        <p class="section-description">Cari, saring berdasarkan peran, dan perbarui informasi akun pengguna yang telah terdaftar.</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('accounts.index') }}" class="row g-2 mb-4 align-items-end p-3 bg-light rounded-3 border">
                    <div class="col-md-6">
                        <label for="account-search" class="form-label">Cari Nama atau ID Login</label>
                        <input id="account-search" name="search" value="{{ $search }}" class="form-control" placeholder="Masukkan nama pengguna, NIS, NIP, atau ID Login...">
                    </div>
                    <div class="col-md-4">
                        <label for="account-role" class="form-label">Peran Pengguna (Role)</label>
                        <select id="account-role" name="role" class="form-select">
                            <option value="">Semua Peran Pengguna</option>
                            <option value="siswa" @selected($selectedRole === 'siswa')>Siswa</option>
                            <option value="orang_tua" @selected($selectedRole === 'orang_tua')>Orang Tua</option>
                            <option value="guru" @selected($selectedRole === 'guru')>Guru</option>
                            <option value="bk" @selected($selectedRole === 'bk')>Guru BK</option>
                            <option value="wali_kelas" @selected($selectedRole === 'wali_kelas')>Wali Kelas</option>
                            <option value="waka" @selected($selectedRole === 'waka')>Admin / Waka</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn-primary-custom flex-grow-1" type="submit"><i class="bi bi-search"></i> Cari</button>
                        <a class="btn-outline-custom" href="{{ route('accounts.index') }}" title="Atur Ulang Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle" style="min-width: 860px;">
                        <thead>
                            <tr>
                                <th width="22%">Identitas Pengguna</th>
                                <th width="18%">Peran & ID Login</th>
                                <th width="16%">Status & Email Pemulihan</th>
                                <th width="44%">Perbarui Data Akun</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $account)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi {{ $account->role?->name === 'orang_tua' ? 'bi-people-fill' : 'bi-person-circle' }} fs-4 text-primary opacity-75 mt-0.5"></i>
                                            <div class="text-break">
                                                <strong class="text-dark d-block">{{ $account->name }}</strong>
                                                <span class="text-muted small d-block">{{ $account->email }}</span>
                                                @if($account->role?->name === 'orang_tua' && $account->students->isNotEmpty())
                                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle mt-1" style="font-size: 9.5px;">
                                                        Orang Tua dari: {{ $account->students->pluck('name')->join(', ') }}
                                                    </span>
                                                @elseif($account->role?->name === 'siswa' && $account->schoolClass)
                                                    <span class="badge bg-light text-secondary border mt-1" style="font-size: 9.5px;">
                                                        Kelas: {{ $account->schoolClass->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border border-primary-subtle fw-bold px-2 py-1 mb-1">
                                            {{ $account->role?->display_name ?? 'Peran tidak tersedia' }}
                                        </span><br>
                                        <code class="text-dark bg-light px-2 py-0.5 rounded border small d-inline-block">{{ $account->login_key }}</code>
                                        @if($account->role?->name === 'orang_tua' && $account->parent_login_key)
                                            <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                                Alternatif: <code>{{ $account->parent_login_key }}</code>
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small d-block text-break">{{ $account->recovery_email ?: 'Email pemulihan belum diatur' }}</span>
                                        <div class="mt-1 d-flex gap-1 flex-wrap">
                                            @if($account->recovery_email_verified_at)
                                                <span class="scp-badge scp-badge-success" style="font-size: 9px; padding: 2px 6px;">Terverifikasi</span>
                                            @else
                                                <span class="scp-badge scp-badge-warning" style="font-size: 9px; padding: 2px 6px;">Belum Verifikasi</span>
                                            @endif

                                            <span class="scp-badge scp-badge-success" style="font-size: 9px; padding: 2px 6px;">Aktif</span>
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
                                                <label class="form-label text-muted mb-1 small" for="password-{{ $account->id }}">Password Baru (Opsional)</label>
                                                <input id="password-{{ $account->id }}" type="password" name="password" class="form-control form-control-sm" minlength="6" autocomplete="new-password">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label text-muted mb-1 small" for="password-confirmation-{{ $account->id }}">Ulangi Password</label>
                                                <input id="password-confirmation-{{ $account->id }}" type="password" name="password_confirmation" class="form-control form-control-sm" minlength="6" autocomplete="new-password">
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
