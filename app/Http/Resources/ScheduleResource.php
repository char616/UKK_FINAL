<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'room_id' => $this->room_id,
            'day' => $this->day,
            'start_time' => substr((string) $this->start_time, 0, 5),
            'end_time' => substr((string) $this->end_time, 0, 5),
            'round' => (int) $this->round,
            'teacher' => $this->whenLoaded('teacher', fn () => $this->teacher ? [
                'id' => $this->teacher->id,
                'name' => $this->teacher->name,
                'login_key' => $this->teacher->login_key,
            ] : null),
            'class' => $this->whenLoaded('class', fn () => $this->class ? [
                'id' => $this->class->id,
                'name' => $this->class->name,
                'major_id' => $this->class->major_id,
                'major' => $this->class->relationLoaded('major') && $this->class->major ? [
                    'id' => $this->class->major->id,
                    'code' => $this->class->major->code,
                    'name' => $this->class->major->name,
                ] : null,
            ] : null),
            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->id,
                'code' => $this->subject->code,
                'name' => $this->subject->name,
            ] : null),
            'room' => $this->whenLoaded('room', fn () => $this->room ? [
                'id' => $this->room->id,
                'code' => $this->room->code,
                'name' => $this->room->name,
                'capacity' => $this->room->capacity,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

