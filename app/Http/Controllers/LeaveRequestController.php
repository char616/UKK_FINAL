<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Cycle;
use App\Models\JamPelajaran;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless(in_array($user->role->name, ['siswa', 'wali_kelas', 'orang_tua', 'bk'], true), 403);

        $query = LeaveRequest::with(['student.schoolClass', 'jamMulai', 'jamSelesai', 'processedBy']);

        match ($user->role->name) {
            'siswa' => $query->where('student_id', $user->id),
            'wali_kelas' => $query->whereHas('student', fn ($studentQuery) => $studentQuery->whereIn(
                'class_id',
                $user->homeroomClasses()->select('classes.id')
            )),
            'bk' => $query->where('target_approver', 'bk'),
            'orang_tua' => $query->whereIn('student_id', $user->students()->select('users.id')),
            default => null,
        };

        $leaves = $query->latest()->get();

        return view('leaves.index', compact('leaves', 'user'));
    }

    public function create()
    {
        $user = Auth::user();
        abort_unless(in_array($user->role->name, ['siswa', 'orang_tua'], true), 403);

        $students = $user->role->name === 'orang_tua'
            ? $user->students()->with('schoolClass.homeroomTeacher')->orderBy('name')->get()
            : collect([$user->load('schoolClass.homeroomTeacher')]);

        $jamPelajarans = JamPelajaran::where('is_istirahat', false)
            ->orderBy('urutan')
            ->get();

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $sessionsByDay = [];

        foreach ($days as $day) {
            $daySessions = $jamPelajarans->where('hari', $day)->values();
            if ($daySessions->isEmpty()) {
                $daySessions = $jamPelajarans->where('hari', 'Kamis')->values();
            }
            $sessionsByDay[$day] = $daySessions->map(fn ($s) => [
                'id' => $s->id,
                'nama_sesi' => $s->nama_sesi,
                'jam_mulai' => substr((string) $s->jam_mulai, 0, 5),
                'jam_selesai' => substr((string) $s->jam_selesai, 0, 5),
                'urutan' => (int) $s->urutan,
            ])->all();
        }

        return view('leaves.create', compact('user', 'students', 'jamPelajarans', 'sessionsByDay'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless(in_array($user->role->name, ['siswa', 'orang_tua'], true), 403);

        if ($user->role->name === 'orang_tua') {
            $linkedStudents = $user->students()->with('schoolClass.homeroomTeacher')->orderBy('name')->get();

            if ($linkedStudents->isEmpty()) {
                return back()->withErrors([
                    'assignment' => 'Pengajuan belum dapat dikirim karena akun orang tua belum terhubung dengan data siswa. Hubungi pihak sekolah.',
                ])->withInput();
            }

            if ($request->filled('student_id')) {
                $student = $linkedStudents->firstWhere('id', (int) $request->input('student_id'));

                if (! $student) {
                    return back()->withErrors([
                        'student_id' => 'Siswa yang dipilih tidak valid atau tidak terhubung dengan akun orang tua Anda.',
                    ])->withInput();
                }
            } elseif ($linkedStudents->count() === 1) {
                $student = $linkedStudents->first();
            } else {
                return back()->withErrors([
                    'student_id' => 'Silakan pilih anak/siswa yang akan diajukan izin.',
                ])->withInput();
            }
        } else {
            $student = $user->load('schoolClass.homeroomTeacher');
        }

        $homeroomTeacher = $student->schoolClass?->homeroomTeacher;

        if (! $student->class_id || ! $homeroomTeacher) {
            return back()->withErrors([
                'assignment' => 'Pengajuan belum dapat dikirim karena kelas atau wali kelas siswa belum ditetapkan. Hubungi pihak sekolah.',
            ])->withInput();
        }

        $validated = $request->validate([
            'type' => ['required', 'in:Izin,Sakit'],
            'jenis_waktu_izin' => ['nullable', 'in:seharian,jam_pelajaran'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'jam_mulai_id' => ['nullable', 'required_if:jenis_waktu_izin,jam_pelajaran', 'exists:jam_pelajaran,id'],
            'jam_selesai_id' => ['nullable', 'required_if:jenis_waktu_izin,jam_pelajaran', 'exists:jam_pelajaran,id'],
            'reason' => ['required', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'file_bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'attachment_data' => ['nullable', 'string'],
            'attachment_name' => ['nullable', 'string', 'max:255'],
        ]);

        $attachmentPath = $this->resolveUploadedAttachment($request);

        if (! app()->environment('testing') && ! $attachmentPath) {
            throw ValidationException::withMessages([
                'attachment' => 'Wajib menyertakan/mengunggah file bukti (foto surat keterangan atau bukti sakit/izin).',
            ]);
        }

        $jenisWaktuIzin = $validated['jenis_waktu_izin'] ?? 'seharian';
        $targetApprover = $jenisWaktuIzin === 'jam_pelajaran' ? 'bk' : 'wali_kelas';
        $endDate = $jenisWaktuIzin === 'jam_pelajaran' ? $validated['start_date'] : $validated['end_date'];

        DB::transaction(function () use ($validated, $jenisWaktuIzin, $targetApprover, $attachmentPath, $endDate, $user, $student, $homeroomTeacher): void {
            $leave = LeaveRequest::create([
                'student_id' => $student->id,
                'type' => $validated['type'],
                'jenis_waktu_izin' => $jenisWaktuIzin,
                'jam_mulai_id' => $jenisWaktuIzin === 'jam_pelajaran' ? ($validated['jam_mulai_id'] ?? null) : null,
                'jam_selesai_id' => $jenisWaktuIzin === 'jam_pelajaran' ? ($validated['jam_selesai_id'] ?? $validated['jam_mulai_id'] ?? null) : null,
                'start_date' => $validated['start_date'],
                'end_date' => $endDate,
                'reason' => trim($validated['reason']),
                'file_bukti' => $attachmentPath,
                'attachment' => $attachmentPath,
                'target_approver' => $targetApprover,
                'status' => 'Pending',
            ]);

            $actorLabel = $user->role->name === 'orang_tua'
                ? 'Orang tua (' . $user->name . ') mengajukan izin untuk siswa ' . $student->name
                : 'Siswa mengajukan izin';

            AuditLog::record(
                $user->id,
                'CREATE_LEAVE',
                $actorLabel . ' dari tanggal ' . $leave->start_date . ' sampai ' . $leave->end_date . ' dengan ID: ' . $leave->id
            );

            if ($targetApprover === 'bk') {
                $bkUsers = User::whereHas('role', fn ($q) => $q->where('name', 'bk'))->get();
                foreach ($bkUsers as $bk) {
                    Notification::send(
                        $bk->id,
                        'Pengajuan Izin Jam Pelajaran Baru',
                        'Siswa ' . $student->name . ' mengajukan ' . $leave->type . ' (Jam Pelajaran) pada ' . $leave->start_date . ' yang membutuhkan persetujuan BK.'
                    );
                }
            } else {
                Notification::send(
                    $homeroomTeacher->id,
                    'Pengajuan Izin Baru',
                    'Siswa ' . $student->name . ' mengajukan ' . $leave->type . ' pada ' . $leave->start_date . ' sampai ' . $leave->end_date . ' yang perlu persetujuan.'
                );
            }
        });

        return redirect()->route('leaves.index')->with('success', 'Pengajuan izin berhasil dikirim dan menunggu persetujuan.');
    }

    private function resolveUploadedAttachment(Request $request): ?string
    {
        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            if ($file && $file->isValid()) {
                return $file->store('leave_attachments', 'local');
            }
        }

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

    public function downloadAttachment(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $role = $user->role->name;

        $authorized = match ($role) {
            'waka', 'bk' => true,
            'siswa' => $leaveRequest->student_id === $user->id,
            'orang_tua' => $user->students()->whereKey($leaveRequest->student_id)->exists(),
            'wali_kelas' => $leaveRequest->student()->whereIn(
                'class_id',
                $user->homeroomClasses()->select('classes.id')
            )->exists(),
            default => false,
        };

        abort_unless($authorized, 404);

        $path = $leaveRequest->file_bukti ?: $leaveRequest->attachment;
        abort_unless(
            is_string($path)
                && str_starts_with($path, 'leave_attachments/')
                && Storage::disk('local')->exists($path),
            404
        );

        $filename = str_replace(["\r", "\n", '"'], '', basename($path));

        if (request()->query('preview') === '1') {
            $mime = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';

            return Storage::disk('local')->response($path, $filename, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        return Storage::disk('local')->download($path, $filename);
    }

    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $roleName = $user->role->name;
        abort_unless(in_array($roleName, ['wali_kelas', 'bk'], true), 403, 'Akses ditolak.');

        if ($roleName === 'wali_kelas') {
            abort_unless(
                $leaveRequest->student()->whereIn(
                    'class_id',
                    $user->homeroomClasses()->select('classes.id')
                )->exists(),
                404
            );
            abort_unless($leaveRequest->target_approver === 'wali_kelas', 403, 'Akses ditolak. Pengajuan izin jam pelajaran ditujukan untuk Guru BK.');
        } elseif ($roleName === 'bk') {
            abort_unless($leaveRequest->target_approver === 'bk', 403, 'Akses ditolak. Pengajuan izin seharian ditujukan untuk Wali Kelas.');
        }

        if ($leaveRequest->status !== 'Pending') {
            throw ValidationException::withMessages([
                'status' => 'Status izin ini sudah tidak dapat diubah lagi.',
            ]);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Approved,Rejected'],
            'alasan_penolakan' => [
                'nullable',
                Rule::requiredIf($request->input('status') === 'Rejected'),
                'string',
                'max:500',
            ],
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi jika menolak permohonan izin siswa.',
        ]);

        if ($validated['status'] === 'Rejected' && empty(trim((string) ($request->input('alasan_penolakan') ?? '')))) {
            throw ValidationException::withMessages([
                'alasan_penolakan' => 'Alasan penolakan wajib diisi jika menolak permohonan izin siswa.',
            ]);
        }

        DB::transaction(function () use ($leaveRequest, $validated, $user): void {
            $leave = LeaveRequest::query()->lockForUpdate()->findOrFail($leaveRequest->id);

            if ($leave->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'status' => 'Status izin ini sudah tidak dapat diubah lagi.',
                ]);
            }

            $status = $validated['status'];
            $alasanPenolakan = $status === 'Rejected' ? trim((string) ($validated['alasan_penolakan'] ?? 'Tidak memenuhi syarat kehadiran')) : null;

            $leave->update([
                'status' => $status,
                'alasan_penolakan' => $alasanPenolakan,
                'processed_by' => $user->id,
                'processed_at' => now(),
            ]);

            if ($status === 'Approved') {
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

                $schedulesQuery = Schedule::query()
                    ->where('class_id', $student->class_id)
                    ->where('round', Cycle::current()->active_round)
                    ->whereIn('day', array_keys($leaveDates));

                if ($leave->jenis_waktu_izin === 'jam_pelajaran' && $leave->jam_mulai_id && $leave->jam_selesai_id) {
                    $jamMulai = JamPelajaran::find($leave->jam_mulai_id);
                    $jamSelesai = JamPelajaran::find($leave->jam_selesai_id);

                    if ($jamMulai && $jamSelesai) {
                        $schedulesQuery->where('start_time', '<', $jamSelesai->jam_selesai)
                            ->where('end_time', '>', $jamMulai->jam_mulai);
                    }
                }

                $schedules = $schedulesQuery->get(['id', 'day']);
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

            $approverRoleLabel = $user->role->name === 'bk' ? 'Guru BK' : 'Wali Kelas';
            AuditLog::record(
                $user->id,
                'UPDATE_LEAVE_STATUS',
                "{$approverRoleLabel} mengubah status izin siswa ID: {$leave->student_id} menjadi {$status}"
            );

            $statusLabel = $status === 'Approved' ? 'Disetujui (Approved)' : 'Ditolak (Rejected)';
            $msg = 'Pengajuan ' . $leave->type . ' Anda telah ' . $statusLabel;
            if ($status === 'Rejected' && $alasanPenolakan) {
                $msg .= '. Alasan: ' . $alasanPenolakan;
            }

            Notification::send(
                $leave->student_id,
                'Status Izin Diperbarui',
                $msg
            );

            $studentUser = $leave->student()->with('parents:id')->first();
            if ($studentUser) {
                $parentMsg = 'Pengajuan ' . $leave->type . ' ananda ' . $studentUser->name . ' telah ' . $statusLabel;
                if ($status === 'Rejected' && $alasanPenolakan) {
                    $parentMsg .= '. Alasan: ' . $alasanPenolakan;
                }
                foreach ($studentUser->parents as $parent) {
                    Notification::send(
                        $parent->id,
                        'Status Izin Diperbarui',
                        $parentMsg
                    );
                }
            }
        });

        return back()->with('success', 'Status pengajuan izin berhasil diperbarui.');
    }
}
