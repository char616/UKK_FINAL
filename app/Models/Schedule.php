<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function jamPelajaran()
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_pelajaran_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function journals()
    {
        return $this->hasMany(TeachingJournal::class);
    }

    /**
     * Label sesi yang informatif (contoh: "Jam 1 s/d Jam 2 (07:30 - 09:00)")
     */
    public function getSessionLabelAttribute(): string
    {
        $startTime = substr((string) $this->start_time, 0, 5);
        $endTime = substr((string) $this->end_time, 0, 5);

        if ($this->jam_pelajaran_id && $this->relationLoaded('jamPelajaran') && $this->jamPelajaran) {
            return $this->jamPelajaran->nama_sesi . " ({$startTime} - {$endTime})";
        }

        try {
            $sessions = JamPelajaran::where('hari', $this->day)
                ->where('is_istirahat', false)
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->whereBetween('jam_mulai', [$startTime, $endTime])
                      ->orWhereBetween('jam_selesai', [$startTime, $endTime])
                      ->orWhere(function ($sub) use ($startTime, $endTime) {
                          $sub->where('jam_mulai', '<=', $startTime)
                              ->where('jam_selesai', '>=', $endTime);
                      });
                })
                ->orderBy('urutan')
                ->pluck('nama_sesi');

            if ($sessions->isNotEmpty()) {
                if ($sessions->count() === 1) {
                    return $sessions->first() . " ({$startTime} - {$endTime})";
                }
                return $sessions->first() . " s/d " . $sessions->last() . " ({$startTime} - {$endTime})";
            }
        } catch (\Throwable) {
            // fallback
        }

        return "{$startTime} - {$endTime}";
    }
}
