<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Cycle;
use App\Models\JamPelajaran;
use App\Models\Notification;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScheduleLock;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $roleName = $user->role?->name ?? '';
        $activeLoginAs = session('login_as') ?? $user?->last_login_as ?? $roleName;
        $isParentMode = ($roleName === 'siswa' && $activeLoginAs === 'orang_tua') || $roleName === 'orang_tua';
        $isStudentMode = ($roleName === 'siswa' && $activeLoginAs !== 'orang_tua');

        $activeRound = Cycle::current()->active_round;
        $scheduleLock = ScheduleLock::firstOrCreate(['id' => 1], ['is_locked' => false]);

        $query = Schedule::with(['teacher', 'class.major', 'subject', 'room', 'jamPelajaran'])
            ->where('round', $activeRound);

        $selectedDay = $request->query('day');
        $selectedClassId = $request->query('class_id');

        if ($selectedDay && in_array($selectedDay, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'], true)) {
            $query->where('day', $selectedDay);
        }

        // Penanganan hak akses data per peran (Siswa dan Orang Tua hanya bisa melihat jadwal kelasnya sendiri)
        if ($isStudentMode) {
            if ($user->class_id) {
                $query->where('class_id', $user->class_id);
                $classes = $user->schoolClass ? collect([$user->schoolClass]) : collect();
            } else {
                $query->whereRaw('1 = 0');
                $classes = collect();
            }
        } elseif ($isParentMode) {
            $parentClassIds = $user->role?->name === 'orang_tua'
                ? $user->students()->pluck('users.class_id')->filter()->unique()->values()->all()
                : [];

            if (empty($parentClassIds) && $user->class_id) {
                $parentClassIds = [$user->class_id];
            }

            if (! empty($parentClassIds)) {
                if ($selectedClassId && in_array((int) $selectedClassId, array_map('intval', $parentClassIds), true)) {
                    $query->where('class_id', $selectedClassId);
                } else {
                    $query->whereIn('class_id', $parentClassIds);
                }
                $classes = Classes::with('major')->whereIn('id', $parentClassIds)->orderBy('name')->get();
            } else {
                $query->whereRaw('1 = 0');
                $classes = collect();
            }
        } elseif ($roleName === 'wali_kelas') {
            $homeroomClassIds = $user->homeroomClasses()->pluck('id')->all();
            if ($selectedClassId && in_array((int) $selectedClassId, $homeroomClassIds, true)) {
                $query->where('class_id', $selectedClassId);
            } else {
                $query->whereIn('class_id', $homeroomClassIds);
            }
            $classes = $user->homeroomClasses()->with('major')->orderBy('name')->get();
        } elseif ($roleName === 'guru') {
            $query->where('teacher_id', $user->id);
            $taughtClassIds = Schedule::where('teacher_id', $user->id)
                ->where('round', $activeRound)
                ->pluck('class_id')
                ->unique()
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($selectedClassId && in_array((int) $selectedClassId, $taughtClassIds, true)) {
                $query->where('class_id', (int) $selectedClassId);
            }

            $classes = Classes::with('major')->whereIn('id', $taughtClassIds)->orderBy('name')->get();
        } else {
            // Waka, BK, Administrator
            if ($selectedClassId) {
                $query->where('class_id', $selectedClassId);
            }
            $classes = Classes::with('major')->orderBy('name')->get();
        }

        $inputClasses = Classes::with('major')->orderBy('name')->get();
        $schedules = $query->orderBy('day')->orderBy('start_time')->get();
        if (! app()->environment('testing') && Room::count() < 60) {
            \Database\Seeders\DatabaseSeeder::ensureOfficialRoomsSeeded();
        }

        $subjects = Subject::orderBy('name')->get();
        $rooms = Room::orderBy('id')->get();
        $teachers = User::whereHas('role', fn ($q) => $q->where('name', 'guru'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

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

        $jamPelajarans = JamPelajaran::where('is_istirahat', false)
            ->orderBy('urutan')
            ->get(['id', 'hari', 'nama_sesi', 'jam_mulai', 'jam_selesai', 'urutan']);

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $sessionsByDay = [];

        foreach ($days as $day) {
            $daySessions = $jamPelajarans->where('hari', $day)->values();
            if ($daySessions->isEmpty()) {
                $daySessions = $jamPelajarans->where('hari', 'Kamis')->values();
            }

            if ($daySessions->isNotEmpty()) {
                $sessionsByDay[$day] = $daySessions->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'nama_sesi' => $s->nama_sesi,
                        'jam_mulai' => substr((string) $s->jam_mulai, 0, 5),
                        'jam_selesai' => substr((string) $s->jam_selesai, 0, 5),
                        'urutan' => (int) $s->urutan,
                    ];
                })->values()->all();
            } else {
                $sessionsByDay[$day] = [
                    ['id' => null, 'nama_sesi' => 'Jam 1', 'jam_mulai' => '07:15', 'jam_selesai' => '07:55', 'urutan' => 1],
                    ['id' => null, 'nama_sesi' => 'Jam 2', 'jam_mulai' => '07:55', 'jam_selesai' => '08:35', 'urutan' => 2],
                    ['id' => null, 'nama_sesi' => 'Jam 3', 'jam_mulai' => '08:35', 'jam_selesai' => '09:15', 'urutan' => 3],
                    ['id' => null, 'nama_sesi' => 'Jam 4', 'jam_mulai' => '09:30', 'jam_selesai' => '10:10', 'urutan' => 4],
                    ['id' => null, 'nama_sesi' => 'Jam 5', 'jam_mulai' => '10:10', 'jam_selesai' => '10:50', 'urutan' => 5],
                    ['id' => null, 'nama_sesi' => 'Jam 6', 'jam_mulai' => '10:50', 'jam_selesai' => '11:30', 'urutan' => 6],
                    ['id' => null, 'nama_sesi' => 'Jam 7', 'jam_mulai' => '12:30', 'jam_selesai' => '13:10', 'urutan' => 7],
                    ['id' => null, 'nama_sesi' => 'Jam 8', 'jam_mulai' => '13:10', 'jam_selesai' => '13:50', 'urutan' => 8],
                    ['id' => null, 'nama_sesi' => 'Jam 9', 'jam_mulai' => '13:50', 'jam_selesai' => '14:30', 'urutan' => 9],
                    ['id' => null, 'nama_sesi' => 'Jam 10', 'jam_mulai' => '14:30', 'jam_selesai' => '15:10', 'urutan' => 10],
                ];
            }
        }

        return view('schedules.index', compact(
            'user',
            'schedules',
            'classes',
            'inputClasses',
            'subjects',
            'rooms',
            'teachers',
            'scheduleLock',
            'activeRound',
            'reservedSchedules',
            'sessionsByDay'
        ));
    }

    public function store(Request $request): RedirectResponse
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
            'schedules.*.day' => ['required', 'string', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.round' => ['required', 'integer', Rule::in([1, 2])],
        ]);

        $user = Auth::user();
        $teacherId = (int) $user->id;
        $rows = array_values($validated['schedules']);

        DB::transaction(function () use ($rows, $teacherId, $user): void {
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
                $startPrefix = substr((string) $row['start_time'], 0, 5);
                $jamPelajaran = JamPelajaran::where('hari', $row['day'])
                    ->where('jam_mulai', 'like', $startPrefix.'%')
                    ->first();

                Schedule::create([
                    'teacher_id' => $teacherId,
                    'class_id' => (int) $row['class_id'],
                    'subject_id' => (int) $row['subject_id'],
                    'room_id' => (int) $row['room_id'],
                    'day' => $row['day'],
                    'jam_pelajaran_id' => $jamPelajaran?->id,
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'round' => (int) $row['round'],
                ]);
            }

            $wakaIds = User::where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('name', 'waka'))
                ->pluck('id');

            foreach ($wakaIds as $wakaId) {
                Notification::send(
                    (int) $wakaId,
                    'Pengajuan Jadwal Guru',
                    $user->name.' mengirim '.count($rows).' jadwal baru untuk ditinjau. Jadwal sudah tersedia pada daftar jadwal.'
                );
            }

            AuditLog::record(
                $teacherId,
                'STORE_SCHEDULES',
                'Guru menambahkan '.count($rows).' jadwal mengajar.'
            );
        });

        return redirect()->route('schedules.index')->with('success', 'Jadwal mengajar berhasil disimpan.');
    }

    public function updateActiveRound(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'active_round' => ['required', 'integer', Rule::in([1, 2])],
        ]);

        $round = (int) $validated['active_round'];

        DB::transaction(function () use ($round): void {
            Cycle::current()->update(['active_round' => $round]);

            $teacherIds = User::where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('name', 'guru'))
                ->pluck('id');

            foreach ($teacherIds as $teacherId) {
                Notification::send(
                    (int) $teacherId,
                    'Putaran Jadwal Sekolah Diperbarui',
                    'WAKA menetapkan Putaran '.$round.' sebagai putaran jadwal aktif sekolah.'
                );
            }

            AuditLog::record(
                Auth::id(),
                'UPDATE_ACTIVE_SCHEDULE_ROUND',
                'WAKA mengubah putaran jadwal aktif sekolah menjadi Putaran '.$round.'.'
            );
        });

        return redirect()->route('schedules.index')->with('success', 'Putaran jadwal aktif berhasil diperbarui menjadi Putaran '.$round.'.');
    }

    public function toggleLock(Request $request): RedirectResponse
    {
        $lock = ScheduleLock::firstOrCreate(['id' => 1], ['is_locked' => false]);
        $newStatus = ! $lock->is_locked;

        $lock->update([
            'is_locked' => $newStatus,
            'locked_by' => Auth::id(),
            'locked_at' => $newStatus ? now() : null,
        ]);

        AuditLog::record(
            Auth::id(),
            $newStatus ? 'LOCK_SCHEDULE' : 'UNLOCK_SCHEDULE',
            $newStatus ? 'WAKA mengunci jadwal pelajaran.' : 'WAKA membuka kunci jadwal pelajaran.'
        );

        return redirect()->route('schedules.index')->with(
            'success',
            $newStatus ? 'Jadwal pelajaran berhasil dikunci.' : 'Kunci jadwal pelajaran berhasil dibuka.'
        );
    }
}
