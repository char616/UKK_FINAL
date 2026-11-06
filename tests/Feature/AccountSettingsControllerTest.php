<?php

namespace Tests\Feature;

use App\Mail\OtpResetPassword;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    public function test_first_login_requires_a_password_change_before_dashboard_access(): void
    {
        $user = $this->createUser(mustChangePassword: true);

        $this->post(route('login.submit'), [
            'login_key' => $user->login_key,
            'password' => 'temporary-password',
        ])->assertRedirect(route('settings.index'));

        $this->get(route('dashboard.siswa'))
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('password_change_required');

        $this->put(route('settings.password.update'), [
            'current_password' => 'temporary-password',
            'password' => 'my-new-secure-password',
            'password_confirmation' => 'my-new-secure-password',
        ])->assertRedirect(route('settings.index'))
            ->assertSessionHas('success');

        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('my-new-secure-password', $user->fresh()->password));
        $this->get(route('dashboard.siswa'))->assertOk();
    }

    public function test_password_change_requires_the_current_password_and_a_confirmed_strong_password(): void
    {
        $user = $this->createUser(mustChangePassword: true);

        $this->actingAs($user)->put(route('settings.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'my-new-secure-password',
            'password_confirmation' => 'my-new-secure-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('temporary-password', $user->fresh()->password));
    }

    public function test_user_can_link_a_recovery_email_and_use_it_for_future_password_reset(): void
    {
        Mail::fake();
        $user = $this->createUser();

        $this->actingAs($user)->post(route('settings.emails.store'), [
            'email' => 'active-address@example.test',
        ])->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->linkedEmails()->where('email', 'active-address@example.test')->exists());

        $this->post(route('password.forgot.submit'), [
            'login_key' => $user->login_key,
            'email' => 'active-address@example.test',
        ])->assertRedirect(route('password.otp'));

        Mail::assertSent(OtpResetPassword::class, fn (OtpResetPassword $mail): bool => $mail->hasTo('active-address@example.test'));
    }

    public function test_duplicate_linked_email_is_rejected(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('settings.emails.store'), [
            'email' => 'candidate@example.test',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('settings.emails.store'), [
            'email' => 'candidate@example.test',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, $user->linkedEmails()->where('email', 'candidate@example.test')->count());
    }

    public function test_linked_email_cannot_be_registered_before_changing_the_temporary_password(): void
    {
        $user = $this->createUser(mustChangePassword: true);

        $this->actingAs($user)->post(route('settings.emails.store'), [
            'email' => 'candidate@example.test',
        ])->assertRedirect(route('settings.index'))
            ->assertSessionHas('password_change_required');

        $this->assertDatabaseMissing('user_emails', ['email' => 'candidate@example.test']);
    }

    private function createUser(bool $mustChangePassword = false): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'siswa'],
            ['display_name' => 'Siswa']
        );
        $sequence = ++$this->sequence;

        return User::create([
            'name' => 'Student ' . $sequence,
            'email' => 'student-' . $sequence . '@school.test',
            'login_key' => '202600000' . $sequence,
            'password' => 'temporary-password',
            'role_id' => $role->id,
            'must_change_password' => $mustChangePassword,
            'is_active' => true,
        ]);
    }
}
