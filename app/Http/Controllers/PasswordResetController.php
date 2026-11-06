<?php

namespace App\Http\Controllers;

use App\Mail\OtpResetPassword;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->session()->forget([
            'password_reset_user_id',
            'password_reset_verified',
            'password_reset_pending',
        ]);

        $validated = $request->validate([
            'login_key' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $loginInput = trim($validated['login_key']);
        $lowerInput = Str::lower($loginInput);
        $underscoreInput = Str::replace(' ', '_', $lowerInput);
        $inputEmail = Str::lower(trim($validated['email']));

        // 1. Cari akun berdasarkan Login ID / NIP / NIS / Nama Siswa_Nama Ortu / Email
        $user = User::query()
            ->where(function ($query) use ($loginInput, $lowerInput, $underscoreInput): void {
                $query->where('nip', $loginInput)
                    ->orWhere('nis', $loginInput)
                    ->orWhereRaw('LOWER(login_key) = ?', [$lowerInput])
                    ->orWhereRaw('LOWER(parent_login_key) = ?', [$lowerInput])
                    ->orWhereRaw("REPLACE(LOWER(parent_login_key), ' ', '_') = ?", [$underscoreInput])
                    ->orWhereRaw('LOWER(email) = ?', [$lowerInput])
                    ->orWhereRaw('LOWER(recovery_email) = ?', [$lowerInput]);
            })
            ->first();

        $genericSuccessMessage = 'Jika Login ID cocok dengan email pemulihan terverifikasi, kode OTP akan dikirim ke email tersebut.';

        if (! $user || ! $user->is_active) {
            $request->session()->put('password_reset_pending', true);

            return redirect()->route('password.otp')->with('success', $genericSuccessMessage);
        }

        // Email harus salah satu email yang tertaut ke akun ini di database
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
                $request->session()->put('password_reset_pending', true);

                return redirect()->route('password.otp')->with('success', $genericSuccessMessage);
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
        $sendError = null;

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
                $sendError = $e->getMessage();
                \Log::warning('Reset password OTP email via SMTP failed: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'email' => $targetEmail,
                ]);
            }
        } else {
            \Log::info('SMTP username masih default/placeholder (.env). Mengalihkan OTP ke mode dev/lokal.', [
                'user_id' => $user->id,
                'email' => $targetEmail,
            ]);
        }

        \Log::info("[OTP RESET PASSWORD] User: {$user->name} ({$user->login_key}) | Email: {$targetEmail} | OTP: {$otp}");

        $request->session()->put('password_reset_user_id', $user->id);
        $request->session()->put('password_reset_pending', true);
        $request->session()->put('password_reset_target_email', $targetEmail);

        if ($emailSent) {
            return redirect()->route('password.otp')->with(
                'success',
                'Kode OTP berhasil dikirim ke alamat email terdaftar Anda (' . $targetEmail . '). Silakan cek kotak masuk atau folder spam.'
            );
        }

        // Jika email gagal terkirim (SMTP offline / dummy SMTP di .env) pada environment lokal/testing/debug:
        if (app()->environment('local', 'testing') || config('app.debug')) {
            $request->session()->flash('dev_otp', $otp);

            $helperNote = $isDummySmtp
                ? 'SMTP Gmail di .env belum diisi akun asli (masih emailkamu@gmail.com)'
                : 'SMTP gagal: ' . Str::limit($sendError, 60);

            return redirect()->route('password.otp')->with(
                'success',
                "Kode OTP berhasil dibuat: {$otp}. [Mode Lokal/Dev: {$helperNote}]"
            );
        }

        // Production fallback jika SMTP gagal:
        $reset->delete();

        return back()
            ->withErrors([
                'email' => 'Gagal mengirim email kode OTP. Periksa konfigurasi SMTP atau coba beberapa saat lagi.',
            ])
            ->withInput();
    }

    public function showVerifyForm()
    {
        if (! session('password_reset_pending')) {
            return redirect()->route('password.forgot');
        }

        $userId = session('password_reset_user_id');
        $user = $userId ? User::find($userId) : null;
        $targetEmail = session('password_reset_target_email') ?: ($user ? ($user->recovery_email ?: $user->email) : null);

        return view('auth.verify-otp', [
            'user' => $user,
            'targetEmail' => $targetEmail,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $userId = $request->session()->get('password_reset_user_id');
        $reset = $userId ? PasswordReset::where('user_id', $userId)->latest()->first() : null;
        $userIsActive = $userId && User::whereKey($userId)->where('is_active', true)->exists();

        if (! $reset || ! $userIsActive || $reset->used_at || now()->greaterThan($reset->expires_at)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.']);
        }

        if ($reset->attempts >= 5) {
            $reset->update(['used_at' => now()]);

            return back()->withErrors(['otp' => 'Anda telah melebihi batas percobaan OTP. Silakan minta kode baru.']);
        }

        if (! Hash::check($request->otp, $reset->otp_hash)) {
            $reset->increment('attempts');

            return back()->withErrors(['otp' => 'OTP yang Anda masukkan salah.']);
        }

        $reset->update([
            'used_at' => now(),
        ]);

        session(['password_reset_verified' => true, 'password_reset_user_id' => $userId]);

        return redirect()->route('password.reset')->with('success', 'OTP valid. Silakan buat password baru.');
    }

    public function showResetForm()
    {
        if (! $this->hasValidVerifiedReset()) {
            return redirect()->route('password.forgot');
        }

        return view('auth.reset-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        if (! $this->hasValidVerifiedReset()) {
            return redirect()->route('password.forgot')->withErrors(['password' => 'Sesi reset password tidak valid.']);
        }

        $user = User::whereKey($request->session()->get('password_reset_user_id'))
            ->where('is_active', true)
            ->firstOrFail();
        $user->update([
            'password' => $request->password,
            'must_change_password' => false,
        ]);
        PasswordReset::where('user_id', $user->id)->delete();

        $request->session()->forget([
            'password_reset_user_id',
            'password_reset_verified',
            'password_reset_pending',
        ]);

        return redirect()->route('login')->with('success', 'Password berhasil diperbarui. Silakan login kembali.');
    }

    private function hasValidVerifiedReset(): bool
    {
        if (! session('password_reset_verified') || ! session('password_reset_user_id')) {
            return false;
        }

        return User::whereKey(session('password_reset_user_id'))
            ->where('is_active', true)
            ->exists()
            && PasswordReset::where('user_id', session('password_reset_user_id'))
            ->whereNotNull('used_at')
            ->where('expires_at', '>', now())
            ->exists();
    }
}