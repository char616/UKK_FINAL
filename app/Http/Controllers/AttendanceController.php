<?php

namespace App\Http\Controllers;

use App\Mail\StudentAttendanceNotification;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Cycle;
use App\Models\JamPelajaran;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\TeachingJournal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::with([
            'student.schoolClass',
            'schedule.class.major',
            'schedule.subject',
            'schedule.teacher',
            'schedule.room',
            'teachingJournal',
        ])->latest('date');

        match ($user->role->name) {
            'siswa' => $query->where('student_id', $user->id),
            'wali_kelas' => $query->whereHas('schedule', fn ($scheduleQuery) => $scheduleQuery->whereIn(
                'class_id',
                $user->homeroomClasses()->select('classes.id')
            )),
            'guru' => $query->whereHas('schedule', fn ($q) => $q->where('teacher_id', $user->id)),
            'orang_tua' => $query->whereIn('student_id', $user->students()->select('users.id')),
            default => null,
        };

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->whereHas('student', function ($studentQuery) use ($search) {
                $studentQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('login_key', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id') && in_array($user->role->name, ['waka', 'bk', 'wali_kelas'], true)) {
            $query->whereHas('schedule', fn ($q) => $q->where('class_id', $request->input('class_id')));
        }

        $attendances = $query->paginate(25)->withQueryString();
        $classes = Classes::orderBy('name')->get();

        $activeRound = Cycle::current()->active_round;
        $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
        $todayDay = $this->indonesianDay(now());

        $schedules = $user->role->name === 'guru'
            ? Schedule::with(['class.major', 'subject', 'room', 'jamPelajaran'])
                ->where('teacher_id', $user->id)
                ->where('round', $activeRound)
                ->where('day', $todayDay)
                ->get()
                ->sortBy([
                    fn ($a, $b) => strcmp((string) ($a->jamPelajaran?->jam_mulai ?? $a->start_time), (string) ($b->jamPelajaran?->jam_mulai ?? $b->start_time)),
                ])
                ->values()
            : collect();

        $hasScheduleToday = $schedules->isNotEmpty();

        $todayDate = now()->toDateString();
        $todayJournals = $user->role->name === 'guru'
            ? TeachingJournal::where('teacher_id', $user->id)
                ->whereDate('date', $todayDate)
                ->pluck('materi', 'schedule_id')
            : collect();

        return view('attendances.index', compact('attendances', 'classes', 'schedules', 'user', 'todayJournals', 'todayDate', 'todayDay', 'hasScheduleToday'));
    }

    public function create(Request $request, Schedule $schedule)
    {
        $user = Auth::user();
        abort_unless($user->role->name === 'guru', 403, 'Akses ditolak.');
        abort_unless((int) $schedule->teacher_id === (int) $user->id, 403, 'Jadwal ini bukan jadwal mengajar Anda.');
        abort_unless((int) $schedule->round === Cycle::current()->active_round, 404);

        $schedule->loadMissing([
            'class.major',
            'class.homeroomTeacher',
            'subject',
            'room',
            'teacher',
            'jamPelajaran',
        ]);

        $students = User::whereHas('role', fn ($q) => $q->where('name', 'siswa'))
            ->where('class_id', $schedule->class_id)
            ->orderBy('name')
            ->get();

        $date = $request->input('date', now()->toDateString());
        $isExpired = ($date < now()->toDateString());
        $dateDay = $this->indonesianDay(Carbon::parse($date));
        $isScheduledForDate = ($schedule->day === $dateDay);

        $journal = TeachingJournal::where('teacher_id', $user->id)
            ->where('schedule_id', $schedule->id)
            ->whereDate('date', $date)
            ->first();

        $existingStatuses = Attendance::where('schedule_id', $schedule->id)
            ->whereDate('date', $date)
            ->pluck('status', 'student_id')
            ->toArray();

        return view('attendances.create', compact('schedule', 'students', 'user', 'journal', 'existingStatuses', 'date', 'isExpired', 'isScheduledForDate', 'dateDay'));
    }

    public function store(Request $request, Schedule $schedule)
    {
        $user = Auth::user();
        abort_unless($user->role->name === 'guru', 403, 'Akses ditolak.');
        abort_unless((int) $schedule->teacher_id === (int) $user->id, 403, 'Jadwal ini bukan jadwal mengajar Anda.');
        abort_unless((int) $schedule->round === Cycle::current()->active_round, 404);

        $validated = $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*' => ['required', 'in:Hadir,Izin,Sakit,Alpa'],
            'materi' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'catatan_kejadian' => ['nullable', 'string'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'attachment_data' => ['nullable', 'string'],
        ], [
            'attachment.image' => 'Bukti pembelajaran harus berupa gambar JPG atau PNG.',
            'attachment.mimes' => 'Bukti pembelajaran hanya boleh JPG atau PNG.',
            'attachment.max' => 'Ukuran foto bukti maksimal 5 MB.',
        ]);

        $date = $validated['date'] ?? now()->toDateString();

        // Batas waktu: maksimal hari itu. Jika tanggal lebih lampau dari hari ini, tidak bisa diisi/diedit lagi.
        if ($date < now()->toDateString()) {
            throw ValidationException::withMessages([
                'date' => 'Batas waktu pengisian dan pengeditan presensi telah berakhir. Presensi hanya dapat diisi pada hari pelaksanaan KBM (maksimal hari ini).',
            ]);
        }

        $studentIds = array_map('intval', array_keys($validated['attendances']));
        $validStudents = User::whereHas('role', fn ($q) => $q->where('name', 'siswa'))
            ->where('class_id', $schedule->class_id)->whereIn('id', $studentIds)->count();
        if ($validStudents !== count($studentIds)) {
            throw ValidationException::withMessages(['attendances' => 'Daftar presensi berisi siswa yang tidak terdaftar di kelas pada jadwal ini.']);
        }

        $journal = TeachingJournal::where('teacher_id', $user->id)->where('schedule_id', $schedule->id)->whereDate('date', $date)->first();
        $oldPhoto = $journal?->photo_proof;
        $previousStatuses = Attendance::where('schedule_id', $schedule->id)->whereDate('date', $date)->whereIn('student_id', $studentIds)->pluck('status', 'student_id');

        $photoPath = $journal?->photo_proof;
        if ($request->filled('attachment_data') && str_starts_with((string) $request->input('attachment_data'), 'data:image/')) {
            $dataUri = (string) $request->input('attachment_data');
            [$meta, $data] = explode(',', $dataUri, 2);
            $ext = str_contains($meta, 'image/png') ? 'png' : 'jpg';
            $decoded = base64_decode($data);
            if ($decoded !== false) {
                $filename = 'journal_attachments/camera_'.time().'_'.bin2hex(random_bytes(6)).'.'.$ext;
                Storage::disk('local')->put($filename, $decoded);
                $photoPath = $filename;
            }
        } elseif ($request->hasFile('attachment')) {
            $photoPath = $request->file('attachment')->store('journal_attachments', 'local');
        }

        $materi = filled($validated['materi'] ?? null) ? trim((string) $validated['materi']) : 'Kegiatan Belajar Mengajar';
        $summary = filled($validated['summary'] ?? null) ? trim((string) $validated['summary']) : 'Aktivitas pembelajaran tatap muka.';
        $catatan = filled($validated['catatan_kejadian'] ?? null) ? trim((string) $validated['catatan_kejadian']) : 'KBM berlangsung lancar.';

        DB::transaction(function () use ($user, $schedule, $date, $materi, $summary, $catatan, $photoPath, $studentIds, $validated): void {
            $journal = TeachingJournal::updateOrCreate(
                [
                    'teacher_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'date' => $date,
                ],
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
                $rows[] = [
                    'schedule_id' => $schedule->id,
                    'teaching_journal_id' => $journal->id,
                    'student_id' => $studentId,
                    'date' => $date,
                    'status' => $validated['attendances'][$studentId] ?? $validated['attendances'][(string) $studentId],
                    'recorded_by' => $user->id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
            Attendance::upsert($rows, ['schedule_id', 'student_id', 'date'], ['teaching_journal_id', 'status', 'recorded_by', 'updated_at']);
            AuditLog::record($user->id, 'SAVE_ATTENDANCE_AND_JOURNAL', 'Menyimpan presensi dan jurnal mengajar untuk jadwal ID '.$schedule->id.' tanggal '.$date);
        });

        if ($journal && $oldPhoto && $oldPhoto !== $photoPath && Storage::disk('local')->exists($oldPhoto)) {
            Storage::disk('local')->delete($oldPhoto);
        }

        $schedule->loadMissing(['class', 'subject']);
        $studentsWithParents = User::with('parents')->whereIn('id', $studentIds)->get()->keyBy('id');
        foreach ($studentIds as $studentId) {
            $status = $validated['attendances'][$studentId] ?? $validated['attendances'][(string) $studentId];
            if ($previousStatuses->get($studentId) === $status || $status === 'Hadir') {
                continue;
            }

            Notification::send((int) $studentId, 'Informasi Presensi', 'Presensi Anda pada '.$date.' tercatat dengan status '.$status.'.');
            $studentModel = $studentsWithParents->get($studentId);
            if (! $studentModel) continue;

            foreach (collect([$studentModel])->merge($studentModel->parents) as $recipient) {
                $targetEmail = filled($recipient->recovery_email)
                    ? $recipient->recovery_email
                    : (filled($recipient->email) && ! str_ends_with((string) $recipient->email, '@accounts.invalid') ? $recipient->email : null);
                if (! $targetEmail) continue;

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
                    Log::warning('Gagal mengirim email notifikasi kehadiran.', [
                        'recipient_id' => $recipient->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('attendances.index')->with('success', 'Presensi dan jurnal mengajar berhasil disimpan. Data tetap dapat diedit melalui jadwal yang sama.');
    }

    private function indonesianDay(Carbon $date): string
    {
        return [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][$date->dayOfWeekIso];
    }
}
