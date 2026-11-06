<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Major;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $majors = Major::withCount('classes')->orderBy('name')->get();
        $classes = Classes::with(['major', 'homeroomTeacher'])
            ->withCount(['students', 'schedules'])
            ->orderBy('name')
            ->get();
        $subjects = Subject::withCount('schedules')->orderBy('name')->get();
        $rooms = Room::withCount('schedules')->orderBy('name')->get();
        $homeroomTeachers = User::with('homeroomClasses:id,name,homeroom_teacher_id')
            ->whereHas('role', fn ($q) => $q->where('name', 'wali_kelas'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $students = User::with(['schoolClass.major', 'parents:id,name,login_key'])
            ->whereHas('role', fn ($q) => $q->where('name', 'siswa'))
            ->orderBy('name')
            ->get();
        $parents = User::with('students:id,name,login_key')
            ->whereHas('role', fn ($q) => $q->where('name', 'orang_tua'))
            ->orderBy('name')
            ->get();

        return view('master-data.index', compact(
            'user',
            'majors',
            'classes',
            'subjects',
            'rooms',
            'homeroomTeachers',
            'students',
            'parents'
        ));
    }

    public function storeMajor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:majors,code'],
        ]);

        $major = Major::create([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
        ]);

        AuditLog::record(Auth::id(), 'CREATE_MAJOR', 'WAKA menambahkan jurusan '.$major->name.' ('.$major->code.')');

        return redirect()->route('master-data.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function updateMajor(Request $request, Major $major): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('majors', 'code')->ignore($major->id)],
        ]);

        $major->update([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
        ]);

        AuditLog::record(Auth::id(), 'UPDATE_MAJOR', 'WAKA memperbarui jurusan ID '.$major->id);

        return redirect()->route('master-data.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroyMajor(Major $major): RedirectResponse
    {
        if ($major->classes()->exists()) {
            return back()->withErrors([
                'major' => 'Jurusan tidak dapat dihapus karena masih memiliki data kelas.',
            ]);
        }

        $name = $major->name;
        $major->delete();

        AuditLog::record(Auth::id(), 'DELETE_MAJOR', 'WAKA menghapus jurusan '.$name);

        return redirect()->route('master-data.index')->with('success', 'Jurusan berhasil dihapus.');
    }

    public function storeClass(Request $request): RedirectResponse
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

        AuditLog::record(Auth::id(), 'CREATE_CLASS', 'WAKA menambahkan kelas '.$schoolClass->name);

        return redirect()->route('master-data.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function updateClass(Request $request, Classes $class): RedirectResponse
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

        AuditLog::record(Auth::id(), 'UPDATE_CLASS', 'WAKA memperbarui kelas ID '.$class->id);

        return redirect()->route('master-data.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroyClass(Classes $class): RedirectResponse
    {
        if ($class->students()->exists() || $class->schedules()->exists()) {
            return back()->withErrors([
                'class' => 'Kelas tidak dapat dihapus karena masih memiliki siswa atau jadwal pelajaran.',
            ]);
        }

        $name = $class->name;
        $class->delete();

        AuditLog::record(Auth::id(), 'DELETE_CLASS', 'WAKA menghapus kelas '.$name);

        return redirect()->route('master-data.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code'],
        ]);

        $subject = Subject::create([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
        ]);

        AuditLog::record(Auth::id(), 'CREATE_SUBJECT', 'WAKA menambahkan mata pelajaran '.$subject->name);

        return redirect()->route('master-data.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('subjects', 'code')->ignore($subject->id)],
        ]);

        $subject->update([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
        ]);

        AuditLog::record(Auth::id(), 'UPDATE_SUBJECT', 'WAKA memperbarui mata pelajaran ID '.$subject->id);

        return redirect()->route('master-data.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroySubject(Subject $subject): RedirectResponse
    {
        if ($subject->schedules()->exists()) {
            return back()->withErrors([
                'subject' => 'Mata pelajaran tidak dapat dihapus karena masih digunakan pada jadwal pelajaran.',
            ]);
        }

        $name = $subject->name;
        $subject->delete();

        AuditLog::record(Auth::id(), 'DELETE_SUBJECT', 'WAKA menghapus mata pelajaran '.$name);

        return redirect()->route('master-data.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    public function storeRoom(Request $request): RedirectResponse
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

        AuditLog::record(Auth::id(), 'CREATE_ROOM', 'WAKA menambahkan ruangan '.$room->name);

        return redirect()->route('master-data.index')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function updateRoom(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', Rule::unique('rooms', 'code')->ignore($room->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('rooms', 'name')->ignore($room->id)],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'rules' => ['nullable', 'string', 'max:1000'],
        ]);

        $room->update([
            'code' => ! empty($validated['code']) ? strtoupper(trim($validated['code'])) : ($room->code ?: strtoupper(str($validated['name'])->slug('-')->limit(50, '')->toString())),
            'name' => trim($validated['name']),
            'capacity' => (int) ($validated['capacity'] ?? $room->capacity ?? 30),
            'rules' => ! empty($validated['rules']) ? trim($validated['rules']) : null,
        ]);

        AuditLog::record(Auth::id(), 'UPDATE_ROOM', 'WAKA memperbarui ruangan ID '.$room->id);

        return redirect()->route('master-data.index')->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroyRoom(Room $room): RedirectResponse
    {
        if ($room->schedules()->exists()) {
            return back()->withErrors([
                'room' => 'Ruangan tidak dapat dihapus karena masih digunakan pada jadwal pelajaran.',
            ]);
        }

        $name = $room->name;
        $room->delete();

        AuditLog::record(Auth::id(), 'DELETE_ROOM', 'WAKA menghapus ruangan '.$name);

        return redirect()->route('master-data.index')->with('success', 'Ruangan berhasil dihapus.');
    }

    public function updateStudentAssignment(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->role?->name === 'siswa', 404);

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

        DB::transaction(function () use ($student, $validated, $parentIds): void {
            $student->update([
                'class_id' => $validated['class_id'] ?? null,
            ]);

            $student->parents()->sync($parentIds);

            AuditLog::record(
                Auth::id(),
                'ASSIGN_STUDENT_CLASS_AND_PARENTS',
                'WAKA memperbarui penugasan kelas dan orang tua untuk siswa ID '.$student->id
            );
        });

        return redirect()->route('master-data.index')->with('success', 'Penugasan kelas dan orang tua siswa berhasil diperbarui.');
    }

    public function updateHomeroomTeacher(Request $request, Classes $class): RedirectResponse
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

        AuditLog::record(
            Auth::id(),
            'ASSIGN_HOMEROOM_TEACHER',
            'WAKA memperbarui wali kelas untuk kelas '.$class->name
        );

        return redirect()->route('master-data.index')->with('success', 'Wali kelas berhasil diperbarui.');
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
}
