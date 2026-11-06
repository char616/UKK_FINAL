<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleLock extends Model
{
    use HasFactory;

    protected $table = 'schedule_locks';

    protected $guarded = ['id'];

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
