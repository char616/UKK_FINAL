<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpResetPassword;
use App\Models\AuditLog;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetApiController extends Controller
{
    use ApiResponses;

    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login_key' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $loginInput = trim($validated['login_key']);
        $inputEmail = Str::lower(trim($validated['email']));
        $user = $this->findUserByIdentifier($loginInput);

        if (! $user) {
            return $this->errorResponse(
                'Akun dengan Login ID / NIP / NIS tersebut tidak ditemukan di database.',
                404,
                ['login_key' => ['Akun dengan Login ID / NIP / NIS tersebut tidak ditemukan di database.']]
            );
        }

        if (! $user->is_active) {
            return $this->errorResponse(
                'Akun Anda sedang dinonaktifkan. Silakan hubungi Admin / WAKA.',
                403,
                ['login_key' => ['Akun Anda sedang dinonaktifkan.']]
            );
        }

        $targetEmail = $user->matchResetEmail($inputEmail);

        if ($targetEmail === null) {
            if ($user->canAdoptRecoveryEmail()) {
                $user->update([
                    'recovery_email' => $inputEmail,
                    'recovery_email_verified_at' => now(),
                ]);
                $user->linkedEmails()->firstOrCreate([
                    'email' => $inputEmail,
                ], [
                    'is_primary' => true,
                ]);
                $targetEmail = $inputEmail;
            } else {
                return $this->errorResponse(
                    'Alamat email yang Anda masukkan tidak tertaut ke akun ini.',
                    422,
                    ['email' => ['Alamat email tidak tertaut ke akun yang sedang diproses.']]
                );
            }
        }

        PasswordReset::where('user_id', $user->id)->delete();

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $reset = PasswordReset::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        $emailSent = false;
        $isDummySmtp = config('mail.default') === 'smtp' && in_array(config('mail.mailers.smtp.username'), [
            null, '', 'emailkamu@gmail.com', 'your-email@gmail.com',
        ], true);

        if (! $isDummySmtp) {
            try {
                Mail::to($targetEmail)->send(
                    new OtpResetPassword($otp, $user->name ?: $user->login_key)
                );
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::warning('Reset password OTP email failed (API): ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("[OTP RESET PASSWORD (API)] User: {$user->name} ({$user->login_key}) | Email: {$targetEmail} | OTP: {$otp}");

        if (! $emailSent && ! app()->environment('local', 'testing') && ! config('app.debug')) {
            $reset->delete();

            return $this->errorResponse(
                'Gagal mengirim email kode OTP. Periksa konfigurasi email atau coba lagi nanti.',
                500,
                ['email' => ['Gagal mengirim email kode OTP.']]
            );
        }

        return $this->successResponse([
            'login_key' => $user->login_key,
            'email' => $targetEmail,
            'expires_in_minutes' => 10,
            'otp_dispatched' => $emailSent,
            'otp_preview' => (app()->environment('local', 'testing') || config('app.debug')) ? $otp : null,
        ], $emailSent ? 'Kode OTP telah dikirim ke alamat email terdaftar Anda.' : 'Kode OTP berhasil dibuat (Mode dev/lokal).');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login_key' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $user = $this->findUserByIdentifier(trim($validated['login_key']));

        if (! $user || ! $user->is_active) {
            return $this->errorResponse(
                'Akun tidak ditemukan atau tidak aktif.',
                422,
                ['login_key' => ['Akun tidak ditemukan atau tidak aktif.']]
            );
        }

        $reset = PasswordReset::where('user_id', $user->id)->latest()->first();

        if (! $reset || $reset->used_at || now()->greaterThan($reset->expires_at)) {
            return $this->errorResponse(
                'Kode OTP tidak valid atau sudah kedaluwarsa.',
                422,
                ['otp' => ['Kode OTP tidak valid atau sudah kedaluwarsa.']]
            );
        }

        if ($reset->attempts >= 5) {
            $reset->update(['used_at' => now()]);

            return $this->errorResponse(
                'Anda telah melebihi batas percobaan OTP. Silakan minta kode baru.',
                422,
                ['otp' => ['Anda telah melebihi batas percobaan OTP. Silakan minta kode baru.']]
            );
        }

        if (! Hash::check($validated['otp'], $reset->otp_hash)) {
            $reset->increment('attempts');

            return $this->errorResponse(
                'OTP yang Anda masukkan salah.',
                422,
                ['otp' => ['OTP yang Anda masukkan salah.']]
            );
        }

        $reset->update(['used_at' => now()]);

        return $this->successResponse([
            'login_key' => $user->login_key,
            'verified' => true,
        ], 'OTP valid. Silakan buat password baru.');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login_key' => ['required', 'string', 'max:255'],
            'otp' => ['nullable', 'string', 'digits:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $this->findUserByIdentifier(trim($validated['login_key']));

        if (! $user || ! $user->is_active) {
            return $this->errorResponse('Sesi reset password tidak valid.', 422, [
                'login_key' => ['Akun tidak ditemukan atau tidak aktif.'],
            ]);
        }

        $reset = PasswordReset::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $reset) {
            return $this->errorResponse('Sesi reset password tidak valid atau sudah kedaluwarsa.', 422, [
                'password' => ['Sesi reset password tidak valid atau sudah kedaluwarsa.'],
            ]);
        }

        if ($reset->used_at === null) {
            if (empty($validated['otp']) || ! Hash::check($validated['otp'], $reset->otp_hash)) {
                return $this->errorResponse('Kode OTP belum diverifikasi atau tidak sesuai.', 422, [
                    'otp' => ['Kode OTP belum diverifikasi atau tidak sesuai.'],
                ]);
            }
        }

        $user->update([
            'password' => $validated['password'],
            'must_change_password' => false,
        ]);

        PasswordReset::where('user_id', $user->id)->delete();
        $user->tokens()->delete();

        AuditLog::record($user->id, 'RESET_PASSWORD_API', 'Pengguna berhasil mereset password melalui OTP API.');

        return $this->successResponse(null, 'Password berhasil diperbarui. Silakan login kembali.');
    }

    private function findUserByIdentifier(string $loginInput): ?User
    {
        $lowerInput = Str::lower($loginInput);
        $underscoreInput = Str::replace(' ', '_', $lowerInput);

        return User::query()
            ->where(function ($query) use ($loginInput, $lowerInput, $underscoreInput): void {
                $query->where('nip', $loginInput)
                    ->orWhere('nis', $loginInput)
                    ->orWhereRaw('LOWER(login_key) = ?', [$lowerInput])
                    ->orWhereRaw('LOWER(parent_login_key) = ?', [$lowerInput])
                    ->orWhereRaw("REPLACE(LOWER(parent_login_key), ' ', '_') = ?", [$underscoreInput]);
            })
            ->first();
    }
}
