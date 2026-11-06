<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class CheckRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_required_role_can_open_role_dashboard(): void
    {
        $user = $this->createUser('waka');

        $this->actingAs($user)
            ->get(route('dashboard.waka'))
            ->assertOk();
    }

    public function test_user_with_different_role_is_forbidden_from_role_dashboard(): void
    {
        $user = $this->createUser('siswa');

        $this->actingAs($user)
            ->get(route('dashboard.waka'))
            ->assertForbidden();
    }

    public function test_sidebar_shows_navigation_for_the_authenticated_role(): void
    {
        $user = $this->createUser('siswa');

        $this->actingAs($user)
            ->get(route('dashboard.siswa'))
            ->assertOk()
            ->assertSee('data-scp-sidebar', false)
            ->assertSee('Jadwal Pelajaran')
            ->assertDontSee('Kelola Akun');
    }

    public function test_database_rejects_users_assigned_to_a_missing_role(): void
    {
        $this->expectException(QueryException::class);

        User::create([
            'name' => 'User Without Role',
            'email' => 'orphan-role@school.test',
            'login_key' => 'ORPHAN-ROLE',
            'password' => 'password',
            'role_id' => 999999,
            'is_active' => true,
        ]);
    }

    private function createUser(string $roleName): User
    {
        $role = Role::create([
            'name' => $roleName,
            'display_name' => str($roleName)->replace('_', ' ')->title()->toString(),
        ]);

        return User::create([
            'name' => 'Test User',
            'email' => $roleName . '@school.test',
            'login_key' => strtoupper($roleName) . '-TEST',
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
