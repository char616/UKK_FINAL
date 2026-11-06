<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper statis untuk mencatat log dengan mudah di seluruh controller
    public static function record($userId, $action, $description)
    {
        self::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
        ]);
    }
}