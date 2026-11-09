<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard WAKA - SCP System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --waka-primary: #174ea6;
            --waka-blue: #2563eb;
            --waka-background: #f4f7fb;
            --waka-text: #1e293b;
            --waka-muted: #64748b;
            --waka-border: #e5eaf2;
            --sidebar-width: 280px;
        }

        html,
        body {
            width: 100%;
            min-width: 320px;
            min-height: 100%;
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--waka-background) !important;
            color: var(--waka-text);
        }

        body {
            display: block !important;
            overflow-x: hidden !important;
        }

        .main-content {
            display: block !important;
            width: calc(100% - var(--sidebar-width)) !important;
            min-width: 0 !important;
            min-height: 100vh !important;
            margin-left: var(--sidebar-width) !important;
            padding: 30px 34px 45px !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            background: var(--waka-background) !important;
        }

        .content-wrapper {
            width: 100%;
            max-width: 1250px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 22px;
            margin-bottom: 26px;
            border-bottom: 1px solid var(--waka-border);
        }

        .page-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--waka-primary);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }

        .page-title {
            margin: 5px 0 0;
            color: #172b4d;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.8px;
        }

        .academic-badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 11px 16px;
            border: 1px solid var(--waka-border);
            border-radius: 10px;
            background: #fff;
            color: #475569;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .academic-badge i {
            color: var(--waka-blue);
            font-size: 16px;
        }

        .hero-banner {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            padding: 35px 38px;
            margin-bottom: 27px;
            border-radius: 16px;
            background: linear-gradient(115deg,#174ea6 0%,#2563eb 65%,#4385ed 100%);
            box-shadow: 0 8px 22px rgba(37,99,235,.12);
            color: #fff;
        }

        .hero-banner::before,
        .hero-banner::after {
            position: absolute;
            z-index: -1;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
            content: "";
        }

        .hero-banner::before {
            top: -190px;
            right: 90px;
            width: 310px;
            height: 310px;
        }

        .hero-banner::after {
            right: -65px;
            bottom: -155px;
            width: 240px;
            height: 240px;
        }

        .hero-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }

        .hero-text {
            max-width: 760px;
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            margin-bottom: 19px;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 7px;
            background: rgba(255,255,255,.14);
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .8px;
        }

        .hero-title {
            margin-bottom: 11px;
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -.7px;
        }

        .hero-description {
            max-width: 720px;
            margin: 0;
            color: rgba(255,255,255,.88);
            font-size: 11px;
            line-height: 1.8;
        }

        .hero-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 90px;
            width: 90px;
            height: 90px;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 20px;
            background: rgba(255,255,255,.13);
            color: #fff;
            font-size: 40px;
        }

        .content-card {
            padding: 26px 28px;
            border: 1px solid var(--waka-border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(15,23,42,.025);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 21px;
            margin-bottom: 7px;
            border-bottom: 1px solid #edf1f6;
        }

        .section-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 45px;
            width: 45px;
            height: 45px;
            border-radius: 11px;
            background: #eff6ff;
            color: var(--waka-blue);
            font-size: 21px;
        }

        .section-title {
            margin: 0 0 5px;
            color: #1e293b;
            font-size: 13px;
            font-weight: 800;
        }

        .section-description {
            margin: 0;
            color: var(--waka-muted);
            font-size: 11px;
            line-height: 1.6;
        }

        .rule-list {
            display: flex;
            flex-direction: column;
        }

        .rule-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 19px 5px;
            border-bottom: 1px solid #edf1f6;
        }

        .rule-item:last-child {
            padding-bottom: 5px;
            border-bottom: none;
        }

        .rule-check {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 30px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e8f8ef;
            color: #16a36a;
            font-size: 13px;
        }

        .rule-content {
            min-width: 0;
        }

        .rule-title {
            display: block;
            margin-bottom: 5px;
            color: #25364d;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.6;
        }

        .rule-description {
            margin: 0;
            color: #64748b;
            font-size: 11px;
            line-height: 1.7;
        }

        @media (max-width: 991.98px) {
            .main-content {
                width: 100% !important;
                height: auto !important;
                min-height: calc(100vh - 64px) !important;
                margin-left: 0 !important;
                padding: 88px 22px 28px !important;
            }

            .hero-banner {
                padding: 30px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 82px 15px 24px !important;
            }

            .page-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 13px;
                margin-bottom: 20px;
                padding-bottom: 18px;
            }

            .page-title {
                font-size: 20px;
            }

            .academic-badge {
                padding: 9px 12px;
            }

            .hero-banner {
                padding: 25px 21px;
                margin-bottom: 20px;
                border-radius: 13px;
            }

            .hero-content {
                align-items: flex-start;
            }

            .hero-title {
                font-size: 18px;
                line-height: 1.5;
            }

            .hero-description {
                font-size: 11px;
            }

            .hero-icon {
                display: none;
            }

            .content-card {
                padding: 21px 17px;
            }

            .section-title {
                font-size: 12px;
            }

            .rule-item {
                gap: 11px;
                padding: 16px 0;
            }

            .rule-title {
                font-size: 11px;
            }

            .rule-description {
                font-size: 10px;
            }
        }
    </style>
</head>

