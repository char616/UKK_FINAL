<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_only_their_notifications_and_opening_page_marks_them_read(): void
    {
        $role = Role::create(['name' => 'siswa', 'display_name' => 'Siswa']);
        $user = User::create([
            'login_key' => 'SISWA-TEST-1',
            'name' => 'Siswa Tes',
            'email' => 'siswa-test@example.test',
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $otherUser = User::create([
            'login_key' => 'SISWA-TEST-2',
            'name' => 'Siswa Lain',
            'email' => 'siswa-test-2@example.test',
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $notification = Notification::send($user->id, 'Informasi', 'Pesan untuk siswa.');
        Notification::send($otherUser->id, 'Rahasia', 'Pesan untuk pengguna lain.');

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('Pesan untuk siswa.')
            ->assertDontSee('Pesan untuk pengguna lain.');
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'is_read' => true,
        ]);
    }

    public function test_notification_inbox_paginates_without_leaking_other_users_records(): void
    {
        $this->travelTo('2026-10-25 10:00:00');
        $role = Role::create(['name' => 'siswa', 'display_name' => 'Siswa']);
        $user = User::create([
            'login_key' => 'SISWA-PAGINATION',
            'name' => 'Siswa Paginasi',
            'email' => 'pagination@example.test',
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        for ($index = 1; $index <= 26; $index++) {
            $message = match ($index) {
                1 => 'Notifikasi pertama',
                26 => 'Notifikasi paling baru',
                default => 'Notifikasi urutan ' . $index,
            };
            $notification = Notification::send($user->id, 'Info ' . $index, $message);
            $notification->forceFill(['created_at' => now()->addSeconds($index)])->save();
        }

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('Notifikasi paling baru')
            ->assertDontSee('Notifikasi pertama');

        $this->get(route('notifications.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Notifikasi pertama');
    }
}
