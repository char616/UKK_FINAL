<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingJournal extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['summary', 'attachment'];

    public function getSummaryAttribute()
    {
        return $this->attributes['summary'] ?? $this->attributes['activity_summary'] ?? null;
    }

    public function setSummaryAttribute($value): void
    {
        $this->attributes['activity_summary'] = $value;
    }

    public function getAttachmentAttribute()
    {
        return $this->attributes['attachment'] ?? $this->attributes['foto_bukti_kbm'] ?? $this->attributes['photo_proof'] ?? null;
    }

    public function setAttachmentAttribute($value): void
    {
        $this->attributes['foto_bukti_kbm'] = $value;
        $this->attributes['photo_proof'] = $value;
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function jamPelajaran()
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_pelajaran_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'teaching_journal_id');
    }
}
