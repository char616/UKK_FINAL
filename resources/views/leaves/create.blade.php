<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan Izin - SCP System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    @include('layouts.sidebar', ['user' => $user ?? auth()->user()])

    <main class="main-content">
        <div class="content-wrapper" style="max-width: 900px;">

            <header class="page-header">
                <div>
                    <div class="page-eyebrow">
                        <i class="bi bi-envelope-plus-fill"></i>
                        Permohonan Siswa
                    </div>
                    <h1 class="page-title">Formulir Pengajuan Izin / Sakit</h1>
                </div>

                <div>
                    <a href="{{ route('leaves.index') }}" class="btn-outline-primary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali ke Riwayat</span>
                    </a>
                </div>
            </header>

            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-file-earmark-medical"></i>
                            LAYANAN PERIZINAN SISWA
                        </div>

                        <h2 class="hero-title">
                            Ajukan Permohonan Izin Belajar
                        </h2>

                        <p class="hero-description">
                            Lengkapi jenis permohonan (seharian atau per jam pelajaran), serta lampirkan surat izin orang tua atau surat keterangan dokter sebagai dokumen pendukung verifikasi resmi.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-send-check-fill"></i>
                    </div>
                </div>
            </section>

            @if($errors->any())
                <div class="alert-scp-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="content-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-pencil-fill"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Detail Pengajuan Izin</h3>
                        <p class="section-description">Isikan informasi izin dengan benar dan jelas.</p>
                    </div>
                </div>

                <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" data-leave-upload-form>
                    @csrf

                    <div class="row g-3 mb-3">
                        @if(($user ?? auth()->user())->role->name === 'orang_tua')
                            <div class="col-md-12">
                                <label class="form-label">Pilih Anak / Siswa <span class="text-danger">*</span></label>
                                @if(isset($students) && $students->isNotEmpty())
                                    <select name="student_id" class="form-select" required>
                                        @if($students->count() > 1)
                                            <option value="">-- Pilih Anak / Siswa --</option>
                                        @endif
                                        @foreach($students as $child)
                                            <option value="{{ $child->id }}" {{ (string) old('student_id', $students->count() === 1 ? $child->id : '') === (string) $child->id ? 'selected' : '' }}>
                                                {{ $child->name }} ({{ $child->schoolClass->name ?? 'Belum ada kelas' }})
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="alert-scp-warning mb-0">
                                        <i class="bi bi-exclamation-circle-fill fs-5"></i>
                                        <span>Akun orang tua Anda belum terhubung ke data siswa. Silakan hubungi WAKA Kurikulum / pihak sekolah.</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label">Kategori Permohonan <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="Izin" {{ old('type') === 'Izin' ? 'selected' : '' }}>Izin (Keperluan Tertentu / Keluarga)</option>
                                <option value="Sakit" {{ old('type') === 'Sakit' ? 'selected' : '' }}>Sakit (Disertai Surat Keterangan / Istirahat)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cakupan Waktu Izin <span class="text-danger">*</span></label>
                            <select name="jenis_waktu_izin" id="jenis_waktu_izin" class="form-select" required>
                                <option value="seharian" {{ old('jenis_waktu_izin', 'seharian') === 'seharian' ? 'selected' : '' }}>Izin Penuh Seharian (Persetujuan: Wali Kelas)</option>
                                <option value="jam_pelajaran" {{ old('jenis_waktu_izin') === 'jam_pelajaran' ? 'selected' : '' }}>Izin Saat Jam Pelajaran Berlangsung (Persetujuan: Guru BK)</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="container-start-date">
                            <label class="form-label" id="label-start-date">Tanggal Izin <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-6" id="container-end-date">
                            <label class="form-label">Sampai Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', date('Y-m-d')) }}" required>
                        </div>

                        <!-- PILIHAN JAM PELAJARAN (JIKA MEMILIH JAM_PELAJARAN) -->
                        <div id="section-jam-pelajaran" class="col-md-12 p-3 bg-light border rounded-3" style="display: {{ old('jenis_waktu_izin') === 'jam_pelajaran' ? 'block' : 'none' }};">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-clock-history text-primary fs-6"></i>
                                <strong class="small text-dark">Tentukan Rentang Sesi Jam Pelajaran yang Diajukan Izin</strong>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Dari Sesi (Jam Ke-X) <span class="text-danger">*</span></label>
                                    <select name="jam_mulai_id" id="jam_mulai_id" class="form-select form-select-sm">
                                        <option value="">-- Pilih Jam Mulai --</option>
                                        @foreach($jamPelajarans ?? [] as $jp)
                                            <option value="{{ $jp->id }}" data-hari="{{ $jp->hari }}" data-order="{{ $jp->urutan }}" data-nama="{{ $jp->nama_sesi }}" data-jam="{{ substr($jp->jam_mulai, 0, 5) }}" {{ (string) old('jam_mulai_id') === (string) $jp->id ? 'selected' : '' }}>
                                                {{ $jp->nama_sesi }} (Mulai {{ substr($jp->jam_mulai, 0, 5) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Sampai Sesi (Jam Ke-Y) <span class="text-danger">*</span></label>
                                    <select name="jam_selesai_id" id="jam_selesai_id" class="form-select form-select-sm">
                                        <option value="">-- Pilih Jam Selesai --</option>
                                        @foreach($jamPelajarans ?? [] as $jp)
                                            <option value="{{ $jp->id }}" data-hari="{{ $jp->hari }}" data-order="{{ $jp->urutan }}" data-nama="{{ $jp->nama_sesi }}" data-jam="{{ substr($jp->jam_selesai, 0, 5) }}" {{ (string) old('jam_selesai_id') === (string) $jp->id ? 'selected' : '' }}>
                                                {{ $jp->nama_sesi }} (Selesai {{ substr($jp->jam_selesai, 0, 5) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                <span class="small text-primary fw-semibold" id="jam-range-preview">
                                    <i class="bi bi-info-circle me-1"></i>Pilih sesi mulai dan selesai sesuai jam KBM hari tersebut.
                                </span>
                                <span class="badge bg-purple-subtle text-purple border px-2 py-1" style="background:#F3E8FF;color:#7E22CE;border-color:#E9D5FF;font-size:10px;">
                                    <i class="bi bi-shield-check me-1"></i>Verifikasi: Guru BK
                                </span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Alasan Ketidakhadiran <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Tuliskan keterangan dan alasan permohonan izin secara lengkap..." required>{{ old('reason') }}</textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Dokumen Bukti Pendukung (Foto Surat Dokter / Bukti Izin) <span class="text-danger">*</span></label>
                            <input type="file" name="attachment" id="attachment-input" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required data-leave-file-input>
                            <input type="hidden" name="attachment_data" data-leave-file-data>
                            <input type="hidden" name="attachment_name" data-leave-file-name>
                            <div class="form-text text-muted small mt-1" data-leave-file-status>Wajib melampirkan foto surat dokter atau surat izin dari orang tua. Format: JPG, PNG, PDF (Maks. 5MB).</div>
                            
                            <div id="leave-preview-container" class="mt-2 text-center p-2 border rounded bg-white shadow-sm" style="display:none;">
                                <div class="small text-muted mb-1 fw-bold"><i class="bi bi-image me-1"></i>Pratinjau Dokumen Bukti:</div>
                                <img id="leave-preview-img" src="" class="img-fluid rounded border" style="max-height: 220px; object-fit: contain;">
                            </div>
                        </div>
                    </div>

                    <!-- TARGET APPROVER INFO -->
                    <div id="target-approver-hint" class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 border-0 rounded-3 mb-0" style="background:#eff6ff;color:#174ea6;">
                        <i class="bi bi-shield-check fs-6"></i>
                        <span id="target-approver-text">Target Verifikator: <strong>Wali Kelas</strong> (Izin Seharian)</span>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('leaves.index') }}" class="btn-outline-primary-custom">
                            <i class="bi bi-x-circle"></i>
                            <span>Batal</span>
                        </a>

                        <button type="submit" class="btn-primary-custom px-4 shadow-sm" data-leave-submit-btn>
                            <i class="bi bi-send-fill"></i>
                            <span>Kirim Pengajuan Izin</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <script type="application/json" id="sessions-by-day-data">@json($sessionsByDay ?? [])</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const jenisWaktuSelect = document.getElementById('jenis_waktu_izin');
        const sectionJamPelajaran = document.getElementById('section-jam-pelajaran');
        const targetApproverText = document.getElementById('target-approver-text');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const containerEndDate = document.getElementById('container-end-date');
        const labelStartDate = document.getElementById('label-start-date');
        const jamMulaiSelect = document.getElementById('jam_mulai_id');
        const jamSelesaiSelect = document.getElementById('jam_selesai_id');
        const jamRangePreview = document.getElementById('jam-range-preview');

        const rawSessionsData = document.getElementById('sessions-by-day-data')?.textContent;
        const sessionsByDay = rawSessionsData ? JSON.parse(rawSessionsData) : {};

        function getIndonesianDayName(dateString) {
            if (!dateString) return 'Senin';
            const date = new Date(dateString + 'T00:00:00');
            const dayIdx = date.getDay();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const name = days[dayIdx] || 'Senin';
            return (name === 'Minggu' || name === 'Sabtu') ? 'Senin' : name;
        }

        function populateSessionsForDate() {
            const dayName = getIndonesianDayName(startDateInput.value);
            const sessions = sessionsByDay[dayName] || sessionsByDay['Senin'] || [];

            const currentMulai = jamMulaiSelect.value;
            const currentSelesai = jamSelesaiSelect.value;

            jamMulaiSelect.innerHTML = '<option value="">-- Pilih Jam Mulai --</option>';
            sessions.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.dataset.nama = s.nama_sesi;
                opt.dataset.order = String(s.urutan);
                opt.dataset.jam = s.jam_mulai;
                opt.dataset.jamEnd = s.jam_selesai;
                opt.textContent = `${s.nama_sesi} (Mulai ${s.jam_mulai})`;
                jamMulaiSelect.appendChild(opt);
            });

            jamSelesaiSelect.innerHTML = '<option value="">-- Pilih Jam Selesai --</option>';
            sessions.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.dataset.nama = s.nama_sesi;
                opt.dataset.order = String(s.urutan);
                opt.dataset.jam = s.jam_selesai;
                opt.dataset.jamStart = s.jam_mulai;
                opt.textContent = `${s.nama_sesi} (Selesai ${s.jam_selesai})`;
                jamSelesaiSelect.appendChild(opt);
            });

            if (currentMulai && Array.from(jamMulaiSelect.options).some(o => o.value == currentMulai)) {
                jamMulaiSelect.value = currentMulai;
            }
            if (currentSelesai && Array.from(jamSelesaiSelect.options).some(o => o.value == currentSelesai)) {
                jamSelesaiSelect.value = currentSelesai;
            }

            updateJamConstraints();
        }

        function updateJamConstraints() {
            const startOpt = jamMulaiSelect.selectedOptions[0];
            const startOrder = parseInt(startOpt?.dataset.order || '0', 10);

            if (startOrder > 0) {
                Array.from(jamSelesaiSelect.options).forEach(opt => {
                    if (!opt.value) return;
                    const endOrder = parseInt(opt.dataset.order || '0', 10);
                    const isInvalid = endOrder < startOrder;
                    opt.disabled = isInvalid;
                    opt.hidden = isInvalid;
                });

                const currentEndOpt = jamSelesaiSelect.selectedOptions[0];
                const currentEndOrder = parseInt(currentEndOpt?.dataset.order || '0', 10);

                if (!jamSelesaiSelect.value || currentEndOrder < startOrder) {
                    const matchOpt = Array.from(jamSelesaiSelect.options).find(o => parseInt(o.dataset.order || '0', 10) === startOrder);
                    if (matchOpt) {
                        jamSelesaiSelect.value = matchOpt.value;
                    }
                }
            }

            if (jamMulaiSelect.value && jamSelesaiSelect.value) {
                const sOpt = jamMulaiSelect.selectedOptions[0];
                const eOpt = jamSelesaiSelect.selectedOptions[0];
                const sName = sOpt?.dataset.nama || 'Jam Mulai';
                const eName = eOpt?.dataset.nama || 'Jam Selesai';
                const sTime = sOpt?.dataset.jam || '';
                const eTime = eOpt?.dataset.jam || '';

                if (sName === eName) {
                    jamRangePreview.innerHTML = `<i class="bi bi-clock-fill me-1"></i>Rentang Sesi: <strong>${sName}</strong> (${sTime} - ${eTime})`;
                } else {
                    jamRangePreview.innerHTML = `<i class="bi bi-clock-fill me-1"></i>Rentang Sesi: <strong>${sName} s/d ${eName}</strong> (${sTime} - ${eTime})`;
                }
            } else {
                jamRangePreview.innerHTML = '<i class="bi bi-info-circle me-1"></i>Pilih sesi jam pelajaran yang sesuai.';
            }
        }

        function toggleWaktuIzin() {
            const isJamPelajaran = (jenisWaktuSelect.value === 'jam_pelajaran');
            if (isJamPelajaran) {
                sectionJamPelajaran.style.display = 'block';
                containerEndDate.style.display = 'none';
                labelStartDate.innerHTML = 'Tanggal Izin <span class="text-danger">*</span>';
                endDateInput.value = startDateInput.value;
                jamMulaiSelect.setAttribute('required', 'required');
                jamSelesaiSelect.setAttribute('required', 'required');
                targetApproverText.innerHTML = 'Target Verifikator Otomatis: <strong>Guru BK (Bimbingan Konseling)</strong> &bull; Izin Saat Jam Pelajaran Berlangsung';
            } else {
                sectionJamPelajaran.style.display = 'none';
                containerEndDate.style.display = 'block';
                labelStartDate.innerHTML = 'Mulai Tanggal <span class="text-danger">*</span>';
                jamMulaiSelect.removeAttribute('required');
                jamSelesaiSelect.removeAttribute('required');
                targetApproverText.innerHTML = 'Target Verifikator Otomatis: <strong>Wali Kelas</strong> &bull; Izin Penuh Seharian';
            }
        }

        jenisWaktuSelect.addEventListener('change', toggleWaktuIzin);
        startDateInput.addEventListener('change', function() {
            if (jenisWaktuSelect.value === 'jam_pelajaran') {
                endDateInput.value = startDateInput.value;
            }
            populateSessionsForDate();
        });
        jamMulaiSelect.addEventListener('change', updateJamConstraints);
        jamSelesaiSelect.addEventListener('change', updateJamConstraints);

        toggleWaktuIzin();
        if (Object.keys(sessionsByDay).length > 0) {
            populateSessionsForDate();
        }

        // Preview File Bukti Upload & Base64 Fallback
        const previewContainer = document.getElementById('leave-preview-container');
        const previewImg = document.getElementById('leave-preview-img');

        document.querySelectorAll('[data-leave-upload-form]').forEach(function (form) {
            const fileInput = form.querySelector('[data-leave-file-input]');
            const dataInput = form.querySelector('[data-leave-file-data]');
            const nameInput = form.querySelector('[data-leave-file-name]');
            const statusText = form.querySelector('[data-leave-file-status]');
            const submitBtn = form.querySelector('[data-leave-submit-btn]');

            if (!fileInput || !dataInput || !nameInput) return;

            fileInput.addEventListener('change', function () {
                const file = fileInput.files && fileInput.files[0];
                if (!file) {
                    dataInput.value = '';
                    nameInput.value = '';
                    if (previewContainer) previewContainer.style.display = 'none';
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file bukti maksimal 5 MB.');
                    fileInput.value = '';
                    dataInput.value = '';
                    nameInput.value = '';
                    if (previewContainer) previewContainer.style.display = 'none';
                    return;
                }

                nameInput.value = file.name;

                // Live Preview for Images
                if (file.type.startsWith('image/')) {
                    if (previewImg && previewContainer) {
                        previewImg.src = URL.createObjectURL(file);
                        previewContainer.style.display = 'block';
                    }
                } else {
                    if (previewContainer) previewContainer.style.display = 'none';
                }

                if (submitBtn) submitBtn.disabled = true;
                if (statusText) statusText.textContent = 'Memproses berkas ' + file.name + '...';

                const reader = new FileReader();
                reader.onload = function (e) {
                    dataInput.value = e.target.result || '';
                    if (submitBtn) submitBtn.disabled = false;
                    if (statusText) statusText.textContent = 'Berkas siap dikirim: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                };
                reader.onerror = function () {
                    if (submitBtn) submitBtn.disabled = false;
                };
                reader.readAsDataURL(file);
            });

            form.addEventListener('submit', function(e) {
                if (!fileInput.files.length && !dataInput.value) {
                    e.preventDefault();
                    alert('Wajib melampirkan berkas dokumen bukti (foto surat keterangan/bukti izin).');
                    fileInput.focus();
                    return false;
                }
            });
        });
    </script>
</body>
</html>
