<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $guarded = ['id'];

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

    public static function send(int $userId, string $title, string $message): self
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
        ]);
    }
}
