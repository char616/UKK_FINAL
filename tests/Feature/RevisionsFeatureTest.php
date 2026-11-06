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
        return Room::firstOrCreate(['code' => 'R101'], ['name' => 'Ruang 101', 'capacity' => 36]);
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
        // Pastikan form tambah kelas, jurusan, ruangan tidak ada
        $response->assertDontSee('Tambah Jurusan');
        $response->assertDontSee('Tambah Kelas');
        $response->assertDontSee('Tambah Ruangan');
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

        // 2. Orang Tua Dashboard (mode orang tua pada akun siswa)
        $resOrtu = $this->actingAs($student)->withSession(['login_as' => 'orang_tua'])->get(route('dashboard.ortu'));
        $resOrtu->assertOk();
        $resOrtu->assertSee('Infografis Kehadiran');
        $resOrtu->assertSee($student->name);

        // 3. Wali Kelas Dashboard
        $resWali = $this->actingAs($wali)->get(route('dashboard.wali'));
        $resWali->assertOk();
        $resWali->assertSee('Infografis Kehadiran Kelas Binaan');
        $resWali->assertSee('Data Kehadiran Peserta Didik Per Anak');

        // 4. BK Dashboard
        $resBk = $this->actingAs($bk)->get(route('dashboard.bk'));
        $resBk->assertOk();
        $resBk->assertSee('Infografis Presensi Tingkat Sekolah');
        $resBk->assertSee('Pemantauan Presensi Per Siswa');
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
}
