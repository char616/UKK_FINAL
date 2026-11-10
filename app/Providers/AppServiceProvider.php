<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

require_once app_path('Sanctum/NewAccessToken.php');
require_once app_path('Sanctum/PersonalAccessToken.php');
require_once app_path('Sanctum/HasApiTokens.php');

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Paginator::defaultView('vendor.pagination.bootstrap-5');
        Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-5');

        $tempDir = storage_path('framework/temp');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        if (is_dir($tempDir)) {
            putenv("TMP={$tempDir}");
            putenv("TEMP={$tempDir}");
            $_ENV['TMP'] = $tempDir;
            $_ENV['TEMP'] = $tempDir;
            $_SERVER['TMP'] = $tempDir;
            $_SERVER['TEMP'] = $tempDir;
            @ini_set('sys_temp_dir', $tempDir);
        }

        foreach (['TMP', 'TEMP', 'USERPROFILE', 'LOCALAPPDATA', 'APPDATA'] as $envVar) {
            if (! in_array($envVar, ServeCommand::$passthroughVariables, true)) {
                ServeCommand::$passthroughVariables[] = $envVar;
            }
        }

        RateLimiter::for('login', function (Request $request) {
            $loginKey = $request->input('login_key');
            $loginKey = is_string($loginKey) ? Str::transliterate(Str::lower($loginKey)) : '';

            return [
                Limit::perMinute(5)->by('login:' . $loginKey . '|' . $request->ip()),
                Limit::perMinute(300)->by('login-ip:' . $request->ip()),
            ];
        });

        RateLimiter::for('password-reset-send', function (Request $request) {
            $loginKey = $request->input('login_key');
            $loginKey = is_string($loginKey) ? Str::transliterate(Str::lower($loginKey)) : '';

            return [
                Limit::perHour(3)->by('password-reset:' . $loginKey . '|' . $request->ip()),
                Limit::perHour(60)->by('password-reset-ip:' . $request->ip()),
            ];
        });

        RateLimiter::for('password-reset-verify', function (Request $request) {
            $resetKey = $request->session()->get('password_reset_user_id')
                ?? $request->session()->getId();

            return [
                Limit::perMinute(5)->by('password-reset-verify:' . $resetKey . '|' . $request->ip()),
                Limit::perHour(300)->by('password-reset-verify-ip:' . $request->ip()),
            ];
        });

        RateLimiter::for('recovery-email-send', function (Request $request) {
            return [
                Limit::perHour(3)->by('recovery-email-send:' . $request->user()->id),
                Limit::perHour(20)->by('recovery-email-send-ip:' . $request->ip()),
            ];
        });

        RateLimiter::for('recovery-email-verify', function (Request $request) {
            return [
                Limit::perMinute(5)->by('recovery-email-verify:' . $request->user()->id),
                Limit::perHour(100)->by('recovery-email-verify-ip:' . $request->ip()),
            ];
        });

        Auth::viaRequest('sanctum', function (Request $request) {
            if ($bearerToken = $request->bearerToken()) {
                $accessToken = PersonalAccessToken::findToken($bearerToken);

                if (
                    $accessToken
                    && (! $accessToken->expires_at || ! $accessToken->expires_at->isPast())
                    && $accessToken->tokenable
                ) {
                    $accessToken->forceFill(['last_used_at' => now()])->save();

                    return $accessToken->tokenable->withAccessToken($accessToken);
                }

                return null;
            }

            if (Auth::guard('web')->check()) {
                return Auth::guard('web')->user();
            }

            return null;
        });
    }
}
