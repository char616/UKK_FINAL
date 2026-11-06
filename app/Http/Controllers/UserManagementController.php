<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const ROLE_NAMES = ['waka', 'bk', 'wali_kelas', 'guru', 'siswa', 'orang_tua'];

    public function index(Request $request): View
    {
        $requestedRole = $request->query('role');
        $selectedRole = is_string($requestedRole) && in_array($requestedRole, self::ROLE_NAMES, true)
            ? $requestedRole
            : null;

        return view('accounts.index', [
            'user' => auth()->user(),
            'users' => User::with(['role', 'schoolClass'])
                ->when($selectedRole, fn ($query) => $query->whereHas('role', fn ($roleQuery) => $roleQuery->where('name', $selectedRole)))
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),
            'roles' => Role::whereIn('name', ['waka', 'bk', 'wali_kelas', 'guru', 'siswa'])->orderBy('id')->get(),
            'classes' => Classes::orderBy('name')->get(),
            'selectedRole' => $selectedRole,
        ]);
    }

    public function store(Request $request)
    {
        $roleInput = (string) $request->input('role');
        $loginKeyInput = (string) $request->input('login_key');

        if ($roleInput === 'siswa' && ! preg_match('/^\d+$/', $loginKeyInput)) {
            throw ValidationException::withMessages([
                'login_key' => 'Login ID untuk siswa harus berupa angka (NIS).',
            ]);
        }

        if ($roleInput === 'orang_tua' && ! preg_match('/^ortu_\d+$/i', $loginKeyInput)) {
            throw ValidationException::withMessages([
                'login_key' => 'Login ID untuk orang tua harus menggunakan format ortu_[NIS].',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'login_key' => ['required', 'string', 'max:255', 'unique:users,login_key'],
            'nip' => ['nullable', 'string', 'max:100', 'unique:users,nip'],
            'nis' => ['nullable', 'string', 'max:100', 'unique:users,nis'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'recovery_email' => ['nullable', 'email', 'max:255'],
            'role' => ['required', Rule::in(self::ROLE_NAMES), Rule::exists('roles', 'name')],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated, $roleInput): void {
            $role = Role::where('name', $validated['role'])->lockForUpdate()->firstOrFail();

            if ($role->name === 'waka' && User::where('role_id', $role->id)->exists()) {
                throw ValidationException::withMessages([
                    'role' => 'Sistem hanya dapat memiliki satu akun WAKA.',
                ]);
            }

            $loginKey = trim($validated['login_key']);
            $name = trim($validated['name']);
            $nip = in_array($roleInput, ['guru', 'wali_kelas'], true)
                ? (filled($validated['nip'] ?? null) ? trim((string) $validated['nip']) : $loginKey)
                : null;
            $nis = $roleInput === 'siswa'
                ? (filled($validated['nis'] ?? null) ? trim((string) $validated['nis']) : $loginKey)
                : null;
            $parentName = $roleInput === 'siswa' && filled($validated['parent_name'] ?? null)
                ? trim((string) $validated['parent_name'])
                : null;
            $parentLoginKey = ($roleInput === 'siswa' && $parentName)
                ? Str::lower($name . '_' . $parentName)
                : null;

            $user = User::create([
                'name' => $name,
                'login_key' => $loginKey,
                'nip' => $nip,
                'nis' => $nis,
                'parent_name' => $parentName,
                'parent_login_key' => $parentLoginKey,
                'class_id' => $roleInput === 'siswa' ? ($validated['class_id'] ?? null) : null,
                'email' => ! empty($validated['email'])
                    ? trim($validated['email'])
                    : $this->internalEmail($loginKey),
                'recovery_email' => ! empty($validated['recovery_email']) ? trim($validated['recovery_email']) : null,
                'role_id' => $role->id,
                'password' => Hash::make($validated['password']),
                'must_change_password' => true,
                'is_active' => true,
            ]);

            AuditLog::record(
                Auth::id(),
                'CREATE_USER',
                'WAKA membuat akun pengguna ID '.$user->id.' dengan role '.$role->name
            );
        });

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('users', 'nip')->ignore($user->id),
            ],
            'nis' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('users', 'nis')->ignore($user->id),
            ],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'recovery_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
        ]);

        $willBeActive = (bool) $validated['is_active'];
        if (! $willBeActive && (int) Auth::id() === (int) $user->id) {
            return back()->withErrors(['is_active' => 'Anda tidak dapat menonaktifkan akun sendiri.'])->withInput();
        }

        DB::transaction(function () use ($request, $validated, $user, $willBeActive): void {
            $roleName = optional($user->role)->name;
            if (! $willBeActive && $roleName === 'waka') {
                $activeWakaIds = User::where('is_active', true)
                    ->whereHas('role', fn ($query) => $query->where('name', 'waka'))
                    ->lockForUpdate()
                    ->pluck('id');

                if ($activeWakaIds->count() <= 1) {
                    throw ValidationException::withMessages([
                        'is_active' => 'Akun WAKA aktif terakhir tidak dapat dinonaktifkan.',
                    ]);
                }
            }

            $attributes = [
                'name' => trim($validated['name']),
                'email' => trim($validated['email']),
                'recovery_email' => ! empty($validated['recovery_email']) ? trim($validated['recovery_email']) : null,
                'is_active' => $willBeActive,
            ];

            // Update NIP untuk Guru / Wali Kelas tanpa merusak relasi jadwal/presensi (karena relasi memakai users.id)
            if (in_array($roleName, ['guru', 'wali_kelas'], true) && $request->exists('nip')) {
                $newNip = filled($validated['nip'] ?? null) ? trim((string) $validated['nip']) : null;
                $attributes['nip'] = $newNip;

                if ($newNip !== null) {
                    $conflictLoginKey = User::where('login_key', $newNip)->where('id', '!=', $user->id)->exists();
                    if (! $conflictLoginKey) {
                        $attributes['login_key'] = $newNip;
                    }
                }
            }

            // Update NIS & Nama Orang Tua untuk Siswa/Ortu
            if ($roleName === 'siswa') {
                if ($request->exists('nis') && filled($validated['nis'] ?? null)) {
                    $newNis = trim((string) $validated['nis']);
                    $attributes['nis'] = $newNis;

                    $conflictLoginKey = User::where('login_key', $newNis)->where('id', '!=', $user->id)->exists();
                    if (! $conflictLoginKey) {
                        $attributes['login_key'] = $newNis;
                    }
                }

                if ($request->exists('parent_name')) {
                    $parentName = filled($validated['parent_name'] ?? null) ? trim((string) $validated['parent_name']) : null;
                    $attributes['parent_name'] = $parentName;
                    $attributes['parent_login_key'] = $parentName
                        ? Str::lower($attributes['name'] . '_' . $parentName)
                        : null;
                } elseif ($user->parent_name) {
                    $attributes['parent_login_key'] = Str::lower($attributes['name'] . '_' . $user->parent_name);
                }
            }

            if (Str::lower((string) $user->recovery_email) !== Str::lower((string) $attributes['recovery_email'])) {
                $attributes['recovery_email_verified_at'] = null;
            }

            if (! empty($validated['password'])) {
                $attributes['password'] = Hash::make($validated['password']);
                $attributes['must_change_password'] = true;
            }

            $user->update($attributes);

            AuditLog::record(
                Auth::id(),
                'UPDATE_USER',
                'WAKA memperbarui akun pengguna ID '.$user->id.($willBeActive ? '' : ' dan menonaktifkannya')
            );
        });

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    private function internalEmail(string $loginKey): string
    {
        return 'account-'.substr(hash('sha256', Str::lower(trim($loginKey))), 0, 32).'@accounts.invalid';
    }
}