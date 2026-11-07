<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PasswordReset;
use App\Models\UserEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->linkLegacyRecoveryEmail();

        return view('account-settings.index', [
            'user' => $user,
            'linkedEmails' => $user->linkedEmails()->get(),
            'maxEmails' => UserEmail::MAX_PER_USER,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ]);

        $user = $request->user();
        $user->update([
            'password' => $validated['password'],
            'must_change_password' => false,
        ]);
        PasswordReset::where('user_id', $user->id)->delete();

        AuditLog::record($user->id, 'CHANGE_PASSWORD', 'Pengguna mengganti password akun sendiri.');

        $request->session()->regenerate();

        return redirect()->route('settings.index')->with('success', 'Password berhasil diperbarui.');
    }

    public function storeEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $user = $request->user();
        $email = mb_strtolower(trim($validated['email']));

        if ($user->linkedEmails()->where('email', $email)->exists()) {
            return back()->withErrors(['email' => 'Email ini sudah tertaut ke akun Anda.'])->withInput();
        }

        if ($user->linkedEmails()->count() >= UserEmail::MAX_PER_USER) {
            return back()->withErrors([
                'email' => 'Maksimal ' . UserEmail::MAX_PER_USER . ' email dapat ditautkan. Hapus salah satu terlebih dahulu.',
            ])->withInput();
        }

        $user->linkedEmails()->create([
            'email' => $email,
            'is_primary' => ! $user->linkedEmails()->where('is_primary', true)->exists(),
        ]);
        $user->syncPrimaryEmail();

        AuditLog::record($user->id, 'ADD_LINKED_EMAIL', 'Pengguna menautkan email ' . $email . ' untuk pemulihan akun.');

        return back()->with('success', 'Email ' . $email . ' berhasil ditautkan dan dapat dipakai untuk menerima OTP reset password.');
    }

    public function makeEmailPrimary(Request $request, UserEmail $userEmail): RedirectResponse
    {
        $user = $request->user();
        abort_unless($userEmail->user_id === $user->id, 404);

        $user->linkedEmails()->update(['is_primary' => false]);
        $userEmail->update(['is_primary' => true]);
        $user->syncPrimaryEmail();

        return back()->with('success', 'Email utama diperbarui menjadi ' . $userEmail->email . '.');
    }

    public function destroyEmail(Request $request, UserEmail $userEmail): RedirectResponse
    {
        $user = $request->user();
        abort_unless($userEmail->user_id === $user->id, 404);

        $email = $userEmail->email;
        $userEmail->delete();
        $user->syncPrimaryEmail();

        AuditLog::record($user->id, 'REMOVE_LINKED_EMAIL', 'Pengguna melepas email ' . $email . ' dari akun.');

        return back()->with('success', 'Email ' . $email . ' berhasil dilepas dari akun.');
    }
}
