<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\SchoolClassResource;
use App\Http\Resources\SubjectResource;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Cycle;
use App\Models\Notification;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScheduleLock;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScheduleApiController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();
        $activeRound = Cycle::current()->active_round;
        $requestedRound = $request->filled('round') ? (int) $request->query('round') : (int) $activeRound;
        $scheduleLock = ScheduleLock::firstOrCreate(['id' => 1], ['is_locked' => false]);

        $query = Schedule::with(['teacher:id,name,login_key', 'class.major', 'subject', 'room'])
            ->where('round', $requestedRound);

        if ($user) {
            $role = $user->role?->name;
            if ($role === 'guru') {
                $query->where('teacher_id', $user->id);
            } elseif ($role === 'wali_kelas') {
                $query->whereIn('class_id', $user->homeroomClasses()->select('classes.id'));
            } elseif ($role === 'siswa') {
                $query->where('class_id', $user->class_id);
            } elseif ($role === 'orang_tua') {
                $query->whereIn('class_id', $user->students()->select('users.class_id'));
            }
        }

        if ($request->filled('day')) {
            $query->where('day', (string) $request->query('day'));
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', (int) $request->query('class_id'));
        }

        $schedules = $query->orderBy('day')->orderBy('start_time')->get();
        $classes = Classes::with('major')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();

        $reservedSchedules = Schedule::query()
            ->get(['id', 'teacher_id', 'class_id', 'room_id', 'day', 'start_time', 'end_time', 'round'])
            ->map(fn ($s) => [
                'id' => $s->id,
                'teacher_id' => $s->teacher_id,
                'class_id' => $s->class_id,
                'room_id' => $s->room_id,
                'day' => $s->day,
                'start_time' => substr((string) $s->start_time, 0, 5),
                'end_time' => substr((string) $s->end_time, 0, 5),
                'round' => (int) $s->round,
            ])
            ->values();

        $resolvedSchedules = ScheduleResource::collection($schedules)->resolve($request);

        return $this->successResponse($resolvedSchedules, 'Daftar jadwal berhasil diambil.', 200, [
            'active_round' => (int) $activeRound,
            'round' => $requestedRound,
            'count' => count($resolvedSchedules),
            'is_locked' => (bool) $scheduleLock->is_locked,
            'meta' => [
                'active_round' => (int) $activeRound,
                'is_locked' => (bool) $scheduleLock->is_locked,
                'classes' => SchoolClassResource::collection($classes)->resolve($request),
                'subjects' => SubjectResource::collection($subjects)->resolve($request),
                'rooms' => RoomResource::collection($rooms)->resolve($request),
                'reserved_schedules' => $reservedSchedules,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $lock = ScheduleLock::first();
        if ($lock && $lock->is_locked) {
            throw ValidationException::withMessages([
                'schedules' => 'Jadwal sedang dikunci oleh WAKA.',
            ]);
        }

        $validated = $request->validate([
            'schedules' => ['required', 'array', 'min:1', 'max:60'],
            'schedules.*.class_id' => ['required', 'integer', 'exists:classes,id'],
            'schedules.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'schedules.*.room_id' => ['required', 'integer', 'exists:rooms,id'],
            'schedules.*.day' => ['required', 'string', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.round' => ['required', 'integer', Rule::in([1, 2])],
        ]);

        $user = $request->user();
        $teacherId = (int) $user->id;
        $rows = array_values($validated['schedules']);
        $createdSchedules = [];

        DB::transaction(function () use ($rows, $teacherId, $user, &$createdSchedules): void {
            $existingSchedules = Schedule::query()->lockForUpdate()->get();

            foreach ($rows as $index => $row) {
                $classId = (int) $row['class_id'];
                $roomId = (int) $row['room_id'];
                $day = $row['day'];
                $round = (int) $row['round'];
                $startTime = substr((string) $row['start_time'], 0, 5);
                $endTime = substr((string) $row['end_time'], 0, 5);

                for ($prevIndex = 0; $prevIndex < $index; $prevIndex++) {
                    $prev = $rows[$prevIndex];
                    $prevStart = substr((string) $prev['start_time'], 0, 5);
                    $prevEnd = substr((string) $prev['end_time'], 0, 5);

                    if (
                        $prev['day'] === $day
                        && (int) $prev['round'] === $round
                        && $startTime < $prevEnd
                        && $endTime > $prevStart
                    ) {
                        throw ValidationException::withMessages([
                            "schedules.{$index}.start_time" => 'Jadwal bentrok dengan baris jadwal lain yang Anda masukkan pada hari, jam, dan putaran yang sama.',
                        ]);
                    }
                }

                foreach ($existingSchedules as $existing) {
                    if ($existing->day !== $day || (int) $existing->round !== $round) {
                        continue;
                    }

                    $existingStart = substr((string) $existing->start_time, 0, 5);
                    $existingEnd = substr((string) $existing->end_time, 0, 5);

                    if ($startTime < $existingEnd && $endTime > $existingStart) {
                        if (
                            (int) $existing->teacher_id === $teacherId
                            || (int) $existing->class_id === $classId
                            || (int) $existing->room_id === $roomId
                        ) {
                            throw ValidationException::withMessages([
                                "schedules.{$index}.start_time" => 'Jadwal bentrok (guru, kelas, atau ruangan sudah terpakai pada waktu dan putaran tersebut).',
                            ]);
                        }
                    }
                }
            }

            foreach ($rows as $row) {
                $createdSchedules[] = Schedule::create([
                    'teacher_id' => $teacherId,
                    'class_id' => (int) $row['class_id'],
                    'subject_id' => (int) $row['subject_id'],
                    'room_id' => (int) $row['room_id'],
                    'day' => $row['day'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'round' => (int) $row['round'],
                ])->load(['teacher', 'class.major', 'subject', 'room']);
            }

            $wakaIds = User::where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('name', 'waka'))
                ->pluck('id');

            foreach ($wakaIds as $wakaId) {
                Notification::send(
                    (int) $wakaId,
                    'Pengajuan Jadwal Guru',
                    $user->name . ' mengirim ' . count($rows) . ' jadwal baru untuk ditinjau. Jadwal sudah tersedia pada daftar jadwal.'
                );
            }

            AuditLog::record(
                $teacherId,
                'STORE_SCHEDULES',
                'Guru menambahkan ' . count($rows) . ' jadwal mengajar.'
            );
        });

        return $this->successResponse(
            ScheduleResource::collection(collect($createdSchedules))->resolve($request),
            'Jadwal mengajar berhasil disimpan.',
            201
        );
    }

    public function updateActiveRound(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'active_round' => ['required', 'integer', Rule::in([1, 2])],
        ]);

        $round = (int) $validated['active_round'];

        DB::transaction(function () use ($request, $round): void {
            Cycle::current()->update(['active_round' => $round]);

            $teacherIds = User::where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('name', 'guru'))
                ->pluck('id');

            foreach ($teacherIds as $teacherId) {
                Notification::send(
                    (int) $teacherId,
                    'Putaran Jadwal Sekolah Diperbarui',
                    'WAKA menetapkan Putaran ' . $round . ' sebagai putaran jadwal aktif sekolah.'
                );
            }

            AuditLog::record(
                $request->user()->id,
                'UPDATE_ACTIVE_SCHEDULE_ROUND',
                'WAKA mengubah putaran jadwal aktif sekolah menjadi Putaran ' . $round . '.'
            );
        });

        return $this->successResponse([
            'active_round' => $round,
        ], 'Putaran jadwal aktif berhasil diperbarui menjadi Putaran ' . $round . '.');
    }

    public function toggleLock(Request $request): JsonResponse
    {
        $lock = ScheduleLock::firstOrCreate(['id' => 1], ['is_locked' => false]);
        $newStatus = ! $lock->is_locked;

        $lock->update([
            'is_locked' => $newStatus,
            'locked_by' => $request->user()->id,
            'locked_at' => $newStatus ? now() : null,
        ]);

        AuditLog::record(
            $request->user()->id,
            $newStatus ? 'LOCK_SCHEDULE' : 'UNLOCK_SCHEDULE',
            $newStatus ? 'WAKA mengunci jadwal pelajaran.' : 'WAKA membuka kunci jadwal pelajaran.'
        );

        return $this->successResponse([
            'is_locked' => (bool) $newStatus,
            'locked_by' => $lock->locked_by,
            'locked_at' => $lock->locked_at?->toIso8601String(),
        ], $newStatus ? 'Jadwal pelajaran berhasil dikunci.' : 'Kunci jadwal pelajaran berhasil dibuka.');
    }
}