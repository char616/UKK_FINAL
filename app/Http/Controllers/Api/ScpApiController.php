<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpResetPassword;
use App\Mail\StudentAttendanceNotification;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Cycle;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\PasswordReset;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ScpApiController extends Controller
{
    public function health(): JsonResponse
    {
        $cycle = Cycle::current();

        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'active_round' => $cycle->active_round,
            'mail_mailer' => config('mail.default'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login_key' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $loginInput = trim($validated['login_key']);
        $lowerInput = Str::lower($loginInput);
        $underscoreInput = Str::replace(' ', '_', $lowerInput);

        $user = User::with(['role', 'schoolClass'])
            ->where(function ($query) use ($loginInput, $lowerInput, $underscoreInput): void {
                $query->where('nip', $loginInput)
                    ->orWhere('nis', $loginInput)
                    ->orWhereRaw('LOWER(login_key) = ?', [$lowerInput])
                    ->orWhereRaw('LOWER(parent_login_key) = ?', [$lowerInput])
                    ->orWhereRaw("REPLACE(LOWER(parent_login_key), ' ', '_') = ?", [$underscoreInput]);
            })
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login ID / NIP / NIS atau password salah.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda tidak aktif. Silakan hubungi WAKA.',
            ], 403);
        }

        $loginAs = $user->role?->name;
        if (in_array($user->role?->name, ['siswa', 'orang_tua'], true)) {
            $userParentKeyLower = Str::lower(trim((string) $user->parent_login_key));
            $userParentKeyUnderscore = Str::replace(' ', '_', $userParentKeyLower);
            $isParentLogin = $userParentKeyLower !== '' && ($lowerInput === $userParentKeyLower || $underscoreInput === $userParentKeyUnderscore);
            $loginAs = $isParentLogin ? 'orang_tua' : 'siswa';
            $user->update(['last_login_as' => $loginAs]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'login_key' => $user->login_key,
                'nip' => $user->nip,
                'nis' => $user->nis,
                'parent_name' => $user->parent_name,
                'parent_login_key' => $user->parent_login_key,
                'login_as' => $loginAs,
                'email' => $user->email,
                'recovery_email' => $user->recovery_email,
                'role' => $user->role?->name,
                'class' => $user->schoolClass?->name,
            ],
        ]);
    }

    public function testOtpEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'login_key' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $inputEmail = Str::lower(trim($validated['email']));
        $user = null;

        if (filled($validated['login_key'] ?? null)) {
            $loginInput = trim((string) $validated['login_key']);
            $lowerInput = Str::lower($loginInput);
            $underscoreInput = Str::replace(' ', '_', $lowerInput);

            $user = User::query()
                ->where(function ($query) use ($loginInput, $lowerInput, $underscoreInput): void {
                    $query->where('nip', $loginInput)
                        ->orWhere('nis', $loginInput)
                        ->orWhereRaw('LOWER(login_key) = ?', [$lowerInput])
                        ->orWhereRaw('LOWER(parent_login_key) = ?', [$lowerInput])
                        ->orWhereRaw("REPLACE(LOWER(parent_login_key), ' ', '_') = ?", [$underscoreInput]);
                })
                ->first();

            if (! $user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun dengan Login ID tersebut tidak ditemukan di database.',
                ], 404);
            }

            $isEmailMatched = $user->matchResetEmail($inputEmail) !== null;

            if (! $isEmailMatched) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Alamat email tidak terdaftar atau tidak cocok dengan data akun tersebut di database.',
                ], 422);
            }
        } else {
            // Jika tidak menyertakan login_key, cari akun atau fallback ke user pertama untuk kebutuhan pengujian
            $user = User::query()
                ->whereRaw('LOWER(recovery_email) = ?', [$inputEmail])
                ->orWhereRaw('LOWER(email) = ?', [$inputEmail])
                ->first() ?: User::first();
        }

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $userName = $user?->name ?? ($validated['name'] ?? 'Pengguna SCP2');

        if ($user) {
            PasswordReset::where('user_id', $user->id)->delete();
            PasswordReset::create([
                'user_id' => $user->id,
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
                'attempts' => 0,
            ]);
        }

        try {
            Mail::to($validated['email'])->send(new OtpResetPassword($otp, $userName));

            return response()->json([
                'status' => 'success',
                'message' => 'Email OTP berhasil dikirim ke ' . $validated['email'],
                'data' => [
                    'recipient_email' => $validated['email'],
                    'recipient_name' => $userName,
                    'otp_preview' => config('app.debug') ? $otp : '******',
                    'expires_in_minutes' => 10,
                    'mailer' => config('mail.default'),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim email OTP: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testAttendanceEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'student_name' => ['required', 'string', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'subject_name' => ['nullable', 'string', 'max:150'],
            'date' => ['nullable', 'date'],
            'status' => ['required', 'in:Izin,Sakit,Alpa,Hadir'],
        ]);

        $recipientName = $validated['recipient_name'] ?? 'Orang Tua / Wali Siswa';
        $className = $validated['class_name'] ?? 'Kelas XII RPL1';
        $subjectName = $validated['subject_name'] ?? 'Pemrograman Berorientasi Objek';
        $date = $validated['date'] ?? now()->toDateString();

        try {
            Mail::to($validated['email'])->send(new StudentAttendanceNotification(
                recipientName: $recipientName,
                studentName: $validated['student_name'],
                className: $className,
                subjectName: $subjectName,
                date: $date,
                status: $validated['status'],
            ));

            return response()->json([
                'status' => 'success',
                'message' => 'Email notifikasi kehadiran berhasil dikirim ke ' . $validated['email'],
                'data' => [
                    'recipient_email' => $validated['email'],
                    'recipient_name' => $recipientName,
                    'student_name' => $validated['student_name'],
                    'class_name' => $className,
                    'subject_name' => $subjectName,
                    'date' => $date,
                    'attendance_status' => $validated['status'],
                    'mailer' => config('mail.default'),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim email notifikasi kehadiran: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function schedules(Request $request): JsonResponse
    {
        $round = (int) ($request->query('round') ?: Cycle::current()->active_round);

        $schedules = Schedule::with(['teacher:id,name,login_key,nip', 'class:id,name,major_id', 'class.major:id,code,name', 'subject:id,code,name', 'room:id,code,name'])
            ->where('round', $round)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'status' => 'success',
            'active_round' => $round,
            'count' => $schedules->count(),
            'data' => $schedules,
        ]);
    }

    public function attendances(Request $request): JsonResponse
    {
        $query = Attendance::with([
            'student:id,name,login_key,nis,class_id',
            'schedule.class:id,name',
            'schedule.subject:id,code,name',
            'schedule.teacher:id,name,nip',
        ])->latest();

        if ($request->filled('date')) {
            $query->whereDate('date', (string) $request->query('date'));
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', (int) $request->query('student_id'));
        }

        $attendances = $query->limit(100)->get();

        return response()->json([
            'status' => 'success',
            'count' => $attendances->count(),
            'data' => $attendances,
        ]);
    }

    public function leaves(Request $request): JsonResponse
    {
        $query = LeaveRequest::with(['student:id,name,login_key,nis,class_id', 'student.schoolClass:id,name'])
            ->latest();

        if ($request->filled('student_id')) {
            $query->where('student_id', (int) $request->query('student_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        $leaves = $query->limit(100)->get();

        return response()->json([
            'status' => 'success',
            'count' => $leaves->count(),
            'data' => $leaves,
        ]);
    }

    public function storeLeave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'actor_login_key' => ['required', 'string', 'max:255'],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'type' => ['required', 'in:Izin,Sakit'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $actorInput = trim($validated['actor_login_key']);
        $lowerActor = Str::lower($actorInput);
        $underscoreActor = Str::replace(' ', '_', $lowerActor);

        $actor = User::with(['role', 'students.schoolClass.homeroomTeacher', 'schoolClass.homeroomTeacher'])
            ->where(function ($query) use ($actorInput, $lowerActor, $underscoreActor): void {
                $query->where('nis', $actorInput)
                    ->orWhereRaw('LOWER(login_key) = ?', [$lowerActor])
                    ->orWhereRaw('LOWER(parent_login_key) = ?', [$lowerActor])
                    ->orWhereRaw("REPLACE(LOWER(parent_login_key), ' ', '_') = ?", [$underscoreActor]);
            })
            ->first();

        if (! $actor || ! in_array($actor->role?->name, ['siswa', 'orang_tua'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan izin hanya dapat dilakukan oleh akun Siswa atau Orang Tua.',
            ], 403);
        }

        $student = $actor;

        $homeroomTeacher = $student->schoolClass?->homeroomTeacher;
        if (! $student->class_id || ! $homeroomTeacher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kelas atau Wali Kelas siswa belum ditetapkan.',
            ], 422);
        }

        $leave = LeaveRequest::create([
            'student_id' => $student->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => trim($validated['reason']),
            'status' => 'Pending',
        ]);

        $isParentActor = $actor->parent_login_key && (
            $lowerActor === Str::lower($actor->parent_login_key)
            || $underscoreActor === Str::replace(' ', '_', Str::lower($actor->parent_login_key))
        );

        AuditLog::record(
            $actor->id,
            'CREATE_LEAVE_API',
            ($isParentActor ? 'Orang tua' : 'Siswa') . ' mengajukan izin via API untuk siswa ' . $student->name
        );

        Notification::send(
            $homeroomTeacher->id,
            'Pengajuan Izin Baru',
            'Siswa ' . $student->name . ' mengajukan ' . $leave->type . ' pada ' . $leave->start_date->toDateString() . ' sampai ' . $leave->end_date->toDateString() . ' yang perlu persetujuan.'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan izin berhasil dikirim dan menunggu persetujuan Wali Kelas.',
            'data' => $leave->load('student:id,name,login_key,nis'),
        ], 201);
    }

    public function journals(Request $request): JsonResponse
    {
        $journals = TeachingJournal::with([
            'teacher:id,name,login_key,nip',
            'schedule.class:id,name',
            'schedule.subject:id,code,name',
        ])->latest('date')->limit(100)->get();

        return response()->json([
            'status' => 'success',
            'count' => $journals->count(),
            'data' => $journals,
        ]);
    }

    public function masterData(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'classes' => Classes::with(['major:id,code,name', 'homeroomTeacher:id,name,login_key,nip'])->orderBy('name')->get(),
                'subjects' => Subject::orderBy('name')->get(),
            ],
        ]);
    }
}