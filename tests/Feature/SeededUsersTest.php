<?php

namespace Tests\Feature;

use App\Models\Classes;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeededUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_bk_and_wali_kelas_accounts_are_created_by_database_seeder(): void
    {
        // Jalankan seeder pada environment local/production
        app()->detectEnvironment(fn () => 'local');

        $this->seed(DatabaseSeeder::class);

        // 1. Verifikasi tepat 6 Akun BK: BK_1 s/d BK_6
        $bkRole = Role::where('name', 'bk')->firstOrFail();
        $bkUsers = User::where('role_id', $bkRole->id)->get();
        $this->assertCount(6, $bkUsers);

        for ($i = 1; $i <= 6; $i++) {
            $bk = User::where('login_key', "BK_{$i}")->first();
            $this->assertNotNull($bk, "Akun BK_{$i} harus ada");
            $this->assertSame("Guru BK {$i}", $bk->name);
            $this->assertSame("bk_{$i}@school.test", $bk->email);
            $this->assertTrue(Hash::check('password123', $bk->password));
            $this->assertTrue($bk->is_active);
        }

        // 2. Verifikasi Akun Wali Kelas menggunakan nama kelas (contoh: "X RPL 1")
        $expectedClasses = [
            'X RPL 1', 'X RPL 2', 'X TKJ 1', 'X TKJ 2', 'X DKV 1', 'X DKV 2',
            'X AK 1', 'X AK 2', 'X AK 3', 'X LPS 1', 'X LPS 2', 'X MP 1', 'X MP 2',
            'X BR 1', 'X BR 2', 'X BD',
            'XI RPL 1', 'XI RPL 2', 'XI TKJ 1', 'XI TKJ 2', 'XI DKV 1', 'XI DKV 2',
            'XI AK 1', 'XI AK 2', 'XI AK 3', 'XI LPS 1', 'XI LPS 2', 'XI MP 1', 'XI MP 2',
            'XI BR 1', 'XI BR 2', 'XI BD',
            'XII RPL 1', 'XII RPL 2', 'XII TKJ 1', 'XII TKJ 2', 'XII DKV 1', 'XII DKV 2',
            'XII AK 1', 'XII AK 2', 'XII AK 3', 'XII AK 4', 'XII LPS', 'XII MP 1', 'XII MP 2',
            'XII BR 1', 'XII BR 2', 'XII BD',
        ];

        $waliRole = Role::where('name', 'wali_kelas')->firstOrFail();
        $waliUsers = User::where('role_id', $waliRole->id)->get();
        $this->assertCount(count($expectedClasses), $waliUsers);

        foreach ($expectedClasses as $className) {
            $class = Classes::with('homeroomTeacher')->where('name', $className)->first();
            $this->assertNotNull($class, "Kelas {$className} harus ada");
            $this->assertNotNull($class->homeroomTeacher, "Wali kelas untuk {$className} harus terhubung");
            $this->assertSame($className, $class->homeroomTeacher->login_key);
            $this->assertSame($className, $class->homeroomTeacher->name);
            $this->assertTrue(Hash::check('password123', $class->homeroomTeacher->password));
        }

        // 3. Verifikasi fleksibilitas login BK dan Wali Kelas
        app()->detectEnvironment(fn () => 'testing');

        $this->post(route('login.submit'), [
            'login_key' => 'BK_1',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.bk'));

        $this->post(route('login.submit'), [
            'login_key' => 'bk_2',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.bk'));

        $this->post(route('login.submit'), [
            'login_key' => 'X RPL 1',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.wali'));

        $this->post(route('login.submit'), [
            'login_key' => 'x_rpl_1',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.wali'));

        $this->post(route('login.submit'), [
            'login_key' => 'xrpl1',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.wali'));

        $this->post(route('login.submit'), [
            'login_key' => 'Kelas X RPL 1',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.wali'));
    }
}
