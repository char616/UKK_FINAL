<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke Sesi Jurnal Mengajar
    public function teachingJournal(): BelongsTo
    {
        return $this->belongsTo(TeachingJournal::class, 'teaching_journal_id');
    }

    // Relasi ke Jadwal
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    // Relasi ke Siswa
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}