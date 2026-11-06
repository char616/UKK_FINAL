<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSanctum
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = null;

        if ($bearerToken = $request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($bearerToken);

            if (
                $accessToken
                && (! $accessToken->expires_at || ! $accessToken->expires_at->isPast())
                && $accessToken->tokenable
            ) {
                $accessToken->forceFill(['last_used_at' => now()])->save();
                $user = $accessToken->tokenable->withAccessToken($accessToken);
            }
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
        }

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.', ['sanctum']);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}

