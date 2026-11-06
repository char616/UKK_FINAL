<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'processed_at' => 'datetime',
    ];

    public function getFileBuktiAttribute()
    {
        return $this->attributes['file_bukti'] ?? $this->attributes['attachment'] ?? null;
    }

    public function setFileBuktiAttribute($value): void
    {
        $this->attributes['file_bukti'] = $value;
        $this->attributes['attachment'] = $value;
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function jamMulai(): BelongsTo
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_mulai_id');
    }

    public function jamSelesai(): BelongsTo
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_selesai_id');
    }
}
