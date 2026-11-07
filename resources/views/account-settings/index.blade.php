<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - SCP System</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #174EA6;
            --primary-dark: #123D80;
            --primary-light: #EAF2FF;
            --background: #F4F7FB;
            --surface: #FFFFFF;
            --text: #253449;
            --muted: #718096;
            --border: #E2E8F0;
            --success: #16845B;
            --danger: #C24141;
            --sidebar-width: 280px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
        }

        .main-content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 30px 34px 45px;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .page-eyebrow {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .page-title {
            margin: 0 0 6px;
            color: #1E3552;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.7px;
        }

        .academic-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
        }

        .academic-badge i {
            color: var(--primary);
            font-size: 17px;
        }

        .academic-badge span {
            color: var(--text);
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* HERO BANNER */
        .hero-banner {
            position: relative;
            overflow: hidden;
            padding: 25px 28px;
            margin-bottom: 22px;
            color: #FFFFFF;
            background: linear-gradient(110deg, #174EA6 0%, #2563EB 100%);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(23, 78, 166, .10);
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 10px;
            margin-bottom: 11px;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 5px;
            background: rgba(255,255,255,.12);
            color: #FFFFFF;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .hero-title {
            margin: 0 0 7px;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -.4px;
        }

        .hero-description {
            margin: 0;
            color: rgba(255,255,255,.82);
            font-size: 11px;
            line-height: 1.6;
        }

        .hero-icon {
            display: grid;
            place-items: center;
            width: 55px;
            height: 55px;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 10px;
            background: rgba(255,255,255,.13);
            color: white;
            font-size: 24px;
        }

        /* CARD & SECTION */
        .content-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(31,52,73,.035);
            padding: 20px;
            height: 100%;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .section-icon {
            display: grid;
            place-items: center;
            width: 37px;
            height: 37px;
            flex-shrink: 0;
            border-radius: 8px;
            background: var(--primary-light);
            color: var(--primary);
            font-size: 16px;
        }

        .section-title {
            margin: 0 0 4px;
            color: #203957;
            font-size: 12px;
            font-weight: 800;
        }

        .section-description {
            margin: 0;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.6;
        }

        /* FORMS & BUTTONS */
        .form-label {
            margin-bottom: 7px;
            color: #34445A;
            font-size: 11px;
            font-weight: 700;
        }

        .form-control {
            min-height: 40px;
            padding: 9px 12px;
            border: 1px solid #DCE4ED;
            border-radius: 6px;
            font-size: 11px;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(23,78,166,.09);
        }

        .btn-primary-custom {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            min-height: 39px;
            padding: 9px 15px;
            border: 1px solid var(--primary);
            border-radius: 6px;
            background: var(--primary);
            color: #FFFFFF;
            font-size: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: .2s ease;
        }

        .btn-primary-custom:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: #FFFFFF;
        }

        .btn-outline-primary-custom {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            min-height: 39px;
            padding: 9px 15px;
            border: 1px solid #D5DFEA;
            border-radius: 6px;
            background: #FFFFFF;
            color: var(--primary);
            font-size: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: .2s ease;
        }

        .btn-outline-primary-custom:hover {
            background: var(--primary-light);
            border-color: #B7CCEB;
            color: var(--primary-dark);
        }

        /* ALERTS */
        .alert-scp-success {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            margin-bottom: 17px;
            background: #EAF7F0;
            border: 1px solid #D5EDDF;
            color: #23764F;
            border-radius: 7px;
            font-size: 11px;
        }

        .alert-scp-danger {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            margin-bottom: 17px;
            background: #FFF1F1;
            border: 1px solid #F3DADA;
            color: #B33F3F;
            border-radius: 7px;
            font-size: 11px;
        }

        @media (max-width: 991.98px) {
            .main-content {
                width: 100% !important;
                margin-left: 0 !important;
                padding: 88px 16px 35px !important;
            }
            .academic-badge {
                display: none;
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
                        <i class="bi bi-shield-lock-fill"></i>
                        Keamanan & Akun
                    </div>
                    <h1 class="page-title">Pengaturan Akun & Profil</h1>
                </div>

                <div class="academic-badge">
                    <i class="bi bi-calendar3"></i>
                    <span>Tahun Ajaran 2026/2027</span>
                </div>
            </header>

            <!-- Banner Utama -->
            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-person-gear"></i>
                            PROFIL PENGGUNA
                        </div>

                        <h2 class="hero-title">
                            Kelola Kata Sandi & Email Tertaut
                        </h2>

                        <p class="hero-description">
                            Pastikan kata sandi Anda kuat dan tautkan email aktif Anda agar kode OTP reset password bisa dikirim jika sewaktu-waktu lupa kata sandi.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </section>

            <!-- Notifikasi -->
            @if (session('password_change_required'))
                <div class="alert alert-warning py-3 px-4 mb-4 small d-flex align-items-center gap-2 border-0 rounded-3 shadow-sm" style="background: #fffbeb; color: #b45309;">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <span>{{ session('password_change_required') }}</span>
                </div>
            @endif
            
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

            <!-- Layout Berdampingan (2 Kolom) -->
            <div class="row g-4">
                
                <!-- Kolom Kiri: Ganti Kata Sandi -->
                <div class="col-lg-6">
                    <section class="content-card">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-key-fill"></i>
                            </div>
                            <div>
                                <h2 class="section-title">Ganti Kata Sandi</h2>
                                <p class="section-description">
                                    Login ID: <code class="bg-light px-1.5 py-0.5 rounded text-primary fw-bold border"><span>{{ $user->login_key }}</span></code>.
                                    @if ($user->must_change_password)
                                        <span class="text-warning fw-bold d-block mt-1">Password sementara wajib diganti.</span>
                                    @else
                                        <span class="d-block mt-1">Perbarui secara berkala.</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <form action="{{ route('settings.password.update') }}" method="POST" class="row g-3">
                            @csrf
                            @method('PUT')
                            <div class="col-12">
                                <label for="current-password" class="form-label">Password Saat Ini</label>
                                <input id="current-password" type="password" name="current_password" class="form-control" autocomplete="current-password" required placeholder="Password lama...">
                            </div>
                            <div class="col-12">
                                <label for="new-password" class="form-label">Password Baru <span class="text-muted fw-normal">(Minimal 6 karakter)</span></label>
                                <input id="new-password" type="password" name="password" class="form-control" minlength="6" autocomplete="new-password" placeholder="Minimal 6 karakter..." required>
                            </div>
                            <div class="col-12">
                                <label for="new-password-confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input id="new-password-confirmation" type="password" name="password_confirmation" class="form-control" minlength="12" autocomplete="new-password" placeholder="Ulangi password baru..." required>
                            </div>
                            <div class="col-12 pt-2">
                                <button type="submit" class="btn-primary-custom w-100">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Simpan Kata Sandi Baru</span>
                                </button>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Kolom Kanan: Email Tertaut -->
                <div class="col-lg-6">
                    <section class="content-card">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-envelope-at-fill"></i>
                            </div>
                            <div>
                                <h2 class="section-title">Email Tertaut Akun</h2>
                                <p class="section-description">
                                    Email yang tersimpan di database dan dipakai untuk menerima kode OTP saat lupa kata sandi.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3 py-2 px-3 bg-light rounded-3 border">
                            <span class="text-muted fw-bold small">Email tertaut:</span>
                            <span class="badge {{ $linkedEmails->isNotEmpty() ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2 py-1">
                                {{ $linkedEmails->count() }} / {{ $maxEmails }}
                            </span>
                        </div>

                        @forelse ($linkedEmails as $linked)
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 py-2 px-3 rounded-3 border">
                                <div class="text-break">
                                    <span class="fw-semibold" style="font-size: 11px;">{{ $linked->email }}</span>
                                    @if ($linked->is_primary)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">Utama</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-1 flex-shrink-0">
                                    @unless ($linked->is_primary)
                                        <form action="{{ route('settings.emails.primary', $linked) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Jadikan email utama">
                                                <i class="bi bi-star"></i>
                                            </button>
                                        </form>
                                    @endunless
                                    <form action="{{ route('settings.emails.destroy', $linked) }}" method="POST" onsubmit="return confirm('Lepas email {{ $linked->email }} dari akun?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Lepas email">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-3">Belum ada email tertaut. Tambahkan email agar bisa reset password lewat OTP.</p>
                        @endforelse

                        <form action="{{ route('settings.emails.store') }}" method="POST" class="row g-2 mt-2">
                            @csrf
                            <div class="col-12">
                                <label for="linked-email" class="form-label">Tambah Email</label>
                                <input id="linked-email" type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="255" autocomplete="email" required placeholder="nama@gmail.com" @disabled($linkedEmails->count() >= $maxEmails)>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn-primary-custom w-100" @disabled($linkedEmails->count() >= $maxEmails)>
                                    <i class="bi bi-plus-circle"></i>
                                    <span>Tautkan Email</span>
                                </button>
                            </div>
                        </form>
                    </section>
                </div>

            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
