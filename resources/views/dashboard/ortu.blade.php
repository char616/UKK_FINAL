<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Orang Tua - SCP System</title>

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
                        <i class="bi bi-heart-pulse-fill"></i>
                        Portal Wali Murid
                    </div>
                    <h1 class="page-title">Dashboard Orang Tua / Wali</h1>
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
                            <i class="bi bi-house-door-fill"></i>
                            ORANG TUA / WALI SISWA
                        </div>

                        <h2 class="hero-title">
                            Selamat Datang, {{ $user->name }}.
                        </h2>

                        <p class="hero-description">
                            Pantau kehadiran dan kegiatan belajar putra-putri Anda di sekolah secara transparan, lakukan konfirmasi perizinan belajar, serta dapatkan informasi akademik terkini melalui SCP System.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </section>

            <!-- 1. INFOGRAFIS KEHADIRAN ANANDA & PENGAJUAN IZIN -->
            @forelse($studentsData ?? [] as $data)
                @php
                    $child = $data['student'];
                    $cStats = $data['stats'];
                    $cLeaves = $data['recentLeaves'];
                @endphp
                <section class="content-card mb-4">
                    <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="section-icon">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div>
                                <h3 class="section-title mb-0">Infografis Kehadiran: {{ $child->name }}</h3>
                                <p class="section-description mb-0">
                                    Kelas: <strong>{{ $child->schoolClass?->name ?? 'Belum ada kelas' }}</strong> &bull; NIS: <strong>{{ $child->nis ?: $child->login_key }}</strong>
                                </p>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fw-bold" style="font-size: 12px;">
                                <i class="bi bi-activity me-1"></i> Kehadiran: {{ $cStats['persentase'] ?? 100 }}%
                            </span>
                        </div>
                    </div>

                    <!-- STAT CARDS (HADIR, IZIN, SAKIT, ALPA) -->
                    <div class="row g-3 my-2">
                        <!-- HADIR -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-bold" style="font-size: 11px;">HADIR</span>
                                    <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-check-lg fs-6"></i></span>
                                </div>
                                <div class="d-flex align-items-baseline gap-1">
                                    <span class="fs-2 fw-bold text-success">{{ $cStats['hadir'] ?? 0 }}</span>
                                    <span class="text-muted small">kali</span>
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 10px;">Jam pelajaran diikuti</div>
                            </div>
                        </div>

                        <!-- IZIN -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-bold" style="font-size: 11px;">IZIN</span>
                                    <span class="badge bg-info-subtle text-primary rounded-circle p-2"><i class="bi bi-envelope fs-6"></i></span>
                                </div>
                                <div class="d-flex align-items-baseline gap-1">
                                    <span class="fs-2 fw-bold text-primary">{{ $cStats['izin'] ?? 0 }}</span>
                                    <span class="text-muted small">kali</span>
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 10px;">Pengajuan izin belajar</div>
                            </div>
                        </div>

                        <!-- SAKIT -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-bold" style="font-size: 11px;">SAKIT</span>
                                    <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-bandaid fs-6"></i></span>
                                </div>
                                <div class="d-flex align-items-baseline gap-1">
                                    <span class="fs-2 fw-bold text-warning">{{ $cStats['sakit'] ?? 0 }}</span>
                                    <span class="text-muted small">kali</span>
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 10px;">Surat keterangan sakit</div>
                            </div>
                        </div>

                        <!-- ALPA -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-bold" style="font-size: 11px;">ALPA</span>
                                    <span class="badge bg-danger-subtle text-danger rounded-circle p-2"><i class="bi bi-x-lg fs-6"></i></span>
                                </div>
                                <div class="d-flex align-items-baseline gap-1">
                                    <span class="fs-2 fw-bold text-danger">{{ $cStats['alpa'] ?? 0 }}</span>
                                    <span class="text-muted small">kali</span>
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 10px;">Tanpa keterangan</div>
                            </div>
                        </div>
                    </div>

                    <!-- PROGRESS BAR -->
                    <div class="mt-2 mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 11px;">
                            <span>Tingkat Disiplin Kehadiran</span>
                            <span class="fw-bold text-dark">{{ $cStats['hadir'] ?? 0 }} dari {{ $cStats['total'] ?? 0 }} Sesi KBM ({{ $cStats['persentase'] ?? 100 }}%)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $cStats['persentase'] ?? 100 }}%;" aria-valuenow="{{ $cStats['persentase'] ?? 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- TABEL PENGAJUAN IZIN ANANDA -->
                    <div class="pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-dark small"><i class="bi bi-envelope-paper me-1 text-primary"></i> Status Pengajuan Izin {{ $child->name }}</strong>
                            <a href="{{ route('leaves.create', ['student_id' => $child->id]) }}" class="btn btn-outline-primary btn-sm py-0.5 px-2" style="font-size: 11px;">
                                <i class="bi bi-plus-lg me-1"></i> Ajukan Izin
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" style="font-size: 11px;">
                                <thead>
                                    <tr>
                                        <th>Jenis & Waktu</th>
                                        <th>Tanggal</th>
                                        <th>Alasan</th>
                                        <th>Verifikator</th>
                                        <th class="text-center">Status & Catatan Penolakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cLeaves as $leave)
                                        <tr>
                                            <td>
                                                <span class="badge {{ strtolower($leave->type) === 'sakit' ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary' }} border">
                                                    {{ $leave->type }} ({{ $leave->jenis_waktu_izin === 'jam_pelajaran' ? 'Per Jam' : 'Seharian' }})
                                                </span>
                                            </td>
                                            <td>{{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $leave->reason }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $leave->target_approver === 'bk' ? 'Guru BK' : 'Wali Kelas' }}</span></td>
                                            <td class="text-center">
                                                @php($st = strtolower($leave->status ?? 'pending'))
                                                @if($st === 'approved' || $st === 'disetujui')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Disetujui
                                                    </span>
                                                @elseif($st === 'rejected' || $st === 'ditolak')
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                        <i class="bi bi-x-circle-fill me-1"></i> Ditolak
                                                    </span>
                                                    @if($leave->alasan_penolakan)
                                                        <div class="mt-1 p-1 bg-danger-subtle rounded border border-danger-subtle text-danger text-start" style="font-size: 10px;">
                                                            <strong>Alasan:</strong> <em>"{{ $leave->alasan_penolakan }}"</em>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                        <i class="bi bi-clock-fill me-1"></i> Pending (Menunggu)
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-2">Belum ada data pengajuan izin.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @empty
                <div class="content-card mb-4 text-center py-4">
                    <i class="bi bi-person-x fs-2 text-muted mb-2"></i>
                    <p class="text-muted mb-0">Akun orang tua ini belum terhubung dengan data siswa. Hubungi pihak sekolah.</p>
                </div>
            @endforelse

            <section aria-labelledby="quick-actions-title">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <h3 id="quick-actions-title" class="section-title">Akses Cepat Wali Murid</h3>
                        <p class="section-description">Navigasi ke menu informasi presensi dan perizinan ananda.</p>
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
                        <span class="scp-dashboard-link-label">Presensi Anak</span>
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
                        <h3 class="section-title">Ketentuan & Panduan Orang Tua</h3>
                        <p class="section-description">Informasi peran orang tua dalam pemantauan KBM ananda.</p>
                    </div>
                </div>

                <div class="rule-list">
                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Pemantauan Presensi KBM Harian</span>
                            <p class="rule-description">
                                Anda dapat melihat catatan kehadiran putra-putri pada setiap jam mata pelajaran secara transparan dan berkala.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Pengajuan & Konfirmasi Perizinan</span>
                            <p class="rule-description">
                                Pastikan mengajukan izin atau konfirmasi sakit disertai dokumen pendukung bila putra/putri tidak dapat hadir di sekolah.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Pemeriksaan Jadwal Pelajaran</span>
                            <p class="rule-description">
                                Jadwal KBM dan perubahan putaran blok dapat dipantau langsung untuk membantu persiapan belajar anak di rumah.
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <div class="rule-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="rule-content">
                            <span class="rule-title">Komunikasi Efektif dengan Sekolah</span>
                            <p class="rule-description">
                                Bila ada catatan ketidakhadiran yang tidak sesuai, orang tua dapat segera menghubungi Wali Kelas atau Guru BK yang bersangkutan.
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
