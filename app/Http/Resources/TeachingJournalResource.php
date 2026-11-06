<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeachingJournalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'schedule_id' => $this->schedule_id,
            'date' => is_object($this->date) && method_exists($this->date, 'toDateString')
                ? $this->date->toDateString()
                : (string) $this->date,
            'materi' => $this->materi,
            'catatan_kejadian' => $this->catatan_kejadian ?? $this->activity_summary,
            'foto_bukti_kbm' => $this->foto_bukti_kbm ?? $this->photo_proof,
            'activity_summary' => $this->activity_summary,
            'summary' => $this->activity_summary,
            'photo_proof' => $this->foto_bukti_kbm ?? $this->photo_proof,
            'has_attachment' => filled($this->foto_bukti_kbm ?? $this->photo_proof),
            'attachment_download_url' => filled($this->foto_bukti_kbm ?? $this->photo_proof)
                ? url('/api/journals/' . $this->id . '/attachment')
                : null,
            'jam_pelajaran_id' => $this->jam_pelajaran_id,
            'teacher' => $this->whenLoaded('teacher', fn () => $this->teacher ? [
                'id' => $this->teacher->id,
                'name' => $this->teacher->name,
                'login_key' => $this->teacher->login_key,
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
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

