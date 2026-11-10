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

            .total-users-pill,
            .btn-manage-users-pill {
                width: 100%;
                justify-content: center;
            }

            .civitas-card {
                padding: 12px 14px;
            }

            .civitas-value {
                font-size: 20px;
            }

            .civitas-badge {
                width: 34px;
                height: 34px;
                font-size: 15px;
            }

            .civitas-legend-row {
                gap: 8px;
            }
        }

        /* Bespoke Civitas Akademika Infographics */
        .civitas-stat-card {
            border: 1px solid var(--waka-border);
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
        }

        .total-users-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            font-size: 11.5px;
            color: #334155;
            font-weight: 600;
        }

        .btn-manage-users-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: #eff6ff;
            color: #174ea6;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-manage-users-pill:hover {
            background: #dbeafe;
            color: #123d80;
            border-color: #93c5fd;
        }

        .civitas-card {
            padding: 16px 18px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.02);
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            position: relative;
            overflow: hidden;
        }
        .civitas-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
            border-color: #cbd5e1;
        }

        .civitas-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .civitas-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            font-size: 17px;
        }

        .civitas-badge-siswa { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .civitas-badge-ortu  { background: #faf5ff; color: #7e22ce; border: 1px solid #f3e8ff; }
        .civitas-badge-guru  { background: #ecfdf5; color: #047857; border: 1px solid #d1fae5; }
        .civitas-badge-wali  { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
        .civitas-badge-bk    { background: #fff1f2; color: #be123c; border: 1px solid #ffe4e6; }

        .civitas-role-tag {
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #475569;
        }

        .civitas-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }

        .civitas-value {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.6px;
            line-height: 1.15;
            margin-bottom: 4px;
        }

        .civitas-caption {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }
        .civitas-pct {
            font-weight: 700;
        }
        .text-purple { color: #7e22ce !important; }
        .text-amber { color: #b45309 !important; }
        .text-rose { color: #be123c !important; }

        .civitas-bar-title {
            font-size: 11.5px;
            font-weight: 700;
            color: #334155;
        }

        .civitas-stacked-progress {
            display: flex;
            height: 7px;
            border-radius: 999px;
            overflow: hidden;
            background: #f1f5f9;
            gap: 2px;
        }
        .progress-segment {
            height: 100%;
            transition: width 0.3s ease;
        }
        .bg-siswa { background-color: #2563eb !important; }
        .bg-ortu  { background-color: #8b5cf6 !important; }
        .bg-guru  { background-color: #10b981 !important; }
        .bg-wali  { background-color: #f59e0b !important; }
        .bg-bk    { background-color: #f43f5e !important; }

        .civitas-legend-row {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .civitas-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 10.5px;
            font-weight: 600;
            color: #475569;
        }
        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
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

            @php
                $totSiswa = (int) ($stats['total_students'] ?? 0);
                $totOrtu = (int) ($stats['total_parents'] ?? 0);
                $totGuru = (int) ($stats['total_teachers'] ?? 0);
                $totWali = (int) ($stats['total_homeroom'] ?? 0);
                $totBk = (int) ($stats['total_bk'] ?? 0);
                $totalCivitas = $totSiswa + $totOrtu + $totGuru + $totWali + $totBk;

                $pctSiswa = $totalCivitas > 0 ? round(($totSiswa / $totalCivitas) * 100, 1) : 0;
                $pctOrtu = $totalCivitas > 0 ? round(($totOrtu / $totalCivitas) * 100, 1) : 0;
                $rasioPendamping = $totSiswa > 0 ? min(100, round(($totOrtu / $totSiswa) * 100)) : 0;
                $pctGuru = $totalCivitas > 0 ? round(($totGuru / $totalCivitas) * 100, 1) : 0;
                $pctWali = $totalCivitas > 0 ? round(($totWali / $totalCivitas) * 100, 1) : 0;
                $pctBk = $totalCivitas > 0 ? round(($totBk / $totalCivitas) * 100, 1) : 0;
            @endphp

            <section class="content-card mb-4 civitas-stat-card">
                <div class="section-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon">
                            <i class="bi bi-pie-chart-fill"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h2 class="section-title mb-0">Distribusi Civitas Akademika</h2>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-bold" style="font-size: 11px;">
                                    <i class="bi bi-shield-check me-1"></i>Sistem Terintegrasi
                                </span>
                            </div>
                            <p class="section-description">Monitoring sebaran akun aktif civitas sekolah dan tenaga pendidik terdaftar.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="total-users-pill">
                            <i class="bi bi-person-check-fill text-primary"></i>
                            <span>Total Civitas: <strong>{{ number_format($totalCivitas) }}</strong> Akun</span>
                        </div>
                        <a href="{{ route('accounts.index') }}" class="btn-manage-users-pill">
                            <i class="bi bi-person-gear"></i>
                            <span>Kelola Akun</span>
                        </a>
                    </div>
                </div>

                {{-- STAT CARDS GRID --}}
                <div class="row g-3 pt-3">
                    {{-- 1. SISWA --}}
                    <div class="col-6 col-md-4 col-xl">
                        <div class="civitas-card civitas-card-siswa h-100">
                            <div class="civitas-card-header">
                                <div class="civitas-badge civitas-badge-siswa">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                                <span class="civitas-role-tag">Siswa</span>
                            </div>
                            <div class="civitas-card-body">
                                <span class="civitas-label">Peserta Didik</span>
                                <div class="civitas-value">{{ number_format($totSiswa) }}</div>
                                <div class="civitas-caption">
                                    <span class="civitas-pct text-primary">{{ $pctSiswa }}%</span> dari total civitas
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. ORANG TUA --}}
                    <div class="col-6 col-md-4 col-xl">
                        <div class="civitas-card civitas-card-ortu h-100">
                            <div class="civitas-card-header">
                                <div class="civitas-badge civitas-badge-ortu">
                                    <i class="bi bi-person-heart"></i>
                                </div>
                                <span class="civitas-role-tag">Orang Tua</span>
                            </div>
                            <div class="civitas-card-body">
                                <span class="civitas-label">Wali Murid</span>
                                <div class="civitas-value">{{ number_format($totOrtu) }}</div>
                                <div class="civitas-caption">
                                    <span class="civitas-pct text-purple">{{ $rasioPendamping }}%</span> rasio pendampingan siswa
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. GURU --}}
                    <div class="col-6 col-md-4 col-xl">
                        <div class="civitas-card civitas-card-guru h-100">
                            <div class="civitas-card-header">
                                <div class="civitas-badge civitas-badge-guru">
                                    <i class="bi bi-person-video3"></i>
                                </div>
                                <span class="civitas-role-tag">Guru</span>
                            </div>
                            <div class="civitas-card-body">
                                <span class="civitas-label">Tenaga Pengajar</span>
                                <div class="civitas-value">{{ number_format($totGuru) }}</div>
                                <div class="civitas-caption">
                                    <span class="civitas-pct text-success">{{ $pctGuru }}%</span> pengampu KBM
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. WALI KELAS --}}
                    <div class="col-6 col-md-6 col-xl">
                        <div class="civitas-card civitas-card-wali h-100">
                            <div class="civitas-card-header">
                                <div class="civitas-badge civitas-badge-wali">
                                    <i class="bi bi-award-fill"></i>
                                </div>
                                <span class="civitas-role-tag">Wali Kelas</span>
                            </div>
                            <div class="civitas-card-body">
                                <span class="civitas-label">Pembina Rombel</span>
                                <div class="civitas-value">{{ number_format($totWali) }}</div>
                                <div class="civitas-caption">
                                    <span class="civitas-pct text-amber">{{ $pctWali }}%</span> pembina kelas
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. GURU BK --}}
                    <div class="col-12 col-md-6 col-xl">
                        <div class="civitas-card civitas-card-bk h-100">
                            <div class="civitas-card-header">
                                <div class="civitas-badge civitas-badge-bk">
                                    <i class="bi bi-heart-pulse-fill"></i>
                                </div>
                                <span class="civitas-role-tag">Guru BK</span>
                            </div>
                            <div class="civitas-card-body">
                                <span class="civitas-label">Bimbingan Konseling</span>
                                <div class="civitas-value">{{ number_format($totBk) }}</div>
                                <div class="civitas-caption">
                                    <span class="civitas-pct text-rose">{{ $pctBk }}%</span> konselor siswa
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEGMENTED RATIO PROGRESS BAR --}}
                <div class="civitas-distribution-bar-wrapper mt-3 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <span class="civitas-bar-title">Proporsi Sebaran Akun Civitas</span>
                        <span class="text-muted small" style="font-size: 11px;">100% Akumulasi Terdaftar</span>
                    </div>
                    <div class="civitas-stacked-progress">
                        <div class="progress-segment bg-siswa" style="width: {{ $pctSiswa }}%;" title="Siswa: {{ $pctSiswa }}%"></div>
                        <div class="progress-segment bg-ortu" style="width: {{ $pctOrtu }}%;" title="Orang Tua: {{ $pctOrtu }}%"></div>
                        <div class="progress-segment bg-guru" style="width: {{ $pctGuru }}%;" title="Guru: {{ $pctGuru }}%"></div>
                        <div class="progress-segment bg-wali" style="width: {{ $pctWali }}%;" title="Wali Kelas: {{ $pctWali }}%"></div>
                        <div class="progress-segment bg-bk" style="width: {{ $pctBk }}%;" title="Guru BK: {{ $pctBk }}%"></div>
                    </div>
                    <div class="civitas-legend-row">
                        <span class="civitas-legend-item"><span class="legend-dot bg-siswa"></span>Siswa ({{ $pctSiswa }}%)</span>
                        <span class="civitas-legend-item"><span class="legend-dot bg-ortu"></span>Orang Tua ({{ $pctOrtu }}%)</span>
                        <span class="civitas-legend-item"><span class="legend-dot bg-guru"></span>Guru ({{ $pctGuru }}%)</span>
                        <span class="civitas-legend-item"><span class="legend-dot bg-wali"></span>Wali Kelas ({{ $pctWali }}%)</span>
                        <span class="civitas-legend-item"><span class="legend-dot bg-bk"></span>Guru BK ({{ $pctBk }}%)</span>
                    </div>
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
