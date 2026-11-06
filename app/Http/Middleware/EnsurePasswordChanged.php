<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user()?->must_change_password
            && ! $request->routeIs(
                'settings.index',
                'settings.password.update',
                'logout',
                'api.auth.profile',
                'api.auth.logout',
                'api.settings.show',
                'api.settings.password.update',
                'api.settings.recovery-email.send',
                'api.settings.recovery-email.verify'
            )
        ) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Silakan ganti password sementara sebelum menggunakan sistem.',
                    'data' => [
                        'must_change_password' => true,
                    ],
                ], 403);
            }

            return redirect()->route('settings.index')
                ->with('password_change_required', 'Silakan ganti password sementara sebelum menggunakan sistem.');
        }

        return $next($request);
    }
}
