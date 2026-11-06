<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'major_id',
        'phase',
        'homeroom_teacher_id',
    ];

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id');
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function students()
    {
        return $this->hasMany(User::class, 'class_id')
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['siswa', 'student', 'Siswa', 'Student']);
            })
            ->orderBy('name');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }
}
