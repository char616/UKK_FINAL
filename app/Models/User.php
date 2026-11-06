<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'login_key',
        'nip',
        'nis',
        'parent_name',
        'parent_login_key',
        'last_login_as',
        'email',
        'recovery_email',
        'recovery_email_verified_at',
        'password',
        'role_id',
        'class_id',
        'guardian_id',
        'is_active',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'recovery_email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function linkedEmails()
    {
        return $this->hasMany(UserEmail::class)->orderByDesc('is_primary')->orderBy('id');
    }

    /**
     * Pastikan recovery_email lama (diisi admin / impor) ikut tampil di daftar email tertaut.
     */
    public function linkLegacyRecoveryEmail(): void
    {
        $email = Str::lower(trim((string) $this->recovery_email));

        if ($email === '' || $this->linkedEmails()->where('email', $email)->exists()) {
            return;
        }

        $this->linkedEmails()->create([
            'email' => $email,
            'is_primary' => ! $this->linkedEmails()->where('is_primary', true)->exists(),
        ]);
    }

    /**
     * Samakan kolom users.recovery_email dengan email utama di tabel user_emails
     * (dipakai fitur lain seperti notifikasi absensi dan tampilan admin).
     */
    public function syncPrimaryEmail(): void
    {
        $primary = $this->linkedEmails()->where('is_primary', true)->first()
            ?? $this->linkedEmails()->first();

        if ($primary && ! $primary->is_primary) {
            $primary->update(['is_primary' => true]);
        }

        $this->forceFill([
            'recovery_email' => $primary?->email,
            'recovery_email_verified_at' => $primary ? now() : null,
        ])->save();
    }

    /**
     * Cocokkan email yang diketik saat "Lupa Password" dengan email yang tertaut ke akun ini.
     * Mengembalikan alamat tujuan OTP, atau null bila tidak cocok.
     */
    public function matchResetEmail(string $input): ?string
    {
        $input = Str::lower(trim($input));

        if ($input === '') {
            return null;
        }

        if ($this->linkedEmails()->where('email', $input)->exists()) {
            return $input;
        }

        $recovery = Str::lower(trim((string) $this->recovery_email));
        if ($recovery !== '' && $this->recovery_email_verified_at !== null && $recovery === $input) {
            return $input;
        }

        $main = Str::lower(trim((string) $this->email));
        if ($main !== '' && ! str_ends_with($main, '@accounts.invalid') && ! str_ends_with($main, '@school.test') && $main === $input) {
            return $input;
        }

        return null;
    }

    /**
     * Cek apakah akun ini belum memiliki email pemulihan terverifikasi
     * dan masih menggunakan email dummy/seeder default (sehingga dapat menautkan email asli saat lupa password).
     */
    public function canAdoptRecoveryEmail(): bool
    {
        if (! empty($this->recovery_email)) {
            return false;
        }

        if ($this->linkedEmails()->exists()) {
            return false;
        }

        $main = Str::lower(trim((string) $this->email));

        return empty($main)
            || str_ends_with($main, '@school.test')
            || str_ends_with($main, '@accounts.invalid')
            || str_ends_with($main, '@example.test')
            || str_ends_with($main, '@example.com')
            || str_ends_with($main, '.test');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function homeroomClasses()
    {
        return $this->hasMany(Classes::class, 'homeroom_teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')->withTimestamps();
    }

    public function children()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')->withTimestamps();
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function journals()
    {
        return $this->hasMany(TeachingJournal::class, 'teacher_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'student_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->latest();
    }
}