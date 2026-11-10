<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classes;
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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private int $userSequence = 0;

    private int $schoolDataSequence = 0;

    public function test_database_seeder_creates_requested_accounts_classes_and_rooms_idempotently(): void
    {
        $this->assertSame(0, Artisan::call('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--no-interaction' => true,
        ]));

        $temporaryAccount = User::query()->where('login_key', 'BK_1')->firstOrFail();
        $temporaryAccount->update([
            'password' => Hash::make('user-changed-password'),
            'must_change_password' => false,
        ]);

        $this->assertSame(0, Artisan::call('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--no-interaction' => true,
        ]));

        $this->assertDatabaseCount('users', 18);
        $this->assertDatabaseCount('classes', 6);
        $this->assertDatabaseCount('rooms', 6);
        $this->assertDatabaseCount('subjects', 3);

        $expectedClasses = [
            '10 RPL 1' => 'WALI_10_RPL_1',
            '10 RPL 2' => 'WALI_10_RPL_2',
            '11 RPL 1' => 'WALI_11_RPL_1',
            '11 RPL 2' => 'WALI_11_RPL_2',
            '12 RPL 1' => 'WALI_12_RPL_1',
            '12 RPL 2' => 'WALI_12_RPL_2',
        ];

        foreach ($expectedClasses as $className => $waliLoginKey) {
            $schoolClass = Classes::query()
                ->where('name', $className)
                ->with('homeroomTeacher')
                ->firstOrFail();

            $this->assertSame($waliLoginKey, $schoolClass->homeroomTeacher->login_key);
        }

        $this->assertSame(6, User::query()->whereHas('role', fn ($query) => $query->where('name', 'bk'))->count());
        $this->assertSame(6, User::query()->whereHas('role', fn ($query) => $query->where('name', 'wali_kelas'))->count());
        $this->assertSame(5, User::query()->whereHas('role', fn ($query) => $query->where('name', 'guru'))->count());
        $this->assertDatabaseHas('users', ['login_key' => 'WAKA001']);
        $this->assertDatabaseHas('users', ['login_key' => 'BK_6']);
        $this->assertDatabaseHas('users', ['login_key' => 'GURU_5']);
        $this->assertDatabaseHas('rooms', ['code' => 'RUANG-3', 'name' => 'Ruang 3']);
        $this->assertDatabaseHas('rooms', ['code' => 'LAB-RPL-3', 'name' => 'Lab RPL 3']);
        $this->assertDatabaseMissing('users', ['login_key' => 'BK001']);

        $this->assertTrue(Hash::check('user-changed-password', $temporaryAccount->fresh()->password));
        $this->assertFalse($temporaryAccount->fresh()->must_change_password);
    }

    public function test_waka_cannot_submit_or_assign_teacher_schedules(): void
    {
        $waka = $this->createUser('waka');
        $teacher = $this->createUser('guru');
        $schoolData = $this->createScheduleData(createSchedule: false);

        $this->actingAs($waka)->post(route('schedules.store'), [
            'schedules' => [[
                'class_id' => $schoolData['class']->id,
                'subject_id' => $schoolData['subject']->id,
                'room_id' => $schoolData['room']->id,
                'day' => 'Senin',
                'start_time' => '08:00',
                'end_time' => '09:00',
                'round' => 1,
                'teacher_id' => $teacher->id,
            ]],
        ])->assertForbidden();

        $this->assertDatabaseCount('schedules', 0);
    }

    public function test_teacher_can_submit_multiple_schedules_for_waka_to_review_in_one_submission(): void
    {
        $teacher = $this->createUser('guru');
        $waka = $this->createUser('waka');
        $first = $this->createScheduleData(createSchedule: false);
        $second = $this->createScheduleData(createSchedule: false);

        $this->actingAs($teacher)
            ->get(route('schedules.index'))
            ->assertOk()
            ->assertSee('data-add-schedule-row', false)
            ->assertSee('schedules[0][class_id]', false)
            ->assertSee('Kirim Semua Jadwal ke WAKA');

        $response = $this->actingAs($teacher)->post(route('schedules.store'), [
            'schedules' => [
                [
                    'class_id' => $first['class']->id,
                    'subject_id' => $first['subject']->id,
                    'room_id' => $first['room']->id,
                    'day' => 'Senin',
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'round' => 1,
                ],
                [
                    'class_id' => $second['class']->id,
                    'subject_id' => $second['subject']->id,
                    'room_id' => $second['room']->id,
                    'day' => 'Senin',
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'round' => 1,
                ],
            ],
        ]);

        $response->assertRedirect(route('schedules.index'));
        $this->assertDatabaseCount('schedules', 2);
        $this->assertDatabaseHas('schedules', [
            'teacher_id' => $teacher->id,
            'class_id' => $first['class']->id,
            'subject_id' => $first['subject']->id,
            'room_id' => $first['room']->id,
            'start_time' => '08:00',
        ]);
        $this->assertDatabaseHas('schedules', [
            'teacher_id' => $teacher->id,
            'class_id' => $second['class']->id,
            'subject_id' => $second['subject']->id,
            'room_id' => $second['room']->id,
            'start_time' => '09:00',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $waka->id,
            'title' => 'Pengajuan Jadwal Guru',
            'message' => $teacher->name.' mengirim 2 jadwal baru untuk ditinjau. Jadwal sudah tersedia pada daftar jadwal.',
        ]);

        $this->actingAs($waka)
            ->get(route('schedules.index'))
            ->assertOk()
            ->assertSee($first['subject']->name)
            ->assertSee($second['subject']->name)
            ->assertSee($teacher->name);
    }

    public function test_teacher_can_submit_a_complete_schedule_with_more_than_fifty_rows(): void
    {
        $teacher = $this->createUser('guru');
        $schedules = [];

        for ($index = 0; $index < 51; $index++) {
            $schoolData = $this->createScheduleData(createSchedule: false);
            $dayIndex = $index % 5;
            $roundIndex = intdiv($index, 30);
            $hour = 8 + intdiv($index % 30, 5);
            $schoolData['class']->update(['phase' => $roundIndex === 0 ? 'ganjil' : 'genap']);

            $schedules[] = [
                'class_id' => $schoolData['class']->id,
                'subject_id' => $schoolData['subject']->id,
                'room_id' => $schoolData['room']->id,
                'day' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'][$dayIndex],
                'start_time' => sprintf('%02d:00', $hour),
                'end_time' => sprintf('%02d:00', $hour + 1),
                'round' => $roundIndex + 1,
            ];
        }

        $response = $this->actingAs($teacher)->post(route('schedules.store'), [
            'schedules' => $schedules,
        ]);

        $response->assertRedirect(route('schedules.index'));
        $this->assertDatabaseCount('schedules', 51);
    }

    public function test_waka_can_change_the_global_active_round_for_all_users(): void
    {
        $waka = $this->createUser('waka');
        $teacher = $this->createUser('guru');
        $anotherTeacher = $this->createUser('guru');
        $firstRound = $this->createScheduleData($teacher);
        $secondRound = $this->createScheduleData($teacher, createSchedule: false);
        $secondRoundSchedule = Schedule::create([
            'teacher_id' => $teacher->id,
            'class_id' => $secondRound['class']->id,
            'subject_id' => $secondRound['subject']->id,
            'room_id' => $secondRound['room']->id,
            'day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'round' => 2,
        ]);

        $this->actingAs($waka)
            ->get(route('schedules.index'))
            ->assertOk()
            ->assertSee('Putaran aktif semua pengguna')
            ->assertSee('Terapkan Global');

        $response = $this->actingAs($waka)->put(route('schedules.update-active-round'), [
            'active_round' => 2,
        ]);

        $response->assertRedirect(route('schedules.index'));
        $this->assertDatabaseHas('cycles', [
            'id' => 1,
            'active_round' => 2,
        ]);
        $this->assertDatabaseHas('schedules', [
            'id' => $firstRound['schedule']->id,
            'teacher_id' => $teacher->id,
            'day' => 'Senin',
            'start_time' => '08:00',
            'round' => 1,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $teacher->id,
            'title' => 'Putaran Jadwal Sekolah Diperbarui',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $anotherTeacher->id,
            'title' => 'Putaran Jadwal Sekolah Diperbarui',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $waka->id,
            'action' => 'UPDATE_ACTIVE_SCHEDULE_ROUND',
        ]);

        $this->actingAs($teacher)
            ->get(route('schedules.index'))
            ->assertOk()
            ->assertSee('<td>'.$secondRound['class']->name, false)
            ->assertDontSee('<td>'.$firstRound['class']->name, false)
            ->assertSee('Putaran 2');

        $student = $this->createUser('siswa');
        $student->update(['class_id' => $secondRound['class']->id]);

        $this->actingAs($student)
            ->get(route('schedules.index'))
            ->assertOk()
            ->assertSee($secondRoundSchedule->subject->name)
            ->assertDontSee($firstRound['class']->name);
    }

    public function test_waka_cannot_set_an_invalid_global_active_round(): void
    {
        $waka = $this->createUser('waka');

        $this->actingAs($waka)->put(route('schedules.update-active-round'), [
            'active_round' => 3,
        ])->assertSessionHasErrors('active_round');

        $this->assertDatabaseHas('cycles', [
            'id' => 1,
            'active_round' => 1,
        ]);
    }

    public function test_teacher_cannot_change_the_global_active_round(): void
    {
        $teacher = $this->createUser('guru');

        $this->actingAs($teacher)->put(route('schedules.update-active-round'), [
            'active_round' => 2,
        ])->assertForbidden();

        $this->assertDatabaseHas('cycles', [
            'id' => 1,
            'active_round' => 1,
        ]);
    }

    public function test_attendance_and_journal_forms_only_use_the_global_active_round(): void
    {
        $teacher = $this->createUser('guru');
        $activeRound = $this->createScheduleData($teacher);
        $inactiveRound = $this->createScheduleData($teacher, createSchedule: false);
        $inactiveSchedule = Schedule::create([
            'teacher_id' => $teacher->id,
            'class_id' => $inactiveRound['class']->id,
            'subject_id' => $inactiveRound['subject']->id,
            'room_id' => $inactiveRound['room']->id,
            'day' => 'Selasa',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'round' => 2,
        ]);

        $this->actingAs($teacher)
            ->get(route('attendances.index'))
            ->assertOk()
            ->assertSee($activeRound['class']->name)
            ->assertDontSee($inactiveRound['class']->name);

        $this->get(route('journals.index'))
            ->assertOk()
            ->assertSee($activeRound['class']->name)
            ->assertDontSee($inactiveRound['class']->name);

        $this->post(route('attendances.store', $inactiveSchedule), [
            'attendances' => [1 => 'Hadir'],
        ])->assertNotFound();

        $this->post(route('journals.store'), [
            'schedule_id' => $inactiveSchedule->id,
            'date' => now()->toDateString(),
            'materi' => 'Materi putaran tidak aktif',
            'summary' => 'Tidak boleh dicatat pada putaran yang tidak aktif.',
        ])->assertNotFound();
    }

    public function test_waka_dashboard_only_shows_the_requested_management_features(): void
    {
        $waka = $this->createUser('waka');

        $this->actingAs($waka)
            ->get(route('dashboard.waka'))
            ->assertOk()
            ->assertSee('Jadwal & penguncian', false)
            ->assertSee('Manajemen Data')
            ->assertSee('Jurnal mengajar')
            ->assertSee('Audit log')
            ->assertSee('Pengaturan akun')
            ->assertDontSee('Rekap Presensi')
            ->assertDontSee('Izin & Sakit')
            ->assertDontSee('Kelola akun')
            ->assertSeeInOrder([
                'SCP System',
                'Panel sekolah',
                'Dashboard',
                'Jadwal Pelajaran',
                'Jurnal Mengajar',
                'Manajemen Data',
                'Audit Log',
                'Pengaturan Akun',
                'User 1',
                'Waka',
                'Logout',
            ]);
    }

    public function test_waka_cannot_open_attendance_management(): void
    {
        $waka = $this->createUser('waka');

        $this->actingAs($waka)->get(route('attendances.index'))->assertForbidden();
    }

    public function test_waka_cannot_open_leave_management(): void
    {
        $waka = $this->createUser('waka');

        $this->actingAs($waka)->get(route('leaves.index'))->assertForbidden();
    }

    public function test_waka_cannot_open_attendance_reports(): void
    {
        $waka = $this->createUser('waka');

        $this->actingAs($waka)->get(route('reports.attendance'))->assertForbidden();
    }

    public function test_waka_cannot_open_the_general_notification_inbox(): void
    {
        $waka = $this->createUser('waka');

        $this->actingAs($waka)->get(route('notifications.index'))->assertForbidden();
    }

    public function test_waka_can_send_one_journal_reminder_to_each_teacher_with_missing_journals(): void
    {
        $waka = $this->createUser('waka');
        $teacher = $this->createUser('guru');
        $schoolData = $this->createScheduleData($teacher);
        $date = now()->startOfWeek()->toDateString();
        $formattedDate = \Carbon\Carbon::parse($date)->format('d-m-Y');
        $message = 'Mohon lengkapi jurnal mengajar untuk tanggal '.$formattedDate.'.';

        $response = $this->actingAs($waka)->post(route('journals.reminders'), [
            'date' => $date,
        ]);

        $response->assertRedirect(route('journals.index', ['date' => $date]));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $teacher->id,
            'title' => 'Pengingat Jurnal Mengajar',
            'message' => $message,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $waka->id,
            'action' => 'SEND_MISSING_JOURNAL_REMINDERS',
        ]);

        $this->actingAs($waka)->post(route('journals.reminders'), [
            'date' => $date,
        ])->assertRedirect(route('journals.index', ['date' => $date]));

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_waka_does_not_send_journal_reminders_when_journals_are_complete(): void
    {
        $waka = $this->createUser('waka');
        $teacher = $this->createUser('guru');
        $schoolData = $this->createScheduleData($teacher);
        $date = now()->startOfWeek()->toDateString();
        TeachingJournal::create([
            'schedule_id' => $schoolData['schedule']->id,
            'teacher_id' => $teacher->id,
            'date' => $date,
            'materi' => 'Materi pembelajaran',
            'activity_summary' => 'Kegiatan pembelajaran selesai.',
        ]);

        $response = $this->actingAs($waka)->post(route('journals.reminders'), [
            'date' => $date,
        ]);

        $response->assertRedirect(route('journals.index', ['date' => $date]));
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_waka_can_view_but_cannot_edit_teacher_journals(): void
    {
        $waka = $this->createUser('waka');
        $teacher = $this->createUser('guru');
        $schoolData = $this->createScheduleData($teacher);
        $journal = TeachingJournal::create([
            'schedule_id' => $schoolData['schedule']->id,
            'teacher_id' => $teacher->id,
            'date' => now()->toDateString(),
            'materi' => 'Materi awal',
            'activity_summary' => 'Ringkasan awal.',
        ]);

        $this->actingAs($waka)->get(route('journals.index'))
            ->assertOk()
            ->assertSee('Materi awal');

        $this->put(route('journals.update', $journal), [
            'date' => now()->toDateString(),
            'materi' => 'Materi diubah Waka',
            'summary' => 'Ringkasan diubah Waka.',
        ])->assertForbidden();

        $this->assertDatabaseHas('teaching_journals', [
            'id' => $journal->id,
            'materi' => 'Materi awal',
        ]);
    }

    public function test_teacher_batch_with_internal_schedule_conflict_is_rejected_without_saving_rows(): void
    {
        $teacher = $this->createUser('guru');
        $first = $this->createScheduleData(createSchedule: false);
        $second = $this->createScheduleData(createSchedule: false);

        $this->actingAs($teacher)->post(route('schedules.store'), [
            'schedules' => [
                [
                    'class_id' => $first['class']->id,
                    'subject_id' => $first['subject']->id,
                    'room_id' => $first['room']->id,
                    'day' => 'Senin',
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'round' => 1,
                ],
                [
                    'class_id' => $second['class']->id,
                    'subject_id' => $second['subject']->id,
                    'room_id' => $second['room']->id,
                    'day' => 'Senin',
                    'start_time' => '08:30',
                    'end_time' => '09:30',
                    'round' => 1,
                ],
            ],
        ])->assertSessionHasErrors('schedules.1.start_time');

        $this->assertDatabaseCount('schedules', 0);
    }

    public function test_teacher_cannot_submit_schedule_after_waka_locks_schedule(): void
    {
        $teacher = $this->createUser('guru');
        $waka = $this->createUser('waka');
        $schoolData = $this->createScheduleData(createSchedule: false);

        $this->actingAs($waka)
            ->post(route('schedules.toggle-lock'))
            ->assertRedirect();

        $this->actingAs($teacher)->post(route('schedules.store'), [
            'schedules' => [[
                'class_id' => $schoolData['class']->id,
                'subject_id' => $schoolData['subject']->id,
                'room_id' => $schoolData['room']->id,
                'day' => 'Senin',
                'start_time' => '08:00',
                'end_time' => '09:00',
                'round' => 1,
            ]],
        ])->assertSessionHasErrors('schedules');

        $this->assertDatabaseCount('schedules', 0);
    }

    public function test_teacher_can_only_record_attendance_for_their_schedule_and_valid_students(): void
    {
        $teacher = $this->createUser('guru');
        $otherTeacher = $this->createUser('guru');
        $student = $this->createUser('siswa');
        $nonStudent = $this->createUser('bk');
        $schedule = $this->createScheduleData($otherTeacher)['schedule'];

        $this->actingAs($teacher)
            ->post(route('attendances.store', $schedule), [
                'attendances' => [$student->id => 'Alpa'],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('attendances', 0);

        $ownedSchedule = $this->createScheduleData($teacher)['schedule'];
        $student->update(['class_id' => $ownedSchedule->class_id]);

        $this->actingAs($teacher)
            ->post(route('attendances.store', $ownedSchedule), [
                'attendances' => [$nonStudent->id => 'Hadir'],
            ])
            ->assertSessionHasErrors('attendances');

        $studentInAnotherClass = $this->createUser('siswa');
        $otherClass = $this->createScheduleData(createSchedule: false)['class'];
        $studentInAnotherClass->update(['class_id' => $otherClass->id]);

        $this->post(route('attendances.store', $ownedSchedule), [
            'attendances' => [
                $student->id => 'Hadir',
                $studentInAnotherClass->id => 'Alpa',
            ],
        ])->assertSessionHasErrors('attendances');

        $this->assertDatabaseCount('attendances', 0);

        $this->post(route('attendances.store', $ownedSchedule), [
            'attendances' => [$student->id => 'Alpa'],
        ])->assertRedirect(route('attendances.index'));

        $this->assertDatabaseHas('attendances', [
            'schedule_id' => $ownedSchedule->id,
            'student_id' => $student->id,
            'status' => 'Alpa',
            'recorded_by' => $teacher->id,
        ]);
        $this->assertTrue(Attendance::query()->firstOrFail()->recordedBy->is($teacher));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'title' => 'Informasi Presensi',
        ]);
    }

    public function test_wali_kelas_can_only_view_attendance_and_schedules_for_assigned_classes(): void
    {
        $wali = $this->createUser('wali_kelas');
        $ownStudent = $this->createUser('siswa');
        $otherStudent = $this->createUser('siswa');
        $ownData = $this->createScheduleData();
        $otherData = $this->createScheduleData();
        $ownData['class']->update(['homeroom_teacher_id' => $wali->id]);
        $ownStudent->update(['class_id' => $ownData['class']->id]);
        $otherStudent->update(['class_id' => $otherData['class']->id]);
        Attendance::create([
            'schedule_id' => $ownData['schedule']->id,
            'student_id' => $ownStudent->id,
            'date' => '2026-10-26',
            'status' => 'Hadir',
        ]);
        Attendance::create([
            'schedule_id' => $otherData['schedule']->id,
            'student_id' => $otherStudent->id,
            'date' => '2026-10-26',
            'status' => 'Alpa',
        ]);
        LeaveRequest::create([
            'student_id' => $ownStudent->id,
            'type' => 'Izin',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-26',
            'reason' => 'Keperluan kelas wali',
            'status' => 'Pending',
        ]);
        LeaveRequest::create([
            'student_id' => $otherStudent->id,
            'type' => 'Izin',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-26',
            'reason' => 'Keperluan kelas lain',
            'status' => 'Pending',
        ]);

        $this->actingAs($wali)
            ->get(route('attendances.index'))
            ->assertOk()
            ->assertSee($ownStudent->name)
            ->assertDontSee($otherStudent->name);

        $this->get(route('schedules.index'))
            ->assertOk()
            ->assertSee($ownData['subject']->name)
            ->assertDontSee($otherData['subject']->name)
            ->assertDontSee($otherData['class']->name);

        $this->get(route('leaves.index'))
            ->assertOk()
            ->assertSee('Keperluan kelas wali')
            ->assertDontSee('Keperluan kelas lain');
    }

    public function test_resubmitting_attendance_updates_the_existing_daily_record(): void
    {
        $this->travelTo('2026-10-25 08:00:00');
        $teacher = $this->createUser('guru');
        $student = $this->createUser('siswa');
        $schedule = $this->createScheduleData($teacher)['schedule'];
        $student->update(['class_id' => $schedule->class_id]);

        $this->actingAs($teacher)->post(route('attendances.store', $schedule), [
            'attendances' => [$student->id => 'Alpa'],
        ])->assertRedirect(route('attendances.index'));

        $this->post(route('attendances.store', $schedule), [
            'attendances' => [$student->id => 'Hadir'],
        ])->assertRedirect(route('attendances.index'));

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', [
            'schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'date' => '2026-10-25',
            'status' => 'Hadir',
            'recorded_by' => $teacher->id,
        ]);
    }

    public function test_approving_leave_updates_attendance_for_each_active_class_subject_and_date(): void
    {
        $this->travelTo('2026-10-26 08:00:00');
        $wali = $this->createUser('wali_kelas');
        $schoolData = $this->createScheduleData();
        $schoolClass = $schoolData['class'];
        $schedule = $schoolData['schedule'];
        $student = $this->createUser('siswa');
        $schoolClass->update(['homeroom_teacher_id' => $wali->id]);
        $student->update(['class_id' => $schoolClass->id]);

        $tuesdaySchedule = Schedule::create([
            'teacher_id' => $schedule->teacher_id,
            'class_id' => $schoolClass->id,
            'subject_id' => $schoolData['subject']->id,
            'room_id' => $schoolData['room']->id,
            'day' => 'Selasa',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'round' => 1,
        ]);
        $inactiveRoundSchedule = Schedule::create([
            'teacher_id' => $schedule->teacher_id,
            'class_id' => $schoolClass->id,
            'subject_id' => $schoolData['subject']->id,
            'room_id' => $schoolData['room']->id,
            'day' => 'Senin',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'round' => 2,
        ]);
        Attendance::create([
            'schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'date' => '2026-10-26',
            'status' => 'Hadir',
            'notes' => 'Catatan lama',
        ]);
        $leave = LeaveRequest::create([
            'student_id' => $student->id,
            'type' => 'Sakit',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-27',
            'reason' => 'Demam',
            'status' => 'Pending',
        ]);

        $this->actingAs($wali)
            ->post(route('leaves.status', $leave), ['status' => 'Approved'])
            ->assertRedirect();

        $this->assertDatabaseCount('attendances', 2);
        $this->assertDatabaseHas('attendances', [
            'schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'date' => '2026-10-26',
            'status' => 'Sakit',
            'notes' => 'Demam',
            'recorded_by' => $wali->id,
        ]);
        $this->assertDatabaseHas('attendances', [
            'schedule_id' => $tuesdaySchedule->id,
            'student_id' => $student->id,
            'date' => '2026-10-27',
            'status' => 'Sakit',
            'notes' => 'Demam',
            'recorded_by' => $wali->id,
        ]);
        $this->assertDatabaseMissing('attendances', [
            'schedule_id' => $inactiveRoundSchedule->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($wali)
            ->get(route('attendances.index'))
            ->assertOk()
            ->assertSee('Demam');
    }

    public function test_leave_submission_and_approval_persist_workflow_metadata_and_notify_users(): void
    {
        $student = $this->createUser('siswa');
        $wali = $this->createUser('wali_kelas');
        $otherWali = $this->createUser('wali_kelas');
        $schoolClass = $this->createScheduleData(createSchedule: false)['class'];
        $schoolClass->update(['homeroom_teacher_id' => $wali->id]);
        $student->update(['class_id' => $schoolClass->id]);

        $this->actingAs($student)->post(route('leaves.store'), [
            'type' => 'Sakit',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-27',
            'reason' => 'Demam',
        ])->assertRedirect(route('leaves.index'));

        $leave = LeaveRequest::query()->firstOrFail();
        $this->assertSame('Sakit', $leave->type);
        $this->assertSame('Pending', $leave->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $wali->id,
            'title' => 'Pengajuan Izin Baru',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $otherWali->id,
            'title' => 'Pengajuan Izin Baru',
        ]);

        $this->actingAs($wali)->post(route('leaves.status', $leave), [
            'status' => 'Approved',
        ])->assertRedirect();

        $leave->refresh();
        $this->assertSame('Approved', $leave->status);
        $this->assertSame($wali->id, $leave->processed_by);
        $this->assertNotNull($leave->processed_at);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'title' => 'Status Izin Diperbarui',
            'message' => 'Pengajuan Sakit Anda telah Disetujui (Approved)',
        ]);

        $this->post(route('leaves.status', $leave), [
            'status' => 'Rejected',
        ])->assertSessionHasErrors('status');
        $this->assertSame('Approved', $leave->fresh()->status);
    }

    public function test_parent_can_only_access_their_childs_leave_records(): void
    {
        $parent = $this->createUser('orang_tua');
        $child = $this->createUser('siswa');
        $otherStudent = $this->createUser('siswa');
        $parent->students()->attach($child->id);
        $childLeave = LeaveRequest::create([
            'student_id' => $child->id,
            'type' => 'Izin',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-26',
            'reason' => 'Keperluan keluarga',
            'status' => 'Pending',
        ]);
        LeaveRequest::create([
            'student_id' => $otherStudent->id,
            'type' => 'Sakit',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-26',
            'reason' => 'Data siswa lain',
            'status' => 'Pending',
        ]);

        $this->actingAs($parent)
            ->get(route('leaves.index'))
            ->assertOk()
            ->assertSee('Ajukan Izin Baru')
            ->assertSee($childLeave->reason)
            ->assertDontSee('Data siswa lain');
    }

    public function test_parent_can_submit_leave_request_for_linked_child_and_not_other_student(): void
    {
        $parent = $this->createUser('orang_tua');
        $child = $this->createUser('siswa');
        $otherStudent = $this->createUser('siswa');
        $wali = $this->createUser('wali_kelas');
        $schoolClass = $this->createScheduleData(createSchedule: false)['class'];
        $schoolClass->update(['homeroom_teacher_id' => $wali->id]);
        $child->update(['class_id' => $schoolClass->id]);
        $otherStudent->update(['class_id' => $schoolClass->id]);
        $parent->students()->attach($child->id);

        $this->actingAs($parent)
            ->get(route('leaves.create'))
            ->assertOk()
            ->assertSee($child->name)
            ->assertDontSee($otherStudent->name);

        $this->actingAs($parent)->post(route('leaves.store'), [
            'student_id' => $otherStudent->id,
            'type' => 'Izin',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-26',
            'reason' => 'Izin tidak sah',
        ])->assertSessionHasErrors('student_id');

        $this->assertDatabaseCount('leave_requests', 0);

        $this->actingAs($parent)->post(route('leaves.store'), [
            'student_id' => $child->id,
            'type' => 'Izin',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-27',
            'reason' => 'Acara keluarga di luar kota',
        ])->assertRedirect(route('leaves.index'));

        $leave = LeaveRequest::query()->firstOrFail();
        $this->assertSame($child->id, $leave->student_id);
        $this->assertSame('Izin', $leave->type);
        $this->assertSame('Pending', $leave->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $wali->id,
            'title' => 'Pengajuan Izin Baru',
        ]);

        $this->actingAs($wali)->post(route('leaves.status', $leave), [
            'status' => 'Approved',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $child->id,
            'title' => 'Status Izin Diperbarui',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $parent->id,
            'title' => 'Status Izin Diperbarui',
        ]);
    }

    public function test_leave_attachment_is_stored_privately_and_only_available_to_related_users(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $student = $this->createUser('siswa');
        $wali = $this->createUser('wali_kelas');
        $schoolClass = $this->createScheduleData(createSchedule: false)['class'];
        $schoolClass->update(['homeroom_teacher_id' => $wali->id]);
        $student->update(['class_id' => $schoolClass->id]);

        $this->actingAs($student)->post(route('leaves.store'), [
            'type' => 'Sakit',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-26',
            'reason' => 'Demam',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('leaves.index'));

        $leave = LeaveRequest::query()->firstOrFail();
        Storage::disk('local')->assertExists($leave->attachment);
        Storage::disk('public')->assertMissing($leave->attachment);
        $parent = $this->createUser('orang_tua');
        $parent->students()->attach($student->id);

        $this->get(route('leaves.attachment', $leave))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename='.basename($leave->attachment));

        $outsider = $this->createUser('siswa');
        $this->actingAs($outsider)
            ->get(route('leaves.attachment', $leave))
            ->assertNotFound();

        $this->actingAs($wali)
            ->get(route('leaves.attachment', $leave))
            ->assertOk();

        $this->actingAs($parent)
            ->get(route('leaves.attachment', $leave))
            ->assertOk();
    }

    public function test_legacy_public_leave_attachments_can_be_moved_to_private_storage(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $student = $this->createUser('siswa');
        $path = 'leave_attachments/legacy-proof.pdf';
        Storage::disk('public')->put($path, 'existing private evidence');
        LeaveRequest::create([
            'student_id' => $student->id,
            'type' => 'Sakit',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-26',
            'reason' => 'Demam',
            'attachment' => $path,
            'status' => 'Pending',
        ]);

        $exitCode = Artisan::call('scp:move-leave-attachments-private');

        $this->assertSame(0, $exitCode);
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_wali_kelas_can_only_approve_leave_for_an_assigned_class(): void
    {
        $wali = $this->createUser('wali_kelas');
        $student = $this->createUser('siswa');
        $schoolClass = $this->createScheduleData(createSchedule: false)['class'];
        $schoolClass->update(['homeroom_teacher_id' => $wali->id]);
        $student->update(['class_id' => $schoolClass->id]);
        $leave = LeaveRequest::create([
            'student_id' => $student->id,
            'type' => 'Izin',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-26',
            'reason' => 'Urusan keluarga',
            'status' => 'Pending',
        ]);
        $otherWali = $this->createUser('wali_kelas');

        $this->actingAs($otherWali)
            ->post(route('leaves.status', $leave), ['status' => 'Approved'])
            ->assertNotFound();

        $this->assertSame('Pending', $leave->fresh()->status);
    }

    public function test_bk_can_view_attendance_history_but_cannot_access_leaves_or_schedules(): void
    {
        $bk = $this->createUser('bk');

        $this->actingAs($bk)
            ->get(route('attendances.index'))
            ->assertOk();

        $this->get(route('reports.attendance'))->assertOk();
        $this->get(route('leaves.index'))->assertForbidden();
        $this->get(route('schedules.index'))->assertForbidden();
    }

    public function test_waka_can_assign_a_student_to_a_class_and_guardian(): void
    {
        $waka = $this->createUser('waka');
        $student = $this->createUser('siswa');
        $parent = $this->createUser('orang_tua');
        $schoolClass = $this->createScheduleData(createSchedule: false)['class'];

        $this->actingAs($waka)
            ->put(route('master-data.students.assignment', $student), [
                'class_id' => $schoolClass->id,
                'parent_ids' => [$parent->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'class_id' => $schoolClass->id,
        ]);
        $this->assertDatabaseHas('parent_student', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
        ]);

        $this->put(route('master-data.students.assignment', $student), [
            'class_id' => $schoolClass->id,
            'parent_ids' => [$waka->id],
        ])->assertSessionHasErrors('parent_ids');
    }

    public function test_waka_can_manage_assignments_and_other_roles_cannot(): void
    {
        $waka = $this->createUser('waka');
        $wali = $this->createUser('wali_kelas');
        $schoolClass = $this->createScheduleData(createSchedule: false)['class'];

        $this->actingAs($waka)
            ->get(route('master-data.index'))
            ->assertOk()
            ->assertSee('Penugasan Kelas dan Wali');

        $this->put(route('master-data.classes.homeroom-teacher', $schoolClass), [
            'homeroom_teacher_id' => $wali->id,
        ])->assertRedirect();

        $this->assertSame($wali->id, $schoolClass->fresh()->homeroom_teacher_id);

        $this->actingAs($wali)
            ->put(route('master-data.classes.homeroom-teacher', $schoolClass), [
                'homeroom_teacher_id' => null,
            ])
            ->assertForbidden();
    }

    public function test_a_wali_kelas_account_cannot_be_assigned_to_multiple_classes(): void
    {
        $waka = $this->createUser('waka');
        $wali = $this->createUser('wali_kelas');
        $firstClass = $this->createScheduleData(createSchedule: false)['class'];
        $secondClass = $this->createScheduleData(createSchedule: false)['class'];

        $this->actingAs($waka)->put(route('master-data.classes.homeroom-teacher', $firstClass), [
            'homeroom_teacher_id' => $wali->id,
        ])->assertRedirect();

        $this->put(route('master-data.classes.homeroom-teacher', $secondClass), [
            'homeroom_teacher_id' => $wali->id,
        ])->assertSessionHasErrors('homeroom_teacher_id');

        $this->assertSame($wali->id, $firstClass->fresh()->homeroom_teacher_id);
        $this->assertNull($secondClass->fresh()->homeroom_teacher_id);
    }

    public function test_parent_attendance_and_schedule_views_are_limited_to_linked_children(): void
    {
        $parent = $this->createUser('orang_tua');
        $child = $this->createUser('siswa');
        $otherStudent = $this->createUser('siswa');
        $childSchedule = $this->createScheduleData();
        $otherSchedule = $this->createScheduleData();
        $child->update([
            'class_id' => $childSchedule['class']->id,
        ]);
        $parent->students()->attach($child->id);
        $otherStudent->update(['class_id' => $otherSchedule['class']->id]);
        Attendance::create([
            'schedule_id' => $childSchedule['schedule']->id,
            'student_id' => $child->id,
            'date' => '2026-10-25',
            'status' => 'Hadir',
        ]);
        Attendance::create([
            'schedule_id' => $otherSchedule['schedule']->id,
            'student_id' => $otherStudent->id,
            'date' => '2026-10-25',
            'status' => 'Alpa',
        ]);

        $this->actingAs($parent)
            ->get(route('attendances.index'))
            ->assertOk()
            ->assertSee($child->name)
            ->assertDontSee($otherStudent->name);

        $this->get(route('schedules.index'))
            ->assertOk()
            ->assertSee($childSchedule['class']->name)
            ->assertDontSee($otherSchedule['class']->name);
    }

    public function test_teacher_can_create_and_update_only_their_teaching_journal(): void
    {
        $teacher = $this->createUser('guru');
        $otherTeacher = $this->createUser('guru');
        $schedule = $this->createScheduleData($teacher)['schedule'];
        $otherSchedule = $this->createScheduleData($otherTeacher)['schedule'];

        $this->actingAs($teacher)->post(route('journals.store'), [
            'schedule_id' => $schedule->id,
            'date' => '2026-10-25',
            'materi' => 'Persamaan linear',
            'summary' => 'Pembelajaran dan latihan soal.',
        ])->assertRedirect(route('journals.index'));

        $journal = TeachingJournal::query()->firstOrFail();
        $this->assertDatabaseHas('teaching_journals', [
            'id' => $journal->id,
            'teacher_id' => $teacher->id,
            'schedule_id' => $schedule->id,
            'materi' => 'Persamaan linear',
            'activity_summary' => 'Pembelajaran dan latihan soal.',
        ]);

        $this->actingAs($otherTeacher)->put(route('journals.update', $journal), [
            'date' => '2026-10-25',
            'materi' => 'Perubahan tidak sah',
            'summary' => 'Tidak boleh diperbarui guru lain.',
        ])->assertForbidden();

        $this->actingAs($teacher)->put(route('journals.update', $journal), [
            'date' => '2026-10-25',
            'materi' => 'Persamaan kuadrat',
            'summary' => 'Materi diperbarui.',
        ])->assertRedirect(route('journals.index'));

        $this->assertDatabaseHas('teaching_journals', [
            'id' => $journal->id,
            'materi' => 'Persamaan kuadrat',
            'activity_summary' => 'Materi diperbarui.',
        ]);
    }

    public function test_journal_evidence_is_private_and_downloaded_only_by_its_teacher_or_waka(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $teacher = $this->createUser('guru');
        $otherTeacher = $this->createUser('guru');
        $waka = $this->createUser('waka');
        $schedule = $this->createScheduleData($teacher)['schedule'];

        $this->actingAs($teacher)->post(route('journals.store'), [
            'schedule_id' => $schedule->id,
            'date' => '2026-10-25',
            'materi' => 'Persamaan linear',
            'summary' => 'Latihan kelas.',
            'attachment' => UploadedFile::fake()->image('jurnal.png'),
        ])->assertRedirect(route('journals.index'));

        $journal = TeachingJournal::query()->firstOrFail();
        Storage::disk('local')->assertExists($journal->photo_proof);
        Storage::disk('public')->assertMissing($journal->photo_proof);

        $this->get(route('journals.attachment', $journal))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename='.basename($journal->photo_proof));

        $this->actingAs($otherTeacher)
            ->get(route('journals.attachment', $journal))
            ->assertNotFound();

        $this->actingAs($waka)
            ->get(route('journals.attachment', $journal))
            ->assertOk();
    }

    public function test_legacy_public_journal_evidence_can_be_moved_to_private_storage(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $teacher = $this->createUser('guru');
        $schedule = $this->createScheduleData($teacher)['schedule'];
        $path = 'journal_attachments/legacy-proof.pdf';
        Storage::disk('public')->put($path, 'existing journal evidence');
        TeachingJournal::create([
            'teacher_id' => $teacher->id,
            'schedule_id' => $schedule->id,
            'date' => '2026-10-25',
            'materi' => 'Materi',
            'activity_summary' => 'Ringkasan',
            'photo_proof' => $path,
        ]);

        $exitCode = Artisan::call('scp:move-journal-attachments-private');

        $this->assertSame(0, $exitCode);
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_inactive_account_is_logged_out_before_opening_an_authenticated_route(): void
    {
        $user = $this->createUser('siswa', active: false);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login_key');
    }

    private function createUser(string $roleName, bool $active = true): User
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => str($roleName)->replace('_', ' ')->title()->toString()]
        );
        $sequence = ++$this->userSequence;

        return User::create([
            'name' => 'User '.$sequence,
            'email' => 'user-'.$sequence.'@school.test',
            'login_key' => 'USER-'.$sequence,
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => $active,
        ]);
    }

    private function createScheduleData(?User $teacher = null, bool $createSchedule = true): array
    {
        $teacher ??= $this->createUser('guru');
        $sequence = ++$this->schoolDataSequence;
        $major = Major::create(['code' => 'MJR-'.$sequence, 'name' => 'Jurusan '.$sequence]);
        $class = Classes::create([
            'name' => 'Kelas '.$sequence,
            'major_id' => $major->id,
            'phase' => 'ganjil',
        ]);
        $subject = Subject::create(['code' => 'SUB-'.$sequence, 'name' => 'Mapel '.$sequence]);
        $room = Room::create([
            'code' => 'ROOM-'.$sequence,
            'name' => 'Ruangan '.$sequence,
            'capacity' => 30,
        ]);
        $schedule = $createSchedule ? Schedule::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'room_id' => $room->id,
            'day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'round' => 1,
        ]) : null;

        return compact('class', 'subject', 'room', 'schedule');
    }
}
