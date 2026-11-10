<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun Pengguna - SCP System</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .user-avatar-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.5px;
            border: 1px solid transparent;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            background: #ffffff;
            color: #475569;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease;
        }
        .filter-chip:hover {
            background: #f1f5f9;
            color: #1e293b;
            border-color: #cbd5e1;
        }
        .filter-chip.active {
            background: #174ea6;
            color: #ffffff;
            border-color: #174ea6;
            box-shadow: 0 2px 4px rgba(23, 78, 166, 0.2);
        }

        .role-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .role-pill-waka { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .role-pill-guru { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .role-pill-wali { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .role-pill-bk   { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
        .role-pill-ortu { background: #faf5ff; color: #7e22ce; border: 1px solid #ddd6fe; }
        .role-pill-siswa{ background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

        .btn-action-edit {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 700;
            color: #174ea6;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-action-edit:hover {
            background: #174ea6;
            color: #ffffff;
            border-color: #174ea6;
        }

        .table > tbody > tr:hover {
            background-color: #f8fafc;
        }

        @media (max-width: 576px) {
            .filter-chip {
                padding: 4px 9px;
                font-size: 10px;
            }
        }
    </style>
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
                        <i class="bi bi-people-fill"></i>
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
                            ADMINISTRASI PENGGUNA TERPADU
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
                <div class="section-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <div>
                            <h2 class="section-title mb-0">Buat Akun Pengguna Baru</h2>
                            <p class="section-description">Daftarkan akun pengguna baru dengan menetapkan peran (role) dan kredensial login.</p>
                        </div>
                    </div>

                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFormCreateUser" aria-expanded="true" aria-controls="collapseFormCreateUser">
                        <i class="bi bi-chevron-expand me-1"></i> Buka / Tutup Formulir
                    </button>
                </div>

                <div class="collapse show pt-3" id="collapseFormCreateUser">
                    <form action="{{ route('accounts.store') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label for="new-name" class="form-label fw-bold text-dark" style="font-size: 12px;">Nama Lengkap Pengguna <span class="text-danger">*</span></label>
                            <input id="new-name" name="name" class="form-control form-control-sm" value="{{ old('name') }}" maxlength="255" required placeholder="Contoh: Ahmad Fauzan, S.Pd.">
                        </div>

                        <div class="col-md-4">
                            <label for="new-login-key" class="form-label fw-bold text-dark" style="font-size: 12px;">ID Login (Username) <span class="text-danger">*</span></label>
                            <input id="new-login-key" name="login_key" class="form-control form-control-sm" value="{{ old('login_key') }}" maxlength="255" required placeholder="Contoh: NIP, NISN, atau nama_siswa_ortu">
                            <div class="form-text text-muted" style="font-size: 10px;">Siswa berupa angka (NISN), Orang Tua berformat <code>nama_siswa_ortu</code>.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="new-role" class="form-label fw-bold text-dark" style="font-size: 12px;">Peran Pengguna (Role) <span class="text-danger">*</span></label>
                            <select id="new-role" name="role" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Peran Pengguna --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="new-class-id" class="form-label fw-bold text-dark" style="font-size: 12px;">Rombel Kelas <span class="text-muted fw-normal">(Khusus Siswa)</span></label>
                            <select id="new-class-id" name="class_id" class="form-select form-select-sm">
                                <option value="">-- Tanpa / Belum Ditentukan --</option>
                                @foreach ($classes as $cl)
                                    <option value="{{ $cl->id }}" @selected(old('class_id') == $cl->id)>{{ $cl->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="new-email" class="form-label fw-bold text-dark" style="font-size: 12px;">Email Akun <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input id="new-email" type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}" maxlength="255" placeholder="Contoh: user@smkn1bantul.sch.id">
                        </div>

                        <div class="col-md-4">
                            <label for="new-recovery-email" class="form-label fw-bold text-dark" style="font-size: 12px;">Email Pemulihan <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input id="new-recovery-email" type="email" name="recovery_email" class="form-control form-control-sm" value="{{ old('recovery_email') }}" maxlength="255" placeholder="Contoh: email.aktif@gmail.com">
                        </div>

                        <div class="col-md-6">
                            <label for="new-password" class="form-label fw-bold text-dark" style="font-size: 12px;">Kata Sandi Awal <span class="text-danger">*</span></label>
                            <input id="new-password" type="password" name="password" class="form-control form-control-sm" minlength="6" autocomplete="new-password" required placeholder="Minimal 6 karakter">
                        </div>

                        <div class="col-md-6">
                            <label for="new-password-confirmation" class="form-label fw-bold text-dark" style="font-size: 12px;">Konfirmasi Kata Sandi Awal <span class="text-danger">*</span></label>
                            <input id="new-password-confirmation" type="password" name="password_confirmation" class="form-control form-control-sm" minlength="6" autocomplete="new-password" required placeholder="Ketik ulang kata sandi awal">
                        </div>

                        <div class="col-12 pt-3 d-flex justify-content-end border-top mt-2">
                            <button type="submit" class="btn-primary-custom px-4 py-2">
                                <i class="bi bi-person-check-fill me-1"></i>
                                <span>Simpan & Buat Akun Baru</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Section: Akun Terdaftar -->
            <section class="content-card">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h3 class="section-title mb-0">Daftar Akun Pengguna Terdaftar</h3>
                            <p class="section-description">Total <strong>{{ $users->total() }}</strong> akun terdaftar dalam sistem SCP.</p>
                        </div>
                    </div>
                </div>

                <!-- FILTER DAN PENCARIAN -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <form method="GET" action="{{ route('accounts.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label for="account-search" class="form-label fw-bold text-dark small mb-1">Cari Nama atau ID Login</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                                <input id="account-search" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama, NIS, NIP, atau username...">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="account-role" class="form-label fw-bold text-dark small mb-1">Peran Pengguna (Role)</label>
                            <select id="account-role" name="role" class="form-select form-select-sm">
                                <option value="">Semua Peran Pengguna</option>
                                <option value="siswa" @selected($selectedRole === 'siswa')>Siswa</option>
                                <option value="orang_tua" @selected($selectedRole === 'orang_tua')>Orang Tua</option>
                                <option value="guru" @selected($selectedRole === 'guru')>Guru</option>
                                <option value="bk" @selected($selectedRole === 'bk')>Guru BK</option>
                                <option value="wali_kelas" @selected($selectedRole === 'wali_kelas')>Wali Kelas</option>
                                <option value="waka" @selected($selectedRole === 'waka')>Admin / Waka</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn-primary-custom btn-sm flex-grow-1" type="submit">
                                <i class="bi bi-filter"></i> Saring
                            </button>
                            <a class="btn-outline-custom btn-sm" href="{{ route('accounts.index') }}" title="Atur Ulang Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </form>

                    <!-- QUICK FILTER CHIPS -->
                    <div class="d-flex align-items-center gap-1.5 flex-wrap mt-2.5 pt-2 border-top">
                        <span class="text-muted small fw-semibold me-1" style="font-size: 11px;">Filter Cepat:</span>
                        <a href="{{ route('accounts.index') }}" class="filter-chip {{ empty($selectedRole) ? 'active' : '' }}">Semua</a>
                        <a href="{{ route('accounts.index', ['role' => 'guru']) }}" class="filter-chip {{ $selectedRole === 'guru' ? 'active' : '' }}">Guru</a>
                        <a href="{{ route('accounts.index', ['role' => 'wali_kelas']) }}" class="filter-chip {{ $selectedRole === 'wali_kelas' ? 'active' : '' }}">Wali Kelas</a>
                        <a href="{{ route('accounts.index', ['role' => 'bk']) }}" class="filter-chip {{ $selectedRole === 'bk' ? 'active' : '' }}">Guru BK</a>
                        <a href="{{ route('accounts.index', ['role' => 'siswa']) }}" class="filter-chip {{ $selectedRole === 'siswa' ? 'active' : '' }}">Siswa</a>
                        <a href="{{ route('accounts.index', ['role' => 'orang_tua']) }}" class="filter-chip {{ $selectedRole === 'orang_tua' ? 'active' : '' }}">Orang Tua</a>
                        <a href="{{ route('accounts.index', ['role' => 'waka']) }}" class="filter-chip {{ $selectedRole === 'waka' ? 'active' : '' }}">Waka</a>
                    </div>
                </div>

                <!-- TABEL USER -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="32%">Identitas Pengguna</th>
                                <th width="24%">Peran & ID Login</th>
                                <th width="24%">Status & Pemulihan</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $account)
                                @php
                                    $userWords = preg_split('/\s+/', trim($account->name));
                                    $monogram = '';
                                    if (count($userWords) >= 2) {
                                        $monogram = mb_strtoupper(mb_substr($userWords[0], 0, 1) . mb_substr(end($userWords), 0, 1));
                                    } else {
                                        $monogram = mb_strtoupper(mb_substr($userWords[0] ?? 'U', 0, 2));
                                    }
                                    $avatarPalettes = [
                                        ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                                        ['bg' => '#f5f3ff', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
                                        ['bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
                                        ['bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a'],
                                        ['bg' => '#fff1f2', 'text' => '#be123c', 'border' => '#fecdd3'],
                                        ['bg' => '#f0fdfa', 'text' => '#0f766e', 'border' => '#99f6e4'],
                                    ];
                                    $themeIdx = abs(crc32($account->name ?? (string)$account->id)) % count($avatarPalettes);
                                    $theme = $avatarPalettes[$themeIdx];

                                    $roleKey = $account->role?->name ?? '';
                                    $roleClass = match($roleKey) {
                                        'waka' => 'role-pill-waka',
                                        'guru' => 'role-pill-guru',
                                        'wali_kelas' => 'role-pill-wali',
                                        'bk' => 'role-pill-bk',
                                        'orang_tua' => 'role-pill-ortu',
                                        'siswa' => 'role-pill-siswa',
                                        default => 'bg-light text-dark border',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="user-avatar-circle" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }}; border-color: {{ $theme['border'] }};">
                                                {{ $monogram }}
                                            </div>
                                            <div class="text-break">
                                                <strong class="text-dark d-block" style="font-size: 13px;">{{ $account->name }}</strong>
                                                <span class="text-muted small d-block" style="font-size: 11px;">{{ $account->email }}</span>
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
                                        <div class="mb-1">
                                            <span class="role-pill {{ $roleClass }}">
                                                <i class="bi bi-shield-check"></i>
                                                {{ $account->role?->display_name ?? 'Peran tidak tersedia' }}
                                            </span>
                                        </div>
                                        <div>
                                            <code class="text-dark bg-light px-2 py-0.5 rounded border small">{{ $account->login_key }}</code>
                                            @if($account->role?->name === 'orang_tua' && $account->parent_login_key)
                                                <small class="text-muted d-block mt-0.5" style="font-size: 10px;">
                                                    Alt: <code>{{ $account->parent_login_key }}</code>
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted small d-block text-break" style="font-size: 11.5px;">
                                            {{ $account->recovery_email ?: 'Email pemulihan belum diatur' }}
                                        </span>
                                        <div class="mt-1 d-flex gap-1 flex-wrap align-items-center">
                                            @if($account->recovery_email_verified_at)
                                                <span class="scp-badge scp-badge-success" style="font-size: 9.5px; padding: 2px 7px;">
                                                    <i class="bi bi-check2-circle me-0.5"></i>Terverifikasi
                                                </span>
                                            @else
                                                <span class="scp-badge scp-badge-warning" style="font-size: 9.5px; padding: 2px 7px;">
                                                    Belum Verifikasi
                                                </span>
                                            @endif

                                            @if($account->is_active ?? true)
                                                <span class="scp-badge scp-badge-success" style="font-size: 9.5px; padding: 2px 7px;">Aktif</span>
                                            @else
                                                <span class="scp-badge scp-badge-danger" style="font-size: 9.5px; padding: 2px 7px;">Nonaktif</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn-action-edit" data-bs-toggle="modal" data-bs-target="#modalEditUser-{{ $account->id }}" title="Edit Kredensial Pengguna">
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Edit Akun</span>
                                        </button>

                                        <!-- MODAL EDIT PENGGUNA -->
                                        <div class="modal fade text-start" id="modalEditUser-{{ $account->id }}" tabindex="-1" aria-labelledby="labelModalEdit-{{ $account->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                                <div class="modal-content border-0 shadow">
                                                    <form action="{{ route('accounts.update', $account) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header border-bottom py-3">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="section-icon" style="width: 32px; height: 32px; font-size: 15px;">
                                                                    <i class="bi bi-person-gear"></i>
                                                                </div>
                                                                <div>
                                                                    <h5 class="modal-title fw-bold text-dark" id="labelModalEdit-{{ $account->id }}" style="font-size: 14px;">Edit Data Akun</h5>
                                                                    <small class="text-muted" style="font-size: 11px;">Kredensial dan akses: <strong>{{ $account->name }}</strong></small>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                        </div>

                                                        <div class="modal-body p-3.5">
                                                            <div class="row g-3">
                                                                <div class="col-12">
                                                                    <label class="form-label text-dark fw-bold small mb-1" for="name-{{ $account->id }}">Nama Lengkap</label>
                                                                    <input id="name-{{ $account->id }}" name="name" class="form-control form-control-sm" value="{{ $account->name }}" maxlength="255" required>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label text-dark fw-bold small mb-1" for="email-{{ $account->id }}">Email Akun</label>
                                                                    <input id="email-{{ $account->id }}" type="email" name="email" class="form-control form-control-sm" value="{{ $account->email }}" maxlength="255" required>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label text-dark fw-bold small mb-1" for="recovery-{{ $account->id }}">Email Pemulihan</label>
                                                                    <input id="recovery-{{ $account->id }}" type="email" name="recovery_email" class="form-control form-control-sm" value="{{ $account->recovery_email }}" maxlength="255" placeholder="Opsional">
                                                                </div>

                                                                <div class="col-12 border-top pt-2">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active-{{ $account->id }}" @checked($account->is_active ?? true)>
                                                                        <label class="form-check-label text-dark fw-semibold small" for="active-{{ $account->id }}">Akun Pengguna Aktif</label>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12 border-top pt-2">
                                                                    <span class="text-muted d-block small fw-bold mb-2">Ganti Kata Sandi (Opsional)</span>
                                                                    <div class="row g-2">
                                                                        <div class="col-sm-6">
                                                                            <label class="form-label text-muted small mb-1" for="password-{{ $account->id }}">Password Baru</label>
                                                                            <input id="password-{{ $account->id }}" type="password" name="password" class="form-control form-control-sm" minlength="6" autocomplete="new-password" placeholder="Kosongkan bila tidak diganti">
                                                                        </div>
                                                                        <div class="col-sm-6">
                                                                            <label class="form-label text-muted small mb-1" for="password-confirmation-{{ $account->id }}">Ulangi Password</label>
                                                                            <input id="password-confirmation-{{ $account->id }}" type="password" name="password_confirmation" class="form-control form-control-sm" minlength="6" autocomplete="new-password" placeholder="Ketik ulang password">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer border-top py-2.5 px-3">
                                                            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn-primary-custom btn-sm px-3">
                                                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bi bi-person-x fs-2 d-block text-secondary mb-2"></i>
                                        Tidak ditemukan akun pengguna yang cocok dengan kriteria filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links('vendor.pagination.bootstrap-5') }}
                </div>
            </section>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
