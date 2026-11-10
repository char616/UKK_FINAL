<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Cycle;
use App\Models\JamPelajaran;
use App\Models\LeaveRequest;
use App\Models\Major;
use App\Models\Role;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RevisionsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->detectEnvironment(fn () => 'local');
        $this->seed(DatabaseSeeder::class);
        app()->detectEnvironment(fn () => 'testing');
        Storage::fake('local');
    }

    private function getOrCreateRoom(): Room
    {
        return Room::where('code', 'RUANG-1')->first()
            ?? Room::firstOrCreate(['code' => 'RUANG-1'], ['name' => 'Ruang 1', 'capacity' => 36]);
    }

    private function getOrCreateStudent(Classes $class): User
    {
        $student = User::whereHas('role', fn ($q) => $q->where('name', 'siswa'))->first();
        if (! $student) {
            $siswaRole = Role::where('name', 'siswa')->firstOrFail();
            $student = User::create([
                'name' => 'Siswa Test',
                'login_key' => '1234567890',
                'password' => bcrypt('password123'),
                'role_id' => $siswaRole->id,
                'class_id' => $class->id,
                'is_active' => true,
            ]);
        } else {
            $student->update(['class_id' => $class->id]);
        }

        return $student;
    }

    public function test_journal_and_attendance_cannot_be_submitted_for_past_dates(): void
    {
        $guru = User::whereHas('role', fn ($q) => $q->where('name', 'guru'))->firstOrFail();
        $class = Classes::firstOrFail();
        $subject = Subject::firstOrFail();
        $room = $this->getOrCreateRoom();
        $activeRound = Cycle::current()->active_round;

        $schedule = Schedule::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $guru->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'round' => $activeRound,
        ]);

        $pastDate = now()->subDays(2)->toDateString();

        // 1. Coba isi jurnal mengajar untuk tanggal lampau
        $this->actingAs($guru)->post(route('journals.store'), [
            'schedule_id' => $schedule->id,
            'date' => $pastDate,
            'materi' => 'Materi Lampau',
            'summary' => 'Ringkasan Lampau',
        ])->assertSessionHasErrors('date');

        // 2. Coba isi presensi untuk tanggal lampau
        $student = $this->getOrCreateStudent($class);

        $this->actingAs($guru)->post(route('attendances.store', $schedule->id), [
            'date' => $pastDate,
            'attendances' => [
                $student->id => 'Hadir',
            ],
            'materi' => 'Materi Presensi Lampau',
            'summary' => 'Ringkasan Presensi Lampau',
        ])->assertSessionHasErrors('date');
    }

    public function test_journal_cannot_be_updated_if_date_is_in_the_past(): void
    {
        $guru = User::whereHas('role', fn ($q) => $q->where('name', 'guru'))->firstOrFail();
        $class = Classes::firstOrFail();
        $subject = Subject::firstOrFail();
        $room = $this->getOrCreateRoom();
        $activeRound = Cycle::current()->active_round;

        $schedule = Schedule::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $guru->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'round' => $activeRound,
        ]);

        $pastDate = now()->subDays(1)->toDateString();
        $journal = TeachingJournal::create([
            'teacher_id' => $guru->id,
            'schedule_id' => $schedule->id,
            'date' => $pastDate,
            'materi' => 'Materi Awal',
            'activity_summary' => 'Aktivitas Awal',
        ]);

        $this->actingAs($guru)->put(route('journals.update', $journal->id), [
            'date' => $pastDate,
            'materi' => 'Materi Revisi',
            'summary' => 'Aktivitas Revisi',
        ])->assertSessionHasErrors('date');
    }

    public function test_attendance_stores_realtime_camera_snapshot(): void
    {
        $guru = User::whereHas('role', fn ($q) => $q->where('name', 'guru'))->firstOrFail();
        $class = Classes::firstOrFail();
        $subject = Subject::firstOrFail();
        $room = $this->getOrCreateRoom();
        $activeRound = Cycle::current()->active_round;

        $schedule = Schedule::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $guru->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'round' => $activeRound,
        ]);

        $student = $this->getOrCreateStudent($class);

        // 1x1 transparent PNG data uri
        $base64Png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $today = now()->toDateString();
        $response = $this->actingAs($guru)->post(route('attendances.store', $schedule->id), [
            'date' => $today,
            'attendances' => [
                $student->id => 'Hadir',
            ],
            'materi' => 'Belajar Realtime Camera',
            'summary' => 'Jepret foto langsung dari kamera',
            'catatan_kejadian' => 'Lancar',
            'attachment_data' => $base64Png,
        ]);

        $response->assertRedirect(route('attendances.index'));

        $journal = TeachingJournal::where('teacher_id', $guru->id)
            ->where('schedule_id', $schedule->id)
            ->whereDate('date', $today)
            ->first();

        $this->assertNotNull($journal);
        $this->assertNotNull($journal->photo_proof);
        $this->assertStringStartsWith('journal_attachments/camera_', $journal->photo_proof);
        Storage::disk('local')->assertExists($journal->photo_proof);

        $attendance = Attendance::where('schedule_id', $schedule->id)
            ->where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        $this->assertNotNull($attendance);
        $this->assertSame('Hadir', $attendance->status);
    }

    public function test_manajemen_data_page_displays_import_and_accounts_for_waka(): void
    {
        $waka = User::whereHas('role', fn ($q) => $q->where('name', 'waka'))->firstOrFail();

        $response = $this->actingAs($waka)->get(route('master-data.index'));
        $response->assertOk();
        $response->assertSee('Manajemen Data Sekolah');
        $response->assertSee('Impor Siswa dan Orang Tua');
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('Logout');
        $response->assertDontSee('Kelola & Atur Password Akun Pengguna');
        // Pastikan form tambah kelas, jurusan, ruangan tidak ada
        $response->assertDontSee('Tambah Jurusan');
        $response->assertDontSee('Tambah Kelas');
        $response->assertDontSee('Tambah Ruangan');
    }

    public function test_waka_revisions_parent_accounts_school_days_audit_log_delete_and_journal_monitoring(): void
    {
        $waka = User::whereHas('role', fn ($q) => $q->where('name', 'waka'))->firstOrFail();

        // 1. Pastikan data akun orang tua ada di database dan muncul di Manajemen Pengguna
        $parentCount = User::whereHas('role', fn ($q) => $q->where('name', 'orang_tua'))->count();
        $this->assertGreaterThan(0, $parentCount, 'Akun orang_tua harus tersedia dari seeder');

        $accountsResponse = $this->actingAs($waka)->get(route('accounts.index'));
        $accountsResponse->assertOk();
        $accountsResponse->assertSee('Buat Akun Pengguna Baru');
        $accountsResponse->assertDontSee('Semua akun aktif otomatis.');
        $accountsResponse->assertSee('Orang Tua dari:');

        // 2. Hari sekolah hanya Senin s/d Jumat (Sabtu ditolak)
        $guru = User::whereHas('role', fn ($q) => $q->where('name', 'guru'))->firstOrFail();
        $class = Classes::firstOrFail();
        $subject = Subject::firstOrFail();
        $room = $this->getOrCreateRoom();

        $this->actingAs($guru)->post(route('schedules.store'), [
            'schedules' => [
                [
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'room_id' => $room->id,
                    'day' => 'Sabtu',
                    'jam_ke_mulai' => 1,
                    'jam_ke_selesai' => 2,
                ],
            ],
        ])->assertSessionHasErrors('schedules.0.day');

        // 3. Fitur hapus audit log (satu log & semua log)
        $log1 = \App\Models\AuditLog::create(['user_id' => $waka->id, 'action' => 'TEST_LOG_1', 'description' => 'Uji log pertama']);
        $log2 = \App\Models\AuditLog::create(['user_id' => $waka->id, 'action' => 'TEST_LOG_2', 'description' => 'Uji log kedua']);

        $this->actingAs($waka)->delete(route('audit-logs.destroy', $log1->id))
            ->assertRedirect();
        $this->assertDatabaseMissing('audit_logs', ['id' => $log1->id]);

        $this->actingAs($waka)->delete(route('audit-logs.destroy-all'))
            ->assertRedirect(route('audit-logs.index'));
        $this->assertDatabaseMissing('audit_logs', ['id' => $log2->id]);

        // 4. Jurnal mengajar Waka adalah monitoring milik guru (bukan mencatat)
        $journalView = $this->actingAs($waka)->get(route('journals.index'));
        $journalView->assertOk();
        $journalView->assertSee('Monitoring Jurnal Mengajar Guru');
        $journalView->assertSee('Pemantauan Jurnal Mengajar & Pelaksanaan KBM');
        $journalView->assertDontSee('Catat dan pantau kegiatan belajar mengajar.');
    }

    public function test_attendance_and_journal_filters_work(): void
    {
        $waka = User::whereHas('role', fn ($q) => $q->where('name', 'waka'))->firstOrFail();
        $class = Classes::firstOrFail();

        // 1. Filter presensi dengan search, date, class_id, status
        $resAttendance = $this->actingAs($waka)->get(route('attendances.index', [
            'date' => now()->toDateString(),
            'class_id' => $class->id,
            'status' => 'Hadir',
            'search' => 'Siswa',
        ]));
        $resAttendance->assertOk();

        // 2. Filter jurnal dengan date, class_id
        $resJournal = $this->actingAs($waka)->get(route('journals.index', [
            'date' => now()->toDateString(),
            'class_id' => $class->id,
        ]));
        $resJournal->assertOk();
    }

    public function test_dashboards_render_infographics_and_student_counts(): void
    {
        $class = Classes::firstOrFail();
        $student = $this->getOrCreateStudent($class);

        $wali = $class->homeroomTeacher;
        if (! $wali) {
            $wali = User::whereHas('role', fn ($q) => $q->where('name', 'wali_kelas'))->firstOrFail();
            $class->update(['homeroom_teacher_id' => $wali->id]);
        }
        $bk = User::whereHas('role', fn ($q) => $q->where('name', 'bk'))->firstOrFail();

        // 1. Siswa Dashboard
        $resSiswa = $this->actingAs($student)->get(route('dashboard.siswa'));
        $resSiswa->assertOk();
        $resSiswa->assertSee('Infografis Kehadiran Saya');
        $resSiswa->assertSee('HADIR');
        $resSiswa->assertSee('IZIN');
        $resSiswa->assertSee('SAKIT');
        $resSiswa->assertSee('ALPA');
        $resSiswa->assertDontSee('Akses Cepat Siswa');
        $resSiswa->assertDontSee('Status Pengajuan Izin & Sakit');

        // 2. Orang Tua Dashboard (mode orang tua pada akun siswa)
        $resOrtu = $this->actingAs($student)->withSession(['login_as' => 'orang_tua'])->get(route('dashboard.ortu'));
        $resOrtu->assertOk();
        $resOrtu->assertSee('Infografis Kehadiran');
        $resOrtu->assertSee($student->name);
        $resOrtu->assertDontSee('Akses Cepat Wali Murid');
        $resOrtu->assertDontSee('Status Pengajuan Izin & Sakit');

        // 3. Wali Kelas Dashboard
        $resWali = $this->actingAs($wali)->get(route('dashboard.wali'));
        $resWali->assertOk();
        $resWali->assertSee('Infografis Kehadiran Kelas Binaan');
        $resWali->assertSee('Data Kehadiran Peserta Didik Per Anak');

        // 4. BK Dashboard
        $resBk = $this->actingAs($bk)->get(route('dashboard.bk'));
        $resBk->assertOk();
        $resBk->assertSee('Infografis Presensi Tingkat Sekolah');
        $resBk->assertDontSee('Pemantauan Presensi Per Siswa');
        $resBk->assertDontSee('Akses Cepat BK');
        $resBk->assertDontSee('Permohonan Izin Jam Pelajaran (Verifikasi BK)');
    }

    public function test_leave_requests_require_reason_when_rejected_and_display_status(): void
    {
        $class = Classes::firstOrFail();
        $student = $this->getOrCreateStudent($class);
        $wali = $class->homeroomTeacher;
        if (! $wali) {
            $wali = User::whereHas('role', fn ($q) => $q->where('name', 'wali_kelas'))->firstOrFail();
            $class->update(['homeroom_teacher_id' => $wali->id]);
        }

        $leave = LeaveRequest::create([
            'student_id' => $student->id,
            'type' => 'Izin',
            'jenis_waktu_izin' => 'seharian',
            'target_approver' => 'wali_kelas',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'reason' => 'Ada acara keluarga penting',
            'status' => 'Pending',
        ]);

        // Status awal harus Pending
        $this->assertSame('Pending', $leave->fresh()->status);

        // Menolak tanpa alasan penolakan harus gagal validasi
        $this->actingAs($wali)->post(route('leaves.status', $leave->id), [
            'status' => 'Rejected',
            'alasan_penolakan' => '',
        ])->assertSessionHasErrors('alasan_penolakan');

        // Menolak dengan alasan penolakan harus berhasil
        $this->actingAs($wali)->post(route('leaves.status', $leave->id), [
            'status' => 'Rejected',
            'alasan_penolakan' => 'Dokumen surat pengantar izin dari orang tua belum dilampirkan.',
        ])->assertSessionHasNoErrors();

        $freshLeave = $leave->fresh();
        $this->assertSame('Rejected', $freshLeave->status);
        $this->assertSame('Dokumen surat pengantar izin dari orang tua belum dilampirkan.', $freshLeave->alasan_penolakan);

        // Siswa melihat alasan penolakan di halaman izin
        $resSiswa = $this->actingAs($student)->get(route('leaves.index'));
        $resSiswa->assertOk();
        $resSiswa->assertSee('Ditolak');
        $resSiswa->assertSee('Dokumen surat pengantar izin dari orang tua belum dilampirkan.');
    }

    public function test_teaching_reminder_command_runs_successfully(): void
    {
        $this->artisan('teaching:send-reminders')
            ->assertSuccessful();
    }

    public function test_student_and_parent_can_only_view_their_own_class_schedules(): void
    {
        $classA = Classes::where('name', 'X RPL 1')->first() ?? Classes::firstOrFail();
        $classB = Classes::where('name', '!=', $classA->name)->firstOrFail();

        $guru = User::whereHas('role', fn ($q) => $q->where('name', 'guru'))->firstOrFail();
        $subjectA = Subject::firstOrFail();
        $subjectB = Subject::where('id', '!=', $subjectA->id)->firstOrFail();
        $room = $this->getOrCreateRoom();
        $activeRound = Cycle::current()->active_round;

        // Jadwal Kelas A
        $scheduleA = Schedule::create([
            'class_id' => $classA->id,
            'subject_id' => $subjectA->id,
            'teacher_id' => $guru->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '07:15:00',
            'end_time' => '08:35:00',
            'round' => $activeRound,
        ]);

        // Jadwal Kelas B
        $scheduleB = Schedule::create([
            'class_id' => $classB->id,
            'subject_id' => $subjectB->id,
            'teacher_id' => $guru->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '08:35:00',
            'end_time' => '09:55:00',
            'round' => $activeRound,
        ]);

        // Siswa terdaftar di Kelas A
        $studentRole = Role::where('name', 'siswa')->firstOrFail();
        $studentA = User::create([
            'name' => 'Siswa Kelas A',
            'login_key' => 'SISWA_KELAS_A_999',
            'email' => 'siswa_a_999@school.test',
            'password' => bcrypt('password123'),
            'role_id' => $studentRole->id,
            'class_id' => $classA->id,
            'is_active' => true,
        ]);

        // 1. Siswa Kelas A melihat jadwal: harus melihat jadwal Kelas A dan TIDAK melihat jadwal Kelas B
        $resStudent = $this->actingAs($studentA)->get(route('schedules.index'));
        $resStudent->assertOk();
        $resStudent->assertSee($subjectA->name);
        $resStudent->assertSee($classA->name);
        $resStudent->assertDontSee($subjectB->name);
        $resStudent->assertDontSee($classB->name);
        $resStudent->assertDontSee('name="class_id"', false);

        // 2. Siswa Kelas A mencoba memanipulasi query string dengan ?class_id=[classB]:
        // Tetap hanya melihat Kelas A dan TIDAK melihat Kelas B
        $resTampered = $this->actingAs($studentA)->get(route('schedules.index', ['class_id' => $classB->id]));
        $resTampered->assertOk();
        $resTampered->assertSee($subjectA->name);
        $resTampered->assertSee($classA->name);
        $resTampered->assertDontSee($subjectB->name);
        $resTampered->assertDontSee($classB->name);

        // 3. Orang Tua dari Siswa Kelas A
        $parentRole = Role::where('name', 'orang_tua')->firstOrFail();
        $parentA = User::create([
            'name' => 'Orang Tua Siswa A',
            'login_key' => 'ORTU_SISWA_A_999',
            'email' => 'ortu_a_999@school.test',
            'password' => bcrypt('password123'),
            'role_id' => $parentRole->id,
            'class_id' => $classA->id,
            'is_active' => true,
        ]);
        $parentA->students()->attach($studentA->id);

        $resParent = $this->actingAs($parentA)->get(route('schedules.index'));
        $resParent->assertOk();
        $resParent->assertSee($subjectA->name);
        $resParent->assertSee($classA->name);
        $resParent->assertDontSee($subjectB->name);
        $resParent->assertDontSee($classB->name);
        $resParent->assertDontSee('name="class_id"', false);

        // 4. Orang Tua mencoba manipulasi URL ?class_id=[classB]:
        // Tetap hanya melihat Kelas A dan TIDAK melihat Kelas B
        $resParentTampered = $this->actingAs($parentA)->get(route('schedules.index', ['class_id' => $classB->id]));
        $resParentTampered->assertOk();
        $resParentTampered->assertSee($subjectA->name);
        $resParentTampered->assertSee($classA->name);
        $resParentTampered->assertDontSee($subjectB->name);
        $resParentTampered->assertDontSee($classB->name);
    }

    public function test_student_and_parent_can_open_journal_photo_only_for_their_own_attendance(): void
    {
        $guru = User::whereHas('role', fn ($q) => $q->where('name', 'guru'))->firstOrFail();
        $class = Classes::firstOrFail();
        $subject = Subject::firstOrFail();
        $room = $this->getOrCreateRoom();

        $schedule = Schedule::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $guru->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'round' => Cycle::current()->active_round,
        ]);

        $photoPath = 'journal_attachments/test_photo.png';
        Storage::disk('local')->put($photoPath, 'fake-image-content');

        $journal = TeachingJournal::create([
            'teacher_id' => $guru->id,
            'schedule_id' => $schedule->id,
            'date' => now()->toDateString(),
            'materi' => 'Materi Foto',
            'activity_summary' => 'Ringkasan Foto',
            'photo_proof' => $photoPath,
        ]);

        $studentRole = Role::where('name', 'siswa')->firstOrFail();
        $parentRole = Role::where('name', 'orang_tua')->firstOrFail();

        $ownStudent = User::create([
            'name' => 'Siswa Pemilik Foto',
            'login_key' => 'SISWA_FOTO_1',
            'email' => 'siswa_foto_1@school.test',
            'password' => bcrypt('password123'),
            'role_id' => $studentRole->id,
            'class_id' => $class->id,
            'is_active' => true,
        ]);
        $otherStudent = User::create([
            'name' => 'Siswa Lain',
            'login_key' => 'SISWA_FOTO_2',
            'email' => 'siswa_foto_2@school.test',
            'password' => bcrypt('password123'),
            'role_id' => $studentRole->id,
            'class_id' => $class->id,
            'is_active' => true,
        ]);
        $parent = User::create([
            'name' => 'Ortu Pemilik Foto',
            'login_key' => 'ORTU_FOTO_1',
            'email' => 'ortu_foto_1@school.test',
            'password' => bcrypt('password123'),
            'role_id' => $parentRole->id,
            'is_active' => true,
        ]);
        $parent->students()->attach($ownStudent->id);

        Attendance::create([
            'schedule_id' => $schedule->id,
            'student_id' => $ownStudent->id,
            'teaching_journal_id' => $journal->id,
            'date' => now()->toDateString(),
            'status' => 'Hadir',
        ]);

        $url = route('journals.attachment', ['journal' => $journal->id, 'preview' => 1]);

        $this->actingAs($ownStudent)->get($url)->assertOk();
        $this->actingAs($parent)->get($url)->assertOk();
        $this->actingAs($otherStudent)->get($url)->assertNotFound();
    }

    public function test_teacher_can_only_view_their_own_inputted_schedules(): void
    {
        $guruRole = Role::where('name', 'guru')->firstOrFail();
        $guruA = User::create([
            'name' => 'Guru Pengajar A',
            'login_key' => 'GURU_A_999',
            'email' => 'guru_a_999@school.test',
            'password' => bcrypt('password123'),
            'role_id' => $guruRole->id,
            'is_active' => true,
        ]);
        $guruB = User::create([
            'name' => 'Guru Pengajar B',
            'login_key' => 'GURU_B_999',
            'email' => 'guru_b_999@school.test',
            'password' => bcrypt('password123'),
            'role_id' => $guruRole->id,
            'is_active' => true,
        ]);

        $classA = Classes::where('name', 'X RPL 1')->first() ?? Classes::firstOrFail();
        $classB = Classes::where('name', '!=', $classA->name)->firstOrFail();
        $subjectA = Subject::firstOrFail();
        $subjectB = Subject::where('id', '!=', $subjectA->id)->firstOrFail();
        $room = $this->getOrCreateRoom();
        $activeRound = Cycle::current()->active_round;

        // Jadwal milik Guru A
        Schedule::create([
            'class_id' => $classA->id,
            'subject_id' => $subjectA->id,
            'teacher_id' => $guruA->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '07:15:00',
            'end_time' => '08:35:00',
            'round' => $activeRound,
        ]);

        // Jadwal milik Guru B
        Schedule::create([
            'class_id' => $classB->id,
            'subject_id' => $subjectB->id,
            'teacher_id' => $guruB->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '08:35:00',
            'end_time' => '09:55:00',
            'round' => $activeRound,
        ]);

        // 1. Guru A membuka halaman jadwal: hanya melihat jadwal yang diinputnya sendiri (Guru A), bukan milik Guru B
        $resGuruA = $this->actingAs($guruA)->get(route('schedules.index'));
        $resGuruA->assertOk();
        $resGuruA->assertSee('Daftar Jadwal Mengajar Saya (Jadwal yang Sudah Diinput)');
        $resGuruA->assertSee('<td>' . $classA->name, false);
        $resGuruA->assertSee('Guru Pengajar A');
        $resGuruA->assertDontSee('<td>' . $classB->name, false);
        $resGuruA->assertDontSee('Guru Pengajar B');

        // 2. Guru A mencoba filter class_id milik Guru B: tetap tidak bisa melihat jadwal Guru B
        $resGuruATampered = $this->actingAs($guruA)->get(route('schedules.index', ['class_id' => $classB->id]));
        $resGuruATampered->assertOk();
        $resGuruATampered->assertDontSee('<td>' . $classB->name, false);
        $resGuruATampered->assertDontSee('Guru Pengajar B');

        // 3. Pastikan 60 data ruangan resmi tersedia lengkap di database & dropdown
        $this->assertSame(60, Room::count());
        $resGuruA->assertSee('Ruang 1');
        $resGuruA->assertSee('Ruang 32');
        $resGuruA->assertSee('Lab 1');
        $resGuruA->assertSee('Lab 22');
        $resGuruA->assertSee('Lab IPA');
        $resGuruA->assertSee('Hall');
        $resGuruA->assertSee('Ruang Siniar');
        $resGuruA->assertSee('Lapangan Olahraga 1 (OR1)');
        $resGuruA->assertSee('Lapangan Olahraga 2 (OR2)');
        $resGuruA->assertSee('Lapangan Olahraga 3 (OR3)');
    }
}