<body>

    @include('layouts.sidebar', ['user' => $user])

    <main class="main-content">
        <div class="content-wrapper">

            <header class="page-header">
                <div>
                    <div class="page-eyebrow">
                        <i class="bi bi-grid-1x2-fill"></i>
                        Waka Kurikulum Panel
                    </div>
                    <h1 class="page-title">Dashboard Utama</h1>
                </div>

                <div class="academic-badge">
                    <i class="bi bi-calendar3"></i>
                    <span>Tahun Ajaran 2026/2027</span>
                </div>
            </header>

            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-shield-check"></i>
                            WAKIL KEPALA SEKOLAH BIDANG KURIKULUM
                        </div>

                        <h2 class="hero-title">
                            Selamat Datang, {{ $user->name }}.
                        </h2>

                        <p class="hero-description">
                            Kelola penguncian jadwal KBM, pencegahan bentrok otomatis,
                            monitoring siklus blok, dan pengawasan jurnal harian guru
                            secara terpusat melalui SCP System.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                </div>
            </section>

            <section class="content-card mb-4">
                <div class="section-header">
                    <div class="section-icon"><i class="bi bi-bar-chart-fill"></i></div>
                    <div>
                        <h2 class="section-title">Infografis Pengguna</h2>
                        <p class="section-description">Jumlah pengguna terdaftar.</p>
                    </div>
                </div>
                <div class="row g-3 pt-2">
                    <div class="col-6 col-md-4 col-xl"><div class="p-3 rounded-3 bg-light h-100"><div class="text-muted small">Siswa</div><div class="fs-4 fw-bold text-primary">{{ $stats['total_students'] }}</div></div></div>
                    <div class="col-6 col-md-4 col-xl"><div class="p-3 rounded-3 bg-light h-100"><div class="text-muted small">Orang Tua</div><div class="fs-4 fw-bold text-primary">{{ $stats['total_parents'] ?? 0 }}</div></div></div>
                    <div class="col-6 col-md-4 col-xl"><div class="p-3 rounded-3 bg-light h-100"><div class="text-muted small">Guru</div><div class="fs-4 fw-bold text-primary">{{ $stats['total_teachers'] }}</div></div></div>
                    <div class="col-6 col-md-6 col-xl"><div class="p-3 rounded-3 bg-light h-100"><div class="text-muted small">Wali Kelas</div><div class="fs-4 fw-bold text-primary">{{ $stats['total_homeroom'] }}</div></div></div>
                    <div class="col-12 col-md-6 col-xl"><div class="p-3 rounded-3 bg-light h-100"><div class="text-muted small">Guru BK</div><div class="fs-4 fw-bold text-primary">{{ $stats['total_bk'] }}</div></div></div>
                </div>
            </section>

            <section class="content-card mb-4">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-compass-fill"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Akses Cepat Fitur Manajemen</h3>
                        <p class="section-description">Pintasan modul utama Waka Kurikulum pada sistem SCP.</p>
                    </div>
                </div>
                <div class="row g-3 pt-2">
                    <div class="col-6 col-md-4 col-xl">
                        <a href="{{ route('schedules.index') }}" class="d-flex align-items-center gap-2 p-3 rounded-3 border text-decoration-none bg-light h-100">
                            <i class="bi bi-calendar-week text-primary fs-5"></i>
                            <span class="fw-bold text-dark" style="font-size: 12px;">Jadwal & penguncian</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-xl">
                        <a href="{{ route('master-data.index') }}" class="d-flex align-items-center gap-2 p-3 rounded-3 border text-decoration-none bg-light h-100">
                            <i class="bi bi-folder2-open text-primary fs-5"></i>
                            <span class="fw-bold text-dark" style="font-size: 12px;">Manajemen Data</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-xl">
                        <a href="{{ route('journals.index') }}" class="d-flex align-items-center gap-2 p-3 rounded-3 border text-decoration-none bg-light h-100">
                            <i class="bi bi-journal-text text-primary fs-5"></i>
                            <span class="fw-bold text-dark" style="font-size: 12px;">Jurnal mengajar</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-6 col-xl">
                        <a href="{{ route('audit-logs.index') }}" class="d-flex align-items-center gap-2 p-3 rounded-3 border text-decoration-none bg-light h-100">
                            <i class="bi bi-shield-check text-primary fs-5"></i>
                            <span class="fw-bold text-dark" style="font-size: 12px;">Audit log</span>
                        </a>
                    </div>
                    <div class="col-12 col-md-6 col-xl">
                        <a href="{{ route('settings.index') }}" class="d-flex align-items-center gap-2 p-3 rounded-3 border text-decoration-none bg-light h-100">
                            <i class="bi bi-gear text-primary fs-5"></i>
                            <span class="fw-bold text-dark" style="font-size: 12px;">Pengaturan akun</span>
                        </a>
                    </div>
                </div>
            </section>

            <section class="content-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>

                    <div>
                        <h3 class="section-title">
                            Ketentuan & Alur Operasional Sistem
                        </h3>
                        <p class="section-description">
                            Pedoman dan aturan teknis utama Waka Kurikulum dalam sistem SCP.
                        </p>
                    </div>
                </div>

                <div class="rule-list">

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">
                                Penguncian Jadwal Pelajaran (Lock Schedule)
                            </span>
                            <p class="rule-description">
                                Saat status jadwal terkunci, seluruh Guru tidak dapat
                                menginput atau mengedit jadwal Kegiatan Belajar Mengajar (KBM).
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">
                                Pencegahan Bentrok (Conflict Prevention)
                            </span>
                            <p class="rule-description">
                                Sistem secara otomatis mencegah penginputan jadwal yang
                                bertabrakan pada Guru maupun Kelas yang sama.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">
                                Kontrol Siklus Blok (Putaran 1 & Putaran 2)
                            </span>
                            <p class="rule-description">
                                Pengalihan siklus blok yang ditentukan oleh Waka berlaku
                                secara global untuk seluruh jadwal kelas.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">
                                Monitoring Jurnal KBM Guru
                            </span>
                            <p class="rule-description">
                                Waka memantau kepatuhan pengisian jurnal harian Guru
                                beserta pemeriksaan bukti foto pelaksanaan KBM.
                            </p>
                        </div>
                    </div>

                </div>
            </section>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
