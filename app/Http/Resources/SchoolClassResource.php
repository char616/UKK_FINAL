<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phase' => $this->phase,
            'major_id' => $this->major_id,
            'major' => $this->whenLoaded('major', fn () => $this->major ? [
                'id' => $this->major->id,
                'code' => $this->major->code,
                'name' => $this->major->name,
            ] : null),
            'homeroom_teacher_id' => $this->homeroom_teacher_id,
            'homeroom_teacher' => $this->whenLoaded('homeroomTeacher', fn () => $this->homeroomTeacher ? [
                'id' => $this->homeroomTeacher->id,
                'name' => $this->homeroomTeacher->name,
                'login_key' => $this->homeroomTeacher->login_key,
            ] : null),
            'students_count' => $this->whenCounted('students', $this->students_count ?? null),
            'schedules_count' => $this->whenCounted('schedules', $this->schedules_count ?? null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

