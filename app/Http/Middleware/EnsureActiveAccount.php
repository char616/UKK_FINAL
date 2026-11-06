<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->is_active) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $request->user()->tokens()->delete();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi WAKA.',
                    'data' => null,
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login_key' => 'Akun Anda tidak aktif. Silakan hubungi WAKA.',
            ]);
        }

        return $next($request);
    }
}
