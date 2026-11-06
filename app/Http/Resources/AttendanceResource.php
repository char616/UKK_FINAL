<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teaching_journal_id' => $this->teaching_journal_id,
            'schedule_id' => $this->schedule_id,
            'student_id' => $this->student_id,
            'date' => is_object($this->date) && method_exists($this->date, 'toDateString')
                ? $this->date->toDateString()
                : (string) $this->date,
            'status' => $this->status,
            'notes' => $this->notes,
            'recorded_by' => $this->recorded_by,
            'journal' => $this->whenLoaded('teachingJournal', fn () => $this->teachingJournal ? [
                'id' => $this->teachingJournal->id,
                'materi' => $this->teachingJournal->materi,
                'summary' => $this->teachingJournal->activity_summary,
                'catatan_kejadian' => $this->teachingJournal->catatan_kejadian ?? $this->teachingJournal->notes,
                'foto_bukti_kbm' => $this->teachingJournal->foto_bukti_kbm ?? $this->teachingJournal->photo_proof,
                'attachment_url' => filled($this->teachingJournal->photo_proof) ? url('/api/journals/'.$this->teachingJournal->id.'/attachment') : null,
            ] : null),
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'name' => $this->student->name,
                'login_key' => $this->student->login_key,
                'class_id' => $this->student->class_id,
            ] : null),
            'schedule' => $this->whenLoaded('schedule', fn () => $this->schedule ? [
                'id' => $this->schedule->id,
                'day' => $this->schedule->day,
                'start_time' => substr((string) $this->schedule->start_time, 0, 5),
                'end_time' => substr((string) $this->schedule->end_time, 0, 5),
                'round' => (int) $this->schedule->round,
                'class' => $this->schedule->relationLoaded('class') && $this->schedule->class ? [
                    'id' => $this->schedule->class->id,
                    'name' => $this->schedule->class->name,
                ] : null,
                'subject' => $this->schedule->relationLoaded('subject') && $this->schedule->subject ? [
                    'id' => $this->schedule->subject->id,
                    'code' => $this->schedule->subject->code,
                    'name' => $this->schedule->subject->name,
                ] : null,
                'teacher' => $this->schedule->relationLoaded('teacher') && $this->schedule->teacher ? [
                    'id' => $this->schedule->teacher->id,
                    'name' => $this->schedule->teacher->name,
                ] : null,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

