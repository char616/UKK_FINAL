<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Cycle;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TeachingJournalController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $journalDate = $request->query('date', now()->toDateString());
        $selectedClassId = $request->query('class_id');
        $selectedTeacherId = $request->query('teacher_id');
        $selectedSubjectId = $request->query('subject_id');

        $query = TeachingJournal::with(['teacher', 'schedule.class.major', 'schedule.subject', 'schedule.room'])
            ->latest('date');

        if ($user->role->name === 'guru') {
            $query->where('teacher_id', $user->id);
            // Panggil pengingat 30 menit otomatis jika ada KBM aktif
            $this->triggerScheduledTeachingReminders($user);
        } elseif ($selectedTeacherId) {
            $query->where('teacher_id', $selectedTeacherId);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $journalDate);
        }

        if ($selectedClassId) {
            $query->whereHas('schedule', fn ($q) => $q->where('class_id', $selectedClassId));
        }

        if ($selectedSubjectId) {
            $query->whereHas('schedule', fn ($q) => $q->where('subject_id', $selectedSubjectId));
        }

        $journals = $query->paginate(25)->withQueryString();
        $activeRound = Cycle::current()->active_round;

        $todayDay = match (now()->dayOfWeek) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Minggu',
        };

        // Ambil jadwal mengajar guru untuk HARI INI
        $schedules = $user->role->name === 'guru'
            ? Schedule::with(['class.major', 'subject', 'room', 'jamPelajaran'])
                ->where('teacher_id', $user->id)
                ->where('round', $activeRound)
                ->where('day', $todayDay)
                ->get()
            : collect();

        $hasScheduleToday = $schedules->isNotEmpty();

        $schedulesWithoutJournal = $user->role->name === 'waka'
            ? $this->schedulesMissingJournal($journalDate)
            : new Collection;

        if ($user->role->name === 'guru') {
            $teacherActiveSchedules = Schedule::where('teacher_id', $user->id)
                ->where('round', $activeRound)
                ->get(['class_id', 'subject_id']);
            $classes = \App\Models\Classes::whereIn('id', $teacherActiveSchedules->pluck('class_id')->unique())->orderBy('name')->get();
            $subjects = \App\Models\Subject::whereIn('id', $teacherActiveSchedules->pluck('subject_id')->unique())->orderBy('name')->get();
        } else {
            $classes = \App\Models\Classes::orderBy('name')->get();
            $subjects = \App\Models\Subject::orderBy('name')->get();
        }
        $teachers = User::whereHas('role', fn ($q) => $q->where('name', 'guru'))->orderBy('name')->get();

        return view('journals.index', compact(
            'journals',
            'schedules',
            'schedulesWithoutJournal',
            'journalDate',
            'todayDay',
            'hasScheduleToday',
            'classes',
            'subjects',
            'teachers',
            'selectedClassId',
            'selectedTeacherId',
            'selectedSubjectId',
            'user'
        ));
    }

    public function triggerScheduledTeachingReminders(?User $user = null): void
    {
        $user = $user ?: Auth::user();
        if (! $user || $user->role?->name !== 'guru') {
            return;
        }

        $todayDate = now()->toDateString();
        $currentTime = now()->format('H:i:s');
        $todayDay = match (now()->dayOfWeek) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Minggu',
        };

        $activeRound = Cycle::current()->active_round;
        $schedules = Schedule::with(['class', 'subject', 'jamPelajaran'])
            ->where('teacher_id', $user->id)
            ->where('round', $activeRound)
            ->where('day', $todayDay)
            ->get();

        foreach ($schedules as $schedule) {
            $endTime = $schedule->jamPelajaran?->jam_selesai ?: $schedule->end_time;
            if (! $endTime) {
                continue;
            }

            $endCarbon = Carbon::parse($endTime);
            $reminderCarbon = $endCarbon->copy()->subMinutes(30);
            $reminderTime = $reminderCarbon->format('H:i:s');

            if ($currentTime >= $reminderTime && $currentTime <= $endTime) {
                $hasJournal = TeachingJournal::where('schedule_id', $schedule->id)->whereDate('date', $todayDate)->exists();
                $hasAttendance = Attendance::where('schedule_id', $schedule->id)->whereDate('date', $todayDate)->exists();

                if ($hasJournal && $hasAttendance) {
                    continue;
                }

                $alreadyNotified = Notification::where('user_id', $user->id)
                    ->whereDate('created_at', $todayDate)
                    ->where('message', 'like', "%[jadwal #{$schedule->id}]%")
                    ->exists();

                if (! $alreadyNotified) {
                    Notification::send(
                        $user->id,
                        'Pengingat KBM: Isi Jurnal & Presensi',
                        "Pembelajaran {$schedule->subject?->name} di kelas {$schedule->class?->name} akan berakhir dalam 30 menit (pukul {$endTime}). Harap segera ambil foto real-time serta lengkapi jurnal mengajar dan presensi siswa hari ini. [jadwal #{$schedule->id}]"
                    );
                }
            }
        }
    }

    public function sendMissingJournalReminders(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
        ]);
        $date = $validated['date'];
        $missingSchedulesByTeacher = $this->schedulesMissingJournal($date)->groupBy('teacher_id');
        $reminderTitle = 'Pengingat Jurnal Mengajar';
        $sentCount = 0;

        $formattedDate = \Carbon\Carbon::parse($date)->format('d-m-Y');

        DB::transaction(function () use ($missingSchedulesByTeacher, $date, $formattedDate, $reminderTitle, &$sentCount): void {
            foreach ($missingSchedulesByTeacher as $teacherId => $schedules) {
                $message = 'Mohon lengkapi jurnal mengajar untuk tanggal '.$formattedDate.'.';
                $alreadySent = Notification::query()
                    ->where('user_id', $teacherId)
                    ->where('title', $reminderTitle)
                    ->where(function ($q) use ($message, $date) {
                        $q->where('message', $message)
                            ->orWhere('message', 'Mohon lengkapi jurnal mengajar untuk tanggal '.$date.'.');
                    })
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                Notification::send((int) $teacherId, $reminderTitle, $message);
                $sentCount++;
            }

            if ($sentCount > 0) {
                AuditLog::record(
                    Auth::id(),
                    'SEND_MISSING_JOURNAL_REMINDERS',
                    'WAKA mengirim pengingat jurnal kepada '.$sentCount.' guru untuk tanggal '.$formattedDate.'.'
                );
            }
        });

        if ($missingSchedulesByTeacher->isEmpty()) {
            return redirect()->route('journals.index', ['date' => $date])
                ->with('success', 'Tidak ada jurnal tertinggal untuk jadwal mengajar pada tanggal tersebut.');
        }

        return redirect()->route('journals.index', ['date' => $date])
            ->with('success', 'Pengingat terkirim ke '.$sentCount.' guru. '.$missingSchedulesByTeacher->count().' guru masih memiliki jurnal yang perlu dilengkapi.');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role->name !== 'guru') {
            abort(403, 'Akses ditolak. Jurnal mengajar hanya dapat diisi oleh Guru.');
        }

        $fallbackAttachmentPath = $this->extractFallbackAttachmentIfNeeded($request);

        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'date' => ['required', 'date'],
            'materi' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], $this->attachmentMessages());

        // Batas waktu: maksimal hari itu. Jika tanggal lebih lampau dari hari ini, tidak bisa diisi lagi.
        if ($validated['date'] < now()->toDateString()) {
            throw ValidationException::withMessages([
                'date' => 'Batas waktu pengisian jurnal mengajar telah berakhir. Jurnal hanya dapat diisi pada hari pelaksanaan KBM (maksimal hari ini).',
            ]);
        }

        $schedule = Schedule::where('teacher_id', $user->id)->findOrFail($validated['schedule_id']);
        abort_unless((int) $schedule->round === Cycle::current()->active_round, 404);

        $attachmentPath = $fallbackAttachmentPath;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('journal_attachments', 'local');
        }

        TeachingJournal::create([
            'teacher_id' => $user->id,
            'schedule_id' => $schedule->id,
            'date' => $validated['date'],
            'materi' => trim($validated['materi']),
            'activity_summary' => trim($validated['summary']),
            'photo_proof' => $attachmentPath,
        ]);

        AuditLog::record($user->id, 'STORE_JOURNAL', 'Guru mengisi jurnal mengajar pada tanggal '.$validated['date']);

        return redirect()->route('journals.index')->with('success', 'Jurnal mengajar berhasil disimpan.');
    }

    public function update(Request $request, TeachingJournal $journal): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role->name !== 'guru' || $journal->teacher_id !== $user->id) {
            abort(403, 'Guru hanya dapat memperbarui jurnal miliknya.');
        }

        // Batas waktu edit: hanya bisa diedit pada hari yang sama (maksimal hari itu)
        $journalDate = is_string($journal->date) ? $journal->date : $journal->date->toDateString();
        if ($journalDate < now()->toDateString()) {
            throw ValidationException::withMessages([
                'date' => 'Batas waktu pengeditan jurnal telah berakhir. Jurnal hari sebelumnya sudah terkunci dan tidak dapat diedit kembali.',
            ]);
        }

        $fallbackAttachmentPath = $this->extractFallbackAttachmentIfNeeded($request);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'materi' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], $this->attachmentMessages());

        $oldAttachmentPath = $journal->photo_proof;
        $attachmentPath = $fallbackAttachmentPath ?? $oldAttachmentPath;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('journal_attachments', 'local');
        }

        $journal->update([
            'date' => $validated['date'],
            'materi' => trim($validated['materi']),
            'activity_summary' => trim($validated['summary']),
            'photo_proof' => $attachmentPath,
        ]);

        if ($attachmentPath !== $oldAttachmentPath && $oldAttachmentPath !== null) {
            Storage::disk('local')->delete($oldAttachmentPath);
        }

        AuditLog::record($user->id, 'UPDATE_JOURNAL', 'Memperbarui jurnal mengajar ID '.$journal->id);

        return redirect()->route('journals.index')->with('success', 'Jurnal mengajar berhasil diperbarui.');
    }

    public function downloadAttachment(TeachingJournal $journal): Response
    {
        $user = Auth::user();
        abort_unless($this->canViewJournalAttachment($user, $journal), 404);

        $path = $journal->photo_proof;
        abort_unless(
            is_string($path)
                && str_starts_with($path, 'journal_attachments/')
                && Storage::disk('local')->exists($path),
            404
        );

        $filename = str_replace(["\r", "\n", '"'], '', basename($path));
        $mime = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';
        $isImage = str_starts_with($mime, 'image/');

        if (request()->query('preview') === '1' || $isImage) {
            return Storage::disk('local')->response($path, $filename, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        return Storage::disk('local')->download($path, $filename);
    }

    /**
     * Waka, BK, dan Wali Kelas melihat foto bukti KBM untuk monitoring kehadiran & kedisiplinan,
     * Guru melihat foto jurnal miliknya, sedangkan Siswa dan Orang Tua hanya foto jurnal yang
     * terhubung dengan presensi dirinya sendiri atau putra/putrinya.
     */
    private function canViewJournalAttachment(User $user, TeachingJournal $journal): bool
    {
        $roleName = $user->role?->name;

        if (in_array($roleName, ['waka', 'bk', 'wali_kelas'], true)) {
            return true;
        }

        if ($roleName === 'guru') {
            return $journal->teacher_id === $user->id;
        }

        if ($roleName === 'siswa') {
            $studentIds = [$user->id];
        } elseif ($roleName === 'orang_tua') {
            $studentIds = $user->students()->pluck('users.id')->all();
        } else {
            return false;
        }

        if (empty($studentIds)) {
            return false;
        }

        return \App\Models\Attendance::query()
            ->where('teaching_journal_id', $journal->id)
            ->whereIn('student_id', $studentIds)
            ->exists();
    }

    /**
     * Handle client-side base64 fallback when PHP's multipart upload fails due to missing temp dir (UPLOAD_ERR_NO_TMP_DIR / UPLOAD_ERR_CANT_WRITE).
     */
    private function extractFallbackAttachmentIfNeeded(Request $request): ?string
    {
        $uploadedFile = $request->file('attachment');
        if ($uploadedFile !== null && $uploadedFile->isValid()) {
            return null;
        }

        $dataUrl = $request->input('attachment_data');
        if (! is_string($dataUrl) || trim($dataUrl) === '') {
            return null;
        }

        // Remove failed PHP upload entry so $request->validate() does not fail with attachment.uploaded
        $request->files->remove('attachment');
        $request->request->remove('attachment');

        if (! preg_match('/^data:([a-zA-Z0-9.+\/-]+);base64,(.+)$/s', $dataUrl, $matches)) {
            throw ValidationException::withMessages([
                'attachment' => $this->attachmentMessages()['attachment.mimes'],
            ]);
        }

        $mime = strtolower(trim($matches[1]));
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
        ];

        if (! isset($allowedMimes[$mime])) {
            throw ValidationException::withMessages([
                'attachment' => $this->attachmentMessages()['attachment.mimes'],
            ]);
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) === 0) {
            throw ValidationException::withMessages([
                'attachment' => $this->attachmentMessages()['attachment.uploaded'],
            ]);
        }

        if (strlen($binary) > 5120 * 1024) {
            throw ValidationException::withMessages([
                'attachment' => $this->attachmentMessages()['attachment.max'],
            ]);
        }

        $extension = $allowedMimes[$mime];
        $path = 'journal_attachments/'.Str::random(40).'.'.$extension;
        Storage::disk('local')->put($path, $binary);

        return $path;
    }

    private function attachmentMessages(): array
    {
        return [
            'attachment.uploaded' => 'Bukti gagal diunggah. Pastikan file berformat JPG, PNG, atau PDF dengan ukuran maksimal 5 MB. Jika ukuran file sudah sesuai dan masih gagal, batas upload PHP di server (upload_max_filesize dan post_max_size) perlu dinaikkan.',
            'attachment.max' => 'Ukuran bukti maksimal 5 MB.',
            'attachment.mimes' => 'Bukti hanya boleh berformat JPG, PNG, atau PDF.',
        ];
    }

    private function schedulesMissingJournal(string $date): Collection
    {
        $day = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ][Carbon::parse($date)->dayOfWeekIso];

        return Schedule::query()
            ->with(['teacher:id,name', 'class', 'subject', 'room', 'jamPelajaran'])
            ->where('day', $day)
            ->where('round', Cycle::current()->active_round)
            ->whereHas('teacher', fn ($query) => $query->where('is_active', true))
            ->whereDoesntHave('journals', fn ($query) => $query->whereDate('date', $date))
            ->get();
    }
}
