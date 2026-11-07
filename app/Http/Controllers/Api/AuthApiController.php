<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthApiController extends Controller
{
    use ApiResponses;

    private const ALLOWED_REGISTER_ROLES = ['guru', 'wali_kelas', 'bk', 'siswa'];

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login_key' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $loginInput = trim($validated['login_key']);
        $lowerInput = Str::lower($loginInput);
        $underscoreInput = Str::replace(' ', '_', $lowerInput);
        $cleanInput = Str::replace([' ', '_'], '', Str::replaceStart('kelas', '', $lowerInput));

        $user = User::with(['role', 'schoolClass.major', 'schoolClass.homeroomTeacher', 'homeroomClasses', 'students.schoolClass', 'parents'])
            ->where(function ($query) use ($loginInput, $lowerInput, $underscoreInput, $cleanInput): void {
                $query->where('nip', $loginInput)
                    ->orWhere('nis', $loginInput)
                    ->orWhereRaw('LOWER(login_key) = ?', [$lowerInput])
                    ->orWhereRaw('LOWER(parent_login_key) = ?', [$lowerInput])
                    ->orWhereRaw("REPLACE(LOWER(parent_login_key), ' ', '_') = ?", [$underscoreInput])
                    ->orWhereRaw("REPLACE(LOWER(login_key), ' ', '_') = ?", [$underscoreInput])
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(LOWER(login_key), 'kelas', ''), ' ', ''), '_', '') = ?", [$cleanInput]);
            })
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Login ID / NIP / NIS atau password salah.', 401);
        }

        // Tentukan state flag saat login apakah login sebagai Siswa atau Orang Tua
        $loginAs = $user->role?->name;
        if (in_array($user->role?->name, ['siswa', 'orang_tua'], true)) {
            $userParentKeyLower = Str::lower(trim((string) $user->parent_login_key));
            $userParentKeyUnderscore = Str::replace(' ', '_', $userParentKeyLower);
            $expectedFormatLower = Str::lower(trim($user->name) . '_' . trim((string) $user->parent_name));
            $expectedFormatUnderscore = Str::replace(' ', '_', $expectedFormatLower);

            $isParentLogin = ($userParentKeyLower !== '' && ($lowerInput === $userParentKeyLower || $underscoreInput === $userParentKeyUnderscore))
                || ($user->parent_name && ($lowerInput === $expectedFormatLower || $underscoreInput === $expectedFormatUnderscore));

            $loginAs = $isParentLogin ? 'orang_tua' : 'siswa';
            $user->update(['last_login_as' => $loginAs]);
        } else {
            $user->update(['last_login_as' => $loginAs]);
        }

        $deviceName = $validated['device_name'] ?? 'flutter-mobile';
        $token = $user->createToken($deviceName)->plainTextToken;

        $activeDisplayName = $loginAs === 'orang_tua'
            ? ($user->parent_name ?: 'Orang Tua ' . $user->name)
            : $user->name;

        AuditLog::record($user->id, 'LOGIN_API', 'Pengguna berhasil login melalui REST API (' . $deviceName . ") sebagai {$loginAs}.");

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user))->resolve($request),
            'id' => $user->id,
            'name' => $user->name,
            'active_identity_name' => $activeDisplayName,
            'active_identity_label' => $loginAs === 'orang_tua' ? 'Orang Tua / Wali Siswa' : ($loginAs === 'siswa' ? 'Siswa' : ($user->role?->display_name ?? 'Pengguna')),
            'login_key' => $user->login_key,
            'nip' => $user->nip,
            'nis' => $user->nis,
            'parent_name' => $user->parent_name,
            'parent_login_key' => $user->parent_login_key,
            'login_as' => $loginAs,
            'email' => $user->email,
            'recovery_email' => $user->recovery_email,
            'role' => $user->role?->name,
            'class' => $user->schoolClass?->name,
            'must_change_password' => (bool) $user->must_change_password,
        ], 'Login berhasil sebagai ' . ($loginAs === 'orang_tua' ? 'Orang Tua' : Str::headline((string) $loginAs)) . '.');
    }

    public function register(Request $request): JsonResponse
    {
        $roleInput = $request->input('role', 'siswa');
        $loginKeyRules = ['required', 'string', 'max:255', 'unique:users,login_key'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'login_key' => $loginKeyRules,
            'nip' => ['nullable', 'string', 'max:100', 'unique:users,nip'],
            'nis' => ['nullable', 'string', 'max:100', 'unique:users,nis'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'recovery_email' => ['nullable', 'email', 'max:255'],
            'role' => ['nullable', Rule::in(self::ALLOWED_REGISTER_ROLES)],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $roleName = $validated['role'] ?? 'siswa';
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => $roleName === 'siswa' ? 'Siswa / Orang Tua' : Str::headline($roleName)]
        );

        $email = ! empty($validated['email'])
            ? trim($validated['email'])
            : 'account-' . substr(hash('sha256', Str::lower(trim($validated['login_key']))), 0, 32) . '@accounts.invalid';

        $studentName = trim($validated['name']);
        $parentName = ! empty($validated['parent_name']) ? trim($validated['parent_name']) : null;
        $parentLoginKey = ($roleName === 'siswa' && $parentName)
            ? Str::lower($studentName . '_' . $parentName)
            : null;

        $user = User::create([
            'name' => $studentName,
            'login_key' => trim($validated['login_key']),
            'nip' => $roleName === 'guru' ? ($validated['nip'] ?? trim($validated['login_key'])) : null,
            'nis' => $roleName === 'siswa' ? ($validated['nis'] ?? trim($validated['login_key'])) : null,
            'parent_name' => $parentName,
            'parent_login_key' => $parentLoginKey,
            'email' => $email,
            'recovery_email' => ! empty($validated['recovery_email']) ? trim($validated['recovery_email']) : null,
            'role_id' => $role->id,
            'class_id' => $validated['class_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $user->load(['role', 'schoolClass.major', 'schoolClass.homeroomTeacher']);
        $deviceName = $validated['device_name'] ?? 'flutter-mobile';
        $token = $user->createToken($deviceName)->plainTextToken;

        AuditLog::record($user->id, 'REGISTER_API', 'Registrasi akun pengguna baru melalui REST API dengan role ' . $role->name);

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user))->resolve($request),
        ], 'Registrasi berhasil.', 201);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'role',
            'schoolClass.major',
            'schoolClass.homeroomTeacher',
            'homeroomClasses.major',
            'students.schoolClass.major',
            'parents',
        ]);

        return $this->successResponse(
            new UserResource($user),
            'Profil pengguna berhasil diambil.'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            } else {
                $user->tokens()->delete();
            }

            AuditLog::record($user->id, 'LOGOUT_API', 'Pengguna keluar dari sistem melalui REST API.');
        }

        return $this->successResponse(null, 'Logout berhasil. Token akses telah dihapus.');
    }
}
