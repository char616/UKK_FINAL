<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Aktivitas Sistem | SCP2</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <!-- SIDEBAR -->
    @include('layouts.sidebar', ['user' => $user])

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <div class="content-wrapper">

            <!-- HEADER HALAMAN -->
            <header class="page-header">
                <div>
                    <div class="page-eyebrow">
                        <i class="bi bi-shield-check"></i>
                        Keamanan & Akuntabilitas
                    </div>
                    <h1 class="page-title">Riwayat Aktivitas Sistem</h1>
                </div>

                <div class="academic-badge">
                    <i class="bi bi-calendar3"></i>
                    <span>Tahun Ajaran 2026/2027</span>
                </div>
            </header>

            <!-- HERO BANNER -->
            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-shield-check"></i>
                            AUDIT TRAIL & PENGAWASAN
                        </div>
                        <h2 class="hero-title">Pencatatan Log Aktivitas Pengguna</h2>
                        <p class="hero-description">Pantau seluruh rekaman jejak digital tindakan penting yang dilakukan oleh pengguna, termasuk perubahan data akademik, jadwal pelajaran, penguncian sistem, dan perizinan.</p>
                    </div>
                    <div class="hero-icon">
                        <i class="bi bi-shield-shaded"></i>
                    </div>
                </div>
            </section>

            @if(session('success'))
                <div class="alert-scp-success">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-scp-danger">
                    <i class="bi bi-exclamation-circle-fill fs-5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- KONTEN TABEL AUDIT LOG -->
            <section class="content-card">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h2 class="section-title">Log Aktivitas Terkini</h2>
                            <p class="section-description">Catatan peristiwa dan tindakan administratif secara kronologis.</p>
                        </div>
                    </div>

                    @if($logs->total() > 0)
                        <form action="{{ route('audit-logs.destroy-all') }}" method="POST" onsubmit="return confirm('{{ $date ? 'Apakah Anda yakin ingin menghapus seluruh audit log pada tanggal ' . $date . '?' : 'Apakah Anda yakin ingin menghapus seluruh riwayat audit log?' }}');">
                            @csrf
                            @method('DELETE')
                            @if($date)
                                <input type="hidden" name="date" value="{{ $date }}">
                            @endif
                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3 py-2 rounded-3 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-trash3-fill"></i>
                                <span>{{ $date ? 'Hapus Log Tanggal Ini' : 'Hapus Semua Audit Log' }}</span>
                            </button>
                        </form>
                    @endif
                </div>

                <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-2 mb-4 align-items-end">
                    <div class="col-md-4">
                        <label for="audit-date" class="form-label">Filter Tanggal</label>
                        <input id="audit-date" type="date" name="date" value="{{ $date }}" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn-primary-custom flex-grow-1" type="submit"><i class="bi bi-filter"></i> Tampilkan</button>
                        <a class="btn-outline-custom" href="{{ route('audit-logs.index') }}" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th width="6%" class="text-center">No</th>
                                <th width="16%">Waktu</th>
                                <th width="20%">Pengguna</th>
                                <th width="16%">Kategori Aksi</th>
                                <th>Keterangan Detail</th>
                                <th width="10%" class="text-end">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $index => $log)
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ ($logs->firstItem() ?? 1) + $index }}</td>
                                    <td>
                                        <span class="text-muted small">
                                            <i class="bi bi-clock me-1"></i>{{ $log->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-person-circle text-primary fs-5"></i>
                                            <div>
                                                <strong class="text-dark d-block">{{ $log->user->name ?? '-' }}</strong>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 9px; padding: 1px 6px;">
                                                    {{ $log->user->role->display_name ?? $log->user->role->name ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1 fw-bold">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td><span class="text-dark small">{{ $log->description }}</span></td>
                                    <td class="text-end">
                                        <form action="{{ route('audit-logs.destroy', $log) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus catatan audit log ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2.5 rounded-2" title="Hapus Log">
                                                <i class="bi bi-trash3"></i>
                                                <span class="d-none d-md-inline ms-1" style="font-size: 11px;">Hapus</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-journal-x fs-2 d-block mb-2 opacity-50"></i>
                                        <strong class="d-block text-dark">Belum Ada Riwayat Aktivitas</strong>
                                        <span class="small">Data log aktivitas yang telah tercatat akan ditampilkan di sini.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="d-flex justify-content-center mt-4">{{ $logs->links() }}</div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
