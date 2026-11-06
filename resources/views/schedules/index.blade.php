<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran - SCP System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <!-- SIDEBAR -->
    @include('layouts.sidebar', ['user' => $user])

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <div class="content-wrapper">
            
            <header class="page-header">
                <div>
                    <div class="page-eyebrow">
                        <i class="bi bi-calendar-week-fill"></i>
                        Kurikulum & Akademik
                    </div>
                    <h1 class="page-title">Jadwal Kegiatan Belajar Mengajar</h1>
                </div>

                <div class="academic-badge">
                    <i class="bi bi-calendar3"></i>
                    <span>Tahun Ajaran 2026/2027</span>
                </div>
            </header>

            <!-- Hero Banner -->
            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-calendar3-range-fill"></i>
                            JADWAL KBM SEKOLAH
                        </div>

                        <h2 class="hero-title">
                            Jadwal Pelajaran & Siklus Putaran Blok
                        </h2>

                        <p class="hero-description">
                            Pantau dan kelola jadwal mengajar seluruh tingkatan kelas, pencegahan bentrok otomatis ruang dan guru, serta kendali penguncian jadwal terpusat oleh Waka Kurikulum.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-calendar2-week-fill"></i>
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

            @if($user->role->name === 'guru' && !($scheduleLock->is_locked ?? false))
                <section class="content-card mb-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="section-icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div>
                                <h2 class="section-title mb-1">{{ $user->role->name === 'guru' ? 'Susun Jadwal Mengajar' : 'Input Jadwal Mengajar' }}</h2>
                                <p class="section-description mb-0">
                                    Masukkan jadwal mengajar Anda. Sistem otomatis mencegah bentrok pada guru, kelas, dan ruangan yang sama.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('schedules.store') }}" method="POST" data-schedule-form>
                        @csrf
                        @php
                            $oldSchedules = old('schedules', [[]]);
                        @endphp
                        <div class="d-flex flex-column gap-3" data-schedule-rows>
                            @foreach($oldSchedules as $index => $oldSchedule)
                                <div class="rounded-3 border bg-light p-3" data-schedule-row>
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                        <span class="fw-bold text-primary" style="font-size: 12px;">
                                            <i class="bi bi-list-Check me-1"></i>Baris Jadwal #<span data-row-number>{{ $loop->iteration }}</span>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-2 py-1" style="font-size: 11px; border-radius: 6px;" data-remove-schedule-row @if($loop->first) hidden @endif>
                                            <i class="bi bi-trash me-1"></i>Hapus Baris #<span data-remove-row-number>{{ $loop->iteration }}</span>
                                        </button>
                                    </div>
                                    <div class="row g-3">
                                        @if($user->role->name === 'waka')
                                            <div class="col-md-6 col-xl-3">
                                                <label class="form-label">Guru Pengampu</label>
                                                <select name="schedules[{{ $index }}][teacher_id]" class="form-select" required>
                                                    <option value="">-- Pilih Guru --</option>
                                                    @foreach($teachers as $teacher)
                                                        <option value="{{ $teacher->id }}" @selected(($oldSchedule['teacher_id'] ?? '') == $teacher->id)>{{ $teacher->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                        <div class="col-md-6 col-xl-3">
                                            <label class="form-label">Kelas / Jurusan</label>
                                            <select name="schedules[{{ $index }}][class_id]" class="form-select" required>
                                                <option value="">-- Pilih Kelas --</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" @selected(($oldSchedule['class_id'] ?? '') == $class->id)>{{ $class->name }} ({{ $class->major?->name ?? '-' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-xl-3">
                                            <label class="form-label">Mata Pelajaran</label>
                                            <select name="schedules[{{ $index }}][subject_id]" class="form-select" required>
                                                <option value="">-- Pilih Mata Pelajaran --</option>
                                                @foreach($subjects as $subject)
                                                    <option value="{{ $subject->id }}" @selected(($oldSchedule['subject_id'] ?? '') == $subject->id)>{{ $subject->name }} ({{ $subject->code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-xl-3">
                                            <label class="form-label">Hari</label>
                                            <select name="schedules[{{ $index }}][day]" class="form-select" required>
                                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                                                    <option value="{{ $day }}" @selected(($oldSchedule['day'] ?? 'Senin') === $day)>{{ $day }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <label class="form-label">Mulai Sesi (Jam Ke)</label>
                                            <select name="schedules[{{ $index }}][start_time]" class="form-select" data-session-start required>
                                                <option value="">-- Pilih Sesi Mulai --</option>
                                                @php
                                                    $currentDay = $oldSchedule['day'] ?? 'Senin';
                                                    $daySessions = $sessionsByDay[$currentDay] ?? ($sessionsByDay['Senin'] ?? []);
                                                    $selectedStart = substr((string) ($oldSchedule['start_time'] ?? ''), 0, 5);
                                                    $hasMatchedStart = false;
                                                @endphp
                                                @foreach($daySessions as $sess)
                                                    @php
                                                        $isSel = ($selectedStart === $sess['jam_mulai']);
                                                        if ($isSel) { $hasMatchedStart = true; }
                                                    @endphp
                                                    <option value="{{ $sess['jam_mulai'] }}" data-nama="{{ $sess['nama_sesi'] }}" data-order="{{ $sess['urutan'] }}" data-end="{{ $sess['jam_selesai'] }}" @selected($isSel)>
                                                        {{ $sess['nama_sesi'] }} (Mulai {{ $sess['jam_mulai'] }})
                                                    </option>
                                                @endforeach
                                                @if($selectedStart && !$hasMatchedStart)
                                                    <option value="{{ $selectedStart }}" selected>{{ $selectedStart }} (Kustom)</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <label class="form-label">Sampai Sesi (Jam Ke)</label>
                                            <select name="schedules[{{ $index }}][end_time]" class="form-select" data-session-end required>
                                                <option value="">-- Pilih Sesi Selesai --</option>
                                                @php
                                                    $selectedEnd = substr((string) ($oldSchedule['end_time'] ?? ''), 0, 5);
                                                    $hasMatchedEnd = false;
                                                @endphp
                                                @foreach($daySessions as $sess)
                                                    @php
                                                        $isSel = ($selectedEnd === $sess['jam_selesai']);
                                                        if ($isSel) { $hasMatchedEnd = true; }
                                                    @endphp
                                                    <option value="{{ $sess['jam_selesai'] }}" data-nama="{{ $sess['nama_sesi'] }}" data-order="{{ $sess['urutan'] }}" data-start="{{ $sess['jam_mulai'] }}" @selected($isSel)>
                                                        {{ $sess['nama_sesi'] }} (Selesai {{ $sess['jam_selesai'] }})
                                                    </option>
                                                @endforeach
                                                @if($selectedEnd && !$hasMatchedEnd)
                                                    <option value="{{ $selectedEnd }}" selected>{{ $selectedEnd }} (Kustom)</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <label class="form-label">Putaran</label>
                                            <select name="schedules[{{ $index }}][round]" class="form-select" required>
                                                <option value="1" @selected(($oldSchedule['round'] ?? (string) $activeRound) == '1')>Putaran 1</option>
                                                <option value="2" @selected(($oldSchedule['round'] ?? (string) $activeRound) == '2')>Putaran 2</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <label class="form-label">Ruangan</label>
                                            <select name="schedules[{{ $index }}][room_id]" class="form-select" required>
                                                <option value="">-- Pilih Ruangan --</option>
                                                @foreach($rooms as $room)
                                                    <option value="{{ $room->id }}" @selected(($oldSchedule['room_id'] ?? '') == $room->id)>{{ $room->name }}{{ $room->rules ? ' - ' . $room->rules : '' }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted" data-room-hint style="font-size: 10px;">Pilihan ruangan menyesuaikan jadwal lain di hari dan jam yang sama.</small>
                                        </div>
                                        <div class="col-12 mt-1">
                                            <div class="d-flex align-items-center gap-2 p-2 rounded-2 bg-white border" style="font-size: 11px;">
                                                <i class="bi bi-clock-history text-primary"></i>
                                                <span class="text-muted">Rentang Jam Sesi:</span>
                                                <span class="fw-bold text-primary" data-session-preview>-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <button type="button" class="btn-outline-primary-custom" data-add-schedule-row>
                                <i class="bi bi-plus-lg"></i>
                                <span>Tambah Baris</span>
                            </button>
                            <button type="submit" class="btn-primary-custom">
                                <i class="bi bi-send-fill"></i>
                                <span>{{ $user->role->name === 'guru' ? 'Kirim Semua Jadwal ke WAKA' : 'Simpan Semua Jadwal' }}</span>
                            </button>
                        </div>
                    </form>
                </section>

                <template data-schedule-row-template>
                    <div class="rounded-3 border bg-light p-3" data-schedule-row>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <span class="fw-bold text-primary" style="font-size: 12px;">
                                <i class="bi bi-list-check me-1"></i>Baris Jadwal #<span data-row-number></span>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-2 py-1" style="font-size: 11px; border-radius: 6px;" data-remove-schedule-row>
                                <i class="bi bi-trash me-1"></i>Hapus Baris #<span data-remove-row-number></span>
                            </button>
                        </div>
                        <div class="row g-3">
                            @if($user->role->name === 'waka')
                                <div class="col-md-6 col-xl-3">
                                    <label class="form-label">Guru Pengampu</label>
                                    <select name="schedules[__INDEX__][teacher_id]" class="form-select" required>
                                        <option value="">-- Pilih Guru --</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label">Kelas / Jurusan</label>
                                <select name="schedules[__INDEX__][class_id]" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->major?->name ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label">Mata Pelajaran</label>
                                <select name="schedules[__INDEX__][subject_id]" class="form-select" required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label">Hari</label>
                                <select name="schedules[__INDEX__][day]" class="form-select" required>
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label class="form-label">Mulai Sesi (Jam Ke)</label>
                                <select name="schedules[__INDEX__][start_time]" class="form-select" data-session-start required>
                                    <option value="">-- Pilih Sesi Mulai --</option>
                                    @php
                                        $seninSessions = $sessionsByDay['Senin'] ?? [];
                                    @endphp
                                    @foreach($seninSessions as $sess)
                                        <option value="{{ $sess['jam_mulai'] }}" data-nama="{{ $sess['nama_sesi'] }}" data-order="{{ $sess['urutan'] }}" data-end="{{ $sess['jam_selesai'] }}">
                                            {{ $sess['nama_sesi'] }} (Mulai {{ $sess['jam_mulai'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label class="form-label">Sampai Sesi (Jam Ke)</label>
                                <select name="schedules[__INDEX__][end_time]" class="form-select" data-session-end required>
                                    <option value="">-- Pilih Sesi Selesai --</option>
                                    @foreach($seninSessions as $sess)
                                        <option value="{{ $sess['jam_selesai'] }}" data-nama="{{ $sess['nama_sesi'] }}" data-order="{{ $sess['urutan'] }}" data-start="{{ $sess['jam_mulai'] }}">
                                            {{ $sess['nama_sesi'] }} (Selesai {{ $sess['jam_selesai'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label class="form-label">Putaran</label>
                                <select name="schedules[__INDEX__][round]" class="form-select" required>
                                    <option value="1" @selected($activeRound === 1)>Putaran 1</option>
                                    <option value="2" @selected($activeRound === 2)>Putaran 2</option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label class="form-label">Ruangan</label>
                                <select name="schedules[__INDEX__][room_id]" class="form-select" required>
                                    <option value="">-- Pilih Ruangan --</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}{{ $room->rules ? ' - ' . $room->rules : '' }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted" data-room-hint style="font-size: 10px;">Pilihan ruangan menyesuaikan jadwal lain di hari dan jam yang sama.</small>
                            </div>
                            <div class="col-12 mt-1">
                                <div class="d-flex align-items-center gap-2 p-2 rounded-2 bg-white border" style="font-size: 11px;">
                                    <i class="bi bi-clock-history text-primary"></i>
                                    <span class="text-muted">Rentang Jam Sesi:</span>
                                    <span class="fw-bold text-primary" data-session-preview>-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <script type="application/json" data-schedule-occupancy>@json($reservedSchedules)</script>
                <script type="application/json" data-schedule-sessions>@json($sessionsByDay)</script>
            @endif

            <div class="content-card">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon">
                            <i class="bi bi-table"></i>
                        </div>
                        <div>
                            <h2 class="section-title mb-1">Daftar Jadwal Pelajaran Sekolah</h2>
                            <p class="section-description mb-0">Tampilan seluruh rombel sesuai putaran blok aktif saat ini.</p>
                        </div>
                    </div>
                    @if($user->role->name === 'waka')
                        <div class="d-flex flex-wrap gap-2 align-items-end">
                            <form action="{{ route('schedules.update-active-round') }}" method="POST" class="d-flex flex-wrap align-items-end gap-2 mb-0">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label for="active-round" class="form-label mb-1 small text-muted">Putaran aktif semua pengguna</label>
                                    <select id="active-round" name="active_round" class="form-select form-select-sm" required>
                                        <option value="1" @selected($activeRound === 1)>Putaran 1</option>
                                        <option value="2" @selected($activeRound === 2)>Putaran 2</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn-outline-primary-custom btn-sm">Terapkan Global</button>
                            </form>
                            <form action="{{ route('schedules.toggle-lock') }}" method="POST" class="mb-0">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $scheduleLock?->is_locked ? 'btn-danger' : 'btn-success' }} fw-bold px-3 py-1.5 rounded-3">
                                    {{ $scheduleLock?->is_locked ? '🔓 Buka Kunci Jadwal' : '🔒 Kunci Jadwal' }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="alert alert-info py-2.5 px-3 mb-3 small d-flex align-items-center gap-2 border-0 rounded-3" style="background: #eff6ff; color: #174ea6;">
                    <i class="bi bi-info-circle-fill fs-6"></i>
                    <span><strong>Putaran {{ $activeRound }}</strong> sedang aktif secara global. Jadwal KBM di bawah menampilkan kelas pada putaran ini.</span>
                </div>

                @if($scheduleLock?->is_locked ?? false)
                    <div class="alert alert-warning py-2.5 px-3 mb-3 small d-flex align-items-center gap-2 border-0 rounded-3" style="background: #fffbeb; color: #b45309;">
                        <i class="bi bi-lock-fill fs-6"></i>
                        <span>Jadwal dikunci oleh Waka Kurikulum. Guru pengajar tidak dapat mengubah atau menambah jadwal KBM baru.</span>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Sesi & Jam KBM</th>
                                <th>Putaran</th>
                                <th>Kelas / Jurusan</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th>Ruangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $sched)
                                <tr>
                                    <td><span class="fw-bold text-dark">{{ $sched->day }}</span></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace">
                                            <i class="bi bi-clock-history me-1"></i>{{ $sched->session_label }}
                                        </span>
                                    </td>
                                    <td><span class="scp-badge scp-badge-info">Putaran {{ $sched->round }}</span></td>
                                    <td>{{ $sched->class?->name ?? '-' }}
                                        @if($sched->class?->major?->name)
                                            <small class="text-muted d-block" style="font-size: 10px;">{{ $sched->class->major->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $sched->subject?->name ?? '-' }}</span>
                                        @if($sched->subject?->code)
                                            <span class="badge bg-light text-secondary border font-monospace ms-1">{{ $sched->subject->code }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold d-block">{{ $sched->teacher?->name ?? '-' }}</span>
                                        @if($sched->teacher?->nip)
                                            <small class="text-muted font-monospace" style="font-size: 10px;">NIP: {{ $sched->teacher->nip }}</small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><i class="bi bi-door-closed me-1"></i>{{ $sched->room?->name ?? '-' }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">Belum ada data jadwal untuk putaran aktif ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const form = document.querySelector('[data-schedule-form]');
            if (!form) return;

            const sessionsNode = document.querySelector('[data-schedule-sessions]');
            const sessionsByDay = sessionsNode ? JSON.parse(sessionsNode.textContent) : {};

            function updateRowSessionConstraints(row) {
                const startSelect = row.querySelector('[data-session-start]');
                const endSelect = row.querySelector('[data-session-end]');
                const previewEl = row.querySelector('[data-session-preview]');
                if (!startSelect || !endSelect) return;

                const startOpt = startSelect.selectedOptions[0];
                const startOrder = parseInt(startOpt?.dataset.order || '0', 10);

                if (startOrder > 0) {
                    Array.from(endSelect.options).forEach(opt => {
                        if (!opt.value) return;
                        const endOrder = parseInt(opt.dataset.order || '0', 10);
                        const isInvalid = endOrder < startOrder;
                        opt.disabled = isInvalid;
                        opt.hidden = isInvalid;
                    });

                    const currentEndOpt = endSelect.selectedOptions[0];
                    const currentEndOrder = parseInt(currentEndOpt?.dataset.order || '0', 10);

                    if (!endSelect.value || currentEndOrder < startOrder) {
                        const sameSessOpt = Array.from(endSelect.options).find(
                            opt => parseInt(opt.dataset.order || '0', 10) === startOrder
                        );
                        if (sameSessOpt) {
                            endSelect.value = sameSessOpt.value;
                        }
                    }
                }

                if (previewEl) {
                    if (startSelect.value && endSelect.value) {
                        const sOpt = startSelect.selectedOptions[0];
                        const eOpt = endSelect.selectedOptions[0];
                        const sName = sOpt?.dataset.nama || startSelect.value;
                        const eName = eOpt?.dataset.nama || endSelect.value;

                        if (sName === eName) {
                            previewEl.textContent = `${sName} (${startSelect.value} - ${endSelect.value})`;
                        } else {
                            previewEl.textContent = `${sName} s/d ${eName} (${startSelect.value} - ${endSelect.value})`;
                        }
                    } else {
                        previewEl.textContent = 'Silakan pilih sesi mulai dan selesai.';
                    }
                }
            }

            function populateRowSessionsForDay(row) {
                const daySelect = row.querySelector('select[name$="[day]"]');
                const startSelect = row.querySelector('[data-session-start]');
                const endSelect = row.querySelector('[data-session-end]');
                if (!daySelect || !startSelect || !endSelect) return;

                const day = daySelect.value || 'Senin';
                const sessions = sessionsByDay[day] || sessionsByDay['Senin'] || [];

                const currentStart = startSelect.value;
                const currentEnd = endSelect.value;

                startSelect.innerHTML = '<option value="">-- Pilih Sesi Mulai --</option>';
                sessions.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.jam_mulai;
                    opt.dataset.nama = s.nama_sesi;
                    opt.dataset.order = String(s.urutan);
                    opt.dataset.end = s.jam_selesai;
                    opt.textContent = `${s.nama_sesi} (Mulai ${s.jam_mulai})`;
                    startSelect.appendChild(opt);
                });

                endSelect.innerHTML = '<option value="">-- Pilih Sesi Selesai --</option>';
                sessions.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.jam_selesai;
                    opt.dataset.nama = s.nama_sesi;
                    opt.dataset.order = String(s.urutan);
                    opt.dataset.start = s.jam_mulai;
                    opt.textContent = `${s.nama_sesi} (Selesai ${s.jam_selesai})`;
                    endSelect.appendChild(opt);
                });

                if (currentStart && Array.from(startSelect.options).some(o => o.value === currentStart)) {
                    startSelect.value = currentStart;
                }
                if (currentEnd && Array.from(endSelect.options).some(o => o.value === currentEnd)) {
                    endSelect.value = currentEnd;
                }

                updateRowSessionConstraints(row);
            }

            form.addEventListener('change', function(e) {
                const row = e.target.closest('[data-schedule-row]');
                if (!row) return;

                if (e.target.matches('select[name$="[day]"]')) {
                    populateRowSessionsForDay(row);
                } else if (e.target.matches('[data-session-start], [data-session-end]')) {
                    updateRowSessionConstraints(row);
                }
            });

            document.querySelectorAll('[data-schedule-row]').forEach(row => {
                updateRowSessionConstraints(row);
            });
        })();
    </script>
</body>
</html>
