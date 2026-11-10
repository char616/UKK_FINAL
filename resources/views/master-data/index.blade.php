<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Sekolah - SCP System</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .master-stat-tile {
            padding: 16px 18px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .master-stat-tile:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        }

        .master-nav-tabs .nav-link {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            font-size: 11.5px;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 9px;
            margin-right: 6px;
            margin-bottom: 6px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.15s ease;
        }
        .master-nav-tabs .nav-link:hover {
            background: #f8fafc;
            color: #174ea6;
            border-color: #cbd5e1;
        }
        .master-nav-tabs .nav-link.active {
            background: #174ea6 !important;
            color: #ffffff !important;
            border-color: #174ea6 !important;
            box-shadow: 0 2px 6px rgba(23, 78, 166, 0.25);
        }

        .table-master th {
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #64748b;
            background-color: #f8fafc;
            padding: 11px 14px;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-master td {
            font-size: 12px;
            padding: 11px 14px;
            vertical-align: middle;
        }

        .btn-action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            font-size: 12px;
            transition: all 0.15s ease;
        }
        .btn-action-icon:hover {
            background: #eff6ff;
            color: #174ea6;
            border-color: #bfdbfe;
        }
        .btn-action-icon.btn-delete:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        @media (max-width: 576px) {
            .master-nav-tabs .nav-link {
                width: 100%;
                justify-content: center;
                margin-right: 0;
            }
        }
    </style>
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
                        <i class="bi bi-folder2-open"></i>
                        Waka Kurikulum Panel
                    </div>
                    <h1 class="page-title">Manajemen Data Sekolah</h1>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('accounts.index') }}" class="btn-primary-custom">
                        <i class="bi bi-people-fill me-1"></i>
                        <span>Buka Manajemen Pengguna</span>
                    </a>
                    <div class="academic-badge">
                        <i class="bi bi-calendar3"></i>
                        <span>Tahun Ajaran 2026/2027</span>
                    </div>
                </div>
            </header>

            <!-- Banner Utama -->
            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-database-fill-gear"></i>
                            PUSAT DATA AKADEMIK TERPADU
                        </div>

                        <h2 class="hero-title">
                            Pusat Manajemen Data Sekolah
                        </h2>

                        <p class="hero-description">
                            Kelola data pokok peserta didik melalui fasilitas impor massal Excel/CSV, data jurusan, rombongan belajar, kurikulum mata pelajaran, serta fasilitas ruangan KBM secara terpadu.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                </div>
            </section>

            <!-- Notifikasi Pesan -->
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

            <!-- KPI OVERVIEW STATISTIK MASTER DATA -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-xl">
                    <div class="master-stat-tile h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small" style="font-size: 10.5px;">JURUSAN</span>
                            <i class="bi bi-diagram-3-fill text-primary fs-5"></i>
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $majors->count() }}</div>
                        <div class="text-muted small" style="font-size: 10px;">Konsentrasi Keahlian</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="master-stat-tile h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small" style="font-size: 10.5px;">ROMBEL KELAS</span>
                            <i class="bi bi-collection-fill text-success fs-5"></i>
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $classes->count() }}</div>
                        <div class="text-muted small" style="font-size: 10px;">Kelas Binaan Aktif</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="master-stat-tile h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small" style="font-size: 10.5px;">MATA PELAJARAN</span>
                            <i class="bi bi-book-half text-warning fs-5"></i>
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $subjects->count() }}</div>
                        <div class="text-muted small" style="font-size: 10px;">Kurikulum KBM</div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-xl">
                    <div class="master-stat-tile h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small" style="font-size: 10.5px;">RUANGAN & LAB</span>
                            <i class="bi bi-door-open-fill text-danger fs-5"></i>
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $rooms->count() }}</div>
                        <div class="text-muted small" style="font-size: 10px;">Ruang Teori & Praktik</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl">
                    <div class="master-stat-tile h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small" style="font-size: 10.5px;">PESERTA DIDIK</span>
                            <i class="bi bi-people-fill text-info fs-5"></i>
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $students->count() }}</div>
                        <div class="text-muted small" style="font-size: 10px;">Siswa Terdaftar</div>
                    </div>
                </div>
            </div>

            <!-- TABS NAVIGASI MASTER DATA -->
            <ul class="nav nav-pills master-nav-tabs mb-3" id="masterDataTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-import-btn" data-bs-toggle="pill" data-bs-target="#tab-import" type="button" role="tab" aria-controls="tab-import" aria-selected="true">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Impor Siswa & Ortu
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-classes-btn" data-bs-toggle="pill" data-bs-target="#tab-classes" type="button" role="tab" aria-controls="tab-classes" aria-selected="false">
                        <i class="bi bi-collection-fill"></i> Penugasan Kelas dan Wali
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-majors-btn" data-bs-toggle="pill" data-bs-target="#tab-majors" type="button" role="tab" aria-controls="tab-majors" aria-selected="false">
                        <i class="bi bi-diagram-3-fill"></i> Konsentrasi Keahlian (Jurusan)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-subjects-btn" data-bs-toggle="pill" data-bs-target="#tab-subjects" type="button" role="tab" aria-controls="tab-subjects" aria-selected="false">
                        <i class="bi bi-book-half"></i> Mata Pelajaran
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-rooms-btn" data-bs-toggle="pill" data-bs-target="#tab-rooms" type="button" role="tab" aria-controls="tab-rooms" aria-selected="false">
                        <i class="bi bi-door-open-fill"></i> Ruang & Lab
                    </button>
                </li>
            </ul>

            <!-- TAB CONTENT -->
            <div class="tab-content" id="masterDataTabsContent">
                
                <!-- TAB 1: IMPOR SISWA & ORANG TUA -->
                <div class="tab-pane fade show active" id="tab-import" role="tabpanel" aria-labelledby="tab-import-btn">
                    <section class="content-card mb-4">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-light text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 10px;">Fitur Utama</span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-semibold" style="font-size: 10px;">Impor Massal Otomatis</span>
                                </div>
                                <h2 class="fw-bold text-dark mb-2" style="font-size: 16px;">Impor Siswa dan Orang Tua dari Excel / CSV</h2>
                                <p class="text-muted mb-3" style="font-size: 12px; line-height: 1.6;">
                                    Unduh template Excel (<code>.xlsx</code>) atau CSV (<code>.csv</code>), isi satu baris per siswa, lalu unggah kembali. Akun orang tua dibuat otomatis terpisah per anak dengan Login ID dari nama siswa berakhiran <code>_ortu</code>.
                                </p>
                                <ul class="text-muted mb-3 ps-3" style="font-size: 12px; line-height: 1.6;">
                                    <li class="mb-1">Pastikan nama kelas di file sesuai dengan data rombel sekolah (misalnya: <code>X RPL 1</code>, <code>XI TKJ 2</code>).</li>
                                    <li class="mb-1">Tidak perlu mengisi password saat impor — semua akun baru otomatis siap digunakan (password default: <code>password123</code>).</li>
                                    <li class="mb-1">Email pemulihan bersifat opsional (boleh dikosongkan).</li>
                                    <li>File divalidasi seluruhnya terlebih dahulu. Jika ada kesalahan baris, tidak ada data yang disimpan sebagian.</li>
                                </ul>
                                <details class="mt-2 text-dark" style="font-size: 12px;">
                                    <summary class="fw-semibold text-primary" style="cursor: pointer;">Arti kolom template Excel / CSV</summary>
                                    <ul class="text-muted mt-2 ps-3" style="line-height: 1.6;">
                                        <li>Wajib: <code>student_login_id</code> (NISN 10 digit), <code>student_name</code>, <code>class_name</code>, <code>major_code</code>, <code>parent_name</code>.</li>
                                        <li>Opsional: <code>student_recovery_email</code> dan <code>parent_recovery_email</code>.</li>
                                        <li>Satu siswa memiliki satu akun orang tua; Login ID akun itu dibuat otomatis dari nama siswa.</li>
                                    </ul>
                                </details>
                            </div>
                            <div class="col-lg-5">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <a href="{{ route('master-data.students.import-template', ['format' => 'xlsx']) }}" class="btn-primary-custom flex-grow-1 justify-content-center py-2">
                                            <i class="bi bi-file-earmark-excel-fill me-1"></i> Unduh Template Excel (.xlsx)
                                        </a>
                                        <a href="{{ route('master-data.students.import-template') }}" class="btn-outline-primary-custom flex-grow-1 justify-content-center py-2">
                                            <i class="bi bi-filetype-csv me-1"></i> Template CSV
                                        </a>
                                    </div>
                                    <form action="{{ route('master-data.students.import') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-3" data-roster-upload-form>
                                        @csrf
                                        <div>
                                            <label for="student-roster" class="form-label fw-bold text-dark" style="font-size: 12px;">Unggah File Data Siswa (<code>.xlsx</code> / <code>.csv</code>)</label>
                                            <input id="student-roster" type="file" name="roster" class="form-control form-control-sm" accept=".xlsx,.csv,.txt,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" required data-roster-file-input>
                                            <input type="hidden" name="roster_data" data-roster-file-data>
                                            <input type="hidden" name="roster_name" data-roster-file-name>
                                            <div class="form-text text-muted" style="font-size: 11px;" data-roster-file-status>Mendukung format Microsoft Excel (<code>.xlsx</code>) atau <code>.csv</code>. Maksimal 5 MB.</div>
                                        </div>
                                        <button type="submit" class="btn-primary-custom w-100" data-roster-submit-btn>
                                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Impor Data Siswa & Orang Tua
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- TAB 2: PENUGASAN KELAS DAN WALI (ROMBEL & WALI KELAS) -->
                <div class="tab-pane fade" id="tab-classes" role="tabpanel" aria-labelledby="tab-classes-btn">
                    <section class="content-card mb-4">
                        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon">
                                    <i class="bi bi-collection-fill"></i>
                                </div>
                                <div>
                                    <h3 class="section-title mb-0">Penugasan Kelas dan Wali</h3>
                                    <p class="section-description">Kelola rombongan belajar kelas, penugasan guru wali kelas, dan pembagian jurusan.</p>
                                </div>
                            </div>
                            <button type="button" class="btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateClass">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Kelas Baru
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-master align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="22%">Nama Rombel Kelas</th>
                                        <th width="20%">Konsentrasi Keahlian</th>
                                        <th width="12%">Fase</th>
                                        <th width="26%">Wali Kelas Pembina</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($classes as $idx => $class)
                                        <tr>
                                            <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <strong class="text-dark d-block">{{ $class->name }}</strong>
                                                <small class="text-muted">{{ $class->students_count }} Siswa Terdaftar</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1">
                                                    {{ $class->major?->name ?? 'Belum Ditentukan' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $class->phase === 'ganjil' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-emphasis' }} px-2 py-1">
                                                    Fase {{ ucfirst($class->phase ?? '-') }}
                                                </span>
                                            </td>
                                            <td>
                                                <form action="{{ route('master-data.classes.homeroom-teacher', $class) }}" method="POST" class="d-flex align-items-center gap-1.5">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="homeroom_teacher_id" class="form-select form-select-sm" style="font-size: 11px;">
                                                        <option value="">-- Tanpa Wali Kelas --</option>
                                                        @foreach($homeroomTeachers as $teacher)
                                                            <option value="{{ $teacher->id }}" @selected($class->homeroom_teacher_id == $teacher->id)>
                                                                {{ $teacher->name }} ({{ $teacher->login_key }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-outline-primary py-1 px-2" title="Simpan Wali Kelas" style="font-size: 11px;">
                                                        <i class="bi bi-check2"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn-action-icon" data-bs-toggle="modal" data-bs-target="#modalEditClass-{{ $class->id }}" title="Edit Rombel">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('master-data.classes.destroy', $class) }}" method="POST" onsubmit="return confirm('Hapus rombel kelas {{ $class->name }}?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-action-icon btn-delete" title="Hapus Rombel">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- MODAL EDIT KELAS -->
                                                <div class="modal fade text-start" id="modalEditClass-{{ $class->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow">
                                                            <form action="{{ route('master-data.classes.update', $class) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header border-bottom py-3">
                                                                    <h5 class="modal-title fw-bold text-dark" style="font-size: 14px;">Edit Rombel Kelas</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                                </div>
                                                                <div class="modal-body p-3.5">
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Nama Rombel Kelas <span class="text-danger">*</span></label>
                                                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $class->name }}" required placeholder="Contoh: X RPL 1">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Konsentrasi Keahlian (Jurusan) <span class="text-danger">*</span></label>
                                                                        <select name="major_id" class="form-select form-select-sm" required>
                                                                            @foreach($majors as $m)
                                                                                <option value="{{ $m->id }}" @selected($class->major_id == $m->id)>{{ $m->name }} ({{ $m->code }})</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Fase Kurikulum</label>
                                                                        <select name="phase" class="form-select form-select-sm">
                                                                            <option value="ganjil" @selected($class->phase === 'ganjil')>Fase Ganjil</option>
                                                                            <option value="genap" @selected($class->phase === 'genap')>Fase Genap</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Wali Kelas Pembina</label>
                                                                        <select name="homeroom_teacher_id" class="form-select form-select-sm">
                                                                            <option value="">-- Belum Ditugaskan --</option>
                                                                            @foreach($homeroomTeachers as $teacher)
                                                                                <option value="{{ $teacher->id }}" @selected($class->homeroom_teacher_id == $teacher->id)>{{ $teacher->name }} ({{ $teacher->login_key }})</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-top py-2.5 px-3">
                                                                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn-primary-custom btn-sm px-3">Simpan Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">Belum ada rombel kelas yang terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <!-- TAB 3: KONSENTRASI KEAHLIAN (JURUSAN) -->
                <div class="tab-pane fade" id="tab-majors" role="tabpanel" aria-labelledby="tab-majors-btn">
                    <section class="content-card mb-4">
                        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon">
                                    <i class="bi bi-diagram-3-fill"></i>
                                </div>
                                <div>
                                    <h3 class="section-title mb-0">Konsentrasi Keahlian (Jurusan)</h3>
                                    <p class="section-description">Kelola program keahlian kejuruan yang diselenggarakan oleh sekolah.</p>
                                </div>
                            </div>
                            <button type="button" class="btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateMajor">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Jurusan
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-master align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="8%">No</th>
                                        <th width="20%">Kode Jurusan</th>
                                        <th width="45%">Nama Konsentrasi Keahlian</th>
                                        <th width="15%">Jumlah Kelas</th>
                                        <th width="12%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($majors as $idx => $major)
                                        <tr>
                                            <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <code class="text-dark bg-light px-2 py-0.5 rounded border fw-bold">{{ $major->code }}</code>
                                            </td>
                                            <td>
                                                <strong class="text-dark">{{ $major->name }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $major->classes_count }} Rombel</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn-action-icon" data-bs-toggle="modal" data-bs-target="#modalEditMajor-{{ $major->id }}" title="Edit Jurusan">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('master-data.majors.destroy', $major) }}" method="POST" onsubmit="return confirm('Hapus jurusan {{ $major->name }}?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-action-icon btn-delete" title="Hapus Jurusan">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- MODAL EDIT MAJOR -->
                                                <div class="modal fade text-start" id="modalEditMajor-{{ $major->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow">
                                                            <form action="{{ route('master-data.majors.update', $major) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header border-bottom py-3">
                                                                    <h5 class="modal-title fw-bold text-dark" style="font-size: 14px;">Edit Jurusan</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                                </div>
                                                                <div class="modal-body p-3.5">
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Nama Jurusan <span class="text-danger">*</span></label>
                                                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $major->name }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Kode Jurusan <span class="text-danger">*</span></label>
                                                                        <input type="text" name="code" class="form-control form-control-sm text-uppercase" value="{{ $major->code }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-top py-2.5 px-3">
                                                                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn-primary-custom btn-sm px-3">Simpan Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">Belum ada jurusan yang terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <!-- TAB 4: MATA PELAJARAN -->
                <div class="tab-pane fade" id="tab-subjects" role="tabpanel" aria-labelledby="tab-subjects-btn">
                    <section class="content-card mb-4">
                        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon">
                                    <i class="bi bi-book-half"></i>
                                </div>
                                <div>
                                    <h3 class="section-title mb-0">Kurikulum Mata Pelajaran</h3>
                                    <p class="section-description">Daftar mata pelajaran yang diampu oleh bapak/ibu guru pada jadwal pembelajaran.</p>
                                </div>
                            </div>
                            <button type="button" class="btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateSubject">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Mapel
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-master align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="8%">No</th>
                                        <th width="20%">Kode Mapel</th>
                                        <th width="45%">Nama Mata Pelajaran</th>
                                        <th width="15%">Total Jadwal KBM</th>
                                        <th width="12%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subjects as $idx => $subject)
                                        <tr>
                                            <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <code class="text-dark bg-light px-2 py-0.5 rounded border fw-bold">{{ $subject->code }}</code>
                                            </td>
                                            <td>
                                                <strong class="text-dark">{{ $subject->name }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-primary border border-primary-subtle">{{ $subject->schedules_count }} Jadwal</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn-action-icon" data-bs-toggle="modal" data-bs-target="#modalEditSubject-{{ $subject->id }}" title="Edit Mapel">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('master-data.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Hapus mata pelajaran {{ $subject->name }}?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-action-icon btn-delete" title="Hapus Mapel">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- MODAL EDIT SUBJECT -->
                                                <div class="modal fade text-start" id="modalEditSubject-{{ $subject->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow">
                                                            <form action="{{ route('master-data.subjects.update', $subject) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header border-bottom py-3">
                                                                    <h5 class="modal-title fw-bold text-dark" style="font-size: 14px;">Edit Mata Pelajaran</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                                </div>
                                                                <div class="modal-body p-3.5">
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                                                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $subject->name }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Kode Mapel <span class="text-danger">*</span></label>
                                                                        <input type="text" name="code" class="form-control form-control-sm text-uppercase" value="{{ $subject->code }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-top py-2.5 px-3">
                                                                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn-primary-custom btn-sm px-3">Simpan Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">Belum ada mata pelajaran yang terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <!-- TAB 5: RUANG KELAS & LAB -->
                <div class="tab-pane fade" id="tab-rooms" role="tabpanel" aria-labelledby="tab-rooms-btn">
                    <section class="content-card mb-4">
                        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon">
                                    <i class="bi bi-door-open-fill"></i>
                                </div>
                                <div>
                                    <h3 class="section-title mb-0">Ruang Pembelajaran & Laboratorium</h3>
                                    <p class="section-description">Fasilitas ruang teori, bengkel kerja, dan laboratorium praktik untuk KBM.</p>
                                </div>
                            </div>
                            <button type="button" class="btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateRoom">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Ruangan
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-master align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="8%">No</th>
                                        <th width="18%">Kode Ruang</th>
                                        <th width="35%">Nama Ruang / Lab</th>
                                        <th width="15%">Kapasitas</th>
                                        <th width="12%">Total Jadwal</th>
                                        <th width="12%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rooms as $idx => $room)
                                        <tr>
                                            <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <code class="text-dark bg-light px-2 py-0.5 rounded border fw-bold">{{ $room->code }}</code>
                                            </td>
                                            <td>
                                                <strong class="text-dark">{{ $room->name }}</strong>
                                                @if($room->rules)
                                                    <small class="text-muted d-block" style="font-size: 10.5px;">Ketentuan: {{ $room->rules }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $room->capacity ?? '-' }} Siswa</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-primary border border-primary-subtle">{{ $room->schedules_count }} Jadwal</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn-action-icon" data-bs-toggle="modal" data-bs-target="#modalEditRoom-{{ $room->id }}" title="Edit Ruangan">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('master-data.rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Hapus ruangan {{ $room->name }}?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-action-icon btn-delete" title="Hapus Ruangan">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- MODAL EDIT ROOM -->
                                                <div class="modal fade text-start" id="modalEditRoom-{{ $room->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow">
                                                            <form action="{{ route('master-data.rooms.update', $room) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header border-bottom py-3">
                                                                    <h5 class="modal-title fw-bold text-dark" style="font-size: 14px;">Edit Ruangan</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                                </div>
                                                                <div class="modal-body p-3.5">
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Nama Ruang / Lab <span class="text-danger">*</span></label>
                                                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $room->name }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Kode Ruang</label>
                                                                        <input type="text" name="code" class="form-control form-control-sm text-uppercase" value="{{ $room->code }}">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Kapasitas Maksimal (Siswa)</label>
                                                                        <input type="number" name="capacity" class="form-control form-control-sm" value="{{ $room->capacity }}" min="1" max="500">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold small">Aturan / Ketentuan Penggunaan</label>
                                                                        <textarea name="rules" class="form-control form-control-sm" rows="2">{{ $room->rules }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-top py-2.5 px-3">
                                                                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn-primary-custom btn-sm px-3">Simpan Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">Belum ada ruangan yang terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

            </div>

        </div>
    </main>

    <!-- MODAL CREATE CLASS -->
    <div class="modal fade" id="modalCreateClass" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('master-data.classes.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title fw-bold text-dark" style="font-size: 14px;">Tambah Rombel Kelas Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body p-3.5">
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Nama Rombel Kelas <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" required placeholder="Contoh: X RPL 2">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Konsentrasi Keahlian (Jurusan) <span class="text-danger">*</span></label>
                            <select name="major_id" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach($majors as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Fase Kurikulum</label>
                            <select name="phase" class="form-select form-select-sm">
                                <option value="ganjil">Fase Ganjil</option>
                                <option value="genap">Fase Genap</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Wali Kelas Pembina (Opsional)</label>
                            <select name="homeroom_teacher_id" class="form-select form-select-sm">
                                <option value="">-- Belum Ditugaskan --</option>
                                @foreach($homeroomTeachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->login_key }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2.5 px-3">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary-custom btn-sm px-3">Simpan Rombel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CREATE MAJOR -->
    <div class="modal fade" id="modalCreateMajor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('master-data.majors.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title fw-bold text-dark" style="font-size: 14px;">Tambah Konsentrasi Keahlian (Jurusan)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body p-3.5">
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" required placeholder="Contoh: Rekayasa Perangkat Lunak">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Kode Jurusan <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control form-control-sm text-uppercase" required placeholder="Contoh: RPL">
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2.5 px-3">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary-custom btn-sm px-3">Simpan Jurusan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CREATE SUBJECT -->
    <div class="modal fade" id="modalCreateSubject" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('master-data.subjects.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title fw-bold text-dark" style="font-size: 14px;">Tambah Mata Pelajaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body p-3.5">
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" required placeholder="Contoh: Pemrograman Berorientasi Objek">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Kode Mapel <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control form-control-sm text-uppercase" required placeholder="Contoh: PBO">
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2.5 px-3">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary-custom btn-sm px-3">Simpan Mapel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CREATE ROOM -->
    <div class="modal fade" id="modalCreateRoom" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('master-data.rooms.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title fw-bold text-dark" style="font-size: 14px;">Tambah Ruangan / Laboratorium</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body p-3.5">
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Nama Ruang / Lab <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" required placeholder="Contoh: Lab Komputer 1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Kode Ruang</label>
                            <input type="text" name="code" class="form-control form-control-sm text-uppercase" placeholder="Contoh: LAB-RPL-1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Kapasitas Maksimal (Siswa)</label>
                            <input type="number" name="capacity" class="form-control form-control-sm" min="1" max="500" placeholder="Contoh: 36">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">Aturan Penggunaan</label>
                            <textarea name="rules" class="form-control form-control-sm" rows="2" placeholder="Ketentuan khusus ruang laboratorium..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2.5 px-3">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary-custom btn-sm px-3">Simpan Ruangan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-roster-upload-form]').forEach(function (form) {
            const fileInput = form.querySelector('[data-roster-file-input]');
            const dataInput = form.querySelector('[data-roster-file-data]');
            const nameInput = form.querySelector('[data-roster-file-name]');
            const statusText = form.querySelector('[data-roster-file-status]');
            const submitBtn = form.querySelector('[data-roster-submit-btn]');

            if (!fileInput || !dataInput || !nameInput) return;

            fileInput.addEventListener('change', function () {
                const file = fileInput.files && fileInput.files[0];
                if (!file) {
                    dataInput.value = '';
                    nameInput.value = '';
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file maksimal 5 MB.');
                    fileInput.value = '';
                    dataInput.value = '';
                    nameInput.value = '';
                    return;
                }

                nameInput.value = file.name;
                if (submitBtn) submitBtn.disabled = true;
                if (statusText) statusText.textContent = 'Memproses file ' + file.name + '...';

                const reader = new FileReader();
                reader.onload = function (e) {
                    dataInput.value = e.target.result || '';
                    if (submitBtn) submitBtn.disabled = false;
                    if (statusText) statusText.textContent = 'File siap diimpor: ' + file.name;
                };
                reader.onerror = function () {
                    if (submitBtn) submitBtn.disabled = false;
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
</body>
</html>
