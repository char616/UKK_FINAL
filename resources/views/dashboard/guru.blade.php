<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - SCP System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            margin-bottom: 25px;
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

        /* SECTION HEADER */
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
            font-size: 13px;
            font-weight: 800;
        }

        .section-description {
            margin: 0;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.6;
        }

        /* CONTENT CARD */
        .content-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(31,52,73,.035);
            padding: 20px;
            margin-bottom: 20px;
        }

        /* BUTTONS */
        .btn-primary-custom {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            min-height: 34px;
            padding: 6px 14px;
            border: 1px solid var(--primary);
            border-radius: 7px;
            background: var(--primary);
            color: #FFFFFF;
            font-size: 11px;
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
            min-height: 34px;
            padding: 6px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 7px;
            background: #FFFFFF;
            color: var(--primary);
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            transition: .2s ease;
        }

        .btn-outline-primary-custom:hover {
            background: var(--primary-light);
            border-color: #B7CCEB;
            color: var(--primary-dark);
        }

        /* DASHBOARD QUICK LINKS */
        .scp-dashboard-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 25px;
        }

        .scp-dashboard-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            text-decoration: none;
            color: var(--text);
            font-weight: 700;
            font-size: 12px;
            transition: all .2s;
        }

        .scp-dashboard-link:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(23, 78, 166, 0.08);
        }

        .scp-dashboard-link-icon {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 7px;
            background: var(--primary-light);
            color: var(--primary);
            font-size: 16px;
        }

        .rule-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .rule-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            background: #F8FAFC;
            border: 1px solid #EDF1F5;
            border-radius: 8px;
        }

        .rule-check {
            display: grid;
            place-items: center;
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #EAF7F0;
            color: var(--success);
            font-size: 11px;
            margin-top: 1px;
        }

        .rule-content {
            flex-grow: 1;
        }

        .rule-title {
            display: block;
            margin-bottom: 3px;
            color: #203957;
            font-size: 11px;
            font-weight: 700;
        }

        .rule-description {
            margin: 0;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.6;
        }

        /* SUCCESS ALERT */
        .alert-scp-success {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
            background: #EAF7F0;
            border: 1px solid #D5EDDF;
            color: #23764F;
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

    @include('layouts.sidebar', ['user' => $user])

    <main class="main-content">
        <div class="content-wrapper">

            <header class="page-header">
                <div>
                    <div class="page-eyebrow">
                        <i class="bi bi-journal-text"></i>
                        Tenaga Pendidik Panel
                    </div>
                    <h1 class="page-title">Dashboard Pengajar</h1>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="academic-badge">
                        <i class="bi bi-calendar3"></i>
                        <span>Tahun Ajaran 2026/2027</span>
                    </div>
                </div>
            </header>

            @if(session('success'))
                <div class="alert-scp-success">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-person-workspace"></i>
                            GURU PENGAJAR
                        </div>

                        <h2 class="hero-title">
                            Selamat Datang, {{ $user->name }}.
                        </h2>

                        <p class="hero-description">
                            Kelola presensi per jam pelajaran dan pencatatan jurnal mengajar dalam satu alur terpadu, lengkap dengan dokumentasi foto kegiatan KBM dan rekapitulasi kehadiran kelas.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                </div>
            </section>

            <!-- AKSES CEPAT GURU -->
            <section class="scp-dashboard-links">
                <a class="scp-dashboard-link" href="{{ route('attendances.index') }}">
                    <div class="d-flex align-items-center gap-3">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-journal-check"></i></span>
                        <div>
                            <span class="d-block text-dark">Presensi & Jurnal</span>
                            <small class="text-muted fw-normal" style="font-size: 10px;">Input terpadu KBM</small>
                        </div>
                    </div>
                    <i class="bi bi-arrow-up-right text-muted"></i>
                </a>

                <a class="scp-dashboard-link" href="{{ route('schedules.index') }}">
                    <div class="d-flex align-items-center gap-3">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-calendar-week"></i></span>
                        <div>
                            <span class="d-block text-dark">Jadwal Mengajar</span>
                            <small class="text-muted fw-normal" style="font-size: 10px;">Plotting jam & ruangan</small>
                        </div>
                    </div>
                    <i class="bi bi-arrow-up-right text-muted"></i>
                </a>

                <a class="scp-dashboard-link" href="{{ route('journals.index') }}">
                    <div class="d-flex align-items-center gap-3">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-clock-history"></i></span>
                        <div>
                            <span class="d-block text-dark">Riwayat Jurnal</span>
                            <small class="text-muted fw-normal" style="font-size: 10px;">Laporan materi & foto</small>
                        </div>
                    </div>
                    <i class="bi bi-arrow-up-right text-muted"></i>
                </a>

                <a class="scp-dashboard-link" href="{{ route('notifications.index') }}">
                    <div class="d-flex align-items-center gap-3">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-bell"></i></span>
                        <div>
                            <span class="d-block text-dark">Notifikasi</span>
                            <small class="text-muted fw-normal" style="font-size: 10px;">Pemberitahuan KBM</small>
                        </div>
                    </div>
                    <i class="bi bi-arrow-up-right text-muted"></i>
                </a>
            </section>

            <!-- CARD JADWAL HARI INI -->
            <section class="content-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div>
                        <h3 class="section-title">
                            Jadwal Mengajar Hari Ini &mdash; {{ $todayDay ?? 'Hari Ini' }}, {{ \Carbon\Carbon::parse($todayDate ?? now())->translatedFormat('d F Y') }}
                        </h3>
                        <p class="section-description">Daftar kelas yang harus Anda ajar hari ini beserta status pengisian presensi & jurnal mengajar.</p>
                    </div>
                </div>

                @if(isset($todaySchedules) && $todaySchedules->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr style="background:#F8FAFC;">
                                    <th width="20%">Sesi & Jam KBM</th>
                                    <th width="22%">Nama Kelas & Jurusan</th>
                                    <th width="25%">Mata Pelajaran</th>
                                    <th width="15%">Ruangan</th>
                                    <th width="18%" class="text-center">Status & Aksi KBM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todaySchedules as $sched)
                                    @php
                                        $isFilled = isset($todayJournalMap) && $todayJournalMap->has($sched->id);
                                        $materi = $isFilled ? $todayJournalMap->get($sched->id) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace">
                                                <i class="bi bi-clock-history me-1"></i>{{ $sched->session_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-dark d-block">{{ $sched->class?->name ?? '-' }}</strong>
                                            <small class="text-muted" style="font-size: 10px;">{{ $sched->class?->major?->name ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <strong class="text-dark d-block">{{ $sched->subject?->name ?? '-' }}</strong>
                                            @if($sched->subject?->code)
                                                <small class="badge bg-light text-secondary border font-monospace">{{ $sched->subject->code }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-door-closed me-1"></i>{{ $sched->room?->name ?? 'Ruang Kelas' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($isFilled)
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Sudah Diisi
                                                    </span>
                                                    <a href="{{ route('attendances.create', ['schedule' => $sched->id, 'date' => $todayDate ?? now()->toDateString()]) }}" class="btn-outline-primary-custom btn-sm py-1 px-2" style="font-size: 10px;">
                                                        <i class="bi bi-pencil-square"></i> Edit Sesi
                                                    </a>
                                                </div>
                                            @else
                                                <a href="{{ route('attendances.create', ['schedule' => $sched->id, 'date' => $todayDate ?? now()->toDateString()]) }}" class="btn-primary-custom btn-sm shadow-sm py-1.5 px-3">
                                                    <i class="bi bi-pencil-fill"></i> Isi Presensi & Jurnal
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info py-3 px-3 d-flex align-items-center gap-2 border-0 rounded-3 mb-0" style="background:#EFF6FF;color:#1D4ED8;">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>
                            <strong>Tidak Ada Jadwal Mengajar Hari Ini.</strong>
                            <div class="small">Anda tidak memiliki jadwal KBM yang terdaftar pada hari {{ $todayDay ?? 'ini' }}. Silakan lihat jadwal mingguan di bawah atau buka menu jadwal pelajaran.</div>
                        </div>
                    </div>
                @endif
            </section>

            <!-- CARD KETENTUAN ALUR MENGAJAR GURU -->
            <section class="content-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Ketentuan & Alur Mengajar Terpadu</h3>
                        <p class="section-description">Pedoman pelaksanaan tugas akademik dan pelaporan mengajar.</p>
                    </div>
                </div>

                <div class="rule-list">
                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Pengisian Terpadu: Presensi Siswa & Jurnal Mengajar Jadi Satu</span>
                            <p class="rule-description">
                                Guru mengisi presensi kehadiran siswa sekaligus materi pembelajaran, rangkuman kegiatan, catatan kelas, dan foto bukti KBM dalam 1 formulir lalu disimpan serentak.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Dokumentasi Foto KBM Wajib Dilampirkan</span>
                            <p class="rule-description">
                                Setiap selesai tatap muka, lampirkan foto dokumentasi pelaksanaan KBM untuk keperluan audit kurikulum dan pelaporan pimpinan sekolah.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Sinkronisasi Izin Siswa Secara Otomatis</span>
                            <p class="rule-description">
                                Status izin atau sakit siswa yang telah disetujui oleh Wali Kelas (seharian) atau Guru BK (per jam pelajaran) otomatis tertera pada daftar presensi pengajar.
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
