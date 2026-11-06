<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JamPelajaran extends Model
{
    use HasFactory;

    protected $table = 'jam_pelajaran';

    protected $fillable = [
        'hari',
        'nama_sesi',
        'jam_mulai',
        'jam_selesai',
        'is_istirahat',
        'urutan',
    ];

    protected $casts = [
        'is_istirahat' => 'boolean',
        'urutan' => 'integer',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'jam_pelajaran_id');
    }

    public function teachingJournals(): HasMany
    {
        return $this->hasMany(TeachingJournal::class, 'jam_pelajaran_id');
    }

    public function leavesStartingHere(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'jam_mulai_id');
    }

    public function leavesEndingHere(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'jam_selesai_id');
    }
}

