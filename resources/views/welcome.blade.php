<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran | SCP System</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

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

        /* PERBAIKAN UTAMA: Gunakan flex pada body agar sidebar & konten berdampingan pas */
        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* KONTEN UTAMA OTOMATIS MENYESUAIKAN SISA RUANG DI KANAN SIDEBAR */
        .schedule-main {
            flex-grow: 1;
            min-width: 0;
            min-height: 100vh;
            padding: 30px 34px 45px;
            height: 100vh;
            overflow-y: auto;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .page-breadcrumb {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 500;
        }

        .page-breadcrumb .current {
            color: var(--primary);
            font-weight: 700;
        }

        .page-title {
            margin: 0 0 6px;
            color: #1E3552;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.7px;
        }

        .page-description {
            margin: 0;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.7;
        }

        .header-date {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
        }

        .header-date i {
            color: var(--primary);
            font-size: 17px;
        }

        .header-date span {
            color: var(--text);
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* HERO BANNER */
        .hero-banner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            position: relative;
            overflow: hidden;
            padding: 25px 28px;
            margin-bottom: 22px;
            color: #FFFFFF;
            background: linear-gradient(110deg, #174EA6 0%, #2563EB 100%);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(23, 78, 166, .10);
        }

        .hero-banner::after {
            content: "";
            position: absolute;
            right: 75px;
            top: -100px;
            width: 200px;
            height: 200px;
            border: 30px solid rgba(255,255,255,.07);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-content {
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

        .hero-banner h2 {
            margin: 0 0 7px;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -.4px;
        }

        .hero-banner p {
            margin: 0;
            color: rgba(255,255,255,.82);
            font-size: 11px;
        }

        .hero-icon {
            position: relative;
            z-index: 1;
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

        /* NOTICES / ALERTS */
        .notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 15px;
            margin-bottom: 17px;
            border-radius: 7px;
            font-size: 11px;
            line-height: 1.6;
        }

        .notice i {
            font-size: 16px;
            flex-shrink: 0;
        }

        .notice-success {
            color: #23764F;
            background: #EAF7F0;
            border: 1px solid #D5EDDF;
        }

        .notice-error {
            color: #B33F3F;
            background: #FFF1F1;
            border: 1px solid #F3DADA;
        }

        /* CARD */
        .custom-card {
            height: 100%;
            overflow: hidden;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(31,52,73,.035);
        }

        .card-header-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
        }

        .card-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-icon {
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

        .card-heading {
            margin: 0 0 4px;
            color: #203957;
            font-size: 12px;
            font-weight: 800;
        }

        .card-subheading {
            margin: 0;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.6;
        }

        .card-body-custom {
            padding: 20px;
        }

        /* BUTTONS */
        .btn-primary-custom {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            min-height: 35px;
            padding: 7px 14px;
            border: 1px solid var(--primary);
            border-radius: 6px;
            background: var(--primary);
            color: #FFFFFF;
            font-family: inherit;
            font-size: 10px;
            font-weight: 700;
            transition: .2s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary-custom:hover {
            border-color: var(--primary-dark);
            background: var(--primary-dark);
            color: #FFFFFF;
        }

        /* TABLES */
        .schedule-table {
            min-width: 650px;
            margin: 0;
            vertical-align: middle;
        }

        .schedule-table thead th {
            padding: 13px 14px;
            border-bottom: 1px solid var(--border);
            background: #F7F9FC;
            color: #64748B;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .4px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .schedule-table tbody td {
            padding: 14px;
            border-bottom: 1px solid #EDF1F5;
            font-size: 10px;
        }

        .schedule-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .schedule-table tbody tr:hover {
            background: #FAFCFF;
        }

        .table-primary-text {
            display: block;
            margin-bottom: 4px;
            color: #263E5A;
            font-size: 10px;
            font-weight: 700;
        }

        .table-secondary-text {
            display: block;
            color: var(--muted);
            font-size: 9px;
            line-height: 1.6;
        }

        .empty-state {
            padding: 45px 15px !important;
            text-align: center;
        }

        .empty-state i {
            display: block;
            margin-bottom: 10px;
            color: #9AAABD;
            font-size: 30px;
        }

        .empty-state strong {
            display: block;
            margin-bottom: 5px;
            color: #344B67;
            font-size: 11px;
        }

        .empty-state span {
            color: var(--muted);
            font-size: 10px;
        }

        /* RESPONSIVE LAYOUT FIXES */
        @media (max-width: 991px) {
            .schedule-main {
                width: 100%;
                margin-left: 0;
                padding: 25px 22px 40px;
            }
        }

        @media (max-width: 768px) {
            .header-date {
                display: none;
            }

            .page-title {
                font-size: 21px;
            }

            .hero-banner {
                padding: 20px;
            }

            .hero-banner h2 {
                font-size: 17px;
            }

            .card-header-custom {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- INCLUDE SIDEBAR -->
    @include('layouts.sidebar', ['user' => $user])

    <main class="schedule-main">

        <!-- HEADER HALAMAN -->
        <div class="page-header">
            <div>
                <div class="page-breadcrumb">
                    <span>Pengajaran & KBM</span>
                    <i class="bi bi-chevron-right"></i>
                    <span class="current">Jadwal Pelajaran</span>
                </div>

                <h1 class="page-title">Jadwal Pelajaran</h1>

                <p class="page-description">
                    Kelola dan pantau jadwal kegiatan belajar mengajar semester ini.
                </p>
            </div>

            <div class="header-date">
                <i class="bi bi-calendar3"></i>
                <span>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
            </div>
        </div>

        <!-- HERO BANNER -->
        <section class="hero-banner">
            <div class="hero-content">
                <div class="hero-label">
                    <i class="bi bi-calendar-week"></i>
                    SCHOOL CLASS PROGRESS
                </div>

                <h2>Pengelolaan Jadwal KBM</h2>

                <p>
                    Informasi lengkap jadwal pelajaran, penguncian, serta alur penyesuaian waktu kelas.
                </p>
            </div>

            <div class="hero-icon">
                <i class="bi bi-calendar-range"></i>
            </div>
        </section>

        <!-- NOTIFIKASI -->
        @if(session('success'))
            <div class="notice notice-success">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="notice notice-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <!-- DAFTAR JADWAL -->
        <section class="custom-card">
            <div class="card-header-custom">
                <div class="card-header-left">
                    <div class="card-icon">
                        <i class="bi bi-list-task"></i>
                    </div>
                    <div>
                        <h2 class="card-heading">Daftar Jadwal Pelajaran</h2>
                        <p class="card-subheading">
                            Seluruh jadwal aktif yang terdaftar dalam sistem.
                        </p>
                    </div>
                </div>

                @if(auth()->user()->role->name === 'waka')
                    <div>
                        <a href="#" class="btn-primary-custom">
                            <i class="bi bi-plus-lg"></i> Tambah Jadwal
                        </a>
                    </div>
                @endif
            </div>

            <div class="card-body-custom p-0">
                <div class="table-responsive">
                    <table class="table schedule-table">
                        <thead>
                            <tr>
                                <th>Hari & Waktu</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th>Status / Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules ?? [] as $schedule)
                                <tr>
                                    <td>
                                        <span class="table-primary-text">{{ $schedule->day }}</span>
                                        <span class="table-secondary-text">
                                            {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="table-primary-text">{{ $schedule->class?->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="table-primary-text">{{ $schedule->subject?->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="table-primary-text">{{ $schedule->teacher?->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 fw-bold" style="font-size: 9px;">Aktif</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <i class="bi bi-calendar-x"></i>
                                        <strong>Belum Ada Jadwal Pelajaran</strong>
                                        <span>Data jadwal pelajaran belum tersedia di dalam sistem.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

</body>
</html>