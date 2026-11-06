<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateInitialWakaTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_the_first_waka_and_school_roles(): void
    {
        $this->artisan('scp:create-initial-waka')
            ->expectsQuestion('Nama lengkap WAKA', 'WAKA Sekolah')
            ->expectsQuestion('Login ID WAKA', 'WAKA-REAL-001')
            ->expectsQuestion('Email akun WAKA', 'waka@school.example')
            ->expectsQuestion('Email pemulihan (boleh kosong)', 'recovery@school.example')
            ->expectsQuestion('Password WAKA (minimal 12 karakter)', 'a-secure-password')
            ->expectsQuestion('Ulangi password WAKA', 'a-secure-password')
            ->expectsOutputToContain('Akun WAKA pertama berhasil dibuat.')
            ->assertExitCode(0);

        $wakaRole = Role::where('name', 'waka')->firstOrFail();
        $waka = User::where('login_key', 'WAKA-REAL-001')->firstOrFail();

        $this->assertSame($wakaRole->id, $waka->role_id);
        $this->assertSame('recovery@school.example', $waka->recovery_email);
        $this->assertTrue(Hash::check('a-secure-password', $waka->password));
        $this->assertDatabaseCount('roles', 6);
    }

    public function test_command_refuses_to_create_a_second_waka_account(): void
    {
        $role = Role::create(['name' => 'waka', 'display_name' => 'WAKA']);
        User::create([
            'name' => 'Existing WAKA',
            'login_key' => 'WAKA-EXISTING',
            'email' => 'existing-waka@example.test',
            'password' => 'existing-password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->artisan('scp:create-initial-waka')
            ->expectsOutputToContain('An account with the WAKA role already exists.')
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_command_rejects_a_weak_password_without_creating_a_user(): void
    {
        $this->artisan('scp:create-initial-waka')
            ->expectsQuestion('Nama lengkap WAKA', 'WAKA Sekolah')
            ->expectsQuestion('Login ID WAKA', 'WAKA-REAL-002')
            ->expectsQuestion('Email akun WAKA', 'waka2@school.example')
            ->expectsQuestion('Email pemulihan (boleh kosong)', '')
            ->expectsQuestion('Password WAKA (minimal 12 karakter)', 'short')
            ->expectsQuestion('Ulangi password WAKA', 'short')
            ->expectsOutputToContain('The password field must be at least 12 characters.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['login_key' => 'WAKA-REAL-002']);
    }
}
