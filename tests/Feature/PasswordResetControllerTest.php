<?php

namespace Tests\Feature;

use App\Mail\OtpResetPassword;
use App\Models\PasswordReset;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_login_receives_the_same_password_reset_confirmation(): void
    {
        $response = $this->post(route('password.forgot.submit'), [
            'login_key' => 'UNKNOWN-USER',
            'email' => 'person@example.test',
        ]);

        $response->assertRedirect(route('password.otp'))
            ->assertSessionHas('success', 'Jika Login ID cocok dengan email pemulihan terverifikasi, kode OTP akan dikirim ke email tersebut.');
        $this->get(route('password.otp'))->assertOk();
        $this->assertDatabaseCount('password_resets', 0);
    }

    public function test_password_reset_requests_are_limited_per_login_key_and_ip(): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('password.forgot.submit'), [
                'login_key' => 'UNKNOWN-USER',
                'email' => 'person@example.test',
            ])->assertRedirect(route('password.otp'));
        }

        $this->post(route('password.forgot.submit'), [
            'login_key' => 'UNKNOWN-USER',
            'email' => 'person@example.test',
        ])->assertTooManyRequests();
    }

    public function test_non_string_otp_is_rejected_before_hash_verification(): void
    {
        $this->post(route('password.otp.submit'), [
            'otp' => ['0', '0', '0', '0', '0', '0'],
        ])->assertSessionHasErrors('otp');
    }

    public function test_otp_verification_is_rate_limited_after_five_failed_attempts(): void
    {
        Mail::fake();
        $user = $this->createUser(recoveryEmail: 'student-recovery@example.test');

        $this->post(route('password.forgot.submit'), [
            'login_key' => $user->login_key,
            'email' => $user->recovery_email,
        ])->assertRedirect(route('password.otp'));

        Mail::assertSent(OtpResetPassword::class);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('password.otp.submit'), [
                'otp' => '000000',
            ])->assertSessionHasErrors('otp');
        }

        $this->post(route('password.otp.submit'), [
            'otp' => '000000',
        ])->assertTooManyRequests();

        $this->assertSame(5, PasswordReset::query()->value('attempts'));
    }

    public function test_verified_otp_can_reset_password_once(): void
    {
        Mail::fake();
        $user = $this->createUser(recoveryEmail: 'student-recovery@example.test');

        $this->post(route('password.forgot.submit'), [
            'login_key' => $user->login_key,
            'email' => $user->recovery_email,
        ])->assertRedirect(route('password.otp'));

        $otp = null;
        Mail::assertSent(OtpResetPassword::class, function (OtpResetPassword $mail) use ($user, &$otp): bool {
            if (! $mail->hasTo($user->recovery_email)) {
                return false;
            }

            $otp = $mail->otp;

            return true;
        });

        $this->assertNotNull($otp);

        $this->post(route('password.otp.submit'), [
            'otp' => $otp,
        ])->assertRedirect(route('password.reset'));

        $this->post(route('password.reset.submit'), [
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertDatabaseMissing('password_resets', ['user_id' => $user->id]);
    }

    public function test_unverified_email_cannot_receive_password_reset_otp(): void
    {
        Mail::fake();
        $user = $this->createUser(recoveryEmail: 'not-verified@example.test', verified: false);

        $this->post(route('password.forgot.submit'), [
            'login_key' => $user->login_key,
            'email' => $user->recovery_email,
        ])->assertRedirect(route('password.otp'));

        Mail::assertNothingSent();
        $this->assertDatabaseCount('password_resets', 0);
    }

    public function test_password_reset_email_must_match_the_verified_account_email(): void
    {
        Mail::fake();
        $user = $this->createUser(recoveryEmail: 'registered@example.test');
        $user->update(['recovery_email_verified_at' => now()]);

        $this->post(route('password.forgot.submit'), [
            'login_key' => $user->login_key,
            'email' => 'attacker@example.test',
        ])->assertRedirect(route('password.otp'));

        Mail::assertNothingSent();
        $this->assertDatabaseCount('password_resets', 0);
    }

    public function test_expired_verified_otp_cannot_change_the_password(): void
    {
        $user = $this->createUser();
        PasswordReset::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->subMinute(),
            'used_at' => now()->subMinutes(2),
            'attempts' => 0,
        ]);

        $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_verified' => true,
            'password_reset_pending' => true,
        ])->post(route('password.reset.submit'), [
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('password.forgot'));

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_deactivated_account_cannot_change_its_password(): void
    {
        $user = $this->createUser();
        $user->update(['is_active' => false]);
        PasswordReset::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
            'used_at' => now(),
            'attempts' => 0,
        ]);

        $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_verified' => true,
            'password_reset_pending' => true,
        ])->post(route('password.reset.submit'), [
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('password.forgot'));

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    private function createUser(?string $recoveryEmail = null, bool $verified = true): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'siswa'],
            ['display_name' => 'Siswa']
        );

        return User::create([
            'name' => 'Test Student',
            'email' => 'student@example.test',
            'login_key' => 'STUDENT-001',
            'password' => 'password',
            'role_id' => $role->id,
            'recovery_email' => $recoveryEmail,
            'recovery_email_verified_at' => $recoveryEmail && $verified ? now() : null,
            'is_active' => true,
        ]);
    }
}
