<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Wali Kelas - SCP System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    @include('layouts.sidebar', ['user' => $user])

    <main class="main-content">
        <div class="content-wrapper">

            <header class="page-header">
                <div>
                    <div class="page-eyebrow">
                        <i class="bi bi-people-fill"></i>
                        Wali Kelas Panel
                    </div>
                    <h1 class="page-title">Dashboard Pengelolaan Kelas</h1>
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
                            <i class="bi bi-mortarboard-fill"></i>
                            WALI KELAS
                        </div>

                        <h2 class="hero-title">
                            Selamat Datang Wali Kelas, {{ $user->name }}.
                        </h2>

                        <p class="hero-description">
                            Kelola verifikasi surat izin dan sakit siswa binaan, pantau kedisiplinan belajar di setiap jam pelajaran, serta amati rekapitulasi presensi kelas secara terpusat.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-person-video3"></i>
                    </div>
                </div>
            </section>

            <!-- 1. INFOGRAFIS AGREGAT KELAS BINAAN -->
            <section class="content-card mb-4">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon">
                            <i class="bi bi-pie-chart-fill"></i>
                        </div>
                        <div>
                            <h3 class="section-title mb-0">Infografis Kehadiran Kelas Binaan</h3>
                            <p class="section-description mb-0">
                                Kelas: <strong>{{ $homeroomClasses->pluck('name')->join(', ') ?: 'Belum ditetapkan' }}</strong> &bull; Total Siswa: <strong>{{ $classStats['total_students'] ?? 0 }} Siswa</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row g-3 my-2">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold" style="font-size: 11px;">TOTAL ANAK</span>
                                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bi bi-people-fill fs-6"></i></span>
                            </div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="fs-2 fw-bold text-primary">{{ $classStats['total_students'] ?? 0 }}</span>
                                <span class="text-muted small">siswa</span>
                            </div>
                            <div class="small text-muted mt-1" style="font-size: 10px;">Jumlah siswa di kelas binaan</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. DATA KEHADIRAN PER ANAK (DETAIL SAKIT, IZIN, ALPA TIAP SISWA) -->
            <section class="content-card mb-4">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <div>
                            <h3 class="section-title mb-0">Data Kehadiran Peserta Didik Per Anak</h3>
                            <p class="section-description mb-0">Rincian berapa kali masing-masing anak izin, sakit, alpa, dan hadir.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('reports.attendance') }}" class="btn-outline-primary-custom btn-sm">
                            <i class="bi bi-file-earmark-text me-1"></i> Rekap Lengkap Presensi
                        </a>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="15%">NIS</th>
                                <th width="30%">Nama Siswa</th>
                                <th width="12%" class="text-center">Hadir</th>
                                <th width="12%" class="text-center">Izin</th>
                                <th width="12%" class="text-center">Sakit</th>
                                <th width="14%" class="text-center">Alpa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students ?? [] as $idx => $st)
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace">
                                            {{ $st->nis ?: $st->login_key }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-dark d-block">{{ $st->name }}</strong>
                                        <small class="text-muted" style="font-size: 10px;">{{ $st->schoolClass?->name ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold">
                                            {{ $st->hadir_count }}x
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold">
                                            {{ $st->izin_count }}x
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 fw-bold">
                                            {{ $st->sakit_count }}x
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($st->alpa_count > 0)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold">
                                                {{ $st->alpa_count }}x
                                            </span>
                                            @if($st->alpa_count >= 3)
                                                <small class="d-block text-danger mt-0.5 fw-semibold" style="font-size: 9px;">Perhatian BK</small>
                                            @endif
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">0x</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Belum ada siswa yang terdaftar di kelas binaan Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- 3. PENGAJUAN IZIN KELAS BINAAN -->
            <section class="content-card mb-4">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon">
                            <i class="bi bi-envelope-paper"></i>
                        </div>
                        <div>
                            <h3 class="section-title mb-0">Permohonan Izin Siswa Kelas Binaan</h3>
                            <p class="section-description mb-0">Verifikasi pengajuan izin seharian siswa kelas binaan.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('leaves.index') }}" class="btn-primary-custom btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Menu Izin & Sakit
                        </a>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th width="25%">Siswa</th>
                                <th width="15%">Jenis & Waktu</th>
                                <th width="15%">Tanggal</th>
                                <th width="25%">Alasan</th>
                                <th width="20%" class="text-center">Status & Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLeaves ?? [] as $leave)
                                <tr>
                                    <td>
                                        <strong class="text-dark d-block">{{ $leave->student?->name ?? '-' }}</strong>
                                        <small class="text-muted" style="font-size: 10px;">NIS: {{ $leave->student?->nis ?: $leave->student?->login_key }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ strtolower($leave->type) === 'sakit' ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary' }} border">
                                            {{ $leave->type }} ({{ $leave->jenis_waktu_izin === 'jam_pelajaran' ? 'Per Jam' : 'Seharian' }})
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-dark">{{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') : '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="small text-dark">{{ $leave->reason }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php($st = strtolower($leave->status ?? 'pending'))
                                        @if($st === 'approved' || $st === 'disetujui')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i> Disetujui
                                            </span>
                                        @elseif($st === 'rejected' || $st === 'ditolak')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="bi bi-x-circle-fill me-1"></i> Ditolak
                                            </span>
                                            @if($leave->alasan_penolakan)
                                                <div class="mt-1 p-1 bg-danger-subtle rounded border border-danger-subtle text-danger text-start" style="font-size: 10px;">
                                                    <strong>Alasan:</strong> <em>"{{ $leave->alasan_penolakan }}"</em>
                                                </div>
                                            @endif
                                        @else
                                            <a href="{{ route('leaves.index') }}" class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 text-decoration-none">
                                                <i class="bi bi-clock-fill me-1"></i> Menunggu Verifikasi
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Belum ada permohonan izin dari siswa kelas binaan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section aria-labelledby="quick-actions-title">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <h3 id="quick-actions-title" class="section-title">Akses Cepat Wali Kelas</h3>
                        <p class="section-description">Navigasi ke fungsi pembinaan dan administrasi kelas binaan.</p>
                    </div>
                </div>

                <div class="scp-dashboard-links">
                    <a class="scp-dashboard-link" href="{{ route('schedules.index') }}">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-calendar-week"></i></span>
                        <span class="scp-dashboard-link-label">Jadwal Pelajaran</span>
                        <i class="bi bi-arrow-up-right scp-dashboard-link-arrow"></i>
                    </a>

                    <a class="scp-dashboard-link" href="{{ route('leaves.index') }}">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-envelope-paper"></i></span>
                        <span class="scp-dashboard-link-label">Izin & Sakit</span>
                        <i class="bi bi-arrow-up-right scp-dashboard-link-arrow"></i>
                    </a>

                    <a class="scp-dashboard-link" href="{{ route('reports.attendance') }}">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-file-earmark-bar-graph"></i></span>
                        <span class="scp-dashboard-link-label">Laporan Presensi</span>
                        <i class="bi bi-arrow-up-right scp-dashboard-link-arrow"></i>
                    </a>

                    <a class="scp-dashboard-link" href="{{ route('settings.index') }}">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-gear"></i></span>
                        <span class="scp-dashboard-link-label">Pengaturan Akun</span>
                        <i class="bi bi-arrow-up-right scp-dashboard-link-arrow"></i>
                    </a>
                </div>
            </section>

            <section class="content-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Ketentuan & Tugas Pokok Wali Kelas</h3>
                        <p class="section-description">Prosedur penanganan kehadiran dan perizinan peserta didik.</p>
                    </div>
                </div>

                <div class="rule-list">
                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Verifikasi Pengajuan Izin & Sakit</span>
                            <p class="rule-description">
                                Tinjau surat keterangan dokter atau permohonan dari wali murid, lalu berikan status persetujuan pada modul Izin dan Sakit.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Pemantauan Presensi Mata Pelajaran</span>
                            <p class="rule-description">
                                Awasi kepatuhan kehadiran siswa pada jam mengajar seluruh guru pengampu di kelas binaan Anda.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Pemeriksaan Rekapitulasi Presensi</span>
                            <p class="rule-description">
                                Unduh rekapitulasi kehadiran kelas secara berkala untuk keperluan evaluasi pembinaan dan bahan rapat orang tua siswa.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Sinergi bersama BK dan Orang Tua</span>
                            <p class="rule-description">
                                Koordinasikan siswa yang memiliki kendala absensi berkelanjutan bersama pihak Bimbingan Konseling dan wali murid.
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
