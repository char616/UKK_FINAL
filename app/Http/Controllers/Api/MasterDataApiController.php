<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentImportController;
use App\Http\Resources\MajorResource;
use App\Http\Resources\RoomResource;
use App\Http\Resources\SchoolClassResource;
use App\Http\Resources\SubjectResource;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Major;
use App\Models\Role;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class MasterDataApiController extends Controller
{
    use ApiResponses;

    private const REQUIRED_HEADERS = [
        'student_login_id',
        'student_name',
        'class_name',
        'major_code',
        'student_recovery_email',
        'parent_name',
        'parent_recovery_email',
    ];

    public function index(Request $request): JsonResponse
    {
        $majors = Major::withCount('classes')->orderBy('name')->get();
        $classes = Classes::with(['major', 'homeroomTeacher'])
            ->withCount(['students', 'schedules'])
            ->orderBy('name')
            ->get();
        $subjects = Subject::withCount('schedules')->orderBy('name')->get();
        $rooms = Room::withCount('schedules')->orderBy('name')->get();
        $homeroomTeachers = User::with(['role', 'homeroomClasses:id,name,homeroom_teacher_id'])
            ->whereHas('role', fn ($q) => $q->where('name', 'wali_kelas'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $students = User::with(['role', 'schoolClass.major', 'parents:id,name,login_key,recovery_email'])
            ->whereHas('role', fn ($q) => $q->where('name', 'siswa'))
            ->orderBy('name')
            ->get();
        $parents = User::with(['role', 'students:id,name,login_key,class_id', 'students.schoolClass:id,name'])
            ->whereHas('role', fn ($q) => $q->where('name', 'orang_tua'))
            ->orderBy('name')
            ->get();

        return $this->successResponse([
            'majors' => MajorResource::collection($majors)->resolve($request),
            'classes' => SchoolClassResource::collection($classes)->resolve($request),
            'subjects' => SubjectResource::collection($subjects)->resolve($request),
            'rooms' => RoomResource::collection($rooms)->resolve($request),
            'homeroom_teachers' => UserResource::collection($homeroomTeachers)->resolve($request),
            'students' => UserResource::collection($students)->resolve($request),
            'parents' => UserResource::collection($parents)->resolve($request),
        ], 'Master data berhasil diambil.');
    }

    public function storeMajor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:majors,code'],
        ]);

        $major = Major::create([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
        ]);

        AuditLog::record($request->user()->id, 'CREATE_MAJOR', 'WAKA menambahkan jurusan ' . $major->name . ' (' . $major->code . ')');

        return $this->successResponse(new MajorResource($major), 'Jurusan berhasil ditambahkan.', 201);
    }

    public function updateMajor(Request $request, Major $major): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('majors', 'code')->ignore($major->id)],
        ]);

        $major->update([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
        ]);

        AuditLog::record($request->user()->id, 'UPDATE_MAJOR', 'WAKA memperbarui jurusan ID ' . $major->id);

        return $this->successResponse(new MajorResource($major->fresh()), 'Jurusan berhasil diperbarui.');
    }

    public function destroyMajor(Request $request, Major $major): JsonResponse
    {
        if ($major->classes()->exists()) {
            return $this->errorResponse(
                'Jurusan tidak dapat dihapus karena masih memiliki data kelas.',
                422,
                ['major' => ['Jurusan tidak dapat dihapus karena masih memiliki data kelas.']]
            );
        }

        $name = $major->name;
        $major->delete();

        AuditLog::record($request->user()->id, 'DELETE_MAJOR', 'WAKA menghapus jurusan ' . $name);

        return $this->successResponse(null, 'Jurusan berhasil dihapus.');
    }

    public function storeClass(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'major_id' => ['required', 'integer', 'exists:majors,id'],
            'phase' => ['nullable', 'string', Rule::in(['ganjil', 'genap'])],
            'homeroom_teacher_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $teacherId = $validated['homeroom_teacher_id'] ?? null;
        if ($teacherId !== null) {
            $this->validateHomeroomTeacherEligibility((int) $teacherId);
        }

        $schoolClass = Classes::create([
            'name' => trim($validated['name']),
            'major_id' => (int) $validated['major_id'],
            'phase' => $validated['phase'] ?? 'ganjil',
            'homeroom_teacher_id' => $teacherId,
        ]);

        $schoolClass->load(['major', 'homeroomTeacher']);
        AuditLog::record($request->user()->id, 'CREATE_CLASS', 'WAKA menambahkan kelas ' . $schoolClass->name);

        return $this->successResponse(new SchoolClassResource($schoolClass), 'Kelas berhasil ditambahkan.', 201);
    }

    public function updateClass(Request $request, Classes $class): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'major_id' => ['required', 'integer', 'exists:majors,id'],
            'phase' => ['nullable', 'string', Rule::in(['ganjil', 'genap'])],
            'homeroom_teacher_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $attributes = [
            'name' => trim($validated['name']),
            'major_id' => (int) $validated['major_id'],
            'phase' => $validated['phase'] ?? $class->phase ?? 'ganjil',
        ];

        if ($request->exists('homeroom_teacher_id')) {
            $teacherId = $validated['homeroom_teacher_id'] ?? null;
            if ($teacherId !== null) {
                $this->validateHomeroomTeacherEligibility((int) $teacherId, $class->id);
            }
            $attributes['homeroom_teacher_id'] = $teacherId;
        }

        $class->update($attributes);
        $class->load(['major', 'homeroomTeacher']);

        AuditLog::record($request->user()->id, 'UPDATE_CLASS', 'WAKA memperbarui kelas ID ' . $class->id);

        return $this->successResponse(new SchoolClassResource($class), 'Kelas berhasil diperbarui.');
    }

    public function destroyClass(Request $request, Classes $class): JsonResponse
    {
        if ($class->students()->exists() || $class->schedules()->exists()) {
            return $this->errorResponse(
                'Kelas tidak dapat dihapus karena masih memiliki siswa atau jadwal pelajaran.',
                422,
                ['class' => ['Kelas tidak dapat dihapus karena masih memiliki siswa atau jadwal pelajaran.']]
            );
        }

        $name = $class->name;
        $class->delete();

        AuditLog::record($request->user()->id, 'DELETE_CLASS', 'WAKA menghapus kelas ' . $name);

        return $this->successResponse(null, 'Kelas berhasil dihapus.');
    }

    public function storeSubject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code'],
        ]);

        $subject = Subject::create([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
        ]);

        AuditLog::record($request->user()->id, 'CREATE_SUBJECT', 'WAKA menambahkan mata pelajaran ' . $subject->name);

        return $this->successResponse(new SubjectResource($subject), 'Mata pelajaran berhasil ditambahkan.', 201);
    }

    public function updateSubject(Request $request, Subject $subject): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('subjects', 'code')->ignore($subject->id)],
        ]);

        $subject->update([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
        ]);

        AuditLog::record($request->user()->id, 'UPDATE_SUBJECT', 'WAKA memperbarui mata pelajaran ID ' . $subject->id);

        return $this->successResponse(new SubjectResource($subject->fresh()), 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroySubject(Request $request, Subject $subject): JsonResponse
    {
        if ($subject->schedules()->exists()) {
            return $this->errorResponse(
                'Mata pelajaran tidak dapat dihapus karena masih digunakan pada jadwal pelajaran.',
                422,
                ['subject' => ['Mata pelajaran tidak dapat dihapus karena masih digunakan pada jadwal pelajaran.']]
            );
        }

        $name = $subject->name;
        $subject->delete();

        AuditLog::record($request->user()->id, 'DELETE_SUBJECT', 'WAKA menghapus mata pelajaran ' . $name);

        return $this->successResponse(null, 'Mata pelajaran berhasil dihapus.');
    }

    public function storeRoom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:rooms,code'],
            'name' => ['required', 'string', 'max:255', 'unique:rooms,name'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'rules' => ['nullable', 'string', 'max:1000'],
        ]);

        $code = ! empty($validated['code'])
            ? strtoupper(trim($validated['code']))
            : strtoupper(str($validated['name'])->slug('-')->limit(50, '')->toString());

        $room = Room::create([
            'code' => $code,
            'name' => trim($validated['name']),
            'capacity' => (int) ($validated['capacity'] ?? 30),
            'rules' => ! empty($validated['rules']) ? trim($validated['rules']) : null,
        ]);

        AuditLog::record($request->user()->id, 'CREATE_ROOM', 'WAKA menambahkan ruangan ' . $room->name);

        return $this->successResponse(new RoomResource($room), 'Ruangan berhasil ditambahkan.', 201);
    }

    public function updateRoom(Request $request, Room $room): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', Rule::unique('rooms', 'code')->ignore($room->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('rooms', 'name')->ignore($room->id)],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'rules' => ['nullable', 'string', 'max:1000'],
        ]);

        $room->update([
            'code' => ! empty($validated['code'])
                ? strtoupper(trim($validated['code']))
                : ($room->code ?: strtoupper(str($validated['name'])->slug('-')->limit(50, '')->toString())),
            'name' => trim($validated['name']),
            'capacity' => (int) ($validated['capacity'] ?? $room->capacity ?? 30),
            'rules' => ! empty($validated['rules']) ? trim($validated['rules']) : null,
        ]);

        AuditLog::record($request->user()->id, 'UPDATE_ROOM', 'WAKA memperbarui ruangan ID ' . $room->id);

        return $this->successResponse(new RoomResource($room->fresh()), 'Ruangan berhasil diperbarui.');
    }

    public function destroyRoom(Request $request, Room $room): JsonResponse
    {
        if ($room->schedules()->exists()) {
            return $this->errorResponse(
                'Ruangan tidak dapat dihapus karena masih digunakan pada jadwal pelajaran.',
                422,
                ['room' => ['Ruangan tidak dapat dihapus karena masih digunakan pada jadwal pelajaran.']]
            );
        }

        $name = $room->name;
        $room->delete();

        AuditLog::record($request->user()->id, 'DELETE_ROOM', 'WAKA menghapus ruangan ' . $name);

        return $this->successResponse(null, 'Ruangan berhasil dihapus.');
    }

    public function updateStudentAssignment(Request $request, User $student): JsonResponse
    {
        if ($student->role?->name !== 'siswa') {
            return $this->errorResponse('Pengguna yang dipilih bukan akun Siswa.', 404);
        }

        $validated = $request->validate([
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'parent_ids' => ['nullable', 'array'],
            'parent_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $parentIds = array_values(array_unique(array_map('intval', $validated['parent_ids'] ?? [])));

        if ($parentIds !== []) {
            $validParentCount = User::whereIn('id', $parentIds)
                ->whereHas('role', fn ($q) => $q->where('name', 'orang_tua'))
                ->count();

            if ($validParentCount !== count($parentIds)) {
                throw ValidationException::withMessages([
                    'parent_ids' => 'Akun yang dipilih bukan akun Orang Tua.',
                ]);
            }
        }

        DB::transaction(function () use ($request, $student, $validated, $parentIds): void {
            $student->update([
                'class_id' => $validated['class_id'] ?? null,
            ]);

            $student->parents()->sync($parentIds);

            AuditLog::record(
                $request->user()->id,
                'ASSIGN_STUDENT_CLASS_AND_PARENTS',
                'WAKA memperbarui penugasan kelas dan orang tua untuk siswa ID ' . $student->id
            );
        });

        $student->load(['role', 'schoolClass.major', 'parents']);

        return $this->successResponse(
            new UserResource($student),
            'Penugasan kelas dan orang tua siswa berhasil diperbarui.'
        );
    }

    public function updateHomeroomTeacher(Request $request, Classes $class): JsonResponse
    {
        $validated = $request->validate([
            'homeroom_teacher_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $teacherId = isset($validated['homeroom_teacher_id']) ? (int) $validated['homeroom_teacher_id'] : null;

        if ($teacherId !== null) {
            $this->validateHomeroomTeacherEligibility($teacherId, $class->id);
        }

        $class->update([
            'homeroom_teacher_id' => $teacherId,
        ]);

        $class->load(['major', 'homeroomTeacher']);

        AuditLog::record(
            $request->user()->id,
            'ASSIGN_HOMEROOM_TEACHER',
            'WAKA memperbarui wali kelas untuk kelas ' . $class->name
        );

        return $this->successResponse(
            new SchoolClassResource($class),
            'Wali kelas berhasil diperbarui.'
        );
    }

    public function downloadImportTemplate(Request $request): StreamedResponse
    {
        return app(StudentImportController::class)->downloadTemplate($request);
    }

    public function importStudents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'roster' => ['nullable', 'file', 'max:5120'],
            'roster_data' => ['nullable', 'string'],
            'roster_name' => ['nullable', 'string', 'max:255'],
            'temporary_password' => ['nullable', 'string'],
        ]);

        [$headers, $dataRows] = $this->extractRowsFromRequest($request);

        foreach (self::REQUIRED_HEADERS as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                throw ValidationException::withMessages([
                    'roster' => 'Header file tidak sesuai template. Kolom wajib: ' . implode(', ', self::REQUIRED_HEADERS),
                ]);
            }
        }

        $studentRole = Role::firstOrCreate(
            ['name' => 'siswa'],
            ['display_name' => 'Siswa']
        );
        $parentRole = Role::firstOrCreate(
            ['name' => 'orang_tua'],
            ['display_name' => 'Orang Tua']
        );

        $classes = Classes::with('major')->get();
        $parsedRows = [];

        foreach ($dataRows as $lineIndex => $values) {
            $isEmptyRow = true;
            foreach ($values as $cellValue) {
                if (trim((string) $cellValue) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }

            if ($isEmptyRow) {
                continue;
            }

            $row = [];
            foreach ($headers as $colIndex => $headerName) {
                $row[$headerName] = isset($values[$colIndex]) ? trim((string) $values[$colIndex]) : '';
            }

            $rowNum = $lineIndex + 2;
            $studentLoginId = $row['student_login_id'] ?? '';
            $studentName = $row['student_name'] ?? '';
            $className = $row['class_name'] ?? '';
            $majorCode = $row['major_code'] ?? '';
            $studentRecoveryEmail = $row['student_recovery_email'] ?? '';
            $parentName = $row['parent_name'] ?? '';
            $parentRecoveryEmail = $row['parent_recovery_email'] ?? '';

            if (! preg_match('/^\d{10}$/', $studentLoginId)) {
                throw ValidationException::withMessages([
                    'roster' => "Baris {$rowNum}: NISN (student_login_id) harus terdiri dari 10 digit angka.",
                ]);
            }

            if ($studentName === '' || $parentName === '') {
                throw ValidationException::withMessages([
                    'roster' => "Baris {$rowNum}: Nama siswa dan nama orang tua wajib diisi.",
                ]);
            }

            $matchedClass = $classes->first(function (Classes $schoolClass) use ($className, $majorCode): bool {
                return Str::lower(trim($schoolClass->name)) === Str::lower($className)
                    && Str::lower(trim((string) $schoolClass->major?->code)) === Str::lower($majorCode);
            });

            if (! $matchedClass) {
                throw ValidationException::withMessages([
                    'roster' => "Baris {$rowNum}: Kelas '{$className}' dengan jurusan '{$majorCode}' tidak ditemukan pada Master Data.",
                ]);
            }

            if ($studentRecoveryEmail !== '' && ! filter_var($studentRecoveryEmail, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'roster' => "Baris {$rowNum}: Format email pemulihan siswa tidak valid.",
                ]);
            }

            if ($parentRecoveryEmail !== '' && ! filter_var($parentRecoveryEmail, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'roster' => "Baris {$rowNum}: Format email pemulihan orang tua tidak valid.",
                ]);
            }

            $slug = Str::slug($studentName, '_');
            $parentLoginKey = ($slug !== '' ? $slug : 'siswa') . '_ortu';

            $parsedRows[] = [
                'row_num' => $rowNum,
                'student_login_id' => $studentLoginId,
                'student_name' => $studentName,
                'class_id' => $matchedClass->id,
                'student_recovery_email' => $studentRecoveryEmail !== '' ? $studentRecoveryEmail : null,
                'parent_login_key' => $parentLoginKey,
                'parent_name' => $parentName,
                'parent_recovery_email' => $parentRecoveryEmail !== '' ? $parentRecoveryEmail : null,
            ];
        }

        if ($parsedRows === []) {
            throw ValidationException::withMessages([
                'roster' => 'Tidak ada baris data siswa yang dapat diimpor.',
            ]);
        }

        $plainPassword = filled($validated['temporary_password'] ?? null)
            ? (string) $validated['temporary_password']
            : StudentImportController::DEFAULT_IMPORT_PASSWORD;
        $hashedPassword = Hash::make($plainPassword);
        $importedStudents = [];

        DB::transaction(function () use ($request, $parsedRows, $studentRole, $parentRole, $hashedPassword, &$importedStudents): void {
            foreach ($parsedRows as $item) {
                $existingStudent = User::where('login_key', $item['student_login_id'])->lockForUpdate()->first();
                if ($existingStudent && (int) $existingStudent->role_id !== (int) $studentRole->id) {
                    throw ValidationException::withMessages([
                        'roster' => "Baris {$item['row_num']}: Login ID siswa '{$item['student_login_id']}' sudah digunakan oleh role lain.",
                    ]);
                }

                $existingParent = User::where('login_key', $item['parent_login_key'])->lockForUpdate()->first();
                if ($existingParent && (int) $existingParent->role_id !== (int) $parentRole->id) {
                    throw ValidationException::withMessages([
                        'roster' => "Baris {$item['row_num']}: Login ID orang tua '{$item['parent_login_key']}' sudah digunakan oleh role lain.",
                    ]);
                }

                if ($existingStudent) {
                    $studentAttrs = [
                        'name' => $item['student_name'],
                        'class_id' => $item['class_id'],
                        'is_active' => true,
                    ];
                    if ($item['student_recovery_email'] !== null) {
                        if (Str::lower((string) $existingStudent->recovery_email) !== Str::lower($item['student_recovery_email'])) {
                            $studentAttrs['recovery_email_verified_at'] = null;
                        }
                        $studentAttrs['recovery_email'] = $item['student_recovery_email'];
                    }
                    $existingStudent->update($studentAttrs);
                    $student = $existingStudent;
                } else {
                    $student = User::create([
                        'name' => $item['student_name'],
                        'login_key' => $item['student_login_id'],
                        'email' => $this->internalEmail($item['student_login_id']),
                        'recovery_email' => $item['student_recovery_email'],
                        'role_id' => $studentRole->id,
                        'class_id' => $item['class_id'],
                        'password' => $hashedPassword,
                        'must_change_password' => false,
                        'is_active' => true,
                    ]);
                }

                if ($existingParent) {
                    $parentAttrs = [
                        'name' => $item['parent_name'],
                        'is_active' => true,
                    ];
                    if ($item['parent_recovery_email'] !== null) {
                        if (Str::lower((string) $existingParent->recovery_email) !== Str::lower($item['parent_recovery_email'])) {
                            $parentAttrs['recovery_email_verified_at'] = null;
                        }
                        $parentAttrs['recovery_email'] = $item['parent_recovery_email'];
                    }
                    $existingParent->update($parentAttrs);
                    $parent = $existingParent;
                } else {
                    $parent = User::create([
                        'name' => $item['parent_name'],
                        'login_key' => $item['parent_login_key'],
                        'email' => $this->internalEmail($item['parent_login_key']),
                        'recovery_email' => $item['parent_recovery_email'],
                        'role_id' => $parentRole->id,
                        'password' => $hashedPassword,
                        'must_change_password' => false,
                        'is_active' => true,
                    ]);
                }

                $student->parents()->syncWithoutDetaching([$parent->id]);
                $importedStudents[] = [
                    'student_id' => $student->id,
                    'student_login_key' => $student->login_key,
                    'student_name' => $student->name,
                    'parent_id' => $parent->id,
                    'parent_login_key' => $parent->login_key,
                    'parent_name' => $parent->name,
                ];
            }

            AuditLog::record(
                $request->user()->id,
                'IMPORT_STUDENTS',
                'WAKA mengimpor ' . count($parsedRows) . ' data siswa beserta akun orang tua.'
            );
        });

        return $this->successResponse([
            'imported_count' => count($importedStudents),
            'default_password' => $plainPassword,
            'items' => $importedStudents,
        ], 'Berhasil mengimpor ' . count($importedStudents) . ' data siswa dan orang tua (Password default: ' . $plainPassword . ').', 201);
    }

    private function validateHomeroomTeacherEligibility(int $teacherId, ?int $ignoreClassId = null): void
    {
        $isHomeroomRole = User::where('id', $teacherId)
            ->whereHas('role', fn ($q) => $q->where('name', 'wali_kelas'))
            ->exists();

        if (! $isHomeroomRole) {
            throw ValidationException::withMessages([
                'homeroom_teacher_id' => 'Pengguna yang dipilih bukan akun Wali Kelas.',
            ]);
        }

        $alreadyAssigned = Classes::where('homeroom_teacher_id', $teacherId)
            ->when($ignoreClassId !== null, fn ($q) => $q->where('id', '!=', $ignoreClassId))
            ->exists();

        if ($alreadyAssigned) {
            throw ValidationException::withMessages([
                'homeroom_teacher_id' => 'Satu wali kelas hanya dapat ditugaskan pada satu kelas.',
            ]);
        }
    }

    private function extractRowsFromRequest(Request $request): array
    {
        $filePath = null;
        $originalName = '';
        $cleanupTempFile = false;

        if ($request->hasFile('roster') && $request->file('roster')?->isValid()) {
            $uploaded = $request->file('roster');
            $filePath = $uploaded->getRealPath();
            $originalName = (string) $uploaded->getClientOriginalName();
        } elseif (is_string($request->input('roster_data')) && trim($request->input('roster_data')) !== '') {
            $base64Data = $request->input('roster_data');
            if (! preg_match('/^data:[^;]+;base64,(.+)$/s', $base64Data, $matches)) {
                throw ValidationException::withMessages([
                    'roster' => 'Format file impor tidak valid.',
                ]);
            }

            $binary = base64_decode($matches[1], true);
            if ($binary === false || $binary === '') {
                throw ValidationException::withMessages([
                    'roster' => 'File impor tidak dapat dibaca.',
                ]);
            }

            $tempDir = storage_path('framework/temp');
            if (! is_dir($tempDir)) {
                @mkdir($tempDir, 0777, true);
            }

            $originalName = (string) $request->input('roster_name', 'roster.csv');
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'csv';
            $filePath = $tempDir . '/import_' . uniqid('', true) . '.' . $ext;
            file_put_contents($filePath, $binary);
            $cleanupTempFile = true;
        } else {
            throw ValidationException::withMessages([
                'roster' => 'Silakan pilih file Excel (.xlsx) atau CSV (.csv) terlebih dahulu.',
            ]);
        }

        try {
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $firstBytes = (string) file_get_contents($filePath, false, null, 0, 4);
            $isZipXlsx = str_starts_with($firstBytes, "PK\x03\x04") || $extension === 'xlsx';

            $allRows = $isZipXlsx
                ? $this->parseXlsxRows($filePath)
                : $this->parseCsvRows((string) file_get_contents($filePath));
        } finally {
            if ($cleanupTempFile && $filePath && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        if (count($allRows) < 2) {
            throw ValidationException::withMessages([
                'roster' => 'File harus berisi baris header dan minimal 1 baris data siswa.',
            ]);
        }

        $headerRow = array_shift($allRows);
        $headers = array_map(
            fn ($value) => Str::lower(trim((string) $value)),
            $headerRow
        );

        return [$headers, array_values($allRows)];
    }

    private function parseCsvRows(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $lines = preg_split('/\r\n|\r|\n/', trim($content)) ?: [];

        if ($lines === [] || trim((string) ($lines[0] ?? '')) === '') {
            return [];
        }

        $headerLine = (string) $lines[0];
        $delimiter = substr_count($headerLine, ';') > substr_count($headerLine, ',') ? ';' : ',';

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $rows[] = array_map(
                fn ($v) => trim((string) $v),
                str_getcsv($line, $delimiter, '"', '')
            );
        }

        return $rows;
    }

    private function parseXlsxRows(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw ValidationException::withMessages([
                'roster' => 'File Excel (.xlsx) tidak valid atau rusak.',
            ]);
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if (is_string($sharedStringsXml) && $sharedStringsXml !== '') {
            $sst = @simplexml_load_string($sharedStringsXml);
            if ($sst instanceof SimpleXMLElement) {
                foreach ($sst->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } else {
                        $text = '';
                        foreach ($si->r as $run) {
                            $text .= (string) $run->t;
                        }
                        $sharedStrings[] = $text;
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! is_string($sheetXml) || $sheetXml === '') {
            throw ValidationException::withMessages([
                'roster' => 'Lembar kerja utama (sheet1) pada file Excel tidak ditemukan.',
            ]);
        }

        $sheet = @simplexml_load_string($sheetXml);
        if (! $sheet instanceof SimpleXMLElement || ! isset($sheet->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $rowElement) {
            $rowCells = [];
            $maxColIndex = -1;

            foreach ($rowElement->c as $cell) {
                $ref = (string) ($cell['r'] ?? '');
                $colLetters = preg_replace('/[^A-Z]/', '', strtoupper($ref)) ?: '';
                $colIndex = $colLetters !== '' ? $this->columnIndexFromLetters($colLetters) : ($maxColIndex + 1);
                $maxColIndex = max($maxColIndex, $colIndex);

                $type = (string) ($cell['t'] ?? '');
                if ($type === 's') {
                    $idx = (int) ($cell->v ?? 0);
                    $value = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                } else {
                    $value = (string) ($cell->v ?? '');
                }

                $rowCells[$colIndex] = trim($value);
            }

            if ($maxColIndex >= 0) {
                $normalizedRow = [];
                for ($i = 0; $i <= $maxColIndex; $i++) {
                    $normalizedRow[] = $rowCells[$i] ?? '';
                }
                $rows[] = $normalizedRow;
            }
        }

        return $rows;
    }

    private function columnIndexFromLetters(string $letters): int
    {
        $index = 0;
        $length = strlen($letters);
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    private function internalEmail(string $loginKey): string
    {
        return 'account-' . substr(hash('sha256', Str::lower(trim($loginKey))), 0, 32) . '@accounts.invalid';
    }
}

