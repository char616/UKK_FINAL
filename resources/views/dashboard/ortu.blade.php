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

    <style>
        .infografis-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .infografis-header {
            padding: 22px 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        .infografis-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .infografis-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #174ea6 0%, #2563eb 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.22);
            flex-shrink: 0;
        }
        .infografis-title {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.3px;
        }
        .infografis-subtitle {
            font-size: 12.5px;
            color: #64748b;
            margin: 3px 0 0 0;
        }
        .infografis-score-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            border-radius: 999px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            font-weight: 700;
            font-size: 13px;
        }
        .infografis-score-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
        }
        .infografis-body {
            padding: 24px;
        }
        .infografis-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        .stat-tile {
            position: relative;
            padding: 20px 22px;
            border-radius: 16px;
            border: 1px solid transparent;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }
        .stat-tile:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }
        .stat-tile::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        .stat-tile.tile-hadir {
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
            border-color: #bbf7d0;
        }
        .stat-tile.tile-hadir::before { background: #16a34a; }
        .stat-tile.tile-izin {
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
            border-color: #bfdbfe;
        }
        .stat-tile.tile-izin::before { background: #2563eb; }
        .stat-tile.tile-sakit {
            background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
            border-color: #fde68a;
        }
        .stat-tile.tile-sakit::before { background: #d97706; }
        .stat-tile.tile-alpa {
            background: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%);
            border-color: #fecaca;
        }
        .stat-tile.tile-alpa::before { background: #dc2626; }

        .stat-tile-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }
        .stat-tile-label {
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .stat-tile-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .tile-hadir .stat-tile-label, .tile-hadir .stat-tile-number { color: #15803d; }
        .tile-hadir .stat-tile-icon { background: #dcfce7; color: #16a34a; }
        .tile-izin .stat-tile-label, .tile-izin .stat-tile-number { color: #1d4ed8; }
        .tile-izin .stat-tile-icon { background: #dbeafe; color: #2563eb; }
        .tile-sakit .stat-tile-label, .tile-sakit .stat-tile-number { color: #b45309; }
        .tile-sakit .stat-tile-icon { background: #fef3c7; color: #d97706; }
        .tile-alpa .stat-tile-label, .tile-alpa .stat-tile-number { color: #b91c1c; }
        .tile-alpa .stat-tile-icon { background: #fee2e2; color: #dc2626; }

        .stat-tile-value-row {
            display: flex;
            align-items: baseline;
            gap: 6px;
            margin-bottom: 8px;
        }
        .stat-tile-number {
            font-size: 32px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.5px;
        }
        .stat-tile-unit {
            font-size: 12.5px;
            font-weight: 600;
            color: #64748b;
        }
        .stat-tile-pct {
            margin-left: auto;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(15, 23, 42, 0.08);
            color: #334155;
        }
        .stat-tile-desc {
            font-size: 11.5px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .infografis-ratio-box {
            margin-top: 22px;
            padding: 18px 20px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .infografis-progress {
            height: 12px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
            display: flex;
            margin: 12px 0 14px 0;
        }
        .infografis-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .infografis-legend-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            font-size: 12px;
            color: #475569;
        }
        .infografis-legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        @media (max-width: 991.98px) {
            .infografis-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 575.98px) {
            .infografis-header, .infografis-body {
                padding: 16px;
            }
            .infografis-grid {
                grid-template-columns: 1fr;
            }
            .stat-tile {
                padding: 16px 18px;
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

            @php
                $childNames = collect($studentsData ?? [])->map(fn($d) => $d['student']->name ?? null)->filter()->join(', ');
            @endphp

            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-house-door-fill"></i>
                            ORANG TUA / WALI SISWA
                        </div>

                        <h2 class="hero-title">
                            @if(($user->role?->name ?? '') === 'orang_tua')
                                Halo, Bapak/Ibu {{ $user->name }}.
                            @else
                                Selamat Datang, Orang Tua / Wali dari {{ $user->name }}.
                            @endif
                        </h2>

                        <p class="hero-description">
                            @if(($user->role?->name ?? '') === 'orang_tua' && $childNames)
                                Wali Murid dari Ananda <strong>{{ $childNames }}</strong>. Pantau kehadiran dan kegiatan belajar putra-putri Anda di sekolah secara transparan, lakukan konfirmasi perizinan belajar, serta dapatkan informasi akademik terkini melalui SCP System.
                            @else
                                Pantau kehadiran dan kegiatan belajar putra-putri Anda di sekolah secara transparan, lakukan konfirmasi perizinan belajar, serta dapatkan informasi akademik terkini melalui SCP System.
                            @endif
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
                    $totalKbm = max($cStats['total'] ?? 0, 1);
                    $hasSessionData = ($cStats['total'] ?? 0) > 0;
                    $hadirPct = $hasSessionData ? round((($cStats['hadir'] ?? 0) / $totalKbm) * 100, 1) : 100;
                    $izinPct = $hasSessionData ? round((($cStats['izin'] ?? 0) / $totalKbm) * 100, 1) : 0;
                    $sakitPct = $hasSessionData ? round((($cStats['sakit'] ?? 0) / $totalKbm) * 100, 1) : 0;
                    $alpaPct = $hasSessionData ? round((($cStats['alpa'] ?? 0) / $totalKbm) * 100, 1) : 0;
                @endphp
                <section class="infografis-card">
                    <div class="infografis-header">
                        <div class="infografis-header-left">
                            <div class="infografis-header-icon">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h3 class="infografis-title">Infografis Kehadiran: {{ $child->name }}</h3>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold" style="font-size: 11px;">
                                        <i class="bi bi-building me-1"></i>Kelas {{ $child->schoolClass?->name ?? 'Belum ditentukan' }}
                                    </span>
                                    <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 11px;">
                                        NIS: {{ $child->nis ?: $child->login_key }}
                                    </span>
                                </div>
                                <p class="infografis-subtitle">
                                    Ringkasan akumulasi kehadiran, izin, sakit, dan alpa putra/putri Anda selama sesi KBM berlangsung.
                                </p>
                            </div>
                        </div>
                        <div class="infografis-score-pill">
                            <span class="infografis-score-dot"></span>
                            <span>Tingkat Kehadiran: <strong>{{ $cStats['persentase'] ?? 100 }}%</strong></span>
                        </div>
                    </div>

                    <div class="infografis-body">
                        <!-- STAT CARDS (HADIR, IZIN, SAKIT, ALPA) -->
                        <div class="infografis-grid">
                            <!-- HADIR -->
                            <div class="stat-tile tile-hadir">
                                <div class="stat-tile-top">
                                    <span class="stat-tile-label">HADIR</span>
                                    <div class="stat-tile-icon">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </div>
                                <div class="stat-tile-value-row">
                                    <span class="stat-tile-number">{{ $cStats['hadir'] ?? 0 }}</span>
                                    <span class="stat-tile-unit">Sesi</span>
                                    <span class="stat-tile-pct">{{ $hadirPct }}%</span>
                                </div>
                                <div class="stat-tile-desc">
                                    <i class="bi bi-clock-history text-success"></i>
                                    <span>Jam pelajaran diikuti</span>
                                </div>
                            </div>

                            <!-- IZIN -->
                            <div class="stat-tile tile-izin">
                                <div class="stat-tile-top">
                                    <span class="stat-tile-label">IZIN</span>
                                    <div class="stat-tile-icon">
                                        <i class="bi bi-envelope-check-fill"></i>
                                    </div>
                                </div>
                                <div class="stat-tile-value-row">
                                    <span class="stat-tile-number">{{ $cStats['izin'] ?? 0 }}</span>
                                    <span class="stat-tile-unit">Sesi</span>
                                    <span class="stat-tile-pct">{{ $izinPct }}%</span>
                                </div>
                                <div class="stat-tile-desc">
                                    <i class="bi bi-file-earmark-check text-primary"></i>
                                    <span>Pengajuan izin belajar</span>
                                </div>
                            </div>

                            <!-- SAKIT -->
                            <div class="stat-tile tile-sakit">
                                <div class="stat-tile-top">
                                    <span class="stat-tile-label">SAKIT</span>
                                    <div class="stat-tile-icon">
                                        <i class="bi bi-bandaid-fill"></i>
                                    </div>
                                </div>
                                <div class="stat-tile-value-row">
                                    <span class="stat-tile-number">{{ $cStats['sakit'] ?? 0 }}</span>
                                    <span class="stat-tile-unit">Sesi</span>
                                    <span class="stat-tile-pct">{{ $sakitPct }}%</span>
                                </div>
                                <div class="stat-tile-desc">
                                    <i class="bi bi-hospital text-warning"></i>
                                    <span>Surat keterangan sakit</span>
                                </div>
                            </div>

                            <!-- ALPA -->
                            <div class="stat-tile tile-alpa">
                                <div class="stat-tile-top">
                                    <span class="stat-tile-label">ALPA (TANPA KET.)</span>
                                    <div class="stat-tile-icon">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                </div>
                                <div class="stat-tile-value-row">
                                    <span class="stat-tile-number">{{ $cStats['alpa'] ?? 0 }}</span>
                                    <span class="stat-tile-unit">Sesi</span>
                                    <span class="stat-tile-pct">{{ $alpaPct }}%</span>
                                </div>
                                <div class="stat-tile-desc">
                                    <i class="bi bi-x-circle text-danger"></i>
                                    <span>Tanpa keterangan izin</span>
                                </div>
                            </div>
                        </div>

                        <!-- PROGRESS BAR & DISTRIBUSI -->
                        <div class="infografis-ratio-box">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="fw-bold text-dark" style="font-size: 13px;">
                                    <i class="bi bi-bar-chart-steps me-1 text-primary"></i> Proporsi & Rasio Distribusi Kehadiran Ananda
                                </span>
                                <span class="text-muted" style="font-size: 12px;">
                                    Total Tercatat: <strong class="text-dark">{{ $cStats['total'] ?? 0 }} Sesi KBM</strong>
                                </span>
                            </div>

                            <div class="infografis-progress">
                                @if(!$hasSessionData)
                                    <div class="progress-bar bg-success" style="width: 100%;"></div>
                                @else
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $hadirPct }}%;" title="Hadir: {{ $cStats['hadir'] }} ({{ $hadirPct }}%)"></div>
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $izinPct }}%;" title="Izin: {{ $cStats['izin'] }} ({{ $izinPct }}%)"></div>
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $sakitPct }}%;" title="Sakit: {{ $cStats['sakit'] }} ({{ $sakitPct }}%)"></div>
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $alpaPct }}%;" title="Alpa: {{ $cStats['alpa'] }} ({{ $alpaPct }}%)"></div>
                                @endif
                            </div>

                            <div class="infografis-legend">
                                <div class="infografis-legend-pill">
                                    <span class="infografis-legend-dot" style="background: #16a34a;"></span>
                                    <span>Hadir: <strong class="text-dark">{{ $cStats['hadir'] ?? 0 }} Sesi</strong> ({{ $hadirPct }}%)</span>
                                </div>
                                <div class="infografis-legend-pill">
                                    <span class="infografis-legend-dot" style="background: #2563eb;"></span>
                                    <span>Izin: <strong class="text-dark">{{ $cStats['izin'] ?? 0 }} Sesi</strong> ({{ $izinPct }}%)</span>
                                </div>
                                <div class="infografis-legend-pill">
                                    <span class="infografis-legend-dot" style="background: #d97706;"></span>
                                    <span>Sakit: <strong class="text-dark">{{ $cStats['sakit'] ?? 0 }} Sesi</strong> ({{ $sakitPct }}%)</span>
                                </div>
                                <div class="infografis-legend-pill">
                                    <span class="infografis-legend-dot" style="background: #dc2626;"></span>
                                    <span>Alpa: <strong class="text-dark">{{ $cStats['alpa'] ?? 0 }} Sesi</strong> ({{ $alpaPct }}%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @empty
                <div class="content-card mb-4 text-center py-4">
                    <i class="bi bi-person-x fs-2 text-muted mb-2"></i>
                    <p class="text-muted mb-0">Akun orang tua ini belum terhubung dengan data siswa. Hubungi pihak sekolah.</p>
                </div>
            @endforelse

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
