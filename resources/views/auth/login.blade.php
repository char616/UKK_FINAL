<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SCP System (School Class Progress)</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bs-body-font-family: 'Plus Jakarta Sans', sans-serif;
            --scp-primary: #174EA6;
            --scp-blue: #2563EB;
            --scp-dark: #0F2744;
        }

        html, body.scp-login-body {
            height: 100% !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0F1E36 !important;
        }

        body.scp-login-body {
            display: flex !important;
            flex-direction: column !important;
            overflow-x: hidden;
            letter-spacing: -0.01em;
        }

        /* WRAPPER FULL-SCREEN CENTERING VERTIKAL & HORIZONTAL */
        .scp-auth-wrapper {
            min-height: 100vh;
            width: 100%;
            flex: 1 0 auto;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 40px 16px !important;
            box-sizing: border-box !important;
            background-position: center center !important;
            background-size: cover !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
        }

        /* KARTU OTENTIKASI TRANSPARAN */
        .auth-card-container {
            display: flex;
            max-width: 980px;
            width: 100%;
            background: transparent !important;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.25);
            margin: auto !important;
        }

        /* PANEL KIRI (SANGAT TRANSPARAN / FROSTED GLASS DENGAN BLUR LEMBUT) */
        .auth-left-panel {
            flex: 1 1 48%;
            background: rgba(10, 20, 35, 0.08) !important;
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
            padding: 42px 34px;
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-right: 1px solid rgba(255, 255, 255, 0.16);
        }

        .auth-school-logo {
            width: 78px;
            height: 78px;
            object-fit: contain;
            margin-bottom: 12px;
            filter: drop-shadow(0 4px 14px rgba(0, 0, 0, 0.65));
        }

        .auth-school-name {
            font-size: 17px;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 20px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.8), 0 0 12px rgba(0, 0, 0, 0.5);
        }

        .auth-photo-card {
            width: 100%;
            max-height: 185px;
            object-fit: cover;
            border-radius: 14px;
            border: 2px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.55);
            margin-bottom: 22px;
        }

        .auth-school-description {
            font-size: 11.5px;
            line-height: 1.65;
            color: #FFFFFF;
            margin: 0;
            text-align: justify;
            text-align-last: center;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.85), 0 0 10px rgba(0, 0, 0, 0.6);
        }

        /* PANEL KANAN (FORM LOGIN PUTIH SOLID DENGAN MOTIF GRID) */
        .auth-right-panel {
            flex: 1 1 52%;
            background-color: #FFFFFF !important;
            background-image: 
                linear-gradient(to right, rgba(23, 78, 166, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(23, 78, 166, 0.05) 1px, transparent 1px) !important;
            background-size: 22px 22px;
            padding: 44px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-system-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--scp-dark);
            letter-spacing: -0.6px;
            margin-bottom: 3px;
        }

        .auth-system-subtitle {
            font-size: 13px;
            color: #64748B;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: "";
            height: 1.5px;
            background: #E2E8F0;
            flex: 1;
            max-width: 55px;
        }

        .auth-divider span {
            color: var(--scp-primary);
            font-weight: 900;
            font-size: 14px;
            letter-spacing: 2px;
        }

        .auth-notice-line {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            color: #64748B;
            font-size: 11.5px;
            margin-bottom: 22px;
            text-align: center;
        }

        .form-label-custom {
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
            display: block;
        }

        .auth-input-group {
            position: relative;
            margin-bottom: 18px;
            display: flex;
        }

        .auth-input-group .input-icon-left {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 17px;
            pointer-events: none;
            z-index: 2;
        }

        .auth-input-group .form-control {
            padding-left: 42px;
            padding-right: 14px;
            height: 46px;
            border-radius: 10px;
            border: 1.5px solid #CBD5E1;
            font-size: 13px;
            background-color: #FFFFFF;
            transition: all 0.2s;
            color: #1E293B;
            width: 100%;
        }

        .auth-input-group .form-control:focus {
            border-color: var(--scp-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        /* PARTISI PASSWORD FIELD WITH EYE TOGGLE BUTTON */
        .auth-password-group {
            position: relative;
            display: flex;
            align-items: stretch;
            border: 1.5px solid #CBD5E1;
            border-radius: 10px;
            background-color: #FFFFFF;
            transition: all 0.2s;
            margin-bottom: 18px;
        }

        .auth-password-group:focus-within {
            border-color: var(--scp-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .auth-password-group .input-icon-left {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 17px;
            pointer-events: none;
            z-index: 2;
        }

        .auth-password-group .form-control {
            border: none;
            box-shadow: none;
            padding-left: 42px;
            padding-right: 12px;
            height: 44px;
            border-radius: 10px 0 0 10px;
            font-size: 13px;
            background-color: transparent;
            color: #1E293B;
            flex: 1;
        }

        .auth-password-group .form-control:focus {
            outline: none;
            box-shadow: none;
        }

        .auth-password-group .btn-toggle-eye {
            background: transparent;
            border: none;
            border-left: 1.5px solid #E2E8F0;
            padding: 0 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94A3B8;
            font-size: 16px;
            cursor: pointer;
            transition: color 0.15s, background-color 0.15s;
            border-radius: 0 9px 9px 0;
        }

        .auth-password-group .btn-toggle-eye:hover {
            color: var(--scp-primary);
            background-color: #F8FAFC;
        }

        .btn-auth-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 46px;
            border-radius: 10px;
            background: linear-gradient(135deg, #174EA6 0%, #2563EB 100%);
            border: none;
            color: #FFFFFF;
            font-size: 13.5px;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-auth-primary:hover {
            background: linear-gradient(135deg, #123D80 0%, #1D4ED8 100%);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.45);
            transform: translateY(-1px);
            color: #FFFFFF;
        }

        .auth-footer-help {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: #64748B;
        }

        .auth-footer-copy {
            margin-top: 6px;
            text-align: center;
            font-size: 10.5px;
            color: #94A3B8;
        }

        @media (max-width: 900px) {
            .auth-card-container {
                flex-direction: column;
                max-width: 480px;
            }
            .auth-left-panel {
                padding: 30px 24px;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.16);
            }
            .auth-right-panel {
                padding: 34px 26px;
            }
        }
    </style>
</head>
<body class="scp-login-body">

    <!-- WRAPPER UNTUK FULL VIEWPORT FLEXBOX & BACKGROUND GAMBAR GEDUNG -->
    <div class="scp-auth-wrapper" style="background-image: linear-gradient(135deg, rgba(15, 30, 54, 0.25) 0%, rgba(10, 24, 44, 0.38) 100%), url('{{ asset('images/auth/gedung-smkn1bantul.jpg') }}') !important;">
        
        <div class="auth-card-container">
            
            <!-- PANEL KIRI (SANGAT TRANSPARAN TEMBUS KE FOTO GEDUNG) -->
            <div class="auth-left-panel">
                <img src="{{ asset('images/auth/logo-smkn1bantul.png') }}" alt="Logo SMK Negeri 1 Bantul" class="auth-school-logo">
                <h1 class="auth-school-name">SMK NEGERI 1 BANTUL</h1>

                <img src="{{ asset('images/auth/guru-smkn1bantul-focused.jpg') }}" alt="Keluarga Besar SMK N 1 Bantul" class="auth-photo-card">

                <p class="auth-school-description">
                    Wujudkan pembelajaran yang lebih terarah dan terukur. SCP menghadirkan sistem presensi, jurnal mengajar, dan perizinan siswa yang terintegrasi, menjembatani dunia pendidikan dengan praktik industri melalui pendekatan Teaching Factory.
                </p>
            </div>

            <!-- PANEL KANAN (FORM LOGIN PUTIH DENGAN GRID MOTIF) -->
            <div class="auth-right-panel">
                <div class="text-center">
                    <h2 class="auth-system-title">SCP SYSTEM</h2>
                    <div class="auth-system-subtitle">School Class Progress</div>
                    <div class="auth-divider">
                        <span>//</span>
                    </div>
                </div>

                <div class="auth-notice-line">
                    <i class="bi bi-info-circle"></i>
                    <span>Silakan masukkan kredensial Anda untuk melanjutkan</span>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger border-0 rounded-3 py-2 px-3 small bg-danger-subtle text-danger mb-3" style="font-size: 12px;">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success border-0 rounded-3 py-2 px-3 small bg-success-subtle text-success mb-3" style="font-size: 12px;">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf

                    <!-- FIELD USERNAME -->
                    <div class="mb-3">
                        <label for="login_key" class="form-label-custom">Username</label>
                        <div class="auth-input-group">
                            <i class="bi bi-person input-icon-left"></i>
                            <input type="text" class="form-control" id="login_key" name="login_key" value="{{ old('login_key') }}" placeholder="Contoh: WAKA001 / NISN" required autofocus autocomplete="username">
                        </div>
                    </div>

                    <!-- FIELD PASSWORD -->
                    <div class="mb-3">
                        <label for="password" class="form-label-custom">Password</label>
                        <div class="auth-password-group">
                            <i class="bi bi-lock input-icon-left"></i>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                            <button type="button" class="btn-toggle-eye" id="togglePassword" aria-label="Tampilkan atau sembunyikan password">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- INGAT SAYA & LUPA PASSWORD -->
                    <div class="d-flex justify-content-between align-items-center mb-4" style="font-size: 12px;">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" style="cursor: pointer;">
                            <label class="form-check-label text-muted fw-semibold" for="remember" style="cursor: pointer;">Ingat Saya</label>
                        </div>
                        <a href="{{ route('password.forgot') }}" class="text-decoration-none fw-bold" style="color: var(--scp-blue);">Lupa Password?</a>
                    </div>

                    <!-- TOMBOL SUBMIT LOGIN -->
                    <button type="submit" class="btn-auth-primary">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Login</span>
                    </button>
                </form>

                <!-- FOOTER INFO -->
                <div class="auth-footer-help">
                    Hubungi Admin jika Anda mengalami kendala saat masuk.
                </div>
                <div class="auth-footer-copy">
                    &copy; 2026 SMK Negeri 1 Bantul
                </div>
            </div>

        </div>

    </div>

    <!-- Toggle Password Visibility Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.querySelector('#togglePassword');
            const passwordInput = document.querySelector('#password');
            const eyeIcon = document.querySelector('#eyeIcon');

            if (toggleBtn && passwordInput && eyeIcon) {
                toggleBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    eyeIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
                });
            }
        });
    </script>
</body>
</html>
