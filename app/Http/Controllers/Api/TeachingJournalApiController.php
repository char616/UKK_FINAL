<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\TeachingJournalResource;
use App\Models\AuditLog;
use App\Models\Cycle;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\TeachingJournal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeachingJournalApiController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();
        $role = $user?->role?->name;

        $validated = $role === 'waka'
            ? $request->validate(['date' => ['nullable', 'date', 'before_or_equal:today']])
            : [];
        $journalDate = $validated['date'] ?? ($request->query('date') ?: now()->toDateString());

        $query = TeachingJournal::with([
            'teacher:id,name,login_key',
            'schedule.class:id,name,major_id',
            'schedule.subject:id,code,name',
        ]);

        if ($role === 'guru') {
            $query->where('teacher_id', $user->id);
            if ($request->filled('date')) {
                $query->whereDate('date', (string) $request->query('date'));
            }
        } elseif ($role === 'waka') {
            $query->whereDate('date', (string) $journalDate);
        }

        $journals = $query->latest('date')->limit(100)->get();
        $activeRound = Cycle::current()->active_round;

        $teacherSchedules = $role === 'guru'
            ? Schedule::with(['class.major', 'subject', 'room'])
                ->where('teacher_id', $user->id)
                ->where('round', $activeRound)
                ->orderBy('day')
                ->orderBy('start_time')
                ->get()
            : new Collection;

        $schedulesWithoutJournal = $role === 'waka'
            ? $this->schedulesMissingJournal((string) $journalDate)
            : new Collection;

        $resolvedJournals = TeachingJournalResource::collection($journals)->resolve($request);

        return $this->successResponse($resolvedJournals, 'Daftar jurnal mengajar berhasil diambil.', 200, [
            'count' => count($resolvedJournals),
            'journal_date' => $journalDate,
            'active_round' => (int) $activeRound,
            'teacher_schedules' => ScheduleResource::collection($teacherSchedules)->resolve($request),
            'schedules_without_journal' => ScheduleResource::collection($schedulesWithoutJournal)->resolve($request),
        ]);
    }

    public function sendMissingJournalReminders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $date = $validated['date'];
        $missingSchedulesByTeacher = $this->schedulesMissingJournal($date)->groupBy('teacher_id');
        $reminderTitle = 'Pengingat Jurnal Mengajar';
        $formattedDate = \Carbon\Carbon::parse($date)->format('d-m-Y');
        $sentCount = 0;

        DB::transaction(function () use ($request, $missingSchedulesByTeacher, $date, $formattedDate, $reminderTitle, &$sentCount): void {
            foreach ($missingSchedulesByTeacher as $teacherId => $schedules) {
                $message = 'Mohon lengkapi jurnal mengajar untuk tanggal ' . $formattedDate . '.';
                $alreadySent = Notification::query()
                    ->where('user_id', $teacherId)
                    ->where('title', $reminderTitle)
                    ->where(function ($q) use ($message, $date) {
                        $q->where('message', $message)
                            ->orWhere('message', 'Mohon lengkapi jurnal mengajar untuk tanggal ' . $date . '.');
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
                    $request->user()->id,
                    'SEND_MISSING_JOURNAL_REMINDERS',
                    'WAKA mengirim pengingat jurnal kepada ' . $sentCount . ' guru untuk tanggal ' . $formattedDate . '.'
                );
            }
        });

        $message = $missingSchedulesByTeacher->isEmpty()
            ? 'Tidak ada jurnal tertinggal untuk jadwal mengajar pada tanggal tersebut.'
            : 'Pengingat terkirim ke ' . $sentCount . ' guru. ' . $missingSchedulesByTeacher->count() . ' guru masih memiliki jurnal yang perlu dilengkapi.';

        return $this->successResponse([
            'date' => $date,
            'sent_count' => $sentCount,
            'teachers_missing_count' => $missingSchedulesByTeacher->count(),
        ], $message);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role?->name !== 'guru') {
            return $this->errorResponse('Akses ditolak. Jurnal mengajar hanya dapat diisi oleh Guru.', 403);
        }

        $fallbackAttachmentPath = $this->extractFallbackAttachmentIfNeeded($request);

        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'date' => ['required', 'date'],
            'materi' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], $this->attachmentMessages());

        $schedule = Schedule::where('teacher_id', $user->id)->find($validated['schedule_id']);
        if (! $schedule) {
            return $this->errorResponse('Jadwal mengajar tidak ditemukan atau bukan milik Anda.', 404);
        }

        if ((int) $schedule->round !== (int) Cycle::current()->active_round) {
            return $this->errorResponse('Jadwal tidak berada pada putaran aktif saat ini.', 404);
        }

        $attachmentPath = $fallbackAttachmentPath;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('journal_attachments', 'local');
        }

        $journal = TeachingJournal::create([
            'teacher_id' => $user->id,
            'schedule_id' => $schedule->id,
            'date' => $validated['date'],
            'materi' => trim($validated['materi']),
            'activity_summary' => trim($validated['summary']),
            'photo_proof' => $attachmentPath,
        ]);

        AuditLog::record($user->id, 'STORE_JOURNAL', 'Guru mengisi jurnal mengajar pada tanggal ' . $validated['date']);
        $journal->load(['teacher', 'schedule.class', 'schedule.subject']);

        return $this->successResponse(
            new TeachingJournalResource($journal),
            'Jurnal mengajar berhasil disimpan.',
            201
        );
    }

    public function update(Request $request, TeachingJournal $journal): JsonResponse
    {
        $user = $request->user();
        if ($user->role?->name !== 'guru' || (int) $journal->teacher_id !== (int) $user->id) {
            return $this->errorResponse('Guru hanya dapat memperbarui jurnal miliknya.', 403);
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

        AuditLog::record($user->id, 'UPDATE_JOURNAL', 'Memperbarui jurnal mengajar ID ' . $journal->id);
        $journal->load(['teacher', 'schedule.class', 'schedule.subject']);

        return $this->successResponse(
            new TeachingJournalResource($journal),
            'Jurnal mengajar berhasil diperbarui.'
        );
    }

    public function downloadAttachment(Request $request, TeachingJournal $journal)
    {
        $user = $request->user();
        $roleName = $user->role?->name;
        $authorized = in_array($roleName, ['waka', 'bk', 'wali_kelas'], true)
            || ($roleName === 'guru' && (int) $journal->teacher_id === (int) $user->id);

        if (! $authorized) {
            return $this->errorResponse('Lampiran jurnal tidak ditemukan atau akses ditolak.', 404);
        }

        $path = $journal->photo_proof;
        if (! is_string($path) || ! str_starts_with($path, 'journal_attachments/') || ! Storage::disk('local')->exists($path)) {
            return $this->errorResponse('File lampiran jurnal tidak ditemukan.', 404);
        }

        $mime = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';
        $filename = str_replace(["\r", "\n", '"'], '', basename($path));

        return Storage::disk('local')->response($path, $filename, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

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
        $path = 'journal_attachments/' . Str::random(40) . '.' . $extension;
        Storage::disk('local')->put($path, $binary);

        return $path;
    }

    private function attachmentMessages(): array
    {
        return [
            'attachment.uploaded' => 'Bukti gagal diunggah. Pastikan file berformat JPG, PNG, atau PDF dengan ukuran maksimal 5 MB.',
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
            ->with(['teacher:id,name,login_key', 'class:id,name,major_id', 'subject:id,code,name', 'room:id,code,name'])
            ->where('day', $day)
            ->where('round', Cycle::current()->active_round)
            ->whereHas('teacher', fn ($query) => $query->where('is_active', true))
            ->whereDoesntHave('journals', fn ($query) => $query->whereDate('date', $date))
            ->get();
    }
}