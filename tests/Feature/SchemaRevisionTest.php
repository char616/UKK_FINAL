<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classes;
use App\Models\JamPelajaran;
use App\Models\LeaveRequest;
use App\Models\Major;
use App\Models\Role;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use Database\Seeders\JamPelajaranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_users_dual_login_and_teacher_nip(): void
    {
        // 1. Pastikan hanya ada 5 role
        $roles = [
            ['name' => 'waka', 'display_name' => 'Admin / Waka'],
            ['name' => 'bk', 'display_name' => 'BK'],
            ['name' => 'wali_kelas', 'display_name' => 'Wali Kelas'],
            ['name' => 'guru', 'display_name' => 'Guru'],
            ['name' => 'siswa', 'display_name' => 'Siswa / Orang Tua'],
        ];
        foreach ($roles as $r) {
            Role::create($r);
        }
        $this->assertEquals(5, Role::count());

        // 2. Akun Siswa/Ortu dengan 2 metode login dalam 1 akun
        $studentRole = Role::where('name', 'siswa')->firstOrFail();
        $studentUser = User::create([
            'name' => 'Budi Pratama',
            'login_key' => '17756',
            'nis' => '17756',
            'parent_name' => 'Joko Santoso',
            'parent_login_key' => 'budi_pratama_joko_santoso',
            'email' => 'budi@school.test',
            'password' => Hash::make('password123'),
            'role_id' => $studentRole->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        // Login Metode A: Siswa login dengan NIS
        $respStudent = $this->post('/login', [
            'login_key' => '17756',
            'password' => 'password123',
        ]);
        $respStudent->assertRedirect(route('dashboard.siswa'));
        $this->assertEquals('siswa', session('login_as'));
        $this->assertEquals('siswa', $studentUser->fresh()->last_login_as);

        $this->post('/logout');

        // Login Metode B: Orang Tua login dengan format nama_siswa_nama_ortu
        $respParent = $this->post('/login', [
            'login_key' => 'budi_pratama_joko_santoso',
            'password' => 'password123',
        ]);
        $respParent->assertRedirect(route('dashboard.ortu'));
        $this->assertEquals('orang_tua', session('login_as'));
        $this->assertEquals('orang_tua', $studentUser->fresh()->last_login_as);

        // 3. Data Guru: NIP fleksibel
        $guruRole = Role::where('name', 'guru')->firstOrFail();
        $guru = User::create([
            'name' => 'Guru Satu',
            'login_key' => 'GURU_1',
            'nip' => '1',
            'email' => 'guru1@school.test',
            'password' => Hash::make('password123'),
            'role_id' => $guruRole->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $this->assertEquals('1', $guru->nip);

        // Update NIP nanti secara fleksibel
        $guru->update(['nip' => '198501012010011001']);
        $this->assertEquals('198501012010011001', $guru->fresh()->nip);
    }

    public function test_jam_pelajaran_table_structure_and_seeder(): void
    {
        $this->assertTrue(Schema::hasTable('jam_pelajaran'));
        $this->assertTrue(Schema::hasColumns('jam_pelajaran', [
            'id', 'hari', 'nama_sesi', 'jam_mulai', 'jam_selesai', 'is_istirahat', 'urutan'
        ]));

        $this->seed(JamPelajaranSeeder::class);

        // Periksa sesi hari Senin: Upacara (is_istirahat: true) dan Jam 1 (is_istirahat: false)
        $upacara = JamPelajaran::where('hari', 'Senin')->where('nama_sesi', 'Upacara')->first();
        $this->assertNotNull($upacara);
        $this->assertTrue($upacara->is_istirahat);

        $jam1 = JamPelajaran::where('hari', 'Senin')->where('nama_sesi', 'Jam 1')->first();
        $this->assertNotNull($jam1);
        $this->assertFalse($jam1->is_istirahat);
    }

    public function test_teaching_journal_and_attendances_unified_relation(): void
    {
        $this->assertTrue(Schema::hasColumns('teaching_journals', [
            'materi', 'catatan_kejadian', 'foto_bukti_kbm', 'jam_pelajaran_id'
        ]));
        $this->assertTrue(Schema::hasColumn('attendances', 'teaching_journal_id'));

        $teacherRole = Role::create(['name' => 'guru', 'display_name' => 'Guru']);
        $studentRole = Role::create(['name' => 'siswa', 'display_name' => 'Siswa / Orang Tua']);

        $teacher = User::create([
            'name' => 'Guru Model',
            'login_key' => 'GURU_MODEL',
            'email' => 'guru_model@school.test',
            'password' => Hash::make('password123'),
            'role_id' => $teacherRole->id,
            'is_active' => true,
        ]);

        $major = Major::create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak']);
        $class = Classes::create(['name' => '10 RPL 1', 'major_id' => $major->id, 'phase' => 'ganjil']);
        $subject = Subject::create(['code' => 'WEB', 'name' => 'Pemrograman Web']);
        $room = Room::create(['code' => 'LAB-1', 'name' => 'Lab 1']);

        $student = User::create([
            'name' => 'Siswa A',
            'login_key' => '1001',
            'nis' => '1001',
            'email' => 'siswa_a@school.test',
            'password' => Hash::make('password123'),
            'role_id' => $studentRole->id,
            'class_id' => $class->id,
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '07:45:00',
            'end_time' => '09:15:00',
            'round' => 1,
        ]);

        // Simpan jurnal dan presensi dalam 1 kesatuan transaksi
        $journal = TeachingJournal::create([
            'schedule_id' => $schedule->id,
            'teacher_id' => $teacher->id,
            'date' => '2026-11-04',
            'materi' => 'Framework Laravel Migration',
            'catatan_kejadian' => 'Siswa sangat antusias saat praktik migrasi database.',
            'foto_bukti_kbm' => 'journal_photos/kbm_evidence_001.jpg',
            'activity_summary' => 'Membahas migrasi dan relasi database.',
        ]);

        $attendance = Attendance::create([
            'teaching_journal_id' => $journal->id,
            'schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'date' => '2026-11-04',
            'status' => 'Hadir',
            'notes' => 'Tepat waktu',
            'recorded_by' => $teacher->id,
        ]);

        $this->assertEquals($journal->id, $attendance->teachingJournal->id);
        $this->assertCount(1, $journal->attendances);
        $this->assertEquals('journal_photos/kbm_evidence_001.jpg', $journal->foto_bukti_kbm);
        $this->assertEquals('Siswa sangat antusias saat praktik migrasi database.', $journal->catatan_kejadian);
    }

    public function test_leave_requests_structure_and_rules(): void
    {
        $this->assertTrue(Schema::hasColumns('leave_requests', [
            'jenis_waktu_izin', 'jam_mulai_id', 'jam_selesai_id', 'file_bukti', 'target_approver', 'status', 'alasan_penolakan'
        ]));

        $studentRole = Role::create(['name' => 'siswa', 'display_name' => 'Siswa / Orang Tua']);
        $student = User::create([
            'name' => 'Siswa B',
            'login_key' => '1002',
            'nis' => '1002',
            'email' => 'siswa_b@school.test',
            'password' => Hash::make('password123'),
            'role_id' => $studentRole->id,
            'is_active' => true,
        ]);

        $jam1 = JamPelajaran::create([
            'hari' => 'Selasa',
            'nama_sesi' => 'Jam 1',
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '08:15:00',
            'is_istirahat' => false,
            'urutan' => 1,
        ]);

        $jam2 = JamPelajaran::create([
            'hari' => 'Selasa',
            'nama_sesi' => 'Jam 2',
            'jam_mulai' => '08:15:00',
            'jam_selesai' => '09:00:00',
            'is_istirahat' => false,
            'urutan' => 2,
        ]);

        // A. Izin Seharian -> Target approver Wali Kelas
        $leaveFullDay = LeaveRequest::create([
            'student_id' => $student->id,
            'type' => 'Sakit',
            'jenis_waktu_izin' => 'seharian',
            'start_date' => '2026-11-05',
            'end_date' => '2026-11-05',
            'reason' => 'Demam tinggi',
            'file_bukti' => 'leaves/surat_dokter.pdf',
            'target_approver' => 'wali_kelas',
            'status' => 'Pending',
        ]);

        $this->assertEquals('seharian', $leaveFullDay->jenis_waktu_izin);
        $this->assertEquals('wali_kelas', $leaveFullDay->target_approver);
        $this->assertEquals('leaves/surat_dokter.pdf', $leaveFullDay->file_bukti);

        // B. Izin Per Jam Pelajaran -> Target approver BK
        $leaveHourly = LeaveRequest::create([
            'student_id' => $student->id,
            'type' => 'Izin',
            'jenis_waktu_izin' => 'jam_pelajaran',
            'jam_mulai_id' => $jam1->id,
            'jam_selesai_id' => $jam2->id,
            'start_date' => '2026-11-05',
            'end_date' => '2026-11-05',
            'reason' => 'Ada dispensasi lomba',
            'file_bukti' => 'leaves/surat_dispensasi.pdf',
            'target_approver' => 'bk',
            'status' => 'Pending',
        ]);

        $this->assertEquals('jam_pelajaran', $leaveHourly->jenis_waktu_izin);
        $this->assertEquals('bk', $leaveHourly->target_approver);
        $this->assertEquals($jam1->id, $leaveHourly->jamMulai->id);
        $this->assertEquals($jam2->id, $leaveHourly->jamSelesai->id);

        // C. Status penolakan dengan alasan penolakan
        $leaveHourly->update([
            'status' => 'Rejected',
            'alasan_penolakan' => 'Surat dispensasi belum ditandatangani panitia penyelenggara.',
        ]);

        $this->assertEquals('Rejected', $leaveHourly->fresh()->status);
        $this->assertEquals('Surat dispensasi belum ditandatangani panitia penyelenggara.', $leaveHourly->fresh()->alasan_penolakan);
    }
}

