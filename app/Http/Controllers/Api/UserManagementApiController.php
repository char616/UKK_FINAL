<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SchoolClassResource;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserManagementApiController extends Controller
{
    use ApiResponses;

    private const ROLE_NAMES = ['waka', 'bk', 'wali_kelas', 'guru', 'siswa'];

    public function index(Request $request): JsonResponse
    {
        $requestedRole = $request->query('role');
        $selectedRole = is_string($requestedRole) && in_array($requestedRole, self::ROLE_NAMES, true)
            ? $requestedRole
            : null;

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);
        $paginator = User::with(['role', 'schoolClass.major'])
            ->when($selectedRole, fn ($query) => $query->whereHas('role', fn ($roleQuery) => $roleQuery->where('name', $selectedRole)))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%' . trim((string) $request->query('search')) . '%';
                $query->where(function ($sub) use ($search): void {
                    $sub->where('name', 'like', $search)
                        ->orWhere('login_key', 'like', $search)
                        ->orWhere('nip', 'like', $search)
                        ->orWhere('nis', 'like', $search)
                        ->orWhere('parent_name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $roles = Role::whereIn('name', self::ROLE_NAMES)->orderBy('id')->get(['id', 'name', 'display_name']);
        $classes = Classes::with('major')->orderBy('name')->get();

        return $this->successResponse([
            'selected_role' => $selectedRole,
            'users' => UserResource::collection($paginator->getCollection())->resolve($request),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'roles' => $roles,
            'classes' => SchoolClassResource::collection($classes)->resolve($request),
        ], 'Daftar akun pengguna berhasil diambil.');
    }

    public function store(Request $request): JsonResponse
    {
        $roleInput = (string) $request->input('role');

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

        $createdUser = DB::transaction(function () use ($request, $validated, $roleInput): User {
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
                $request->user()->id,
                'CREATE_USER',
                'WAKA membuat akun pengguna ID ' . $user->id . ' dengan role ' . $role->name
            );

            return $user;
        });

        $createdUser->load(['role', 'schoolClass']);

        return $this->successResponse(
            new UserResource($createdUser),
            'Akun berhasil dibuat.',
            201
        );
    }

    public function update(Request $request, User $user): JsonResponse
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
        if (! $willBeActive && (int) $request->user()->id === (int) $user->id) {
            return $this->errorResponse(
                'Anda tidak dapat menonaktifkan akun sendiri.',
                422,
                ['is_active' => ['Anda tidak dapat menonaktifkan akun sendiri.']]
            );
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

            if (! $willBeActive) {
                $user->tokens()->delete();
            }

            AuditLog::record(
                $request->user()->id,
                'UPDATE_USER',
                'WAKA memperbarui akun pengguna ID ' . $user->id . ($willBeActive ? '' : ' dan menonaktifkannya')
            );
        });

        $user->load(['role', 'schoolClass']);

        return $this->successResponse(
            new UserResource($user),
            'Akun berhasil diperbarui.'
        );
    }

    private function internalEmail(string $loginKey): string
    {
        return 'account-' . substr(hash('sha256', Str::lower(trim($loginKey))), 0, 32) . '@accounts.invalid';
    }
}