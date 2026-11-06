<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Major;
use App\Models\Role;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    public function test_bk_can_filter_attendance_report_by_date_and_class(): void
    {
        $this->travelTo('2026-10-25 12:00:00');
        $bk = $this->createUser('bk');
        $student = $this->createUser('siswa', 'Laporan Siswa A');
        $otherStudent = $this->createUser('siswa', 'Laporan Siswa B');
        $firstClass = $this->createClass('Kelas A');
        $secondClass = $this->createClass('Kelas B');
        $firstSchedule = $this->createSchedule($firstClass);
        $secondSchedule = $this->createSchedule($secondClass);
        $this->createAttendance($firstSchedule, $student, '2026-10-20', 'Hadir');
        $this->createAttendance($secondSchedule, $otherStudent, '2026-10-20', 'Alpa');
        $this->createAttendance($firstSchedule, $student, '2026-09-20', 'Sakit');

        $this->actingAs($bk)
            ->get(route('reports.attendance', [
                'date_from' => '2026-10-01',
                'date_to' => '2026-10-31',
                'class_id' => $firstClass->id,
            ]))
            ->assertOk()
            ->assertSee('Laporan Siswa A')
            ->assertDontSee('Laporan Siswa B')
            ->assertSee('Hadir')
            ->assertDontSee('2026-09-20');
    }

    public function test_homeroom_teacher_only_sees_assigned_classes_and_cannot_filter_another_class(): void
    {
        $wali = $this->createUser('wali_kelas');
        $student = $this->createUser('siswa', 'Siswa Kelas Wali');
        $otherStudent = $this->createUser('siswa', 'Siswa Kelas Lain');
        $assignedClass = $this->createClass('Kelas Wali');
        $otherClass = $this->createClass('Kelas Lain');
        $assignedClass->update(['homeroom_teacher_id' => $wali->id]);
        $assignedSchedule = $this->createSchedule($assignedClass);
        $otherSchedule = $this->createSchedule($otherClass);
        $this->createAttendance($assignedSchedule, $student, '2026-10-20', 'Hadir');
        $this->createAttendance($otherSchedule, $otherStudent, '2026-10-20', 'Alpa');

        $this->actingAs($wali)
            ->get(route('reports.attendance', [
                'date_from' => '2026-10-01',
                'date_to' => '2026-10-31',
            ]))
            ->assertOk()
            ->assertSee('Siswa Kelas Wali')
            ->assertDontSee('Siswa Kelas Lain');

        $this->get(route('reports.attendance', [
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-31',
            'class_id' => $otherClass->id,
        ]))->assertSessionHasErrors('class_id');
    }

    public function test_invalid_date_range_is_rejected(): void
    {
        $bk = $this->createUser('bk');

        $this->actingAs($bk)
            ->get(route('reports.attendance', [
                'date_from' => '2026-10-20',
                'date_to' => '2026-10-19',
            ]))
            ->assertSessionHasErrors('date_to');
    }

    public function test_student_cannot_access_attendance_reports(): void
    {
        $student = $this->createUser('siswa');

        $this->actingAs($student)
            ->get(route('reports.attendance'))
            ->assertForbidden();

        $this->get(route('reports.attendance.export'))
            ->assertForbidden();
    }

    public function test_csv_export_uses_report_filters_and_escapes_formula_values(): void
    {
        $bk = $this->createUser('bk');
        $student = $this->createUser('siswa', '=HYPERLINK("https://example.test")');
        $schoolClass = $this->createClass('Kelas Ekspor');
        $schedule = $this->createSchedule($schoolClass);
        $this->createAttendance($schedule, $student, '2026-10-20', 'Hadir', '=1+1');

        $response = $this->actingAs($bk)
            ->get(route('reports.attendance.export', [
                'date_from' => '2026-10-01',
                'date_to' => '2026-10-31',
            ]));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename=rekap-presensi-2026-10-01-sampai-2026-10-31.csv');

        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'=1+1", $csv);
        $this->assertStringContainsString('Kelas Ekspor', $csv);
    }

    private function createUser(string $roleName, string $name = 'Test User'): User
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => str($roleName)->replace('_', ' ')->title()->toString()]
        );
        $identifier = strtoupper($roleName).'-'.++$this->sequence;

        return User::create([
            'name' => $name,
            'email' => strtolower($identifier).'@school.test',
            'login_key' => $identifier,
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    private function createClass(string $name): Classes
    {
        $major = Major::create([
            'code' => 'MAJOR-'.++$this->sequence,
            'name' => 'Jurusan '.$name,
        ]);

        return Classes::create([
            'name' => $name,
            'major_id' => $major->id,
            'phase' => 'ganjil',
        ]);
    }

    private function createSchedule(Classes $schoolClass): Schedule
    {
        $teacher = $this->createUser('guru');
        $subject = Subject::create([
            'code' => 'SUB-'.++$this->sequence,
            'name' => 'Matematika',
        ]);
        $room = Room::create([
            'code' => 'ROOM-'.++$this->sequence,
            'name' => 'Ruang Kelas',
            'capacity' => 30,
        ]);

        return Schedule::create([
            'teacher_id' => $teacher->id,
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'round' => 1,
        ]);
    }

    private function createAttendance(Schedule $schedule, User $student, string $date, string $status, ?string $notes = null): void
    {
        Attendance::create([
            'schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'date' => $date,
            'status' => $status,
            'notes' => $notes,
        ]);
    }
}
