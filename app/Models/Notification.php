<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Notification extends Model
{
    /**
     * Role yang boleh menerima notifikasi berdasarkan jenis notifikasinya.
     * Notifikasi yang tidak tercantum tetap bersifat umum.
     *
     * @var array<string, array<int, string>>
     */
    private const ROLE_AUDIENCES = [
        'Pengajuan Jadwal Guru' => ['waka'],
        'Putaran Jadwal Sekolah Diperbarui' => ['guru'],
        'Pengingat Jurnal Mengajar' => ['guru'],
        'Pengingat KBM: Isi Jurnal & Presensi' => ['guru'],
        'Pengajuan Izin Jam Pelajaran Baru' => ['bk'],
        'Pengajuan Izin Baru' => ['wali_kelas'],
        'Status Izin Diperbarui' => ['siswa', 'orang_tua'],
        'Informasi Presensi' => ['siswa'],
    ];

    protected $guarded = ['id'];

    public function scopeVisibleToRole(Builder $query, string $roleName): Builder
    {
        $restrictedTitles = array_keys(self::ROLE_AUDIENCES);
        $allowedTitles = collect(self::ROLE_AUDIENCES)
            ->filter(fn (array $roles): bool => in_array($roleName, $roles, true))
            ->keys()
            ->all();

        return $query->where(function (Builder $notificationQuery) use ($restrictedTitles, $allowedTitles): void {
            $notificationQuery
                ->whereNotIn('title', $restrictedTitles)
                ->when($allowedTitles !== [], fn (Builder $allowedQuery): Builder => $allowedQuery->orWhereIn('title', $allowedTitles));
        });
    }

    public function isVisibleToRole(string $roleName): bool
    {
        return ! array_key_exists($this->title, self::ROLE_AUDIENCES)
            || in_array($roleName, self::ROLE_AUDIENCES[$this->title], true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tampilkan pesan notifikasi dengan format tanggal Indonesia yang rapi (DD-MM-YYYY)
     */
    public function getFormattedMessageAttribute(): string
    {
        return preg_replace_callback('/\b(\d{4})-(\d{2})-(\d{2})\b/', function ($matches) {
            return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }, $this->message ?? '');
    }

    /**
     * Tentukan URL tujuan ketika notifikasi diklik
     */
    public function getTargetUrlAttribute(): string
    {
        $title = strtolower($this->title ?? '');
        $message = strtolower($this->message ?? '');

        // 1. Pengingat / Aktivitas Jurnal Mengajar
        if (str_contains($title, 'jurnal') || str_contains($message, 'jurnal')) {
            // Cek apakah ada format tanggal DD-MM-YYYY
            if (preg_match('/\b(\d{2})-(\d{2})-(\d{4})\b/', $this->message, $m)) {
                return route('journals.index', ['date' => "{$m[3]}-{$m[2]}-{$m[1]}"]);
            }
            // Cek apakah ada format tanggal YYYY-MM-DD
            if (preg_match('/\b(\d{4})-(\d{2})-(\d{2})\b/', $this->message, $m)) {
                return route('journals.index', ['date' => $m[0]]);
            }
            return route('journals.index');
        }

        // 2. Jadwal Pelajaran (Pengajuan Jadwal Guru, Putaran Jadwal, Kunci Jadwal)
        if (str_contains($title, 'jadwal') || str_contains($message, 'jadwal')) {
            return route('schedules.index');
        }

        // 3. Izin dan Sakit (Pengajuan Izin, Persetujuan Izin)
        if (str_contains($title, 'izin') || str_contains($message, 'izin') || str_contains($title, 'sakit') || str_contains($message, 'sakit')) {
            return route('leaves.index');
        }

        // 4. Presensi KBM & Siswa
        if (str_contains($title, 'presensi') || str_contains($message, 'presensi') || str_contains($title, 'kehadiran')) {
            return route('attendances.index');
        }

        // 5. Audit Log (Khusus WAKA)
        if (str_contains($title, 'audit') || str_contains($message, 'audit')) {
            if (auth()->user()?->role?->name === 'waka') {
                return route('audit-logs.index');
            }
        }

        // 6. Pengaturan Akun
        if (str_contains($title, 'password') || str_contains($title, 'akun') || str_contains($message, 'profil')) {
            return route('settings.index');
        }

        // Default: halaman notifikasi
        return route('notifications.index');
    }

    /**
     * Icon informatif sesuai kategori notifikasi
     */
    public function getCategoryIconAttribute(): string
    {
        $title = strtolower($this->title ?? '');
        $message = strtolower($this->message ?? '');

        if (str_contains($title, 'jurnal') || str_contains($message, 'jurnal')) {
            return 'bi-journal-text text-success';
        }
        if (str_contains($title, 'jadwal') || str_contains($message, 'jadwal')) {
            return 'bi-calendar-week text-primary';
        }
        if (str_contains($title, 'izin') || str_contains($message, 'izin') || str_contains($title, 'sakit')) {
            return 'bi-envelope-paper text-warning';
        }
        if (str_contains($title, 'presensi') || str_contains($message, 'presensi')) {
            return 'bi-calendar-check text-info';
        }
        if (str_contains($title, 'audit') || str_contains($message, 'audit')) {
            return 'bi-shield-check text-danger';
        }

        return 'bi-bell text-primary';
    }

    public static function send(int $userId, string $title, string $message): ?self
    {
        $user = User::with('role')->find($userId);
        if (! $user || ! (new self(['title' => $title]))->isVisibleToRole($user->role?->name ?? '')) {
            return null;
        }

        return self::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
        ]);
    }
}
