<?php

namespace Tests\Feature;

use App\Mail\OtpResetPassword;
use App\Mail\StudentAttendanceNotification;
use App\Models\Classes;
use App\Models\Major;
use App\Models\Role;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ScpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_and_login_api_endpoints_work(): void
    {
        $wakaRole = Role::create(['name' => 'waka', 'display_name' => 'WAKA']);
        User::create([
            'name' => 'Bapak Waka',
            'email' => 'waka@school.test',
            'login_key' => 'WAKA001',
            'password' => 'password123',
            'role_id' => $wakaRole->id,
            'is_active' => true,
        ]);

        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $loginResponse = $this->postJson('/api/auth/login', [
            'login_key' => 'WAKA001',
            'password' => 'password123',
            'device_name' => 'flutter-test',
        ])->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.login_key', 'WAKA001')
            ->assertJsonPath('data.role', 'waka')
            ->assertJsonPath('data.token_type', 'Bearer');

        $token = $loginResponse->json('data.token');
        $this->assertNotEmpty($token);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/profile')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.login_key', 'WAKA001');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.role', 'waka');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_sanctum_register_and_validation_error_format(): void
    {
        Role::create(['name' => 'siswa', 'display_name' => 'Siswa']);

        // Validation error must return 422 with status, message, errors, data
        $this->postJson('/api/auth/register', [
            'name' => 'Siswa Baru',
            'login_key' => 'INVALID_NISN',
            'role' => 'siswa',
            'password' => 'short',
        ])->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['status', 'message', 'errors', 'data']);

        $regResponse = $this->postJson('/api/auth/register', [
            'name' => 'Siswa Baru',
            'login_key' => '2026100088',
            'role' => 'siswa',
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ])->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.login_key', '2026100088');

        $this->assertNotEmpty($regResponse->json('data.token'));
    }

    public function test_mail_otp_and_attendance_notification_api_endpoints_send_emails(): void
    {
        Mail::fake();

        $this->postJson('/api/mail/test-otp', [
            'email' => 'uji.otp@school.test',
            'name' => 'Siswa Uji',
        ])->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.recipient_email', 'uji.otp@school.test');

        Mail::assertSent(OtpResetPassword::class, fn (OtpResetPassword $mail) => $mail->hasTo('uji.otp@school.test'));

        $this->postJson('/api/mail/test-attendance', [
            'email' => 'uji.ortu@school.test',
            'recipient_name' => 'Orang Tua Uji',
            'student_name' => 'Ahmad Fauzi',
            'class_name' => '10 RPL 1',
            'subject_name' => 'Pemrograman Web',
            'status' => 'Sakit',
        ])->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.attendance_status', 'Sakit');

        Mail::assertQueued(StudentAttendanceNotification::class, fn (StudentAttendanceNotification $mail) => $mail->hasTo('uji.ortu@school.test'));
    }

    public function test_leave_submission_and_full_mobile_workflow_via_bearer_token(): void
    {
        $wakaRole = Role::create(['name' => 'waka', 'display_name' => 'WAKA']);
        $guruRole = Role::create(['name' => 'guru', 'display_name' => 'Guru']);
        $waliRole = Role::create(['name' => 'wali_kelas', 'display_name' => 'Wali Kelas']);
        $siswaRole = Role::create(['name' => 'siswa', 'display_name' => 'Siswa']);
        $ortuRole = Role::create(['name' => 'orang_tua', 'display_name' => 'Orang Tua']);

        $waka = User::create([
            'name' => 'Waka Kurikulum',
            'email' => 'waka2@school.test',
            'login_key' => 'WAKA002',
            'password' => 'password123',
            'role_id' => $wakaRole->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $guru = User::create([
            'name' => 'Guru Mapel',
            'email' => 'guru@school.test',
            'login_key' => 'GURU001',
            'password' => 'password123',
            'role_id' => $guruRole->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $wali = User::create([
            'name' => 'Wali Kelas',
            'email' => 'wali@school.test',
            'login_key' => 'WALI_10_RPL_1',
            'password' => 'password123',
            'role_id' => $waliRole->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $major = Major::create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak']);
        $class = Classes::create([
            'name' => '10 RPL 1',
            'major_id' => $major->id,
            'phase' => 'ganjil',
            'homeroom_teacher_id' => $wali->id,
        ]);
        $subject = Subject::create(['code' => 'PBO', 'name' => 'Pemrograman Berorientasi Objek']);
        $room = Room::create(['code' => 'LAB-1', 'name' => 'Lab RPL 1', 'capacity' => 36]);

        $student = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'siswa@school.test',
            'login_key' => '2026100001',
            'password' => 'password123',
            'role_id' => $siswaRole->id,
            'class_id' => $class->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $parent = User::create([
            'name' => 'Budi Santoso',
            'email' => 'ortu@school.test',
            'login_key' => 'ahmad_fauzi_ortu',
            'password' => 'password123',
            'role_id' => $ortuRole->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $parent->students()->attach($student->id);

        // Guru creates schedule via API
        $guruToken = $guru->createToken('flutter')->plainTextToken;
        $scheduleResp = $this->withHeader('Authorization', 'Bearer ' . $guruToken)
            ->postJson('/api/schedules', [
                'schedules' => [
                    [
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'room_id' => $room->id,
                        'day' => 'Sabtu',
                        'start_time' => '07:00',
                        'end_time' => '08:30',
                        'round' => 1,
                    ],
                ],
            ])->assertCreated()
            ->assertJsonPath('status', 'success');

        $scheduleId = $scheduleResp->json('data.0.id');

        // Guru records attendance via API
        $this->withHeader('Authorization', 'Bearer ' . $guruToken)
            ->postJson('/api/attendances/schedules/' . $scheduleId, [
                'attendances' => [
                    $student->id => 'Hadir',
                ],
            ])->assertOk()
            ->assertJsonPath('status', 'success');

        // Guru records teaching journal via API
        $this->withHeader('Authorization', 'Bearer ' . $guruToken)
            ->postJson('/api/journals', [
                'schedule_id' => $scheduleId,
                'date' => now()->toDateString(),
                'materi' => 'Pengenalan Flutter REST API',
                'summary' => 'Integrasi Sanctum Bearer Token dengan dio/http.',
            ])->assertCreated()
            ->assertJsonPath('status', 'success');

        // Parent submits leave via API
        $parentToken = $parent->createToken('flutter')->plainTextToken;
        $leaveResp = $this->withHeader('Authorization', 'Bearer ' . $parentToken)
            ->postJson('/api/leaves', [
                'type' => 'Izin',
                'start_date' => '2026-10-31',
                'end_date' => '2026-10-31',
                'reason' => 'Keperluan keluarga mendadak',
            ])->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.student_id', $student->id);

        $leaveId = $leaveResp->json('data.id');

        // Wali Kelas approves leave via API
        app('auth')->forgetGuards();
        $waliToken = $wali->createToken('flutter')->plainTextToken;
        $waliResp = $this->withHeader('Authorization', 'Bearer ' . $waliToken)
            ->postJson('/api/leaves/' . $leaveId . '/status', [
                'status' => 'Approved',
            ]);
        $waliResp->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'Approved');

        // WAKA checks audit logs via API
        app('auth')->forgetGuards();
        $wakaToken = $waka->createToken('flutter')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer ' . $wakaToken)
            ->getJson('/api/audit-logs')
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }
}
