<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard BK - SCP System</title>

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
                        <i class="bi bi-shield-check"></i>
                        Bimbingan Konseling Panel
                    </div>
                    <h1 class="page-title">Dashboard Konseling</h1>
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
                            <i class="bi bi-people-fill"></i>
                            GURU BIMBINGAN KONSELING
                        </div>

                        <h2 class="hero-title">
                            Selamat Datang, {{ $user->name }}.
                        </h2>

                        <p class="hero-description">
                            Pantau tingkat kehadiran peserta didik secara menyeluruh, lakukan identifikasi dini terhadap siswa yang membutuhkan perhatian khusus, dan akses laporan presensi komprehensif.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-person-heart"></i>
                    </div>
                </div>
            </section>

            <!-- 1. INFOGRAFIS PRESENSI TINGKAT SEKOLAH (BK) -->
            <section class="content-card mb-4">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon">
                            <i class="bi bi-pie-chart-fill"></i>
                        </div>
                        <div>
                            <h3 class="section-title mb-0">Infografis Presensi Tingkat Sekolah</h3>
                            <p class="section-description mb-0">Pemantauan kehadiran dan ketidakhadiran seluruh peserta didik oleh Guru BK.</p>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 fw-bold" style="font-size: 12px;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Alpa Hari Ini: {{ $overallStats['today_alpa'] ?? 0 }} Siswa
                        </span>
                    </div>
                </div>

                <div class="row g-3 my-2">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold" style="font-size: 11px;">TOTAL HADIR</span>
                                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-check-lg fs-6"></i></span>
                            </div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="fs-2 fw-bold text-success">{{ $overallStats['hadir'] ?? 0 }}</span>
                                <span class="text-muted small">kali</span>
                            </div>
                            <div class="small text-muted mt-1" style="font-size: 10px;">Presensi KBM sekolah</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold" style="font-size: 11px;">TOTAL IZIN</span>
                                <span class="badge bg-info-subtle text-primary rounded-circle p-2"><i class="bi bi-envelope fs-6"></i></span>
                            </div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="fs-2 fw-bold text-primary">{{ $overallStats['izin'] ?? 0 }}</span>
                                <span class="text-muted small">kali</span>
                            </div>
                            <div class="small text-muted mt-1" style="font-size: 10px;">Izin resmi terdata</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold" style="font-size: 11px;">TOTAL SAKIT</span>
                                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-bandaid fs-6"></i></span>
                            </div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="fs-2 fw-bold text-warning">{{ $overallStats['sakit'] ?? 0 }}</span>
                                <span class="text-muted small">kali</span>
                            </div>
                            <div class="small text-muted mt-1" style="font-size: 10px;">Keterangan sakit</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold" style="font-size: 11px;">TOTAL ALPA</span>
                                <span class="badge bg-danger-subtle text-danger rounded-circle p-2"><i class="bi bi-x-lg fs-6"></i></span>
                            </div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="fs-2 fw-bold text-danger">{{ $overallStats['alpa'] ?? 0 }}</span>
                                <span class="text-muted small">kali</span>
                            </div>
                            <div class="small text-muted mt-1" style="font-size: 10px;">Tanpa keterangan</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. DATA KEHADIRAN PER ANAK (DETAIL SAKIT, IZIN, ALPA UNTUK BK) -->
            <section class="content-card mb-4">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <div>
                            <h3 class="section-title mb-0">Pemantauan Presensi Per Siswa</h3>
                            <p class="section-description mb-0">Identifikasi jumlah izin, sakit, dan alpa setiap anak untuk pembinaan bimbingan konseling.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('reports.attendance') }}" class="btn-outline-primary-custom btn-sm">
                            <i class="bi bi-file-earmark-text me-1"></i> Rekap Laporan Presensi
                        </a>
                    </div>
                </div>

                <!-- FILTER KELAS & SEARCH SISWA -->
                <form action="{{ route('dashboard.bk') }}" method="GET" class="p-3 bg-light rounded-3 border my-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-6 col-md-4">
                            <label class="form-label small fw-bold mb-1">Filter Kelas</label>
                            <select name="class_id" class="form-select form-select-sm">
                                <option value="">Semua Kelas Rombel</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}" @selected($selectedClassId == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-5">
                            <label class="form-label small fw-bold mb-1">Cari Nama / NIS Siswa</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Ketik nama atau NIS peserta didik..." value="{{ $search }}">
                        </div>
                        <div class="col-sm-12 col-md-3 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-bold">
                                <i class="bi bi-search me-1"></i> Cari Data
                            </button>
                            @if(request()->filled('class_id') || request()->filled('search'))
                                <a href="{{ route('dashboard.bk') }}" class="btn btn-outline-secondary btn-sm" title="Reset Pencarian">
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
                                <th width="5%" class="text-center">No</th>
                                <th width="15%">NIS</th>
                                <th width="25%">Nama Siswa</th>
                                <th width="15%">Kelas</th>
                                <th width="10%" class="text-center">Hadir</th>
                                <th width="10%" class="text-center">Izin</th>
                                <th width="10%" class="text-center">Sakit</th>
                                <th width="10%" class="text-center">Alpa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students ?? [] as $idx => $st)
                                <tr>
                                    <td class="text-center text-muted fw-bold">
                                        {{ method_exists($students, 'firstItem') ? ($students->firstItem() + $idx) : ($idx + 1) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace">
                                            {{ $st->nis ?: $st->login_key }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-dark d-block">{{ $st->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border">{{ $st->schoolClass?->name ?? 'Belum ada kelas' }}</span>
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
                                                <small class="d-block text-danger mt-0.5 fw-bold" style="font-size: 9px;">Perlu Konseling</small>
                                            @endif
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">0x</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Tidak ada data siswa yang cocok dengan kriteria pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($students, 'hasPages') && $students->hasPages())
                    <div class="mt-3">
                        {{ $students->links() }}
                    </div>
                @endif
            </section>

            <!-- 3. VERIFIKASI IZIN JAM PELAJARAN (BK) -->
            <section class="content-card mb-4">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon">
                            <i class="bi bi-envelope-paper-heart-fill"></i>
                        </div>
                        <div>
                            <h3 class="section-title mb-0">Permohonan Izin Jam Pelajaran (Verifikasi BK)</h3>
                            <p class="section-description mb-0">Daftar pengajuan izin meninggalkan jam KBM yang ditujukan kepada Guru BK.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('leaves.index') }}" class="btn-primary-custom btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Manajemen Izin
                        </a>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th width="25%">Siswa & Kelas</th>
                                <th width="15%">Waktu Sesi</th>
                                <th width="15%">Tanggal</th>
                                <th width="25%">Alasan Izin</th>
                                <th width="20%" class="text-center">Status & Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLeaves ?? [] as $leave)
                                <tr>
                                    <td>
                                        <strong class="text-dark d-block">{{ $leave->student?->name ?? '-' }}</strong>
                                        <small class="text-muted" style="font-size: 10px;">{{ $leave->student?->schoolClass?->name ?? '-' }} &bull; NIS: {{ $leave->student?->nis ?: $leave->student?->login_key }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border font-monospace">
                                            {{ $leave->jamMulai?->nama_sesi ?? 'Mulai' }} - {{ $leave->jamSelesai?->nama_sesi ?? 'Selesai' }}
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
                                        Belum ada permohonan izin jam pelajaran siswa.
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
                        <h3 id="quick-actions-title" class="section-title">Akses Cepat BK</h3>
                        <p class="section-description">Navigasi ke modul pemantauan dan rekapitulasi data kehadiran.</p>
                    </div>
                </div>

                <div class="scp-dashboard-links">
                    <a class="scp-dashboard-link" href="{{ route('attendances.index') }}">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-calendar-check"></i></span>
                        <span class="scp-dashboard-link-label">Data Presensi</span>
                        <i class="bi bi-arrow-up-right scp-dashboard-link-arrow"></i>
                    </a>

                    <a class="scp-dashboard-link" href="{{ route('reports.attendance') }}">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-file-earmark-bar-graph"></i></span>
                        <span class="scp-dashboard-link-label">Laporan Presensi</span>
                        <i class="bi bi-arrow-up-right scp-dashboard-link-arrow"></i>
                    </a>

                    <a class="scp-dashboard-link" href="{{ route('notifications.index') }}">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-bell"></i></span>
                        <span class="scp-dashboard-link-label">Notifikasi</span>
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
                        <h3 class="section-title">Pedoman Operasional Bimbingan Konseling</h3>
                        <p class="section-description">Panduan pemantauan disiplin dan bimbingan kehadiran peserta didik.</p>
                    </div>
                </div>

                <div class="rule-list">
                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Monitoring Ketidakhadiran Siswa</span>
                            <p class="rule-description">
                                BK memonitor siswa dengan catatan alpa atau ketidakhadiran berulang guna memberikan konseling dan pendampingan seawal mungkin.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Rekapitulasi dan Analisis Kehadiran</span>
                            <p class="rule-description">
                                Manfaatkan modul Laporan Presensi untuk menyaring data per tanggal dan per kelas serta mencetak rekapitulasi kehadiran.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Koordinasi dengan Wali Kelas</span>
                            <p class="rule-description">
                                Bersinergi dengan Wali Kelas dalam penelusuran surat izin, surat keterangan sakit, dan pemanggilan orang tua siswa jika diperlukan.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Kerahasiaan dan Catatan Pembinaan</span>
                            <p class="rule-description">
                                Data presensi dan histori kehadiran siswa dikelola secara aman untuk keperluan pelaporan bimbingan dan tindak lanjut edukatif.
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
