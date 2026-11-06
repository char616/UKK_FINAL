<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Presensi & Jurnal Mengajar - SCP System</title>

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
            padding: 24px 28px;
            margin-bottom: 25px;
            color: #FFFFFF;
            background: linear-gradient(115deg, #174EA6 0%, #1D4ED8 50%, #2563EB 100%);
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(23, 78, 166, .15);
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            position: relative;
            z-index: 1;
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 11px;
            margin-bottom: 10px;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 6px;
            background: rgba(255,255,255,.14);
            color: #FFFFFF;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
        }

        .hero-title {
            margin: 0 0 12px;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -.5px;
        }

        /* INFO METADATA PILLS */
        .hero-meta-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 12px;
            background: rgba(255,255,255,.16);
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #FFFFFF;
        }

        .meta-pill i {
            font-size: 13px;
            opacity: .9;
        }

        .hero-icon {
            display: grid;
            place-items: center;
            width: 58px;
            height: 58px;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 12px;
            background: rgba(255,255,255,.15);
            color: white;
            font-size: 26px;
        }

        /* CONTENT CARDS */
        .content-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(31,52,73,.04);
            padding: 24px;
            margin-bottom: 22px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            padding-bottom: 16px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .section-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-icon {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 8px;
            background: var(--primary-light);
            color: var(--primary);
            font-size: 17px;
        }

        .section-title {
            margin: 0 0 3px;
            color: #1E3552;
            font-size: 14px;
            font-weight: 800;
        }

        .section-description {
            margin: 0;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        /* STATS COUNTER BAR */
        .attendance-counters {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .counter-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }

        .counter-badge-total {
            background: #F1F5F9;
            color: #475569;
            border: 1px solid #CBD5E1;
        }

        .counter-badge-hadir {
            background: #E8F8EF;
            color: #16A36A;
            border: 1px solid #A7F3D0;
        }

        .counter-badge-izin {
            background: #FEF3C7;
            color: #D97706;
            border: 1px solid #FDE68A;
        }

        .counter-badge-sakit {
            background: #EFF6FF;
            color: #2563EB;
            border: 1px solid #BFDBFE;
        }

        .counter-badge-alpa {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        /* TABLE */
        .table {
            margin: 0;
            vertical-align: middle;
        }

        .table thead th {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
            background: #F8FAFC;
            color: #64748B;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .5px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #EDF1F5;
            font-size: 11px;
        }

        /* ATTENDANCE RADIO BUTTONS */
        .attendance-radio-group {
            display: inline-flex;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            overflow: hidden;
        }

        .attendance-radio-group .btn-check:checked + .btn-status-hadir {
            background-color: #16a36a !important;
            border-color: #16a36a !important;
            color: #ffffff !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,.15);
        }
        .btn-status-hadir {
            color: #16a36a;
            background: #ffffff;
            font-size: 11px;
            font-weight: 700;
            border: none;
            border-right: 1px solid #E2E8F0;
            padding: 6px 14px;
            transition: all .15s ease;
        }

        .attendance-radio-group .btn-check:checked + .btn-status-izin {
            background-color: #d97706 !important;
            border-color: #d97706 !important;
            color: #ffffff !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,.15);
        }
        .btn-status-izin {
            color: #d97706;
            background: #ffffff;
            font-size: 11px;
            font-weight: 700;
            border: none;
            border-right: 1px solid #E2E8F0;
            padding: 6px 14px;
            transition: all .15s ease;
        }

        .attendance-radio-group .btn-check:checked + .btn-status-sakit {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,.15);
        }
        .btn-status-sakit {
            color: #2563eb;
            background: #ffffff;
            font-size: 11px;
            font-weight: 700;
            border: none;
            border-right: 1px solid #E2E8F0;
            padding: 6px 14px;
            transition: all .15s ease;
        }

        .attendance-radio-group .btn-check:checked + .btn-status-alpa {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
            color: #ffffff !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,.15);
        }
        .btn-status-alpa {
            color: #dc2626;
            background: #ffffff;
            font-size: 11px;
            font-weight: 700;
            border: none;
            padding: 6px 14px;
            transition: all .15s ease;
        }

        /* BUTTONS */
        .btn-primary-custom {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            min-height: 42px;
            padding: 10px 20px;
            border: 1px solid var(--primary);
            border-radius: 8px;
            background: var(--primary);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: .2s ease;
            cursor: pointer;
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
            gap: 7px;
            min-height: 38px;
            padding: 8px 16px;
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

        .btn-mark-all {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #F0FDF4;
            color: #16A36A;
            border: 1px solid #BBF7D0;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s;
        }

        .btn-mark-all:hover {
            background: #DCFCE7;
            color: #15803D;
        }

        /* STICKY FOOTER ACTION */
        .sticky-action-bar {
            position: sticky;
            bottom: 15px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 22px;
            box-shadow: 0 8px 24px rgba(31, 52, 73, 0.12);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            z-index: 100;
            margin-top: 25px;
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
            .sticky-action-bar {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>

    @include('layouts.sidebar', ['user' => auth()->user()])

    <main class="main-content">
        <div class="content-wrapper">

            <!-- PAGE HEADER -->
            <header class="page-header">
                <div>
                    <div class="page-eyebrow">
                        <i class="bi bi-pencil-square"></i>
                        Formulir Mengajar & Pembelajaran Terpadu
                    </div>
                    <h1 class="page-title">Input Presensi & Jurnal Mengajar</h1>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="academic-badge">
                        <i class="bi bi-calendar3"></i>
                        <span>Tahun Ajaran 2026/2027</span>
                    </div>
                    <a href="{{ route('attendances.index') }}" class="btn-outline-primary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali ke Riwayat</span>
                    </a>
                </div>
            </header>

            <!-- HERO BANNER INFORMASI KBM LENGKAP -->
            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text w-100">
                        <div class="hero-label">
                            <i class="bi bi-journal-bookmark-fill"></i>
                            PENCATATAN KBM SATU KESATUAN TRANSAKSI
                        </div>

                        <h2 class="hero-title">
                            Kelas {{ $schedule->class?->name ?? '-' }} • {{ $schedule->subject?->name ?? '-' }} ({{ $schedule->subject?->code ?? '-' }})
                        </h2>

                        <!-- DETAIL LENGKAP: KELAS, MAPEL, JAM SESI -->
                        <div class="hero-meta-grid">
                            <span class="meta-pill">
                                <i class="bi bi-easel2-fill"></i>
                                <span>Kelas: <strong>{{ $schedule->class?->name ?? '-' }}</strong> ({{ $schedule->class?->major?->name ?? '-' }})</span>
                            </span>

                            <span class="meta-pill">
                                <i class="bi bi-book-half"></i>
                                <span>Mapel: <strong>{{ $schedule->subject?->name ?? '-' }}</strong> ({{ $schedule->subject?->code ?? '-' }})</span>
                            </span>

                            <span class="meta-pill">
                                <i class="bi bi-clock-history"></i>
                                <span>Sesi: <strong>{{ $schedule->session_label }}</strong></span>
                            </span>

                            <span class="meta-pill">
                                <i class="bi bi-calendar-check"></i>
                                <span>Hari & Tanggal: <strong>{{ $schedule->day }}, {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</strong></span>
                            </span>

                            <span class="meta-pill">
                                <i class="bi bi-door-open-fill"></i>
                                <span>Ruangan: <strong>{{ $schedule->room?->name ?? 'Ruang Kelas' }}</strong></span>
                            </span>

                            <span class="meta-pill">
                                <i class="bi bi-people-fill"></i>
                                <span>Peserta Didik: <strong>{{ $students->count() }} Siswa</strong></span>
                            </span>
                        </div>
                    </div>

                    <div class="hero-icon d-none d-md-grid">
                        <i class="bi bi-clipboard2-check-fill"></i>
                    </div>
                </div>
            </section>

            @if(!empty($isExpired))
                <div class="alert alert-danger d-flex align-items-center gap-3 mb-4 p-3 rounded-3 border-danger shadow-sm">
                    <i class="bi bi-lock-fill fs-2 text-danger"></i>
                    <div>
                        <h5 class="fw-bold mb-1 text-danger">Batas Waktu Pengisian & Pengeditan Telah Berakhir</h5>
                        <p class="mb-0 text-muted" style="font-size: 12px;">Presensi dan jurnal mengajar hanya dapat diisi atau diedit pada hari pelaksanaan KBM (maksimal hari ini). Data tanggal <strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</strong> sudah terkunci secara permanen dan tidak dapat diubah lagi.</p>
                    </div>
                </div>
            @endif

            @if(isset($isScheduledForDate) && !$isScheduledForDate)
                <div class="alert alert-warning d-flex align-items-center gap-3 mb-4 p-3 rounded-3 border-warning shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Informasi Hari Pembelajaran</h6>
                        <p class="mb-0 text-muted" style="font-size: 12px;">Jadwal pelajaran ini terdaftar untuk hari <strong>{{ $schedule->day }}</strong>, sedangkan tanggal yang dipilih jatuh pada hari <strong>{{ $dateDay ?? '' }}</strong>. Pastikan Anda mengisi presensi pada hari KBM yang sesuai.</p>
                    </div>
                </div>
            @endif

            <!-- FORMULIR TERPADU: JURNAL & PRESENSI MENJADI SATU -->
            <form action="{{ route('attendances.store', $schedule->id) }}" method="POST" enctype="multipart/form-data" id="unified-kbm-form">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">

                <!-- CARD 1: DATA JURNAL MENGAJAR GURU -->
                <div class="content-card">
                    <div class="section-header">
                        <div class="section-header-left">
                            <div class="section-icon">
                                <i class="bi bi-journal-richtext"></i>
                            </div>
                            <div>
                                <h3 class="section-title">1. Data Jurnal Mengajar Guru</h3>
                                <p class="section-description">Isi materi yang diajarkan, ringkasan aktivitas KBM, catatan kelas, dan ambil foto dokumentasi real-time kegiatan belajar.</p>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fw-bold">
                            <i class="bi bi-asterisk me-1 text-danger"></i> Wajib Dilengkapi
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Materi Pembelajaran <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="materi" class="form-control @error('materi') is-invalid @enderror" maxlength="255" required value="{{ old('materi', $journal?->materi) }}" placeholder="Contoh: Pemrograman Web - Integrasi REST API dan Token Sanctum" {{ !empty($isExpired) ? 'disabled' : '' }}>
                            @error('materi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" style="font-size: 10px;">Topik atau bab bahasan materi pada jam pelajaran ini.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Ringkasan Kegiatan Pembelajaran <span class="text-danger">*</span>
                            </label>
                            <textarea name="summary" class="form-control @error('summary') is-invalid @enderror" rows="3" required placeholder="Jelaskan jalannya kegiatan pembelajaran (contoh: Penyampaian teori 20 menit, live coding praktik 45 menit, dan tanya jawab evaluasi)." {{ !empty($isExpired) ? 'disabled' : '' }}>{{ old('summary', $journal?->activity_summary) }}</textarea>
                            @error('summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-bold">
                                Catatan Kejadian / Kendala KBM <span class="text-danger">*</span>
                            </label>
                            <textarea name="catatan_kejadian" class="form-control @error('catatan_kejadian') is-invalid @enderror" rows="3" required placeholder="Tuliskan kejadian kelas, respon siswa, atau kendala fasilitas (contoh: Seluruh siswa aktif mengerjakan tugas, proyektor ruangan berjalan lancar)." {{ !empty($isExpired) ? 'disabled' : '' }}>{{ old('catatan_kejadian', $journal?->catatan_kejadian ?? $journal?->notes) }}</textarea>
                            @error('catatan_kejadian')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold">
                                Foto Bukti Pembelajaran (Real-Time Kamera) <span class="text-danger">*</span>
                            </label>

                            <!-- Hidden snapshot data -->
                            <input type="hidden" name="attachment_data" id="camera-snapshot-data">
                            <!-- Hidden fallback file with camera capture directive for mobile/fallback -->
                            <input type="file" name="attachment" id="journal-photo" accept="image/*" capture="environment" style="display:none;">

                            <!-- Camera Widget Container -->
                            <div class="camera-box p-3 bg-light rounded-3 border text-center">
                                <!-- Video Stream element -->
                                <video id="camera-stream" autoplay playsinline muted style="width: 100%; max-height: 220px; object-fit: cover; border-radius: 8px; background: #111; display: none;" class="mb-2 shadow-sm"></video>
                                
                                <!-- Canvas for capturing snapshot -->
                                <canvas id="camera-canvas" style="display: none;"></canvas>

                                <!-- Captured Preview Image -->
                                <img id="camera-preview-img" style="width: 100%; max-height: 220px; object-fit: cover; border-radius: 8px; display: none;" class="mb-2 border shadow-sm" alt="Foto Real-Time KBM">

                                <!-- Status / Instructions -->
                                <div id="camera-status-text" class="small text-muted mb-2" style="font-size: 11px;">
                                    <i class="bi bi-camera me-1"></i> Wajib ambil foto real-time suasana KBM langsung melalui kamera perangkat.
                                </div>

                                <!-- Camera Action Buttons -->
                                <div class="d-flex flex-wrap justify-content-center gap-2" id="camera-btn-group">
                                    <button type="button" class="btn btn-sm btn-primary fw-bold" id="btn-open-camera" {{ !empty($isExpired) ? 'disabled' : '' }}>
                                        <i class="bi bi-camera-video me-1"></i> Buka Kamera
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger fw-bold shadow-sm" id="btn-capture-photo" style="display: none;">
                                        <i class="bi bi-camera-fill me-1"></i> Jepret Foto KBM
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" id="btn-retake-photo" style="display: none;">
                                        <i class="bi bi-arrow-repeat me-1"></i> Jepret Ulang
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border text-muted" id="btn-trigger-fallback-file" title="Gunakan kamera perangkat (alternatif)" style="font-size: 10px;" {{ !empty($isExpired) ? 'disabled' : '' }}>
                                        <i class="bi bi-phone"></i> Kamera HP
                                    </button>
                                </div>
                            </div>

                            @if(filled($journal?->photo_proof))
                                <div class="mt-2 p-2 bg-light border rounded-2 d-flex align-items-center justify-content-between">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle-fill me-1"></i> Foto Bukti Tersimpan
                                    </span>
                                    <a href="{{ route('journals.attachment', $journal) }}" target="_blank" rel="noopener" class="small fw-bold text-primary">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Foto Lama
                                    </a>
                                </div>
                            @endif

                            @error('attachment')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('attachment_data')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- CARD 2: DAFTAR PRESENSI KEHADIRAN SISWA -->
                <div class="content-card">
                    <div class="section-header">
                        <div class="section-header-left">
                            <div class="section-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <h3 class="section-title">2. Daftar Presensi Kehadiran Siswa</h3>
                                <p class="section-description">Pilih status kehadiran untuk setiap siswa yang terdaftar di rombel kelas <strong>{{ $schedule->class?->name }}</strong>.</p>
                            </div>
                        </div>

                        <!-- LIVE ATTENDANCE COUNTERS & QUICK ACTION -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="attendance-counters">
                                <span class="counter-badge counter-badge-total">
                                    Total: <strong>{{ $students->count() }}</strong>
                                </span>
                                <span class="counter-badge counter-badge-hadir">
                                    Hadir: <strong id="cnt-hadir">0</strong>
                                </span>
                                <span class="counter-badge counter-badge-izin">
                                    Izin: <strong id="cnt-izin">0</strong>
                                </span>
                                <span class="counter-badge counter-badge-sakit">
                                    Sakit: <strong id="cnt-sakit">0</strong>
                                </span>
                                <span class="counter-badge counter-badge-alpa">
                                    Alpa: <strong id="cnt-alpa">0</strong>
                                </span>
                            </div>

                            <button type="button" class="btn-mark-all" id="btn-set-all-hadir">
                                <i class="bi bi-check2-all"></i>
                                <span>Tandai Semua Hadir</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="15%">NIS</th>
                                    <th width="35%">Nama Peserta Didik</th>
                                    <th width="45%" class="text-center">Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $index => $student)
                                    <tr>
                                        <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                                {{ $student->nis ?: $student->login_key }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-primary-subtle text-primary d-grid place-items-center" style="width:28px;height:28px;font-size:12px;font-weight:700;">
                                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-dark d-block">{{ $student->name }}</span>
                                                    @if($student->parent_name)
                                                        <small class="text-muted" style="font-size: 10px;">Ortu: {{ $student->parent_name }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="attendance-radio-group shadow-sm" role="group">
                                                <input type="radio" class="btn-check attendance-radio" name="attendances[{{ $student->id }}]" value="Hadir" id="hadir_{{ $student->id }}" @checked(($existingStatuses[$student->id] ?? 'Hadir') === 'Hadir') {{ !empty($isExpired) ? 'disabled' : '' }}>
                                                <label class="btn btn-status-hadir" for="hadir_{{ $student->id }}">
                                                    <i class="bi bi-check-lg me-1"></i>Hadir
                                                </label>

                                                <input type="radio" class="btn-check attendance-radio" name="attendances[{{ $student->id }}]" value="Izin" id="izin_{{ $student->id }}" @checked(($existingStatuses[$student->id] ?? '') === 'Izin') {{ !empty($isExpired) ? 'disabled' : '' }}>
                                                <label class="btn btn-status-izin" for="izin_{{ $student->id }}">
                                                    <i class="bi bi-envelope me-1"></i>Izin
                                                </label>

                                                <input type="radio" class="btn-check attendance-radio" name="attendances[{{ $student->id }}]" value="Sakit" id="sakit_{{ $student->id }}" @checked(($existingStatuses[$student->id] ?? '') === 'Sakit') {{ !empty($isExpired) ? 'disabled' : '' }}>
                                                <label class="btn btn-status-sakit" for="sakit_{{ $student->id }}">
                                                    <i class="bi bi-bandaid me-1"></i>Sakit
                                                </label>

                                                <input type="radio" class="btn-check attendance-radio" name="attendances[{{ $student->id }}]" value="Alpa" id="alpa_{{ $student->id }}" @checked(($existingStatuses[$student->id] ?? '') === 'Alpa') {{ !empty($isExpired) ? 'disabled' : '' }}>
                                                <label class="btn btn-status-alpa" for="alpa_{{ $student->id }}">
                                                    <i class="bi bi-x-lg me-1"></i>Alpa
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="bi bi-people fs-2 d-block text-secondary mb-2"></i>
                                            Belum ada data siswa yang terdaftar pada kelas {{ $schedule->class?->name ?? '' }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- STICKY BOTTOM ACTION BAR: SATU TOMBOL SIMPAN TERPADU -->
                <div class="sticky-action-bar">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-primary fs-5"></i>
                        <div>
                            <strong class="d-block text-dark" style="font-size: 12px;">Satu Transaksi Terpadu</strong>
                            <small class="text-muted" style="font-size: 11px;">Jurnal mengajar dan seluruh rekaman presensi kelas akan disimpan serentak.</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('attendances.index') }}" class="btn-outline-primary-custom">
                            <i class="bi bi-x-circle"></i>
                            <span>Batal</span>
                        </a>

                        @if(!empty($isExpired))
                            <button type="button" class="btn btn-secondary shadow" disabled>
                                <i class="bi bi-lock-fill me-1"></i>
                                <span>Terkunci (Batas Waktu Telah Berakhir)</span>
                            </button>
                        @else
                            <button type="submit" class="btn-primary-custom shadow">
                                <i class="bi bi-cloud-arrow-up-fill"></i>
                                <span>{{ $journal ? 'Simpan Perubahan Presensi & Jurnal' : 'Simpan Presensi & Jurnal Mengajar' }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </form>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-Time Camera Handler
        let cameraStream = null;
        const videoEl = document.getElementById('camera-stream');
        const canvasEl = document.getElementById('camera-canvas');
        const previewImgEl = document.getElementById('camera-preview-img');
        const snapshotInputEl = document.getElementById('camera-snapshot-data');
        const openCameraBtn = document.getElementById('btn-open-camera');
        const capturePhotoBtn = document.getElementById('btn-capture-photo');
        const retakePhotoBtn = document.getElementById('btn-retake-photo');
        const statusTextEl = document.getElementById('camera-status-text');
        const fallbackBtnEl = document.getElementById('btn-trigger-fallback-file');
        const fileInputEl = document.getElementById('journal-photo');

        openCameraBtn?.addEventListener('click', async function () {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Akses kamera langsung tidak didukung browser ini. Gunakan tombol Kamera HP.');
                    fileInputEl?.click();
                    return;
                }
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false
                });
                videoEl.srcObject = cameraStream;
                videoEl.style.display = 'block';
                if (previewImgEl) previewImgEl.style.display = 'none';
                openCameraBtn.style.display = 'none';
                capturePhotoBtn.style.display = 'inline-flex';
                retakePhotoBtn.style.display = 'none';
                statusTextEl.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-record-circle-fill text-danger me-1"></i>Kamera Aktif:</span> Arahkan kamera ke suasana KBM lalu klik "Jepret Foto KBM".';
            } catch (err) {
                console.error('Camera access error:', err);
                statusTextEl.innerHTML = '<span class="text-warning fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Akses kamera tidak diizinkan.</span> Silakan gunakan tombol Kamera HP.';
                fileInputEl?.click();
            }
        });

        capturePhotoBtn?.addEventListener('click', function () {
            if (!videoEl || !cameraStream) return;
            canvasEl.width = videoEl.videoWidth || 640;
            canvasEl.height = videoEl.videoHeight || 480;
            const ctx = canvasEl.getContext('2d');
            ctx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);
            const dataUrl = canvasEl.toDataURL('image/jpeg', 0.85);
            snapshotInputEl.value = dataUrl;
            previewImgEl.src = dataUrl;
            previewImgEl.style.display = 'block';
            videoEl.style.display = 'none';

            // Stop active tracks
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;

            capturePhotoBtn.style.display = 'none';
            retakePhotoBtn.style.display = 'inline-flex';
            statusTextEl.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Foto KBM Berhasil Dijepret!</span> Foto real-time siap disimpan.';
        });

        retakePhotoBtn?.addEventListener('click', function () {
            snapshotInputEl.value = '';
            if (previewImgEl) previewImgEl.style.display = 'none';
            retakePhotoBtn.style.display = 'none';
            openCameraBtn?.click();
        });

        fallbackBtnEl?.addEventListener('click', function () {
            fileInputEl?.click();
        });

        fileInputEl?.addEventListener('change', function (e) {
            const file = e.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (evt) {
                snapshotInputEl.value = evt.target.result;
                if (previewImgEl) {
                    previewImgEl.src = evt.target.result;
                    previewImgEl.style.display = 'block';
                }
                if (videoEl) videoEl.style.display = 'none';
                if (openCameraBtn) openCameraBtn.style.display = 'none';
                if (capturePhotoBtn) capturePhotoBtn.style.display = 'none';
                if (retakePhotoBtn) retakePhotoBtn.style.display = 'inline-flex';
                statusTextEl.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Foto Kamera Dipilih!</span>';
            };
            reader.readAsDataURL(file);
        });

        // Live Counter Status Kehadiran
        function updateAttendanceCounters() {
            let hadir = 0, izin = 0, sakit = 0, alpa = 0;
            document.querySelectorAll('.attendance-radio:checked').forEach(radio => {
                const val = radio.value;
                if (val === 'Hadir') hadir++;
                else if (val === 'Izin') izin++;
                else if (val === 'Sakit') sakit++;
                else if (val === 'Alpa') alpa++;
            });
            document.getElementById('cnt-hadir').textContent = hadir;
            document.getElementById('cnt-izin').textContent = izin;
            document.getElementById('cnt-sakit').textContent = sakit;
            document.getElementById('cnt-alpa').textContent = alpa;
        }

        document.querySelectorAll('.attendance-radio').forEach(radio => {
            radio.addEventListener('change', updateAttendanceCounters);
        });

        // Quick action: Set Semua Hadir
        document.getElementById('btn-set-all-hadir')?.addEventListener('click', function () {
            document.querySelectorAll('.attendance-radio[value="Hadir"]').forEach(radio => {
                radio.checked = true;
            });
            updateAttendanceCounters();
        });

        // Initial count
        updateAttendanceCounters();
    </script>
</body>
</html>
