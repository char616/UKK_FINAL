<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Presensi Siswa - SCP System</title>

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

        /* CARD & SECTION */
        .content-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(31,52,73,.035);
            padding: 20px;
            margin-bottom: 20px;
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

        /* TABLE */
        .table {
            margin: 0;
            vertical-align: middle;
        }

        .table thead th {
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

        .table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #EDF1F5;
            font-size: 10px;
        }

        /* FORM CONTROLS */
        .form-label {
            font-weight: 700;
            color: #334E68;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 11px;
            color: var(--text);
            background-color: #FFFFFF;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(23, 78, 166, 0.1);
        }

        /* BUTTONS */
        .btn-primary-custom {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            min-height: 38px;
            padding: 8px 15px;
            border: 1px solid var(--primary);
            border-radius: 6px;
            background: var(--primary);
            color: #FFFFFF;
            font-size: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: .2s ease;
            width: 100%;
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
            min-height: 35px;
            padding: 8px 14px;
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

        /* BADGES */
        .scp-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: 700;
        }
        .scp-badge-success { background: #EAF7F0; color: #16845B; border: 1px solid #D5EDDF; }
        .scp-badge-danger { background: #FDF2F2; color: #C24141; border: 1px solid #FADCDC; }
        .scp-badge-warning { background: #FEF9E7; color: #B45309; border: 1px solid #FDE68A; }

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
                        <i class="bi bi-file-earmark-bar-graph-fill"></i>
                        Laporan & Analisis
                    </div>
                    <h1 class="page-title">Rekapitulasi Presensi Siswa</h1>
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
                            <i class="bi bi-bar-chart-fill"></i>
                            REKAP KEHADIRAN PERIODIK
                        </div>

                        <h2 class="hero-title">
                            Laporan Presensi Siswa
                        </h2>

                        <p class="hero-description">
                            Pantau statistik dan rincian catatan kehadiran peserta didik berdasarkan rentang tanggal dan kelas binaan, serta ekspor berkas data presensi ke format CSV.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                </div>
            </section>

            <!-- Bagian Filter -->
            <div class="content-card mb-4">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-funnel-fill"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Filter Rekapitulasi Presensi</h3>
                        <p class="section-description">Tentukan rentang tanggal dan rombongan belajar yang ingin dianalisis.</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('reports.attendance') }}" class="row g-3 align-items-end">
                    <div class="col-sm-6 col-lg-3">
                        <label for="date_from" class="form-label">Dari Tanggal</label>
                        <input id="date_from" name="date_from" type="date" class="form-control" value="{{ $filters['date_from'] }}" required>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label for="date_to" class="form-label">Sampai Tanggal</label>
                        <input id="date_to" name="date_to" type="date" class="form-control" value="{{ $filters['date_to'] }}" required>
                    </div>
                    <div class="col-sm-8 col-lg-4">
                        <label for="class_id" class="form-label">Pilihan Kelas</label>
                        <select id="class_id" name="class_id" class="form-select">
                            <option value="">Semua kelas yang dapat diakses</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected((string) $filters['class_id'] === (string) $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4 col-lg-2">
                        <button type="submit" class="btn-primary-custom mt-1">
                            <i class="bi bi-search"></i>
                            <span>Tampilkan</span>
                        </button>
                    </div>
                    @error('date_from')
                        <div class="col-12"><div class="text-danger small">{{ $message }}</div></div>
                    @enderror
                    @error('date_to')
                        <div class="col-12"><div class="text-danger small">{{ $message }}</div></div>
                    @enderror
                    @error('class_id')
                        <div class="col-12"><div class="text-danger small">{{ $message }}</div></div>
                    @enderror
                </form>
            </div>

            <!-- Bagian Data & Tabel -->
            <div class="content-card">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                    <div>
                        <h2 class="section-title mb-1">Ringkasan Statistik Kehadiran</h2>
                        <p class="section-description mb-0">Periode terpilih: <strong>{{ $filters['date_from'] }}</strong> s.d. <strong>{{ $filters['date_to'] }}</strong></p>
                    </div>
                    <a href="{{ route('reports.attendance.export', $filters) }}" class="btn-outline-primary-custom">
                        <i class="bi bi-file-earmark-spreadsheet-fill text-success"></i>
                        <span>Unduh Format CSV</span>
                    </a>
                </div>

                <!-- Kartu Ringkasan Status -->
                <div class="row g-3 mb-4">
                    @forelse ($attendanceSummary as $row)
                        <div class="col-6 col-md-3">
                            <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px; letter-spacing: .5px;">{{ $row->status }}</div>
                                    <div class="fs-3 fw-bold text-dark">{{ $row->total }}</div>
                                </div>
                                <div class="fs-3 text-primary opacity-50">
                                    @if(strtolower($row->status) === 'hadir')
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif(strtolower($row->status) === 'alpa')
                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                    @else
                                        <i class="bi bi-info-circle-fill text-warning"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted small py-2">Belum ada catatan presensi untuk filter pencarian ini.</div>
                    @endforelse
                </div>

                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-table"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Rincian Catatan Presensi Siswa</h3>
                        <p class="section-description">Daftar kehadiran siswa per pertemuan mata pelajaran.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th class="text-center">Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendances as $attendance)
                                <tr>
                                    <td><span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>{{ $attendance->date }}</span></td>
                                    <td><strong class="text-dark">{{ $attendance->student?->name ?? '-' }}</strong></td>
                                    <td><span class="badge bg-light text-dark border">{{ $attendance->schedule?->class?->name ?? '-' }}</span></td>
                                    <td><span class="text-dark">{{ $attendance->schedule?->subject?->name ?? '-' }}</span></td>
                                    <td><span class="text-muted">{{ $attendance->schedule?->teacher?->name ?? '-' }}</span></td>
                                    <td class="text-center">
                                        @php
                                            $st = strtolower($attendance->status);
                                        @endphp
                                        @if($st === 'hadir')
                                            <span class="scp-badge scp-badge-success">
                                                <i class="bi bi-check-circle-fill"></i> Hadir
                                            </span>
                                        @elseif(str_contains($st, 'alpa'))
                                            <span class="scp-badge scp-badge-danger">
                                                <i class="bi bi-x-circle-fill"></i> Alpa
                                            </span>
                                        @else
                                            <span class="scp-badge scp-badge-warning">
                                                <i class="bi bi-info-circle-fill"></i> {{ $attendance->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td><span class="text-muted small">{{ $attendance->notes ?: '-' }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">Tidak ditemukan data presensi pada kriteria filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $attendances->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>