<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\PasswordReset;
use App\Models\UserEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountSettingsApiController extends Controller
{
    use ApiResponses;

    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['role', 'schoolClass.major', 'homeroomClasses', 'students', 'parents']);

        return $this->successResponse(
            new UserResource($user),
            'Data pengaturan akun berhasil diambil.'
        );
    }

    public function updatePassword(Request $request): JsonResponse
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
        AuditLog::record($user->id, 'CHANGE_PASSWORD_API', 'Pengguna mengganti password akun sendiri melalui API.');

        return $this->successResponse(
            new UserResource($user->fresh(['role', 'schoolClass'])),
            'Password berhasil diperbarui.'
        );
    }

    public function emails(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->linkLegacyRecoveryEmail();

        return $this->successResponse(
            $this->emailPayload($user),
            'Daftar email tertaut berhasil diambil.'
        );
    }

    public function storeEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $user = $request->user();
        $email = mb_strtolower(trim($validated['email']));

        if ($user->linkedEmails()->where('email', $email)->exists()) {
            return $this->errorResponse('Email ini sudah tertaut ke akun Anda.', 422, [
                'email' => ['Email ini sudah tertaut ke akun Anda.'],
            ]);
        }

        if ($user->linkedEmails()->count() >= UserEmail::MAX_PER_USER) {
            $message = 'Maksimal ' . UserEmail::MAX_PER_USER . ' email dapat ditautkan. Hapus salah satu terlebih dahulu.';

            return $this->errorResponse($message, 422, ['email' => [$message]]);
        }

        $user->linkedEmails()->create([
            'email' => $email,
            'is_primary' => ! $user->linkedEmails()->where('is_primary', true)->exists(),
        ]);
        $user->syncPrimaryEmail();

        AuditLog::record($user->id, 'ADD_LINKED_EMAIL_API', 'Pengguna menautkan email ' . $email . ' melalui API.');

        return $this->successResponse(
            $this->emailPayload($user),
            'Email berhasil ditautkan dan dapat dipakai untuk menerima OTP reset password.',
            201
        );
    }

    public function makeEmailPrimary(Request $request, UserEmail $userEmail): JsonResponse
    {
        $user = $request->user();
        abort_unless($userEmail->user_id === $user->id, 404);

        $user->linkedEmails()->update(['is_primary' => false]);
        $userEmail->update(['is_primary' => true]);
        $user->syncPrimaryEmail();

        return $this->successResponse($this->emailPayload($user), 'Email utama berhasil diperbarui.');
    }

    public function destroyEmail(Request $request, UserEmail $userEmail): JsonResponse
    {
        $user = $request->user();
        abort_unless($userEmail->user_id === $user->id, 404);

        $email = $userEmail->email;
        $userEmail->delete();
        $user->syncPrimaryEmail();

        AuditLog::record($user->id, 'REMOVE_LINKED_EMAIL_API', 'Pengguna melepas email ' . $email . ' melalui API.');

        return $this->successResponse($this->emailPayload($user), 'Email berhasil dilepas dari akun.');
    }

    private function emailPayload($user): array
    {
        return [
            'max_emails' => UserEmail::MAX_PER_USER,
            'emails' => $user->linkedEmails()->get()->map(fn (UserEmail $item): array => [
                'id' => $item->id,
                'email' => $item->email,
                'is_primary' => $item->is_primary,
            ])->values()->all(),
        ];
    }
}
