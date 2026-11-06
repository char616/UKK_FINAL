<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveRequestResource;
use App\Http\Resources\UserResource;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Cycle;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LeaveRequestApiController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveApiUser($request);
        $query = LeaveRequest::with(['student.schoolClass']);

        if ($user) {
            $role = $user->role?->name;
            if (! in_array($role, ['siswa', 'wali_kelas', 'orang_tua', 'waka'], true)) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            match ($role) {
                'siswa' => $query->where('student_id', $user->id),
                'wali_kelas' => $query->whereHas('student', fn ($studentQuery) => $studentQuery->whereIn(
                    'class_id',
                    $user->homeroomClasses()->select('classes.id')
                )),
                'orang_tua' => $query->whereIn('student_id', $user->students()->select('users.id')),
                default => null,
            };
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', (int) $request->query('student_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        $leaves = $query->latest()->limit(100)->get();
        $resolvedLeaves = LeaveRequestResource::collection($leaves)->resolve($request);

        return $this->successResponse($resolvedLeaves, 'Daftar pengajuan izin berhasil diambil.', 200, [
            'count' => count($resolvedLeaves),
        ]);
    }

    public function createMeta(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user->role?->name, ['siswa', 'orang_tua'], true)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $students = $user->role->name === 'orang_tua'
            ? $user->students()->with(['role', 'schoolClass.homeroomTeacher', 'schoolClass.major'])->orderBy('name')->get()
            : collect([$user->load(['role', 'schoolClass.homeroomTeacher', 'schoolClass.major'])]);

        return $this->successResponse([
            'allowed_types' => ['Izin', 'Sakit'],
            'students' => UserResource::collection($students)->resolve($request),
        ], 'Data formulir pengajuan izin berhasil diambil.');
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->resolveApiUser($request);

        if (! $user && $request->filled('actor_login_key')) {
            $user = User::with(['role', 'students.schoolClass.homeroomTeacher', 'schoolClass.homeroomTeacher'])
                ->where('login_key', trim((string) $request->input('actor_login_key')))
                ->first();
        }

        if (! $user || ! in_array($user->role?->name, ['siswa', 'orang_tua'], true)) {
            return $this->errorResponse('Pengajuan izin hanya dapat dilakukan oleh akun Siswa atau Orang Tua.', 403);
        }

        if ($user->role->name === 'orang_tua') {
            $linkedStudents = $user->students()->with('schoolClass.homeroomTeacher')->orderBy('name')->get();

            if ($linkedStudents->isEmpty()) {
                return $this->errorResponse(
                    'Pengajuan belum dapat dikirim karena akun orang tua belum terhubung dengan data siswa. Hubungi pihak sekolah.',
                    422,
                    ['assignment' => ['Akun orang tua belum terhubung dengan data siswa.']]
                );
            }

            if ($request->filled('student_id')) {
                $student = $linkedStudents->firstWhere('id', (int) $request->input('student_id'));

                if (! $student) {
                    return $this->errorResponse(
                        'Siswa yang dipilih tidak valid atau tidak terhubung dengan akun orang tua Anda.',
                        422,
                        ['student_id' => ['Siswa yang dipilih tidak valid atau tidak terhubung dengan akun orang tua Anda.']]
                    );
                }
            } elseif ($linkedStudents->count() === 1) {
                $student = $linkedStudents->first();
            } else {
                return $this->errorResponse(
                    'Silakan pilih anak/siswa yang akan diajukan izin.',
                    422,
                    ['student_id' => ['Silakan pilih anak/siswa yang akan diajukan izin.']]
                );
            }
        } else {
            $student = $user->load('schoolClass.homeroomTeacher');
        }

        $homeroomTeacher = $student->schoolClass?->homeroomTeacher;
        if (! $student->class_id || ! $homeroomTeacher) {
            return $this->errorResponse(
                'Pengajuan belum dapat dikirim karena kelas atau wali kelas siswa belum ditetapkan. Hubungi pihak sekolah.',
                422,
                ['assignment' => ['Kelas atau wali kelas siswa belum ditetapkan.']]
            );
        }

        $validated = $request->validate([
            'type' => ['required', 'in:Izin,Sakit'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'attachment_data' => ['nullable', 'string'],
            'attachment_name' => ['nullable', 'string', 'max:255'],
        ]);

        $attachmentPath = $this->resolveUploadedAttachment($request);

        $leave = DB::transaction(function () use ($validated, $attachmentPath, $user, $student, $homeroomTeacher): LeaveRequest {
            $leave = LeaveRequest::create([
                'student_id' => $student->id,
                'type' => $validated['type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => trim($validated['reason']),
                'attachment' => $attachmentPath,
                'status' => 'Pending',
            ]);

            $actorLabel = $user->role->name === 'orang_tua'
                ? 'Orang tua (' . $user->name . ') mengajukan izin untuk siswa ' . $student->name
                : 'Siswa mengajukan izin';

            AuditLog::record(
                $user->id,
                'CREATE_LEAVE',
                $actorLabel . ' dari tanggal ' . $leave->start_date->toDateString() . ' sampai ' . $leave->end_date->toDateString() . ' dengan ID: ' . $leave->id
            );

            Notification::send(
                $homeroomTeacher->id,
                'Pengajuan Izin Baru',
                'Siswa ' . $student->name . ' mengajukan ' . $leave->type . ' pada ' . $leave->start_date->toDateString() . ' sampai ' . $leave->end_date->toDateString() . ' yang perlu persetujuan.'
            );

            return $leave;
        });

        $leave->load(['student.schoolClass']);

        return $this->successResponse(
            new LeaveRequestResource($leave),
            'Pengajuan izin berhasil dikirim dan menunggu persetujuan Wali Kelas.',
            201
        );
    }

    public function updateStatus(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $user = $request->user();
        $roleName = $user->role?->name;
        if (! in_array($roleName, ['wali_kelas', 'bk'], true)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        if ($roleName === 'wali_kelas') {
            $isInHomeroom = $leaveRequest->student()->whereIn(
                'class_id',
                $user->homeroomClasses()->select('classes.id')
            )->exists();

            if (! $isInHomeroom) {
                return $this->errorResponse('Pengajuan izin tidak ditemukan pada kelas binaan Anda.', 404);
            }

            if ($leaveRequest->target_approver !== 'wali_kelas') {
                return $this->errorResponse('Akses ditolak. Pengajuan izin jam pelajaran ditujukan untuk Guru BK.', 403);
            }
        } elseif ($roleName === 'bk') {
            if ($leaveRequest->target_approver !== 'bk') {
                return $this->errorResponse('Akses ditolak. Pengajuan izin seharian ditujukan untuk Wali Kelas.', 403);
            }
        }

        if ($leaveRequest->status !== 'Pending') {
            throw ValidationException::withMessages([
                'status' => 'Status izin ini sudah tidak dapat diubah lagi.',
            ]);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Approved,Rejected'],
            'alasan_penolakan' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['status'] === 'Rejected' && empty(trim((string) ($request->input('alasan_penolakan') ?? '')))) {
            throw ValidationException::withMessages([
                'alasan_penolakan' => 'Alasan penolakan wajib diisi jika menolak permohonan izin siswa.',
            ]);
        }

        $updatedLeave = DB::transaction(function () use ($user, $leaveRequest, $validated, $request): LeaveRequest {
            $leave = LeaveRequest::query()->lockForUpdate()->findOrFail($leaveRequest->id);

            if ($leave->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'status' => 'Status izin ini sudah tidak dapat diubah lagi.',
                ]);
            }

            $alasan = $validated['status'] === 'Rejected' ? trim((string) ($request->input('alasan_penolakan') ?? '')) : null;

            $leave->update([
                'status' => $validated['status'],
                'alasan_penolakan' => $alasan,
                'processed_by' => $user->id,
                'processed_at' => now(),
            ]);

            if ($validated['status'] === 'Approved') {
                $student = $leave->student()->firstOrFail();
                $dayNames = [
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                ];
                $leaveDates = [];

                foreach (CarbonPeriod::create($leave->start_date, $leave->end_date) as $date) {
                    $day = $dayNames[$date->format('l')] ?? null;
                    if ($day !== null) {
                        $leaveDates[$day][] = $date->toDateString();
                    }
                }

                $schedules = Schedule::query()
                    ->where('class_id', $student->class_id)
                    ->where('round', Cycle::current()->active_round)
                    ->whereIn('day', array_keys($leaveDates))
                    ->get(['id', 'day']);
                $attendanceRows = [];
                $timestamp = now();

                foreach ($schedules as $schedule) {
                    foreach ($leaveDates[$schedule->day] as $date) {
                        $attendanceRows[] = [
                            'schedule_id' => $schedule->id,
                            'student_id' => $student->id,
                            'date' => $date,
                            'status' => $leave->type,
                            'notes' => $leave->reason,
                            'recorded_by' => $user->id,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }

                if ($attendanceRows !== []) {
                    Attendance::upsert(
                        $attendanceRows,
                        ['schedule_id', 'student_id', 'date'],
                        ['status', 'notes', 'recorded_by', 'updated_at']
                    );
                }
            }

            AuditLog::record(
                $user->id,
                'UPDATE_LEAVE_STATUS',
                'Wali kelas mengubah status izin siswa ID: ' . $leave->student_id . ' menjadi ' . $validated['status']
            );

            $statusLabel = $validated['status'] === 'Approved' ? 'Disetujui (Approved)' : 'Ditolak (Rejected)';

            Notification::send(
                $leave->student_id,
                'Status Izin Diperbarui',
                'Pengajuan ' . $leave->type . ' Anda telah ' . $statusLabel
            );

            $studentUser = $leave->student()->with('parents:id')->first();
            if ($studentUser) {
                foreach ($studentUser->parents as $parent) {
                    Notification::send(
                        $parent->id,
                        'Status Izin Diperbarui',
                        'Pengajuan ' . $leave->type . ' ananda ' . $studentUser->name . ' telah ' . $statusLabel
                    );
                }
            }

            return $leave;
        });

        $updatedLeave->load(['student.schoolClass']);

        return $this->successResponse(
            new LeaveRequestResource($updatedLeave),
            'Status pengajuan izin berhasil diperbarui.'
        );
    }

    public function downloadAttachment(Request $request, LeaveRequest $leaveRequest)
    {
        $user = $request->user();
        $role = $user->role?->name;

        $authorized = match ($role) {
            'waka', 'bk' => true,
            'siswa' => (int) $leaveRequest->student_id === (int) $user->id,
            'orang_tua' => $user->students()->whereKey($leaveRequest->student_id)->exists(),
            'wali_kelas' => $leaveRequest->student()->whereIn(
                'class_id',
                $user->homeroomClasses()->select('classes.id')
            )->exists(),
            default => false,
        };

        if (! $authorized) {
            return $this->errorResponse('Lampiran izin tidak ditemukan atau akses ditolak.', 404);
        }

        $path = $leaveRequest->file_bukti ?: $leaveRequest->attachment;
        if (! is_string($path) || ! str_starts_with($path, 'leave_attachments/') || ! Storage::disk('local')->exists($path)) {
            return $this->errorResponse('File lampiran izin tidak ditemukan.', 404);
        }

        $filename = str_replace(["\r", "\n", '"'], '', basename($path));

        if ($request->query('preview') === '1') {
            $mime = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';

            return Storage::disk('local')->response($path, $filename, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        return Storage::disk('local')->download($path, $filename);
    }

    private function resolveUploadedAttachment(Request $request): ?string
    {
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            if ($file && $file->isValid()) {
                return $file->store('leave_attachments', 'local');
            }
        }

        $base64Data = $request->input('attachment_data');
        if (! is_string($base64Data) || trim($base64Data) === '') {
            return null;
        }

        if (! preg_match('/^data:([a-zA-Z0-9\/+.-]+);base64,(.+)$/s', $base64Data, $matches)) {
            throw ValidationException::withMessages([
                'attachment' => 'Format lampiran bukti tidak valid.',
            ]);
        }

        $mimeType = strtolower(trim($matches[1]));
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
        ];

        if (! isset($allowedMimes[$mimeType])) {
            throw ValidationException::withMessages([
                'attachment' => 'Lampiran izin harus berupa file JPG, JPEG, PNG, atau PDF.',
            ]);
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) === 0) {
            throw ValidationException::withMessages([
                'attachment' => 'Data lampiran bukti tidak dapat dibaca.',
            ]);
        }

        if (strlen($binary) > 5 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'attachment' => 'Ukuran file bukti maksimal 5 MB.',
            ]);
        }

        $extension = $allowedMimes[$mimeType];
        $filename = 'leave_attachments/' . uniqid('leave_', true) . '.' . $extension;
        Storage::disk('local')->put($filename, $binary);

        return $filename;
    }
}
