<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Menangani incoming request untuk 5 role sistem
     * sekaligus mendukung dual-login Siswa / Orang Tua.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
                    'data' => null,
                ], 401);
            }

            return redirect()->route('login');
        }

        $userRole = $user->role?->name;
        $loginAs = ($request->hasSession() ? $request->session()->get('login_as') : null) ?: $user->last_login_as;

        // Karena role 'siswa' dan 'orang_tua' digabung menjadi 1 role ('siswa'),
        // izinkan akses ke rute 'orang_tua' jika akun memiliki role 'siswa'
        $isAuthorized = false;

        if ($user->is_active && $userRole !== null) {
            if (in_array($userRole, $roles, true)) {
                $isAuthorized = true;
            } elseif ($userRole === 'siswa' && in_array('orang_tua', $roles, true)) {
                $isAuthorized = true;
            } elseif ($userRole === 'orang_tua' && in_array('siswa', $roles, true)) {
                $isAuthorized = true;
            }

            // Jika hanya khusus dashboard siswa vs dashboard ortu, arahkan sesuai identitas login aktif
            if ($userRole === 'siswa' && $roles === ['orang_tua'] && $loginAs === 'siswa') {
                if (! $request->is('api/*') && ! $request->expectsJson()) {
                    return redirect()->route('dashboard.siswa');
                }
            }

            if ($userRole === 'siswa' && $roles === ['siswa'] && $loginAs === 'orang_tua') {
                if (! $request->is('api/*') && ! $request->expectsJson()) {
                    return redirect()->route('dashboard.ortu');
                }
            }
        }

        if ($isAuthorized) {
            return $next($request);
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Anda tidak memiliki hak akses ke fitur ini.',
                'data' => null,
            ], 403);
        }

        abort(403, 'Akses ditolak. Anda tidak memiliki hak akses ke halaman ini.');
    }
}