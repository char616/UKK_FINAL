<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateInitialWaka extends Command
{
    protected $signature = 'scp:create-initial-waka';

    protected $description = 'Create the first WAKA account using secure interactive prompts';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Run this command in an interactive terminal so the password is not exposed in command history.');

            return self::FAILURE;
        }

        $roles = [
            'waka' => 'WAKA',
            'bk' => 'BK',
            'wali_kelas' => 'Wali Kelas',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
            'orang_tua' => 'Orang Tua/Wali',
        ];

        foreach ($roles as $name => $displayName) {
            Role::firstOrCreate(['name' => $name], ['display_name' => $displayName]);
        }

        $wakaRole = Role::where('name', 'waka')->firstOrFail();

        if (User::where('role_id', $wakaRole->id)->exists()) {
            $this->error('An account with the WAKA role already exists. Use the existing WAKA account to manage users.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nama lengkap WAKA'));
        $loginKey = trim((string) $this->ask('Login ID WAKA'));
        $email = trim((string) $this->ask('Email akun WAKA'));
        $recoveryEmail = trim((string) $this->ask('Email pemulihan (boleh kosong)'));
        $password = (string) $this->secret('Password WAKA (minimal 12 karakter)');
        $passwordConfirmation = (string) $this->secret('Ulangi password WAKA');

        $validator = Validator::make([
            'name' => $name,
            'login_key' => $loginKey,
            'email' => $email,
            'recovery_email' => $recoveryEmail === '' ? null : $recoveryEmail,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'login_key' => ['required', 'string', 'max:255', 'unique:users,login_key'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'recovery_email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'same:password_confirmation'],
            'password_confirmation' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $created = DB::transaction(function () use ($wakaRole, $name, $loginKey, $email, $recoveryEmail, $password): bool {
            Role::query()->whereKey($wakaRole->id)->lockForUpdate()->firstOrFail();

            if (User::where('role_id', $wakaRole->id)->exists()) {
                return false;
            }

            User::create([
                'name' => $name,
                'login_key' => $loginKey,
                'email' => $email,
                'recovery_email' => $recoveryEmail === '' ? null : $recoveryEmail,
                'password' => Hash::make($password),
                'role_id' => $wakaRole->id,
                'must_change_password' => true,
                'is_active' => true,
            ]);

            return true;
        });

        if (! $created) {
            $this->error('A WAKA account was created by another process. No account was created.');

            return self::FAILURE;
        }

        $this->info('Akun WAKA pertama berhasil dibuat. Login ID dan email pemulihan sudah tersimpan.');

        return self::SUCCESS;
    }
}
