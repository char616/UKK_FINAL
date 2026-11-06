<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perbarui tabel roles menjadi total 5 roles
        if (Schema::hasTable('roles')) {
            // Pastikan role siswa memiliki display_name 'Siswa / Orang Tua'
            DB::table('roles')
                ->where('name', 'siswa')
                ->update(['display_name' => 'Siswa / Orang Tua']);

            $studentRole = DB::table('roles')->where('name', 'siswa')->first();
            $parentRole = DB::table('roles')->where('name', 'orang_tua')->first();

            // Migrasi akun yang sebelumnya memiliki role 'orang_tua' ke role 'siswa'
            if ($parentRole && $studentRole) {
                DB::table('users')
                    ->where('role_id', $parentRole->id)
                    ->update(['role_id' => $studentRole->id]);

                // Hapus role orang_tua sehingga tersisa tepat 5 role
                DB::table('roles')->where('id', $parentRole->id)->delete();
            }
        }

        // 2. Tambahkan kolom pendukung di tabel users
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'nip')) {
                $table->string('nip')->nullable()->after('login_key'); // NIP Guru fleksibel
            }

            if (! Schema::hasColumn('users', 'nis')) {
                $table->string('nis')->nullable()->after('nip'); // NIS Siswa (login siswa)
            }

            if (! Schema::hasColumn('users', 'parent_name')) {
                $table->string('parent_name')->nullable()->after('name'); // Nama Orang Tua
            }

            if (! Schema::hasColumn('users', 'parent_login_key')) {
                $table->string('parent_login_key')->nullable()->after('nis'); // Format: nama_siswa_nama_ortu
            }

            if (! Schema::hasColumn('users', 'last_login_as')) {
                $table->string('last_login_as', 20)->nullable()->after('parent_login_key'); // 'siswa' atau 'orang_tua'
            }
        });

        // 3. Tambahkan index untuk pencarian login cepat
        Schema::table('users', function (Blueprint $table) {
            $table->index('nip');
            $table->index('nis');
            $table->index('parent_login_key');
        });

        // 4. Inisialisasi NIP fleksibel untuk data Guru (angka urut 1 sampai 86 untuk sementara)
        $teacherRoleIds = DB::table('roles')
            ->whereIn('name', ['guru', 'wali_kelas'])
            ->pluck('id');

        if ($teacherRoleIds->isNotEmpty()) {
            $teachers = DB::table('users')
                ->whereIn('role_id', $teacherRoleIds)
                ->whereNull('nip')
                ->orderBy('id')
                ->get();

            $nipCounter = 1;
            foreach ($teachers as $teacher) {
                if ($nipCounter > 86) {
                    break;
                }
                DB::table('users')
                    ->where('id', $teacher->id)
                    ->update(['nip' => (string) $nipCounter]);
                $nipCounter++;
            }
        }

        // 5. Backfill NIS dan parent_login_key untuk akun siswa yang sudah ada
        $studentRoleId = DB::table('roles')->where('name', 'siswa')->value('id');
        if ($studentRoleId) {
            $students = DB::table('users')
                ->where('role_id', $studentRoleId)
                ->whereNull('nis')
                ->get();

            foreach ($students as $student) {
                $nis = is_numeric($student->login_key) ? $student->login_key : null;
                $parentName = $student->parent_name ?: 'Orang Tua';
                $parentLoginKey = Str::slug($student->name, '_') . '_' . Str::slug($parentName, '_');

                DB::table('users')
                    ->where('id', $student->id)
                    ->update([
                        'nis' => $nis,
                        'parent_name' => $student->parent_name ?: $parentName,
                        'parent_login_key' => $parentLoginKey,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['nip']);
            $table->dropIndex(['nis']);
            $table->dropIndex(['parent_login_key']);

            $table->dropColumn([
                'nip',
                'nis',
                'parent_name',
                'parent_login_key',
                'last_login_as',
            ]);
        });

        // Kembalikan role orang_tua jika di-rollback
        if (Schema::hasTable('roles') && ! DB::table('roles')->where('name', 'orang_tua')->exists()) {
            DB::table('roles')->insert([
                'name' => 'orang_tua',
                'display_name' => 'Orang Tua/Wali',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('roles')
                ->where('name', 'siswa')
                ->update(['display_name' => 'Siswa']);
        }
    }
};

