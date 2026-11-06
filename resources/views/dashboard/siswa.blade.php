<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - SCP System</title>

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
                        <i class="bi bi-book-half"></i>
                        Portal Peserta Didik
                    </div>
                    <h1 class="page-title">Dashboard Siswa</h1>
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
                            <i class="bi bi-backpack-fill"></i>
                            PESERTA DIDIK
                        </div>

                        <h2 class="hero-title">
                            Halo, {{ $user->name }}.
                        </h2>

                        <p class="hero-description">
                            Pantau jadwal mata pelajaran harian, periksa catatan kehadiran di setiap jam KBM, serta lakukan pengajuan surat perizinan atau sakit secara online melalui SCP System.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                </div>
            </section>

            <!-- 1. INFOGRAFIS KEHADIRAN SISWA (SAKIT, IZIN, ALPA, HADIR) -->
            <section class="content-card mb-4">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon">
                            <i class="bi bi-pie-chart-fill"></i>
                        </div>
                        <div>
                            <h3 class="section-title mb-0">Infografis Kehadiran Saya</h3>
                            <p class="section-description mb-0">Ringkasan akumulasi kehadiran, sakit, izin, dan alpa selama KBM berlangsung.</p>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fw-bold" style="font-size: 12px;">
                            <i class="bi bi-activity me-1"></i> Tingkat Kehadiran: {{ $stats['persentase'] ?? 100 }}%
                        </span>
                    </div>
                </div>

                <div class="row g-3 my-2">
                    <!-- HADIR -->
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100 position-relative overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold" style="font-size: 11px;">HADIR</span>
                                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-check-lg fs-6"></i></span>
                            </div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="fs-2 fw-bold text-success">{{ $stats['hadir'] ?? 0 }}</span>
                                <span class="text-muted small">kali</span>
                            </div>
                            <div class="small text-muted mt-1" style="font-size: 10px;">Jam pelajaran diikuti</div>
                        </div>
                    </div>

                    <!-- IZIN -->
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100 position-relative overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold" style="font-size: 11px;">IZIN</span>
                                <span class="badge bg-info-subtle text-primary rounded-circle p-2"><i class="bi bi-envelope fs-6"></i></span>
                            </div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="fs-2 fw-bold text-primary">{{ $stats['izin'] ?? 0 }}</span>
                                <span class="text-muted small">kali</span>
                            </div>
                            <div class="small text-muted mt-1" style="font-size: 10px;">Pengajuan izin terdata</div>
                        </div>
                    </div>

                    <!-- SAKIT -->
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100 position-relative overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold" style="font-size: 11px;">SAKIT</span>
                                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-bandaid fs-6"></i></span>
                            </div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="fs-2 fw-bold text-warning">{{ $stats['sakit'] ?? 0 }}</span>
                                <span class="text-muted small">kali</span>
                            </div>
                            <div class="small text-muted mt-1" style="font-size: 10px;">Surat sakit terkonfirmasi</div>
                        </div>
                    </div>

                    <!-- ALPA -->
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100 position-relative overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold" style="font-size: 11px;">ALPA (TANPA KETERANGAN)</span>
                                <span class="badge bg-danger-subtle text-danger rounded-circle p-2"><i class="bi bi-x-lg fs-6"></i></span>
                            </div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="fs-2 fw-bold text-danger">{{ $stats['alpa'] ?? 0 }}</span>
                                <span class="text-muted small">kali</span>
                            </div>
                            <div class="small text-muted mt-1" style="font-size: 10px;">Ketidakhadiran tanpa izin</div>
                        </div>
                    </div>
                </div>

                <!-- PROGRESS BAR KEHADIRAN -->
                <div class="mt-3 pt-2">
                    <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 11px;">
                        <span>Progress Kehadiran Siswa</span>
                        <span class="fw-bold text-dark">{{ $stats['hadir'] ?? 0 }} dari {{ $stats['total'] ?? 0 }} Total KBM ({{ $stats['persentase'] ?? 100 }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['persentase'] ?? 100 }}%;" aria-valuenow="{{ $stats['persentase'] ?? 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </section>

            <!-- 2. STATUS PENGAJUAN PERIZINAN SISWA -->
            <section class="content-card mb-4">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon">
                            <i class="bi bi-envelope-paper-heart-fill"></i>
                        </div>
                        <div>
                            <h3 class="section-title mb-0">Status Pengajuan Izin & Sakit</h3>
                            <p class="section-description mb-0">Pantau apakah permohonan izin Anda berstatus Pending, Approved, atau Rejected.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('leaves.create') }}" class="btn-primary-custom btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Ajukan Izin Baru
                        </a>
                    </div>
                </div>

                <div class="table-responsive mt-2">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th width="15%">Jenis & Waktu</th>
                                <th width="20%">Rentang Tanggal</th>
                                <th width="30%">Alasan / Keterangan</th>
                                <th width="15%">Verifikator</th>
                                <th width="20%" class="text-center">Status & Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLeaves ?? [] as $leave)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if(strtolower($leave->type) === 'sakit')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle d-inline-block" style="width: fit-content;">
                                                    <i class="bi bi-bandaid me-1"></i> Sakit
                                                </span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-inline-block" style="width: fit-content;">
                                                    <i class="bi bi-envelope me-1"></i> Izin
                                                </span>
                                            @endif
                                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 9px; width: fit-content;">
                                                {{ $leave->jenis_waktu_izin === 'jam_pelajaran' ? 'Per Jam KBM' : 'Seharian Penuh' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-dark fw-semibold">
                                            <i class="bi bi-calendar3 me-1 text-muted"></i>
                                            {{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') : '-' }}
                                            @if($leave->end_date && $leave->start_date != $leave->end_date)
                                                s/d {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark small d-block">{{ $leave->reason }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $leave->target_approver === 'bk' ? 'Guru BK' : 'Wali Kelas' }}
                                        </span>
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
                                                <div class="mt-1 p-1.5 bg-danger-subtle rounded border border-danger-subtle text-danger text-start" style="font-size: 10px;">
                                                    <strong>Alasan:</strong> <em>"{{ $leave->alasan_penolakan }}"</em>
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                                <i class="bi bi-clock-fill me-1"></i> Pending (Menunggu)
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block text-secondary mb-1"></i>
                                        Belum ada riwayat permohonan izin atau sakit.
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
                        <h3 id="quick-actions-title" class="section-title">Akses Cepat Siswa</h3>
                        <p class="section-description">Navigasi ke informasi jadwal dan modul presensi harian.</p>
                    </div>
                </div>

                <div class="scp-dashboard-links">
                    <a class="scp-dashboard-link" href="{{ route('schedules.index') }}">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-calendar-week"></i></span>
                        <span class="scp-dashboard-link-label">Jadwal Pelajaran</span>
                        <i class="bi bi-arrow-up-right scp-dashboard-link-arrow"></i>
                    </a>

                    <a class="scp-dashboard-link" href="{{ route('attendances.index') }}">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-calendar-check"></i></span>
                        <span class="scp-dashboard-link-label">Presensi Saya</span>
                        <i class="bi bi-arrow-up-right scp-dashboard-link-arrow"></i>
                    </a>

                    <a class="scp-dashboard-link" href="{{ route('leaves.index') }}">
                        <span class="scp-dashboard-link-icon"><i class="bi bi-envelope-paper"></i></span>
                        <span class="scp-dashboard-link-label">Izin & Sakit</span>
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
                        <h3 class="section-title">Ketentuan & Panduan Siswa</h3>
                        <p class="section-description">Aturan presensi dan perizinan belajar di sekolah.</p>
                    </div>
                </div>

                <div class="rule-list">
                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Tertib Mengikuti Jadwal KBM</span>
                            <p class="rule-description">
                                Hadir tepat waktu di kelas sesuai jadwal pelajaran yang berlaku pada putaran blok aktif saat ini.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Pengajuan Izin dan Keterangan Sakit</span>
                            <p class="rule-description">
                                Bila berhalangan hadir, segera ajukan izin melalui menu Izin & Sakit dengan melampirkan foto surat dokter atau surat izin orang tua.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Cek Catatan Kehadiran Mandiri</span>
                            <p class="rule-description">
                                Pantau riwayat presensi Anda secara berkala. Jika terjadi kesalahan pencatatan, segera konfirmasi kepada Guru Pengampu atau Wali Kelas.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Keamanan Akun Peserta Didik</span>
                            <p class="rule-description">
                                Jaga kerahasiaan kata sandi akun dan atur email pemulihan pada menu Pengaturan Akun untuk kemudahan reset password.
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
