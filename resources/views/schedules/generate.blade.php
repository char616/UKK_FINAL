<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran KBM - SCP System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #174EA6;
            --primary-dark: #123D80;
            --background: #F4F7FB;
            --surface: #FFFFFF;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border-color: #CBD5E1;
            --sidebar-width: 280px;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text-dark);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
        }

        .main-content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 24px 32px 60px;
        }

        /* FILTER & KONTROL PANEL */
        .filter-box {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 18px 22px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .jump-nav-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            padding-top: 12px;
            margin-top: 14px;
            border-top: 1px dashed #E2E8F0;
        }

        .jump-link {
            padding: 3px 10px;
            border-radius: 6px;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            color: #334155;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.1s ease;
        }

        .jump-link:hover {
            background: #EFF6FF;
            color: #1D4ED8;
            border-color: #93C5FD;
        }

        /* LEMBAR DOKUMEN JADWAL RESMI */
        .schedule-document-card {
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            padding: 32px 36px 40px;
            margin-bottom: 36px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
        }

        /* KOP RESMI DOKUMEN */
        .document-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 3px double #334155;
        }

        .doc-school-title {
            font-size: 18px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0 0 3px;
        }

        .doc-sub-title {
            font-size: 14px;
            font-weight: 700;
            color: #334155;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            margin: 0 0 3px;
        }

        .doc-academic-year {
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            margin: 0;
        }

        /* INFO META KELAS */
        .class-meta-table {
            width: 100%;
            margin-bottom: 16px;
            font-size: 12px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 10px 14px;
        }

        /* TABEL RESMI KBM */
        .table-academic {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #475569;
            font-size: 12px;
        }

        .table-academic th, 
        .table-academic td {
            border: 1px solid #64748B;
            padding: 8px 10px;
            vertical-align: middle;
        }

        .table-academic thead th {
            background-color: #1E293B;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 12px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 10px 8px;
        }

        .col-jam-ke {
            width: 100px;
            text-align: center;
            background-color: #F8FAFC;
            font-weight: 700;
            color: #1E293B;
        }

        .jam-ke-number {
            font-size: 13px;
            font-weight: 800;
            display: block;
        }

        /* BARIS ISTIRAHAT */
        .row-break td {
            background-color: #F1F5F9 !important;
            color: #334155;
            font-weight: 700;
            letter-spacing: 4px;
            font-size: 11.5px;
            text-align: center;
            padding: 7px 10px;
        }

        /* SEL KBM MATA PELAJARAN */
        .schedule-cell-content {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .cell-time-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            color: #64748B;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .cell-subject-name {
            font-weight: 700;
            font-size: 12.5px;
            color: #0F172A;
            line-height: 1.25;
        }

        .cell-teacher-name {
            font-size: 11px;
            color: #475569;
            font-weight: 500;
            margin-top: 1px;
        }

        .cell-room-tag {
            font-size: 10px;
            color: #1E40AF;
            font-weight: 600;
            margin-top: 2px;
        }

        .cell-special-activity {
            text-align: center;
            padding: 6px;
            background: #F8FAFC;
            border-radius: 4px;
        }

        .cell-special-title {
            font-weight: 700;
            color: #1E293B;
            font-size: 11.5px;
        }

        .cell-special-time {
            font-size: 10px;
            color: #64748B;
            font-family: 'JetBrains Mono', monospace;
            margin-top: 2px;
        }

        .cell-empty-sign {
            text-align: center;
            color: #94A3B8;
            font-size: 13px;
            display: block;
        }

        /* FOOTER TANDA TANGAN */
        .document-sign-row {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }

        .sign-box {
            text-align: center;
            width: 240px;
        }

        .sign-space {
            height: 60px;
        }

        .sign-title {
            font-size: 12px;
            color: #334155;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .sign-name {
            font-weight: 700;
            font-size: 12.5px;
            color: #0F172A;
            border-bottom: 1px solid #0F172A;
            display: inline-block;
            padding-bottom: 2px;
        }

        .sign-nip {
            font-size: 11px;
            color: #64748B;
            margin-top: 3px;
        }

        /* PRINT MEDIA STYLES */
        @media print {
            body {
                background: #FFFFFF !important;
                color: #000000 !important;
            }

            .main-sidebar,
            .filter-box,
            .page-header,
            .no-print {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .schedule-document-card {
                box-shadow: none !important;
                border: none !important;
                padding: 10px 0 35px !important;
                margin: 0 !important;
                max-width: 100% !important;
                page-break-after: always;
            }

            .schedule-document-card:last-child {
                page-break-after: avoid;
            }

            .table-academic {
                width: 100% !important;
                border: 1.5px solid #000000 !important;
            }

            .table-academic th, 
            .table-academic td {
                border: 1px solid #000000 !important;
            }

            .table-academic thead th {
                background-color: #1E293B !important;
                color: #FFFFFF !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .row-break td {
                background-color: #E2E8F0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    @include('layouts.sidebar', ['user' => $user])

    <!-- KONTEN UTAMA -->
    <main class="main-content">

        <!-- HEADER HALAMAN (Disembunyikan saat cetak) -->
        <header class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
            <div>
                <div class="d-flex align-items-center gap-2 text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                    <i class="bi bi-calendar3-week text-primary"></i>
                    <span>Jadwal Pelajaran KBM</span>
                </div>
                <h1 class="h4 fw-bold text-dark mb-1" style="font-size: 22px;">Cetak Jadwal Pelajaran</h1>
                <p class="text-muted small mb-0">
                    Rekap format tabel jadwal KBM per kelas yang sinkron langsung dengan data mengajar guru dan sesi jam sekolah.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('schedules.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i> Kembali ke Jadwal
                </a>
                <button type="button" onclick="downloadScheduleDocument()" class="btn btn-primary btn-sm fw-bold px-3.5 py-2 rounded-3 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-file-earmark-word-fill fs-6"></i> Unduh Dokumen Jadwal
                </button>
            </div>
        </header>

        <!-- PANEL FILTER (Disembunyikan saat cetak) -->
        <div class="filter-box no-print">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-2 mb-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-primary fs-5"></i>
                    <strong class="text-dark">Pengaturan Dokumen Jadwal</strong>
                </div>
                <span class="text-muted small">
                    Menampilkan <strong>{{ $targetClasses->count() }} kelas</strong> &bull; Putaran Blok Aktif: <strong>Putaran {{ $selectedRound }}</strong>
                </span>
            </div>

            <form action="{{ route('schedules.generate') }}" method="GET" class="row g-3">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-bold text-dark mb-1">Pilih Kelas</label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="all" @selected(($selectedClassId ?? 'all') === 'all')>
                            -- {{ $roleName === 'guru' ? 'Semua Kelas yang Saya Ajar' : 'Semua Kelas (Default)' }} --
                        </option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" @selected(($selectedClassId ?? '') == $c->id)>
                                {{ $c->name }} {{ $c->major ? '('.$c->major->name.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-lg-2">
                    <label class="form-label small fw-bold text-dark mb-1">Putaran Blok</label>
                    <select name="round" class="form-select form-select-sm">
                        <option value="1" @selected(($selectedRound ?? 1) === 1)>Putaran 1</option>
                        <option value="2" @selected(($selectedRound ?? 1) === 2)>Putaran 2</option>
                    </select>
                </div>

                <div class="col-md-5 col-lg-4">
                    <label class="form-label small fw-bold text-dark mb-1">Nama Lembaga / Sekolah</label>
                    <input type="text" name="school_name" class="form-control form-control-sm" value="{{ $schoolName ?? 'SMKN 1 Bantul' }}" placeholder="Contoh: SMKN 1 Bantul">
                </div>

                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-bold text-dark mb-1">Tahun Ajaran</label>
                    <input type="text" name="academic_year" class="form-control form-control-sm" value="{{ $academicYear ?? 'Tahun Ajaran 2025/2026' }}" placeholder="Contoh: Tahun Ajaran 2025/2026">
                </div>

                <div class="col-md-8 col-lg-7 d-flex align-items-center gap-3 pt-md-2">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="checkbox" name="show_teacher" id="show_teacher" value="1" @checked($showTeacher ?? true)>
                        <label class="form-check-label small fw-semibold" for="show_teacher">Tampilkan Nama Guru</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="checkbox" name="show_room" id="show_room" value="1" @checked($showRoom ?? true)>
                        <label class="form-check-label small fw-semibold" for="show_room">Tampilkan Ruang Belajar</label>
                    </div>
                </div>

                <div class="col-md-4 col-lg-5 d-flex align-items-center justify-content-md-end gap-2 pt-md-2">
                    <a href="{{ route('schedules.generate', ['class_id' => 'all']) }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
                        Reset Default
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3 py-1.5 rounded-2">
                        <i class="bi bi-arrow-repeat me-1"></i> Terapkan
                    </button>
                </div>
            </form>

            @if($targetClasses->count() > 1)
                <!-- LOMPAT KE KELAS -->
                <div class="jump-nav-bar">
                    <span class="small text-muted fw-bold me-1" style="font-size: 11px;">
                        Lompat ke Kelas:
                    </span>
                    @foreach($targetClasses as $tc)
                        <a href="#class-doc-{{ $tc->id }}" class="jump-link">
                            {{ $tc->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- DAFTAR LEMBAR DOKUMEN JADWAL KELAS -->
        @forelse($targetClasses as $targetClass)
            @php
                $gridData = $gridByClass[$targetClass->id] ?? null;
                $rows = $gridData['rows'] ?? [];
                $scheduleCount = $gridData['schedule_count'] ?? 0;
            @endphp

            <article class="schedule-document-card" id="class-doc-{{ $targetClass->id }}">
                
                <!-- KOP RESMI DOKUMEN -->
                <header class="document-header">
                    <h2 class="doc-school-title">{{ $schoolName ?? 'SMKN 1 Bantul' }}</h2>
                    <h3 class="doc-sub-title">Jadwal Pelajaran Kegiatan Belajar Mengajar (KBM)</h3>
                    <p class="doc-academic-year">{{ $academicYear ?? 'Tahun Ajaran 2025/2026' }} &bull; Putaran Blok {{ $selectedRound }}</p>
                </header>

                <!-- KOTAK DATA KELAS -->
                <div class="class-meta-table">
                    <div class="row g-2 align-items-center">
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block" style="font-size: 10.5px;">KELAS / TINGKAT:</span>
                            <strong class="text-dark" style="font-size: 13px;">{{ strtoupper($targetClass->name) }}</strong>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <span class="text-muted d-block" style="font-size: 10.5px;">PROGRAM KEAHLIAN / JURUSAN:</span>
                            <span class="text-dark fw-bold">{{ $targetClass->major?->name ?? 'Umum' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block" style="font-size: 10.5px;">WALI KELAS:</span>
                            <span class="text-dark fw-semibold">{{ $targetClass->homeroomTeacher?->name ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-2 text-md-end">
                            <span class="text-muted d-block" style="font-size: 10.5px;">TOTAL JAM:</span>
                            <strong class="text-primary">{{ $scheduleCount }} Jam Terjadwal</strong>
                        </div>
                    </div>
                </div>

                <!-- TABEL FORMAL JADWAL KBM -->
                <div class="table-responsive">
                    <table class="table-academic">
                        <thead>
                            <tr>
                                <th class="col-jam-ke">Jam Ke</th>
                                @foreach($days as $day)
                                    <th>
                                        <div>{{ $day }}</div>
                                        <div style="font-size: 9.5px; font-weight: 500; opacity: 0.8; text-transform: none; margin-top: 1px;">
                                            @if($day === 'Senin')
                                                07.00 - 15.10
                                            @elseif($day === 'Selasa')
                                                07.00 - 15.10
                                            @elseif($day === 'Jumat')
                                                07.15 - 13.50
                                            @else
                                                07.15 - 15.10
                                            @endif
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @if(!empty($row['is_break']))
                                    <!-- BARIS ISTIRAHAT -->
                                    <tr class="row-break">
                                        <td class="col-jam-ke">-</td>
                                        <td colspan="{{ count($days) }}">
                                            {{ $row['break_text'] }}
                                        </td>
                                    </tr>
                                @elseif($row['type'] === 'pembiasaan')
                                    <!-- BARIS PEMBIASAAN PAGI -->
                                    <tr style="background-color: #FAFAFA;">
                                        <td class="col-jam-ke">
                                            <span style="font-size: 11px; font-weight: 700; color: #475569;">Pagi</span>
                                        </td>
                                        @foreach($days as $day)
                                            @php
                                                $cell = $row['cells'][$day] ?? null;
                                            @endphp
                                            <td style="text-align: center; vertical-align: middle;">
                                                @if($cell && !empty($cell['subject']))
                                                    <div class="cell-special-activity">
                                                        <div class="cell-special-title">{{ $cell['subject'] }}</div>
                                                        <div class="cell-special-time">{{ $cell['time'] }}</div>
                                                    </div>
                                                @else
                                                    <span class="cell-empty-sign">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @else
                                    <!-- BARIS JAM KBM (JAM 1 S/D JAM 10) -->
                                    <tr>
                                        <td class="col-jam-ke">
                                            <span class="jam-ke-number">{{ $row['jam_ke'] }}</span>
                                        </td>

                                        @foreach($days as $day)
                                            @php
                                                $cell = $row['cells'][$day] ?? null;
                                            @endphp
                                            <td>
                                                @if($cell && $cell['type'] === 'schedule')
                                                    <div class="schedule-cell-content">
                                                        @if(!empty($cell['time']))
                                                            <div class="cell-time-badge">
                                                                <i class="bi bi-clock me-0.5"></i>{{ $cell['time'] }}
                                                            </div>
                                                        @endif

                                                        <div class="cell-subject-name">
                                                            {{ $cell['subject'] }}
                                                            @if(!empty($cell['subject_code']))
                                                                <span style="font-size: 10px; color: #64748B; font-weight: 600;">[{{ $cell['subject_code'] }}]</span>
                                                            @endif
                                                        </div>

                                                        @if($showTeacher && !empty($cell['teacher']))
                                                            <div class="cell-teacher-name">
                                                                {{ $cell['teacher'] }}
                                                            </div>
                                                        @endif

                                                        @if($showRoom && !empty($cell['room']))
                                                            <div class="cell-room-tag">
                                                                Ruang: {{ $cell['room'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @elseif($cell && $cell['type'] === 'ended')
                                                    <div class="text-center py-1">
                                                        <span style="font-size: 10px; color: #94A3B8; font-style: italic;">Selesai KBM</span>
                                                    </div>
                                                @else
                                                    <div class="text-center py-1">
                                                        @if(!empty($cell['time']) && $cell['time'] !== '-')
                                                            <div class="cell-time-badge" style="opacity: 0.6;">{{ $cell['time'] }}</div>
                                                        @endif
                                                        <span class="cell-empty-sign">-</span>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- PENGESAHAN DOKUMEN -->
                <footer class="document-sign-row">
                    <div class="sign-box">
                        <div class="sign-title">Mengetahui,</div>
                        <div class="sign-title">Wali Kelas</div>
                        <div class="sign-space"></div>
                        <div class="sign-name">{{ $targetClass->homeroomTeacher?->name ?? '( ............................................... )' }}</div>
                        <div class="sign-nip">NIP. {{ $targetClass->homeroomTeacher?->nip ?? '............................................' }}</div>
                    </div>

                    <div class="sign-box">
                        <div class="sign-title">Bantul, {{ now()->translatedFormat('d F Y') }}</div>
                        <div class="sign-title">Waka Bidang Kurikulum</div>
                        <div class="sign-space"></div>
                        <div class="sign-name">( ............................................... )</div>
                        <div class="sign-nip">NIP. ............................................</div>
                    </div>
                </footer>

            </article>
        @empty
            <div class="schedule-document-card text-center py-5 text-muted">
                <i class="bi bi-calendar-x fs-1 d-block text-secondary mb-2"></i>
                <h3 class="h5 fw-bold text-dark">Data Kelas Tidak Ditemukan</h3>
                <p class="small mb-0">Pastikan data kelas telah tersedia pada master data sekolah.</p>
            </div>
        @endforelse

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function downloadScheduleDocument() {
            const documentContent = document.documentElement.cloneNode(true);
            const controls = documentContent.querySelectorAll('.main-sidebar, .filter-box, .page-header, .no-print');

            controls.forEach((element) => element.remove());

            const documentCards = documentContent.querySelectorAll('.schedule-document-card');
            documentCards.forEach((card, index) => {
                card.style.pageBreakBefore = index === 0 ? 'auto' : 'always';
                card.style.pageBreakAfter = index === documentCards.length - 1 ? 'avoid' : 'always';
                card.style.breakInside = 'avoid';
                card.style.setProperty('mso-page-break-before', index === 0 ? 'auto' : 'always');
                card.style.setProperty('mso-page-break-after', index === documentCards.length - 1 ? 'avoid' : 'always');
            });

            const exportStyles = documentContent.querySelector('style');
            if (exportStyles) {
                exportStyles.textContent += `
                    @page { size: A4 landscape; margin: 10mm; }
                    html, body { width: 100%; min-height: 0; }
                    body, .main-content, .schedule-document-card, .class-meta-table,
                    .table-academic, .table-academic th, .table-academic td {
                        background: #ffffff !important;
                        color: #000000 !important;
                    }
                    .main-content {
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100% !important;
                    }
                    .schedule-document-card {
                        box-sizing: border-box;
                        box-shadow: none !important;
                        border: none !important;
                        width: 100% !important;
                        max-width: none !important;
                        min-height: 0 !important;
                        padding: 0 0 8mm !important;
                        margin: 0 !important;
                        page-break-after: always;
                        page-break-inside: avoid;
                        break-after: page;
                        break-inside: avoid;
                        mso-page-break-after: always;
                    }
                    .schedule-document-card:last-child {
                        page-break-after: avoid;
                        break-after: avoid;
                        mso-page-break-after: avoid;
                    }
                    .document-header {
                        margin-bottom: 8px !important;
                        padding-bottom: 8px !important;
                        border-bottom: 1px solid #000000 !important;
                    }
                    .table-responsive { overflow: visible !important; }
                    .table-academic {
                        table-layout: fixed !important;
                        font-size: 9px !important;
                        border: 1px solid #000000 !important;
                        border-collapse: collapse !important;
                        border-spacing: 0 !important;
                    }
                    .table-academic th,
                    .table-academic td {
                        padding: 4px 5px !important;
                        border: 1px solid #000000 !important;
                        border-radius: 0 !important;
                    }
                    .table-academic thead th {
                        font-size: 9px !important;
                        padding: 5px 4px !important;
                        background: #ffffff !important;
                        color: #000000 !important;
                    }
                    .class-meta-table {
                        border: 1px solid #000000 !important;
                        border-radius: 0 !important;
                    }
                    .row-break td,
                    .cell-special-activity,
                    .col-jam-ke {
                        background: #ffffff !important;
                        color: #000000 !important;
                    }
                    .doc-school-title { font-size: 15px !important; color: #000000 !important; }
                    .doc-sub-title { font-size: 11px !important; color: #000000 !important; }
                    .doc-academic-year { font-size: 10px !important; color: #000000 !important; }
                    .document-sign-row {
                        margin-top: 14px !important;
                        padding: 0 10px !important;
                    }
                    .sign-space { height: 35px !important; }
                    .sign-box { width: 200px !important; }
                    .sign-title, .sign-name { font-size: 10px !important; color: #000000 !important; }
                    .sign-name { border-bottom: 1px solid #000000 !important; }
                    .sign-nip { font-size: 9px !important; color: #000000 !important; }
                    .cell-subject-name { font-size: 10px !important; color: #000000 !important; }
                    .cell-teacher-name, .cell-room-tag, .cell-time-badge {
                        font-size: 8px !important;
                        color: #000000 !important;
                    }
                `;
            }

            const filename = `jadwal-pelajaran-putaran-{{ $selectedRound }}.doc`;
            const blob = new Blob([
                '<!DOCTYPE html>\n' + documentContent.outerHTML,
            ], { type: 'application/msword;charset=utf-8' });
            const downloadLink = document.createElement('a');

            downloadLink.href = URL.createObjectURL(blob);
            downloadLink.download = filename;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            downloadLink.remove();
            URL.revokeObjectURL(downloadLink.href);
        }
    </script>
</body>
</html>
