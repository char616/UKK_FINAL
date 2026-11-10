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

    public static function send(int $userId, string $title, string $message): self
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
        ]);
    }
}
