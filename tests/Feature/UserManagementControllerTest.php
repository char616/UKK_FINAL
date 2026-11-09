<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    public function test_waka_can_create_school_user_accounts(): void
    {
        $waka = $this->createUser('waka');
        Role::create(['name' => 'guru', 'display_name' => 'Guru']);

        $this->actingAs($waka)->post(route('accounts.store'), [
            'name' => 'Guru Baru',
            'login_key' => 'GURU-NEW-001',
            'email' => 'guru-new@example.test',
            'recovery_email' => 'guru-recovery@example.test',
            'role' => 'guru',
            'password' => 'initial-password',
            'password_confirmation' => 'initial-password',
        ])->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Guru Baru',
            'login_key' => 'GURU-NEW-001',
            'email' => 'guru-new@example.test',
            'recovery_email' => 'guru-recovery@example.test',
            'must_change_password' => true,
            'is_active' => true,
        ]);
        $this->assertTrue(Hash::check('initial-password', User::where('login_key', 'GURU-NEW-001')->firstOrFail()->password));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $waka->id,
            'action' => 'CREATE_USER',
        ]);
    }

    public function test_waka_is_limited_to_one_account_and_account_targets_are_shown(): void
    {
        $waka = $this->createUser('waka');

        $this->actingAs($waka)->post(route('accounts.store'), [
            'name' => 'WAKA Kedua',
            'login_key' => 'WAKA-SECOND',
            'email' => 'waka-second@example.test',
            'role' => 'waka',
            'password' => 'secure-temporary-password',
            'password_confirmation' => 'secure-temporary-password',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseCount('users', 1);
        $this->get(route('accounts.index'))
            ->assertOk()
            ->assertSee('Buat Akun Pengguna Baru')
            ->assertDontSee('Semua akun aktif otomatis');
    }

    public function test_student_and_parent_login_ids_follow_the_school_convention(): void
    {
        $waka = $this->createUser('waka');
        Role::firstOrCreate(['name' => 'siswa'], ['display_name' => 'Siswa']);
        Role::firstOrCreate(['name' => 'orang_tua'], ['display_name' => 'Orang Tua']);

        $this->actingAs($waka)->from(route('accounts.index'))->post(route('accounts.store'), [
            'name' => 'Siswa Baru',
            'login_key' => 'SISWA-001',
            'email' => 'student-new@example.test',
            'role' => 'siswa',
            'password' => 'secure-temporary-password',
            'password_confirmation' => 'secure-temporary-password',
        ])->assertSessionHasErrors('login_key');

        $this->from(route('accounts.index'))->post(route('accounts.store'), [
            'name' => 'Orang Tua Siswa',
            'login_key' => 'wali-001',
            'email' => 'parent-new@example.test',
            'role' => 'orang_tua',
            'password' => 'secure-temporary-password',
            'password_confirmation' => 'secure-temporary-password',
        ])->assertSessionHasErrors('login_key');

        $this->from(route('accounts.index'))->post(route('accounts.store'), [
            'name' => 'Siswa Baru',
            'login_key' => '1234567890',
            'role' => 'siswa',
            'password' => 'secure-temporary-password',
            'password_confirmation' => 'secure-temporary-password',
        ])->assertRedirect(route('accounts.index'));

        $student = User::where('login_key', '1234567890')->firstOrFail();
        $this->assertStringEndsWith('@accounts.invalid', $student->email);
        $this->assertTrue($student->must_change_password);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_waka_can_update_contact_activation_and_password_without_changing_role_or_login_id(): void
    {
        $waka = $this->createUser('waka');
        $student = $this->createUser('siswa');
        $student->update([
            'recovery_email' => 'verified-before@example.test',
            'recovery_email_verified_at' => now(),
        ]);
        $loginKey = $student->login_key;

        $this->actingAs($waka)->put(route('accounts.update', $student), [
            'name' => 'Nama Siswa Baru',
            'email' => 'new-student@example.test',
            'recovery_email' => 'recovery-new@example.test',
            'is_active' => '0',
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
            'role_id' => Role::where('name', 'waka')->value('id'),
            'login_key' => 'CHANGED-IDENTIFIER',
        ])->assertRedirect(route('accounts.index'));

        $student->refresh();
        $this->assertSame('Nama Siswa Baru', $student->name);
        $this->assertSame($loginKey, $student->login_key);
        $this->assertSame('siswa', $student->role->name);
        $this->assertFalse($student->is_active);
        $this->assertTrue(Hash::check('replacement-password', $student->password));
        $this->assertTrue($student->must_change_password);
        $this->assertNull($student->recovery_email_verified_at);
    }

    public function test_non_waka_cannot_manage_accounts(): void
    {
        $student = $this->createUser('siswa');

        $this->actingAs($student)
            ->get(route('accounts.index'))
            ->assertForbidden();

        $this->post(route('accounts.store'), [
            'name' => 'Akun Ilegal',
            'login_key' => 'ILLEGAL-001',
            'email' => 'illegal@example.test',
            'role' => 'waka',
            'password' => 'initial-password',
            'password_confirmation' => 'initial-password',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['login_key' => 'ILLEGAL-001']);
    }

    public function test_waka_cannot_deactivate_their_own_account(): void
    {
        $waka = $this->createUser('waka');

        $this->actingAs($waka)->put(route('accounts.update', $waka), [
            'name' => $waka->name,
            'email' => $waka->email,
            'recovery_email' => null,
            'is_active' => '0',
        ])->assertSessionHasErrors('is_active');

        $this->assertDatabaseHas('users', ['id' => $waka->id, 'is_active' => true]);
    }

    public function test_account_creation_rejects_a_role_outside_school_roles(): void
    {
        $waka = $this->createUser('waka');

        $this->actingAs($waka)->post(route('accounts.store'), [
            'name' => 'Akun Tak Valid',
            'login_key' => 'INVALID-ROLE-001',
            'email' => 'invalid-role@example.test',
            'role' => 'administrator',
            'password' => 'initial-password',
            'password_confirmation' => 'initial-password',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['login_key' => 'INVALID-ROLE-001']);
    }

    private function createUser(string $roleName, bool $active = true): User
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => str($roleName)->replace('_', ' ')->title()->toString()]
        );
        $identifier = strtoupper($roleName) . '-' . ++$this->sequence;

        return User::create([
            'name' => 'User ' . $this->sequence,
            'email' => strtolower($identifier) . '@school.test',
            'login_key' => $identifier,
            'password' => 'original-password',
            'role_id' => $role->id,
            'is_active' => $active,
        ]);
    }
}
