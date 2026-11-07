<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login_key' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $loginInput = trim($validated['login_key']);
        $lowerInput = Str::lower($loginInput);
        $underscoreInput = Str::replace(' ', '_', $lowerInput);
        $cleanInput = Str::replace([' ', '_'], '', Str::replaceStart('kelas', '', $lowerInput));

        // Cari user berdasarkan login_key, nip (untuk Guru), nis (untuk Siswa), atau parent_login_key (untuk Ortu, case-insensitive)
        $user = User::with('role')
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
            return back()
                ->withErrors(['login_key' => 'Login ID / NIP / NIS atau password salah.'])
                ->onlyInput('login_key');
        }

        // Tentukan identitas aktif apakah login sebagai Siswa atau Orang Tua
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

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('login_as', $loginAs);

        $actorLabel = $loginAs === 'orang_tua'
            ? "Orang Tua ({$user->parent_name}) dari siswa {$user->name}"
            : ($loginAs === 'siswa' ? "Siswa ({$user->name})" : $user->name);

        AuditLog::record($user->id, 'LOGIN', "Pengguna {$actorLabel} berhasil login ke sistem sebagai {$loginAs}.");

        if ($user->must_change_password) {
            return redirect()->route('settings.index');
        }

        if (in_array($user->role?->name, ['siswa', 'orang_tua'], true)) {
            return $loginAs === 'orang_tua'
                ? redirect()->route('dashboard.ortu')
                : redirect()->route('dashboard.siswa');
        }

        return match ($user->role?->name) {
            'waka' => redirect()->route('dashboard.waka'),
            'bk' => redirect()->route('dashboard.bk'),
            'wali_kelas' => redirect()->route('dashboard.wali'),
            'guru' => redirect()->route('dashboard.guru'),
            default => redirect()->route('login'),
        };
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLog::record(Auth::id(), 'LOGOUT', 'Pengguna keluar dari sistem.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
