<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\UserResource;
use App\Mail\StudentAttendanceNotification;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Cycle;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceApiController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();
        $activeRound = Cycle::current()->active_round;

        $query = Attendance::with([
            'teachingJournal',
            'student:id,name,login_key,class_id',
            'schedule.teacher:id,name,login_key',
            'schedule.class:id,name,major_id',
            'schedule.subject:id,code,name',
        ]);

        if ($user) {
            $role = $user->role?->name;
            if ($role === 'guru') {
                $query->whereHas('schedule', fn ($q) => $q->where('teacher_id', $user->id));
            } elseif ($role === 'wali_kelas') {
                $query->whereHas('schedule', fn ($scheduleQuery) => $scheduleQuery->whereIn(
                    'class_id',
                    $user->homeroomClasses()->select('classes.id')
                ));
            } elseif ($role === 'siswa') {
                $query->where('student_id', $user->id);
            } elseif ($role === 'orang_tua') {
                $query->whereIn('student_id', $user->children()->select('users.id'));
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('date', (string) $request->query('date'));
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', (int) $request->query('student_id'));
        }

        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', (int) $request->query('schedule_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        $attendances = $query->latest()->limit(200)->get();

        $schedules = ($user && $user->role?->name === 'guru')
            ? Schedule::with(['class.major', 'subject', 'room'])
                ->where('teacher_id', $user->id)
                ->where('round', $activeRound)
                ->orderBy('day')
                ->orderBy('start_time')
                ->get()
            : collect();

        $resolvedAttendances = AttendanceResource::collection($attendances)->resolve($request);

        return $this->successResponse($resolvedAttendances, 'Daftar presensi berhasil diambil.', 200, [
            'count' => count($resolvedAttendances),
            'active_round' => (int) $activeRound,
            'teacher_schedules' => ScheduleResource::collection($schedules)->resolve($request),
        ]);
    }

    public function showSchedule(Request $request, Schedule $schedule): JsonResponse
    {
        $user = $request->user();

        if ($user->role?->name !== 'guru') {
            return $this->errorResponse('Akses ditolak. Presensi hanya dapat diisi oleh Guru.', 403);
        }

        if ((int) $schedule->teacher_id !== (int) $user->id) {
            return $this->errorResponse('Anda hanya dapat mengisi presensi pada jadwal mengajar sendiri.', 403);
        }

        $schedule->loadMissing(['teacher', 'class.major', 'subject', 'room']);
        $date = $request->filled('date') ? (string) $request->query('date') : now()->toDateString();
        $existingJournal = TeachingJournal::where('teacher_id', $user->id)->where('schedule_id', $schedule->id)->whereDate('date', $date)->first();
        if ((int) $schedule->round !== (int) Cycle::current()->active_round && ! $existingJournal) {
            return $this->errorResponse('Jadwal tidak berada pada putaran aktif saat ini.', 404);
        }

        $students = User::with('role')
            ->whereHas('role', fn ($q) => $q->where('name', 'siswa'))
            ->where('class_id', $schedule->class_id)
            ->orderBy('name')
            ->get();

        $existingAttendances = Attendance::where('schedule_id', $schedule->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        $studentItems = $students->map(function (User $student) use ($existingAttendances, $request) {
            $existing = $existingAttendances->get($student->id);

            return [
                'student' => (new UserResource($student))->resolve($request),
                'current_status' => $existing?->status ?? 'Hadir',
                'notes' => $existing?->notes,
            ];
        })->values();

        return $this->successResponse([
            'schedule' => (new ScheduleResource($schedule))->resolve($request),
            'date' => $date,
            'journal' => $existingJournal ? [
                'id' => $existingJournal->id,
                'materi' => $existingJournal->materi,
                'summary' => $existingJournal->activity_summary,
                'catatan_kejadian' => $existingJournal->catatan_kejadian ?? $existingJournal->notes,
                'foto_bukti_kbm' => $existingJournal->foto_bukti_kbm ?? $existingJournal->photo_proof,
                'attachment_url' => filled($existingJournal->photo_proof) ? url('/api/journals/'.$existingJournal->id.'/attachment') : null,
            ] : null,
            'students' => $studentItems,
        ], 'Detail jadwal dan daftar siswa presensi berhasil diambil.');
    }

    public function store(Request $request, Schedule $schedule): JsonResponse
    {
        $user = $request->user();

        if ($user->role?->name !== 'guru') {
            return $this->errorResponse('Akses ditolak. Presensi hanya dapat diisi oleh Guru.', 403);
        }

        if ((int) $schedule->teacher_id !== (int) $user->id) {
            return $this->errorResponse('Anda hanya dapat mengisi presensi pada jadwal mengajar sendiri.', 403);
        }

        $validated = $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*' => ['required', 'in:Hadir,Izin,Sakit,Alpa'],
            'materi' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'catatan_kejadian' => ['nullable', 'string'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $date = $validated['date'] ?? now()->toDateString();
        $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $day = $dayNames[\Illuminate\Support\Carbon::parse($date)->dayOfWeekIso] ?? 'Senin';

        $existingJournal = TeachingJournal::where('teacher_id', $user->id)
            ->where('schedule_id', $schedule->id)->whereDate('date', $date)->first();
        if ((int) $schedule->round !== (int) Cycle::current()->active_round && ! $existingJournal) {
            return $this->errorResponse('Jadwal tidak berada pada putaran aktif saat ini.', 404);
        }

        $photoPath = $request->hasFile('attachment')
            ? $request->file('attachment')->store('journal_attachments', 'local')
            : $existingJournal?->photo_proof;
        $oldPhotoPath = $existingJournal?->photo_proof;

        $studentIds = array_map('intval', array_keys($validated['attendances']));
        $validStudents = User::whereHas('role', fn ($query) => $query->where('name', 'siswa'))
            ->where('class_id', $schedule->class_id)
            ->whereIn('id', $studentIds)
            ->count();

        if ($validStudents !== count($studentIds)) {
            throw ValidationException::withMessages([
                'attendances' => 'Presensi hanya dapat dicatat untuk siswa yang terdaftar di kelas pada jadwal ini.',
            ]);
        }

        DB::transaction(function () use ($validated, $studentIds, $schedule, $user, $date, $photoPath): void {
            $existingAttendances = Attendance::query()
                ->where('schedule_id', $schedule->id)
                ->where('date', $date)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->keyBy('student_id');
            $materi = filled($validated['materi'] ?? null) ? trim((string) $validated['materi']) : 'Kegiatan Belajar Mengajar';
            $summary = filled($validated['summary'] ?? null) ? trim((string) $validated['summary']) : 'Penyampaian materi dan aktivitas belajar.';
            $catatan = filled($validated['catatan_kejadian'] ?? null) ? trim((string) $validated['catatan_kejadian']) : 'KBM berlangsung lancar.';

            $journal = TeachingJournal::updateOrCreate(
                ['teacher_id' => $user->id, 'schedule_id' => $schedule->id, 'date' => $date],
                [
                    'materi' => $materi,
                    'activity_summary' => $summary,
                    'notes' => $catatan,
                    'catatan_kejadian' => $catatan,
                    'photo_proof' => $photoPath,
                    'foto_bukti_kbm' => $photoPath,
                ]
            );
            $timestamp = now();
            $rows = [];

            foreach ($studentIds as $studentId) {
                $statusValue = $validated['attendances'][$studentId] ?? $validated['attendances'][(string) $studentId];
                $rows[] = [
                    'schedule_id' => $schedule->id,
                    'teaching_journal_id' => $journal->id,
                    'student_id' => $studentId,
                    'date' => $date,
                    'status' => $statusValue,
                    'recorded_by' => $user->id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            Attendance::upsert(
                $rows,
                ['schedule_id', 'student_id', 'date'],
                ['teaching_journal_id', 'status', 'recorded_by', 'updated_at']
            );

            $schedule->loadMissing(['class', 'subject']);
            $studentsWithParents = User::with('parents')
                ->whereIn('id', $studentIds)
                ->get()
                ->keyBy('id');

            foreach ($studentIds as $studentId) {
                $status = $validated['attendances'][$studentId] ?? $validated['attendances'][(string) $studentId];
                $previousStatus = $existingAttendances->get($studentId)?->status;

                if ($previousStatus !== $status && $status !== 'Hadir') {
                    Notification::send(
                        (int) $studentId,
                        'Informasi Presensi',
                        'Presensi Anda pada ' . $date . ' tercatat dengan status ' . $status . '.'
                    );

                    $studentModel = $studentsWithParents->get($studentId);
                    if ($studentModel) {
                        $recipients = collect([$studentModel])->merge($studentModel->parents);

                        foreach ($recipients as $recipient) {
                            $targetEmail = filled($recipient->recovery_email)
                                ? $recipient->recovery_email
                                : (filled($recipient->email) && ! str_ends_with((string) $recipient->email, '@accounts.invalid') ? $recipient->email : null);

                            if (! $targetEmail) {
                                continue;
                            }

                            try {
                                Mail::to($targetEmail)->send(new StudentAttendanceNotification(
                                    recipientName: $recipient->name,
                                    studentName: $studentModel->name,
                                    className: $schedule->class?->name ?? '-',
                                    subjectName: $schedule->subject?->name ?? '-',
                                    date: $date,
                                    status: $status,
                                ));
                            } catch (\Throwable $e) {
                                Log::warning('Gagal mengirim email notifikasi kehadiran (API).', [
                                    'recipient_id' => $recipient->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }
                }
            }

            AuditLog::record($user->id, 'SAVE_ATTENDANCE_AND_JOURNAL', 'Guru menyimpan presensi dan jurnal untuk jadwal ID ' . $schedule->id);
        });

        if ($request->hasFile('attachment') && $oldPhotoPath && $oldPhotoPath !== $photoPath) {
            Storage::disk('local')->delete($oldPhotoPath);
        }

        $savedAttendances = Attendance::with(['student', 'schedule.class', 'schedule.subject', 'schedule.teacher', 'teachingJournal'])
            ->where('schedule_id', $schedule->id)
            ->whereDate('date', $date)
            ->get();

        return $this->successResponse(
            AttendanceResource::collection($savedAttendances)->resolve($request),
            'Presensi dan jurnal mengajar berhasil disimpan.'
        );
    }
}