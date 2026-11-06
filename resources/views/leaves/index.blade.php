<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Izin & Sakit - SCP System</title>

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
                        <i class="bi bi-envelope-paper-heart-fill"></i>
                        Layanan Administrasi Siswa
                    </div>
                    <h1 class="page-title">Manajemen Izin & Sakit</h1>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if(in_array($user->role->name, ['siswa', 'orang_tua'], true))
                        <a href="{{ route('leaves.create') }}" class="btn-primary-custom">
                            <i class="bi bi-plus-lg"></i>
                            <span>Ajukan Izin Baru</span>
                        </a>
                    @else
                        <div class="academic-badge">
                            <i class="bi bi-calendar3"></i>
                            <span>Tahun Ajaran 2026/2027</span>
                        </div>
                    @endif
                </div>
            </header>

            @if(session('success'))
                <div class="alert-scp-success">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-scp-danger">
                    <i class="bi bi-x-circle-fill fs-5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-clipboard2-pulse-fill"></i>
                            MODUL PERIZINAN TERPADU
                        </div>

                        <h2 class="hero-title">
                            Rekapitulasi Izin & Sakit Peserta Didik
                        </h2>

                        <p class="hero-description">
                            Kelola pengajuan permohonan ketidakhadiran peserta didik (seharian maupun per jam pelajaran), periksa kelengkapan berkas bukti surat keterangan, serta lakukan verifikasi oleh Wali Kelas atau Guru BK.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-envelope-check-fill"></i>
                    </div>
                </div>
            </section>

            <div class="content-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Daftar Pengajuan Perizinan</h3>
                        <p class="section-description">Riwayat permohonan izin belajar dan status verifikasi dari wali kelas / BK.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th width="4%" class="text-center">No</th>
                                @if($user->role->name !== 'siswa')
                                    <th width="20%">Peserta Didik / Kelas</th>
                                @endif
                                <th width="14%">Jenis & Waktu</th>
                                <th width="12%">Target Verifikator</th>
                                <th width="14%">Rentang Tanggal</th>
                                <th width="18%">Alasan & Keterangan</th>
                                <th width="10%">Dokumen Bukti</th>
                                <th width="10%" class="text-center">Status</th>
                                @if(in_array($user->role->name, ['wali_kelas', 'bk'], true))
                                    <th width="12%" class="text-center">Aksi Verifikasi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $index => $leave)
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                    @if($user->role->name !== 'siswa')
                                        <td>
                                            <strong class="text-dark d-block">{{ $leave->student->name ?? '-' }}</strong>
                                            <small class="text-muted" style="font-size: 10px;">
                                                NIS: {{ $leave->student->nis ?: $leave->student->login_key }} &bull; {{ $leave->student->schoolClass->name ?? 'Belum ada kelas' }}
                                            </small>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if(strtolower($leave->type) === 'sakit')
                                                <span class="scp-badge scp-badge-warning d-inline-block">
                                                    <i class="bi bi-bandaid"></i> Sakit
                                                </span>
                                            @else
                                                <span class="scp-badge scp-badge-info d-inline-block">
                                                    <i class="bi bi-envelope"></i> Izin
                                                </span>
                                            @endif

                                            @if($leave->jenis_waktu_izin === 'jam_pelajaran')
                                                <span class="badge bg-light text-primary border font-monospace" style="font-size: 9px;">
                                                    <i class="bi bi-clock me-1"></i>{{ $leave->jamMulai?->nama_sesi ?? 'Jam Mulai' }} - {{ $leave->jamSelesai?->nama_sesi ?? 'Jam Selesai' }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border" style="font-size: 9px;">
                                                    <i class="bi bi-sun me-1"></i>Seharian Penuh
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($leave->target_approver === 'bk')
                                            <span class="badge bg-purple-subtle text-purple border px-2 py-1" style="background:#F3E8FF;color:#7E22CE;border-color:#E9D5FF;">
                                                <i class="bi bi-person-badge me-1"></i>Guru BK
                                            </span>
                                        @else
                                            <span class="badge bg-blue-subtle text-primary border px-2 py-1" style="background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE;">
                                                <i class="bi bi-person-check me-1"></i>Wali Kelas
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $leave->start_date->format('d/m/Y') }}
                                            @if($leave->start_date->ne($leave->end_date))
                                                &mdash; {{ $leave->end_date->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark small d-block">{{ $leave->reason }}</span>
                                        @if($leave->status === 'Rejected' && $leave->alasan_penolakan)
                                            <div class="mt-1.5 p-2 bg-danger-subtle rounded border border-danger-subtle text-danger" style="font-size: 11px;">
                                                <div class="fw-bold d-flex align-items-center gap-1">
                                                    <i class="bi bi-x-circle-fill"></i>
                                                    <span>Ditolak oleh {{ $leave->target_approver === 'bk' ? 'Guru BK' : 'Wali Kelas' }}</span>
                                                </div>
                                                <div class="mt-0.5 text-dark">Alasan: <em>"{{ $leave->alasan_penolakan }}"</em></div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $filePath = $leave->file_bukti ?: $leave->attachment;
                                            $hasFile = filled($filePath);
                                            $isPdf = $hasFile && str_ends_with(strtolower($filePath), '.pdf');
                                        @endphp
                                        @if($hasFile)
                                            <div class="d-flex align-items-center gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 js-leave-proof-btn"
                                                    style="font-size: 11px; font-weight: 600; border-radius: 6px;"
                                                    data-proof-url="{{ route('leaves.attachment', ['leaveRequest' => $leave->id, 'preview' => 1]) }}"
                                                    data-download-url="{{ route('leaves.attachment', $leave->id) }}"
                                                    data-proof-title="Bukti {{ $leave->type }} - {{ $leave->student->name ?? 'Siswa' }} ({{ $leave->start_date->format('d/m/Y') }})"
                                                    data-file-type="{{ $isPdf ? 'pdf' : 'image' }}">
                                                    <i class="bi bi-eye me-1"></i>Lihat Bukti
                                                </button>
                                                <a href="{{ route('leaves.attachment', $leave->id) }}" class="btn btn-sm btn-light border py-1 px-1.5 text-muted" title="Unduh File" style="font-size: 11px; border-radius: 6px;">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted small">Tidak ada bukti</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $st = strtolower($leave->status);
                                        @endphp
                                        @if($st === 'approved' || $st === 'disetujui')
                                            <span class="scp-badge scp-badge-success">
                                                <i class="bi bi-check-circle-fill"></i> Disetujui
                                            </span>
                                        @elseif($st === 'rejected' || $st === 'ditolak')
                                            <span class="scp-badge scp-badge-danger">
                                                <i class="bi bi-x-circle-fill"></i> Ditolak
                                            </span>
                                        @else
                                            <span class="scp-badge scp-badge-warning">
                                                <i class="bi bi-clock-fill"></i> Menunggu
                                            </span>
                                        @endif
                                    </td>
                                    @if(in_array($user->role->name, ['wali_kelas', 'bk'], true))
                                        <td class="text-center">
                                            @php
                                                $canVerify = ($user->role->name === 'bk' && $leave->target_approver === 'bk')
                                                    || ($user->role->name === 'wali_kelas' && $leave->target_approver === 'wali_kelas');
                                            @endphp

                                            @if(strtolower($leave->status) === 'pending' && $canVerify)
                                                <div class="d-flex justify-content-center gap-1">
                                                    <form action="{{ route('leaves.status', $leave->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="status" value="Approved">
                                                        <button type="submit" class="btn btn-sm btn-success px-2 py-1" style="font-size: 11px; font-weight: 700; border-radius: 6px;">
                                                            <i class="bi bi-check-lg"></i> Setuju
                                                        </button>
                                                    </form>

                                                    <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" style="font-size: 11px; font-weight: 700; border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}">
                                                        <i class="bi bi-x-lg"></i> Tolak
                                                    </button>
                                                </div>

                                                <!-- MODAL PENOLAKAN DENGAN ALASAN WAJIB -->
                                                <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content text-start">
                                                            <form action="{{ route('leaves.status', $leave->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="status" value="Rejected">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title fs-6 fw-bold">Konfirmasi Penolakan Izin</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="small text-muted mb-2">Tolak pengajuan izin untuk siswa <strong>{{ $leave->student->name ?? '' }}</strong>.</p>
                                                                    <label class="form-label small fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                                    <textarea name="alasan_penolakan" class="form-control" rows="3" required placeholder="Tuliskan alasan penolakan izin siswa secara jelas..."></textarea>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-danger btn-sm fw-bold">Tolak Pengajuan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif(strtolower($leave->status) === 'pending')
                                                <span class="text-muted small" style="font-size: 10px;">
                                                    <i class="bi bi-hourglass-split me-1"></i>Menunggu verifikasi {{ $leave->target_approver === 'bk' ? 'Guru BK' : 'Wali Kelas' }}
                                                </span>
                                            @else
                                                <span class="text-muted small"><i class="bi bi-check-all text-success me-1"></i>Selesai</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    @php
                                        $colSpan = 9;
                                        if ($user->role->name === 'siswa') $colSpan = 7;
                                    @endphp
                                    <td colspan="{{ $colSpan }}" class="text-center text-muted py-5">Belum ada riwayat permohonan izin yang diajukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- MODAL PREVIEW BUKTI IZIN / SAKIT (FOTO & PDF) -->
    <div class="modal fade" id="leaveProofModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fs-6 fw-bold" id="leaveProofTitle">Dokumen Bukti Permohonan Izin</h5>
                    <div class="d-flex align-items-center gap-2">
                        <a id="leaveProofDownloadLink" href="#" class="btn btn-sm btn-outline-secondary" download>
                            <i class="bi bi-download me-1"></i>Unduh Berkas
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                </div>
                <div class="modal-body text-center bg-light p-3" style="min-height: 380px;">
                    <div id="leaveProofLoading" class="py-5 text-muted small">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <div>Memuat dokumen bukti...</div>
                    </div>
                    <img id="leaveProofImage" src="" alt="Bukti Izin" class="img-fluid rounded border shadow-sm d-none" style="max-height: 75vh; object-fit: contain;">
                    <iframe id="leaveProofPdf" src="" class="w-100 rounded border d-none" style="height: 70vh;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.js-leave-proof-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.dataset.proofUrl;
                const downloadUrl = this.dataset.downloadUrl;
                const title = this.dataset.proofTitle || 'Dokumen Bukti Permohonan Izin';
                const fileType = this.dataset.fileType || 'image';

                document.getElementById('leaveProofTitle').textContent = title;
                document.getElementById('leaveProofDownloadLink').href = downloadUrl;

                const img = document.getElementById('leaveProofImage');
                const pdf = document.getElementById('leaveProofPdf');
                const loader = document.getElementById('leaveProofLoading');

                loader.classList.remove('d-none');
                img.classList.add('d-none');
                pdf.classList.add('d-none');

                if (fileType === 'pdf') {
                    pdf.src = url;
                    pdf.onload = () => {
                        loader.classList.add('d-none');
                        pdf.classList.remove('d-none');
                    };
                    setTimeout(() => {
                        loader.classList.add('d-none');
                        pdf.classList.remove('d-none');
                    }, 500);
                } else {
                    img.src = url;
                    img.onload = () => {
                        loader.classList.add('d-none');
                        img.classList.remove('d-none');
                    };
                    img.onerror = () => {
                        loader.classList.add('d-none');
                        img.classList.remove('d-none');
                    };
                }

                bootstrap.Modal.getOrCreateInstance(document.getElementById('leaveProofModal')).show();
            });
        });

        document.getElementById('leaveProofModal')?.addEventListener('hidden.bs.modal', function() {
            document.getElementById('leaveProofImage').src = '';
            document.getElementById('leaveProofPdf').src = '';
        });
    </script>
</body>
</html>
