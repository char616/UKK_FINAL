
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Mengajar | SCP2</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

        /* MAIN CONTENT */
        .journal-main {
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

        /* HERO */
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

        /* ALERT */
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
            align-items: center;
            gap: 12px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
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

        /* FORM */
        .form-group {
            margin-bottom: 17px;
        }

        .form-label {
            display: block;
            margin-bottom: 7px;
            color: #34445A;
            font-size: 11px;
            font-weight: 700;
        }

        .form-label .optional {
            color: var(--muted);
            font-weight: 400;
        }

        .form-control,
        .form-select {
            min-height: 40px;
            padding: 9px 12px;
            border: 1px solid #DCE4ED;
            border-radius: 6px;
            background: #FFFFFF;
            color: var(--text);
            font-family: inherit;
            font-size: 11px;
            box-shadow: none;
        }

        .form-control::placeholder {
            color: #A0ACBA;
        }

        textarea.form-control {
            min-height: 90px;
            resize: vertical;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(23,78,166,.09);
        }

        .form-text {
            color: var(--muted);
            font-size: 10px;
            line-height: 1.6;
        }

        /* BUTTON */
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
            font-family: inherit;
            font-size: 10px;
            font-weight: 700;
            transition: .2s ease;
        }

        .btn-primary-custom:hover {
            border-color: var(--primary-dark);
            background: var(--primary-dark);
            color: #FFFFFF;
        }

        .btn-outline-custom {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            min-height: 35px;
            padding: 8px 11px;
            border: 1px solid #D5DFEA;
            border-radius: 6px;
            background: #FFFFFF;
            color: var(--primary);
            font-family: inherit;
            font-size: 10px;
            font-weight: 700;
            transition: .2s ease;
        }

        .btn-outline-custom:hover {
            border-color: #B7CCEB;
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        /* MONITORING WAKA */
        .monitor-filter {
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }

        .monitor-box {
            padding: 16px;
            margin: 18px 20px 20px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: #F8FAFD;
        }

        .monitor-stat {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 11px;
            color: var(--muted);
            font-size: 11px;
        }

        .monitor-stat strong {
            color: var(--primary);
        }

        .monitor-list {
            padding: 0;
            margin: 0 0 15px;
            list-style: none;
        }

        .monitor-list li {
            display: flex;
            gap: 9px;
            padding: 10px 0;
            border-bottom: 1px solid #E8EDF3;
            color: #536477;
            font-size: 10px;
            line-height: 1.6;
        }

        .monitor-list li:last-child {
            border-bottom: 0;
        }

        .monitor-list i {
            flex-shrink: 0;
            color: #D49A37;
        }

        .monitor-empty {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--success);
            font-size: 11px;
            font-weight: 600;
        }

        /* CAMERA */
        .camera-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-bottom: 10px;
        }

        .camera-box {
            padding: 5px;
            margin-bottom: 12px;
            overflow: hidden;
            border-radius: 7px;
            background: #1E293B;
        }

        #camera-preview {
            display: block;
            width: 100%;
            max-height: 260px;
            border-radius: 5px;
            object-fit: cover;
        }

        .attachment-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: #F8FAFC;
        }

        #attachment-thumb {
            width: 55px;
            height: 55px;
            flex-shrink: 0;
            border: 1px solid var(--border);
            border-radius: 5px;
            object-fit: cover;
        }

        .attachment-info {
            min-width: 0;
            overflow-wrap: anywhere;
            color: var(--muted);
            font-size: 10px;
        }

        .attachment-info strong {
            display: block;
            margin-bottom: 4px;
            color: var(--text);
        }

        /* TABLE */
        .journal-table {
            min-width: 650px;
            margin: 0;
            vertical-align: middle;
        }

        .journal-table thead th {
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

        .journal-table tbody td {
            padding: 14px;
            border-bottom: 1px solid #EDF1F5;
            font-size: 10px;
        }

        .journal-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .journal-table tbody tr:hover {
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

        .date-label {
            color: #536477;
            font-weight: 600;
            white-space: nowrap;
        }

        .evidence-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 9px;
            border: 1px solid #C9D9ED;
            border-radius: 5px;
            background: #F4F8FF;
            color: var(--primary);
            font-size: 9px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        .evidence-link:hover {
            background: var(--primary);
            color: #FFFFFF;
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

        /* RESPONSIVE */
        @media (max-width: 991.98px) {
            .journal-main {
                width: 100% !important;
                margin-left: 0 !important;
                padding: 88px 22px 35px !important;
            }
        }

        @media (max-width: 768px) {
            .journal-main {
                width: 100%;
                margin-left: 0;
                padding: 82px 16px 30px !important;
            }

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

            .card-header-custom,
            .card-body-custom {
                padding: 15px;
            }

            .monitor-box {
                margin: 14px 15px 15px;
            }
        }

        @media (max-width: 480px) {
            .hero-icon {
                display: none;
            }

            .monitor-filter {
                align-items: stretch;
                flex-direction: column;
            }

            .monitor-filter button {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    @include('layouts.sidebar', ['user' => $user])

    <main class="journal-main">
        <div class="content-wrapper">

        <!-- HEADER HALAMAN -->
        <div class="page-header">
            <div>
                <div class="page-eyebrow">
                    <i class="bi bi-journal-text"></i>
                    Manajemen KBM
                </div>

                <h1 class="page-title">Jurnal Mengajar</h1>
            </div>

            <div class="academic-badge">
                <i class="bi bi-calendar3"></i>
                <span>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
            </div>
        </div>

        <!-- BANNER -->
        <section class="hero-banner">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-label">
                        <i class="bi bi-journal-check"></i>
                        SCHOOL CLASS PROGRESS
                    </div>

                    <h2 class="hero-title">Jurnal Mengajar</h2>

                    <p class="hero-description">
                        Catat dan pantau kegiatan belajar mengajar.
                    </p>
                </div>

                <div class="hero-icon">
                    <i class="bi bi-journal-bookmark"></i>
                </div>
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

        <!-- MONITORING WAKA -->
        @if($user->role->name === 'waka')
            <section class="custom-card mb-4">

                <div class="card-header-custom">
                    <div class="card-icon">
                        <i class="bi bi-clipboard-data"></i>
                    </div>

                    <div>
                        <h2 class="card-heading">Monitoring Jurnal Guru</h2>
                        <p class="card-subheading">
                            Periksa jadwal pembelajaran yang belum memiliki jurnal.
                        </p>
                    </div>
                </div>

                <div class="card-body-custom pb-0">
                    <form action="{{ route('journals.index') }}" method="GET" class="monitor-filter">
                        <div style="max-width: 230px; width: 100%;">
                            <label for="journal-date" class="form-label">
                                Tanggal Monitoring
                            </label>

                            <input
                                id="journal-date"
                                type="date"
                                name="date"
                                class="form-control"
                                value="{{ $journalDate }}"
                                max="{{ date('Y-m-d') }}"
                                required
                            >
                        </div>

                        <button type="submit" class="btn-primary-custom">
                            <i class="bi bi-search"></i>
                            Periksa Jurnal
                        </button>
                    </form>
                </div>

                <div class="monitor-box">
                    @if($schedulesWithoutJournal->isEmpty())

                        <div class="monitor-empty">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>
                                Tidak ada jurnal tertinggal untuk jadwal pada tanggal
                                {{ $journalDate }}.
                            </span>
                        </div>

                    @else

                        <div class="monitor-stat">
                            <span>Terdapat</span>
                            <strong>{{ $schedulesWithoutJournal->count() }} jadwal</strong>
                            <span>yang belum memiliki jurnal dari</span>
                            <strong>{{ $schedulesWithoutJournal->groupBy('teacher_id')->count() }} guru.</strong>
                        </div>

                        <ul class="monitor-list">
                            @foreach($schedulesWithoutJournal as $schedule)
                                <li>
                                    <i class="bi bi-exclamation-circle-fill"></i>

                                    <span>
                                        <strong>{{ $schedule->teacher?->name ?? '-' }}</strong>
                                        &mdash;
                                        {{ $schedule->day }},
                                        {{ substr($schedule->start_time, 0, 5) }}:
                                        {{ $schedule->class?->name ?? '-' }}
                                        /
                                        {{ $schedule->subject?->name ?? '-' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>

                        <form action="{{ route('journals.reminders') }}" method="POST">
                            @csrf

                            <input type="hidden" name="date" value="{{ $journalDate }}">

                            <button type="submit" class="btn-primary-custom">
                                <i class="bi bi-bell"></i>
                                Kirim Pengingat ke Guru
                            </button>
                        </form>

                    @endif
                </div>
            </section>
        @endif

        <div class="row g-4">

            <!-- INPUT GURU TERPADU DENGAN PRESENSI -->
            @if($user->role->name === 'guru')
                <div class="col-12">
                    <div class="notice" style="background:#EFF6FF;border:1px solid #BFDBFE;color:#1E40AF;">
                        <i class="bi bi-info-circle-fill"></i>
                        <div>
                            <strong>Presensi dan Jurnal Mengajar telah menjadi satu alur.</strong>
                            Pilih jadwal pada menu <a href="{{ route('attendances.index') }}" class="fw-bold text-decoration-underline" style="color:#174EA6;">Presensi & Jurnal</a>, lalu isi kehadiran siswa, materi, catatan, dan foto bukti dalam satu formulir.
                        </div>
                    </div>
                </div>

                @if(isset($hasScheduleToday) && !$hasScheduleToday)
                    <div class="col-12">
                        <div class="notice" style="background:#F8FAFC;border:1px solid #E2E8F0;color:#475569;">
                            <i class="bi bi-calendar-x text-primary fs-5"></i>
                            <div>
                                <strong class="text-dark">Tidak Ada Jam Pelajaran Hari Ini ({{ $todayDay ?? 'Hari Ini' }}).</strong>
                                <div>Anda tidak memiliki jadwal mengajar yang dijadwalkan pada hari ini. Sesuai ketentuan, jurnal mengajar dan presensi hanya dapat diisi pada hari pelaksanaan KBM yang berlaku.</div>
                            </div>
                        </div>
                    </div>
                @elseif(isset($schedules) && $schedules->isNotEmpty())
                    <div class="col-12">
                        <div class="custom-card p-3 mb-2">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <strong class="table-primary-text"><i class="bi bi-calendar-week me-1 text-primary"></i>Jadwal Mengajar Hari Ini ({{ $todayDay ?? 'Hari Ini' }})</strong>
                                <a href="{{ route('attendances.index') }}" class="btn-outline-custom py-1 px-2" style="font-size:10px;">Buka Presensi & Jurnal Terpadu</a>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($schedules as $sched)
                                    <a href="{{ route('attendances.create', ['schedule' => $sched->id, 'date' => now()->toDateString()]) }}" class="badge bg-light text-dark border p-2 text-decoration-none">
                                        <span class="fw-bold text-primary">{{ $sched->class?->name }}</span> &bull; {{ $sched->subject?->name }} ({{ $sched->session_label }})
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- RIWAYAT JURNAL -->
            <div class="col-12">
                <section class="custom-card">

                    <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="card-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h2 class="card-heading">Riwayat Jurnal Mengajar</h2>
                                <p class="card-subheading">
                                    Daftar laporan kegiatan pembelajaran yang telah dicatat.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- FORM FILTER DATA JURNAL -->
                    <div class="p-3 bg-light border-bottom">
                        <form action="{{ route('journals.index') }}" method="GET" class="row g-2 align-items-end">
                            <div class="col-sm-6 col-md-3">
                                <label class="form-label small fw-bold mb-1">Filter Tanggal</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" name="date" class="form-control" value="{{ request('date', $journalDate ?? '') }}">
                                    <a href="{{ route('journals.index', array_merge(request()->except('date', 'page'), ['date' => now()->toDateString()])) }}" class="btn btn-outline-secondary" title="Hari Ini">
                                        Hari Ini
                                    </a>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-3">
                                <label class="form-label small fw-bold mb-1">Kelas</label>
                                <select name="class_id" class="form-select form-select-sm">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}" @selected(request('class_id') == $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-6 col-md-3">
                                <label class="form-label small fw-bold mb-1">Mata Pelajaran</label>
                                <select name="subject_id" class="form-select form-select-sm">
                                    <option value="">Semua Mapel</option>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s->id }}" @selected(request('subject_id') == $s->id)>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($user->role->name === 'waka')
                                <div class="col-sm-6 col-md-2">
                                    <label class="form-label small fw-bold mb-1">Guru</label>
                                    <select name="teacher_id" class="form-select form-select-sm">
                                        <option value="">Semua Guru</option>
                                        @foreach($teachers as $t)
                                            <option value="{{ $t->id }}" @selected(request('teacher_id') == $t->id)>{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-sm-12 col-md d-flex gap-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-bold">
                                    <i class="bi bi-funnel-fill me-1"></i> Filter
                                </button>
                                @if(request()->hasAny(['date', 'class_id', 'subject_id', 'teacher_id']))
                                    <a href="{{ route('journals.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="card-body-custom p-0">
                        <div class="table-responsive">
                            <table class="table journal-table">
                                <thead>
                                    <tr>
                                        @if($user->role->name !== 'guru')
                                            <th>Guru</th>
                                        @endif

                                        <th>Tanggal</th>
                                        <th>Kelas & Mapel</th>
                                        <th>Materi & Ringkasan</th>
                                        <th>Bukti</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($journals as $jurnal)
                                        <tr>
                                            @if($user->role->name !== 'guru')
                                                <td>
                                                    <span class="table-primary-text">
                                                        {{ $jurnal->teacher?->name ?? '-' }}
                                                    </span>
                                                </td>
                                            @endif

                                            <td>
                                                <span class="date-label">
                                                    {{ \Carbon\Carbon::parse($jurnal->date)->translatedFormat('d M Y') }}
                                                </span>
                                                <span class="badge bg-light text-primary border font-monospace mt-1 d-inline-block" style="font-size:9px;">
                                                    <i class="bi bi-clock-history me-1"></i>{{ $jurnal->schedule?->session_label ?? '-' }}
                                                </span>
                                            </td>

                                            <td>
                                                <strong class="table-primary-text d-block">
                                                    {{ $jurnal->schedule?->class?->name ?? '-' }}
                                                </strong>

                                                <span class="table-secondary-text">
                                                    {{ $jurnal->schedule?->subject?->name ?? '-' }}@if($jurnal->schedule?->subject?->code) ({{ $jurnal->schedule->subject->code }})@endif
                                                </span>
                                                @if($jurnal->schedule?->room)
                                                    <span class="table-secondary-text d-block" style="font-size:9px;">
                                                        <i class="bi bi-door-closed me-1"></i>{{ $jurnal->schedule->room->name }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td>
                                                <span class="table-primary-text fw-bold">
                                                    {{ $jurnal->materi }}
                                                </span>

                                                <span class="table-secondary-text d-block mt-0.5">
                                                    {{ $jurnal->summary }}
                                                </span>
                                                @if($jurnal->catatan_kejadian && $jurnal->catatan_kejadian !== $jurnal->summary)
                                                    <small class="text-muted d-block mt-1 fst-italic" style="font-size:10px;">
                                                        <i class="bi bi-chat-left-text me-1"></i>Catatan: {{ $jurnal->catatan_kejadian }}
                                                    </small>
                                                @endif
                                            </td>

                                            <td>
                                                @if($jurnal->attachment)
                                                    @if(str_ends_with(strtolower($jurnal->attachment), '.jpg') || str_ends_with(strtolower($jurnal->attachment), '.jpeg') || str_ends_with(strtolower($jurnal->attachment), '.png'))
                                                        <button type="button" class="evidence-link border-0 bg-transparent p-0 js-proof-preview" data-proof-url="{{ route('journals.attachment', $jurnal) }}" data-proof-title="Bukti KBM - {{ $jurnal->teacher?->name ?? 'Guru' }}">
                                                            <i class="bi bi-eye"></i> Lihat Foto
                                                        </button>
                                                    @else
                                                        <a href="{{ route('journals.attachment', $jurnal) }}" class="evidence-link" target="_blank" rel="noopener noreferrer"><i class="bi bi-eye"></i> Lihat Bukti</a>
                                                    @endif
                                                @else
                                                    <span class="table-secondary-text">
                                                        Tidak ada bukti
                                                    </span>
                                                @endif

                                                @if($user->role->name === 'guru' && $jurnal->teacher_id === $user->id)
                                                    @if(\Carbon\Carbon::parse($jurnal->date)->toDateString() < now()->toDateString())
                                                        <span class="badge bg-light text-muted border mt-2 py-1 px-2 d-inline-flex align-items-center" style="font-size:9px;" title="Batas waktu pengeditan telah berakhir (Terkunci)">
                                                            <i class="bi bi-lock-fill me-1"></i> Terkunci
                                                        </span>
                                                    @else
                                                        <a href="{{ route('attendances.create', ['schedule' => $jurnal->schedule_id, 'date' => $jurnal->date]) }}" class="btn-outline-custom mt-2 py-1 px-2 d-inline-flex" style="font-size:9px;min-height:26px;">
                                                            <i class="bi bi-pencil-square"></i> Edit Presensi & Jurnal
                                                        </a>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td
                                                colspan="{{ $user->role->name === 'guru' ? 4 : 5 }}"
                                                class="empty-state"
                                            >
                                                <i class="bi bi-journal-x"></i>

                                                <strong>Belum Ada Jurnal Mengajar</strong>

                                                <span>
                                                    Data jurnal yang telah dicatat akan ditampilkan di sini.
                                                </span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        </div>
    </main>

    <!-- SCRIPT KAMERA -->
    <script>
    (function () {
        const openBtn = document.getElementById('btn-open-camera');

        if (!openBtn) return;

        const switchBtn = document.getElementById('btn-switch-camera');
        const captureBtn = document.getElementById('btn-capture');
        const closeBtn = document.getElementById('btn-close-camera');

        const cameraBox = document.getElementById('camera-box');
        const video = document.getElementById('camera-preview');
        const canvas = document.getElementById('camera-canvas');

        const fileInput = document.getElementById('attachment-input');
        const fallbackInput = document.getElementById('camera-fallback');
        const attachmentDataInput = document.getElementById('attachment-data');

        const preview = document.getElementById('attachment-preview');
        const thumb = document.getElementById('attachment-thumb');
        const nameLabel = document.getElementById('attachment-name');
        const message = document.getElementById('camera-message');

        const MAX_BYTES = 5 * 1024 * 1024;
        const MAX_IMAGE_EDGE = 1600;

        const fileToDataUrl = (file) => new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(typeof reader.result === 'string' ? reader.result : '');
            reader.onerror = () => reject(new Error('Gagal membaca file.'));
            reader.readAsDataURL(file);
        });

        const supportsLiveCamera = Boolean(
            navigator.mediaDevices &&
            navigator.mediaDevices.getUserMedia
        );

        let stream = null;
        let facingMode = 'user';
        let previewUrl = null;

        const showMessage = (text) => {
            message.textContent = text;
            message.hidden = !text;
        };

        const showPreview = (file) => {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }

            nameLabel.textContent =
                file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';

            if (file.type.startsWith('image/')) {
                previewUrl = URL.createObjectURL(file);
                thumb.src = previewUrl;
                thumb.hidden = false;
            } else {
                thumb.removeAttribute('src');
                thumb.hidden = true;
            }

            preview.hidden = false;
        };

        const compressImage = (file) => new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const img = new Image();

            img.onload = () => {
                const scale = Math.min(
                    1,
                    MAX_IMAGE_EDGE / Math.max(img.naturalWidth, img.naturalHeight)
                );

                const width = Math.round(img.naturalWidth * scale);
                const height = Math.round(img.naturalHeight * scale);

                const work = document.createElement('canvas');
                work.width = width;
                work.height = height;

                const context = work.getContext('2d');

                if (!context) {
                    URL.revokeObjectURL(url);
                    reject(new Error('Browser tidak dapat memproses foto.'));
                    return;
                }

                context.drawImage(img, 0, 0, width, height);
                URL.revokeObjectURL(url);

                work.toBlob((blob) => {
                    if (blob) {
                        resolve(blob);
                    } else {
                        reject(new Error('Gagal mengubah foto.'));
                    }
                }, 'image/jpeg', 0.85);
            };

            img.onerror = () => {
                URL.revokeObjectURL(url);
                reject(new Error('Format foto tidak dapat dibaca browser.'));
            };

            img.src = url;
        });

        const prepareFile = async (file) => {
            if (!file.type.startsWith('image/')) {
                return file;
            }

            try {
                const blob = await compressImage(file);
                const baseName = file.name.replace(/\.[^.]+$/, '') || 'bukti-jurnal';

                return new File(
                    [blob],
                    baseName + '.jpg',
                    { type: 'image/jpeg' }
                );
            } catch (error) {
                return file;
            }
        };

        const setFile = async (file) => {
            if (!file) return false;

            showMessage('Sedang menyiapkan file...');

            const prepared = await prepareFile(file);

            if (prepared.size > MAX_BYTES) {
                showMessage('Ukuran bukti maksimal 5 MB. Silakan pilih file lain.');
                if (attachmentDataInput) {
                    attachmentDataInput.value = '';
                }
                return false;
            }

            try {
                if (typeof DataTransfer !== 'undefined') {
                    const transfer = new DataTransfer();
                    transfer.items.add(prepared);
                    fileInput.files = transfer.files;
                }
            } catch (e) {
                // Keep original fileInput if DataTransfer fails
            }

            try {
                if (attachmentDataInput) {
                    attachmentDataInput.value = await fileToDataUrl(prepared);
                }
            } catch (e) {
                if (attachmentDataInput) {
                    attachmentDataInput.value = '';
                }
            }

            showMessage('');
            showPreview(prepared);

            return true;
        };

        document.querySelectorAll('.edit-attachment-input').forEach((input) => {
            input.addEventListener('change', async () => {
                const file = input.files[0];
                const hiddenData = input.closest('form')?.querySelector('.edit-attachment-data');
                if (!file || !hiddenData) return;
                const prepared = await prepareFile(file);
                if (prepared.size <= MAX_BYTES) {
                    try {
                        hiddenData.value = await fileToDataUrl(prepared);
                    } catch (e) {
                        hiddenData.value = '';
                    }
                }
            });
        });

        const releaseStream = () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }

            video.srcObject = null;
        };

        const showCameraControls = (isOpen) => {
            cameraBox.hidden = !isOpen;
            captureBtn.hidden = !isOpen;
            switchBtn.hidden = !isOpen;
            closeBtn.hidden = !isOpen;
            openBtn.hidden = isOpen;
        };

        const closeCamera = () => {
            releaseStream();
            showCameraControls(false);
        };

        const startCamera = async () => {
            if (!supportsLiveCamera) {
                showMessage('Kamera langsung tidak didukung browser ini. Silakan pilih foto dari galeri.');
                return;
            }

            releaseStream();

            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: facingMode }
                    },
                    audio: false
                });

                video.srcObject = stream;
                showCameraControls(true);
                showMessage('');

                await video.play();

            } catch (error) {
                closeCamera();

                showMessage(
                    'Kamera tidak dapat dibuka. Periksa izin kamera atau pilih foto dari galeri.'
                );
            }
        };

        openBtn.addEventListener('click', () => {
            if (!supportsLiveCamera) {
                fallbackInput.click();
                return;
            }

            startCamera();
        });

        switchBtn.addEventListener('click', () => {
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            startCamera();
        });

        closeBtn.addEventListener('click', closeCamera);

        captureBtn.addEventListener('click', () => {
            if (!video.videoWidth) {
                showMessage('Kamera belum siap. Tunggu sebentar lalu coba lagi.');
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const context = canvas.getContext('2d');

            if (!context) {
                showMessage('Browser tidak dapat memproses foto.');
                return;
            }

            context.drawImage(
                video,
                0,
                0,
                canvas.width,
                canvas.height
            );

            canvas.toBlob(async (blob) => {
                if (!blob) {
                    showMessage('Foto gagal diambil. Silakan coba lagi.');
                    return;
                }

                const file = new File(
                    [blob],
                    'bukti-jurnal-' + Date.now() + '.jpg',
                    { type: 'image/jpeg' }
                );

                if (await setFile(file)) {
                    closeCamera();
                }
            }, 'image/jpeg', 0.95);
        });

        fileInput.addEventListener('change', async () => {
            const file = fileInput.files[0];

            if (!file) {
                preview.hidden = true;
                return;
            }

            if (!(await setFile(file))) {
                fileInput.value = '';
                preview.hidden = true;
            }
        });

        fallbackInput.addEventListener('change', async () => {
            const file = fallbackInput.files[0];

            if (file) {
                await setFile(file);
            }

            fallbackInput.value = '';
        });

        window.addEventListener('pagehide', () => {
            releaseStream();

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }
        });
    })();
    </script>


<div class="modal fade" id="proofPreviewModal" tabindex="-1" aria-labelledby="proofPreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 rounded-3">
            <div class="modal-header">
                <h5 class="modal-title" id="proofPreviewTitle">Preview Foto Bukti Pembelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center bg-light">
                <img id="proofPreviewImage" src="" alt="Foto bukti pembelajaran" class="img-fluid rounded" style="max-height:75vh;object-fit:contain;">
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-proof-preview').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('proofPreviewTitle').textContent = button.dataset.proofTitle || 'Preview Foto Bukti Pembelajaran';
            document.getElementById('proofPreviewImage').src = button.dataset.proofUrl;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('proofPreviewModal')).show();
        });
    });
    document.getElementById('proofPreviewModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('proofPreviewImage').src = '';
    });
});
</script>

</body>
</html>
