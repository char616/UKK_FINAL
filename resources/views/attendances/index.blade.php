<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi & Jurnal Mengajar - SCP System</title>

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
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -.4px;
        }

        .hero-description {
            margin: 0;
            color: rgba(255,255,255,.86);
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
            padding: 22px;
            margin-bottom: 22px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-bottom: 15px;
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
            font-size: 11px;
            line-height: 1.5;
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
            letter-spacing: .4px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #EDF1F5;
            font-size: 11px;
        }

        /* BUTTONS */
        .btn-primary-custom {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            min-height: 36px;
            padding: 8px 14px;
            border: 1px solid var(--primary);
            border-radius: 6px;
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

        /* ALERT SUCCESS */
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
                        <i class="bi bi-calendar-check-fill"></i>
                        Aktivitas Belajar Mengajar
                    </div>
                    <h1 class="page-title">{{ $user->role->name === 'guru' ? 'Presensi & Jurnal Mengajar Terpadu' : 'Manajemen Presensi Siswa' }}</h1>
                </div>

                <div class="academic-badge">
                    <i class="bi bi-calendar3"></i>
                    <span>Tahun Ajaran 2026/2027</span>
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
                            <i class="bi bi-person-check-fill"></i>
                            PRESENSI & JURNAL TERPADU
                        </div>

                        <h2 class="hero-title">
                            {{ $user->role->name === 'guru' ? 'Pencatatan Presensi & Jurnal Mengajar Satu Kesatuan' : 'Pencatatan Kehadiran Siswa & Jurnal KBM' }}
                        </h2>

                        <p class="hero-description">
                            Pilih jadwal mengajar aktif Anda untuk membuka formulir terpadu. Presensi kehadiran seluruh siswa dan jurnal mengajar (materi, catatan kendala, dan foto bukti KBM) disimpan bersamaan dalam satu kesatuan transaksi.
                        </p>
                    </div>

                    <div class="hero-icon d-none d-md-grid">
                        <i class="bi bi-clipboard2-check-fill"></i>
                    </div>
                </div>
            </section>

            <!-- TABEL JADWAL MENGAJAR GURU AKTIF (HARI INI) -->
            @if($user->role->name === 'guru')
                @if(!$hasScheduleToday)
                    <div class="content-card mb-4 border-info-subtle">
                        <div class="d-flex align-items-center gap-3 py-2">
                            <div class="rounded-circle bg-info-subtle text-primary p-3 d-grid place-items-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-calendar-x fs-4"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1" style="font-size: 14px;">Tidak Ada Jam Pelajaran Hari Ini ({{ $todayDay }})</h4>
                                <p class="text-muted mb-0" style="font-size: 12px;">
                                    Anda tidak memiliki jadwal mengajar aktif pada hari {{ $todayDay }}. Sesuai ketentuan sistem, pengisian presensi dan jurnal mengajar hanya aktif jika terdapat jam pelajaran pada hari yang berlaku.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="content-card mb-4">
                        <div class="section-header">
                            <div class="section-header-left">
                                <div class="section-icon">
                                    <i class="bi bi-calendar2-week"></i>
                                </div>
                                <div>
                                    <h3 class="section-title">Jadwal Mengajar Anda Hari Ini ({{ $todayDay }})</h3>
                                    <p class="section-description">Pilih jadwal KBM hari ini untuk melakukan penginputan presensi siswa dan jurnal mengajar terpadu.</p>
                                </div>
                            </div>

                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 fw-bold font-monospace">
                                {{ $schedules->count() }} Jadwal KBM Hari Ini
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th width="10%">Hari</th>
                                        <th width="22%">Sesi & Jam Pelajaran</th>
                                        <th width="22%">Kelas / Jurusan</th>
                                        <th width="24%">Mata Pelajaran</th>
                                        <th width="10%">Ruangan</th>
                                        <th width="12%" class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $schedule)
                                        @php($isDoneToday = isset($todayJournals[$schedule->id]))
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary text-white fw-bold">{{ $schedule->day }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace">
                                                    <i class="bi bi-clock-history me-1"></i>{{ $schedule->session_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-dark d-block">{{ $schedule->class->name ?? '-' }}</strong>
                                                <small class="text-muted" style="font-size: 10px;">{{ $schedule->class->major?->name ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">{{ $schedule->subject->name ?? '-' }}</span>
                                                @if($schedule->subject?->code)
                                                    <span class="badge bg-light text-secondary border font-monospace ms-1">{{ $schedule->subject->code }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi bi-door-closed me-1"></i>{{ $schedule->room->name ?? 'Ruang Kelas' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('attendances.create', $schedule) }}" class="btn-primary-custom btn-sm">
                                                    <i class="bi bi-pencil-square"></i>
                                                    <span>{{ $isDoneToday ? 'Edit Sesi' : 'Isi Sesi' }}</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif

            <!-- TABEL RIWAYAT PRESENSI & JURNAL -->
            <div class="content-card">
                <div class="section-header">
                    <div class="section-header-left">
                        <div class="section-icon">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <div>
                            <h3 class="section-title">Riwayat Presensi & Jurnal KBM</h3>
                            <p class="section-description">Daftar rekaman kehadiran siswa beserta materi pembelajaran dan bukti foto per sesi.</p>
                        </div>
                    </div>
                </div>

                <!-- FORM FILTER DATA MULTI-KRITERIA -->
                <form action="{{ route('attendances.index') }}" method="GET" class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label small fw-bold mb-1">Filter Tanggal</label>
                            <div class="input-group input-group-sm">
                                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                                <a href="{{ route('attendances.index', array_merge(request()->except('date', 'page'), ['date' => $todayDate])) }}" class="btn btn-outline-secondary" title="Hari Ini">
                                    Hari Ini
                                </a>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <label class="form-label small fw-bold mb-1">Cari Siswa / NIS</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama atau NIS siswa..." value="{{ request('search') }}">
                        </div>

                        @if(in_array($user->role->name, ['waka', 'bk', 'wali_kelas'], true))
                            <div class="col-sm-6 col-md-2">
                                <label class="form-label small fw-bold mb-1">Kelas</label>
                                <select name="class_id" class="form-select form-select-sm">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}" @selected(request('class_id') == $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-sm-6 col-md-2">
                            <label class="form-label small fw-bold mb-1">Status Kehadiran</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Semua Status</option>
                                <option value="Hadir" @selected(request('status') === 'Hadir')>Hadir</option>
                                <option value="Izin" @selected(request('status') === 'Izin')>Izin</option>
                                <option value="Sakit" @selected(request('status') === 'Sakit')>Sakit</option>
                                <option value="Alpa" @selected(request('status') === 'Alpa')>Alpa</option>
                            </select>
                        </div>

                        <div class="col-sm-12 col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-bold">
                                <i class="bi bi-funnel-fill me-1"></i> Filter
                            </button>
                            @if(request()->hasAny(['date', 'search', 'class_id', 'status']))
                                <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal & Sesi</th>
                                <th>Peserta Didik (NIS)</th>
                                <th>Kelas / Jurusan</th>
                                <th>Mata Pelajaran</th>
                                <th>Jurnal & Bukti KBM</th>
                                @if(in_array($user->role->name, ['waka', 'bk', 'wali_kelas'], true))
                                    <th>Guru Pengampu</th>
                                @endif
                                <th>Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <i class="bi bi-calendar-event me-1 text-primary"></i>
                                            {{ \Carbon\Carbon::parse($attendance->date)->translatedFormat('d/m/Y') }}
                                        </div>
                                        <small class="text-muted font-monospace" style="font-size: 10px;">
                                            {{ $attendance->schedule?->session_label ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-dark d-block">{{ $attendance->student->name ?? '-' }}</strong>
                                        @if($attendance->student?->nis)
                                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 9px;">
                                                NIS: {{ $attendance->student->nis }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $attendance->schedule->class->name ?? '-' }}</span>
                                        @if($attendance->schedule?->class?->major)
                                            <small class="text-muted d-block" style="font-size: 10px;">{{ $attendance->schedule->class->major->code }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $attendance->schedule->subject->name ?? '-' }}</span>
                                        @if($attendance->schedule?->room)
                                            <small class="text-muted d-block" style="font-size: 10px;"><i class="bi bi-door-closed me-1"></i>{{ $attendance->schedule->room->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->teachingJournal)
                                            <div class="fw-semibold text-dark" style="font-size: 11px;">
                                                {{ $attendance->teachingJournal->materi }}
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                @if(filled($attendance->teachingJournal->photo_proof))
                                                    <button type="button" class="btn btn-sm btn-outline-primary py-0.5 px-2 js-attendance-proof" style="font-size: 10px;" data-proof-url="{{ route('journals.attachment', $attendance->teachingJournal) }}" data-proof-title="Bukti KBM - {{ $attendance->schedule->subject->name ?? 'Pembelajaran' }} ({{ $attendance->schedule->class->name ?? '-' }})">
                                                        <i class="bi bi-image me-1"></i>Foto KBM
                                                    </button>
                                                @endif
                                                @if($user->role->name === 'guru' && $attendance->teachingJournal->teacher_id === $user->id)
                                                    @if(\Carbon\Carbon::parse($attendance->date)->toDateString() < $todayDate)
                                                        <span class="badge bg-light text-muted border py-1 px-2" title="Batas waktu pengeditan presensi telah berakhir (Terkunci)" style="font-size: 10px;">
                                                            <i class="bi bi-lock-fill me-1"></i>Terkunci
                                                        </span>
                                                    @else
                                                        <a class="btn btn-sm btn-outline-secondary py-0.5 px-2" style="font-size: 10px;" href="{{ route('attendances.create', ['schedule' => $attendance->schedule_id, 'date' => $attendance->date]) }}">
                                                            <i class="bi bi-pencil me-1"></i>Edit Sesi
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">Belum terhubung</span>
                                        @endif
                                    </td>
                                    @if(in_array($user->role->name, ['waka', 'bk', 'wali_kelas'], true))
                                        <td><span class="text-muted">{{ $attendance->schedule->teacher->name ?? '-' }}</span></td>
                                    @endif
                                    <td>
                                        @if($attendance->status === 'Hadir')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i> Hadir
                                            </span>
                                        @elseif($attendance->status === 'Alpa')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="bi bi-x-circle-fill me-1"></i> Alpa
                                            </span>
                                        @elseif(in_array($attendance->status, ['Izin', 'Sakit'], true))
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                                <i class="bi bi-info-circle-fill me-1"></i> {{ $attendance->status }}
                                            </span>
                                            @if($attendance->notes)
                                                <small class="d-block text-muted mt-1" style="font-size: 10px;">{{ $attendance->notes }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                                {{ $attendance->status }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                @php($columns = in_array($user->role->name, ['waka', 'bk', 'wali_kelas'], true) ? 7 : 6)
                                <tr>
                                    <td colspan="{{ $columns }}" class="text-center text-muted py-5">
                                        <i class="bi bi-calendar-x fs-2 d-block text-secondary mb-2"></i>
                                        Belum ada rekaman presensi yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- MODAL PREVIEW FOTO BUKTI KBM -->
    <div class="modal fade" id="attendanceProofModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold" id="attendanceProofTitle">Foto Bukti Pembelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center bg-light p-3">
                    <img id="attendanceProofImage" src="" alt="Foto bukti pembelajaran" class="img-fluid rounded border shadow-sm" style="max-height:75vh;object-fit:contain;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.js-attendance-proof').forEach(button => button.addEventListener('click', () => {
            document.getElementById('attendanceProofTitle').textContent = button.dataset.proofTitle || 'Foto Bukti Pembelajaran';
            document.getElementById('attendanceProofImage').src = button.dataset.proofUrl;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('attendanceProofModal')).show();
        }));
        document.getElementById('attendanceProofModal')?.addEventListener('hidden.bs.modal', () => document.getElementById('attendanceProofImage').src = '');
    </script>
</body>
</html>
