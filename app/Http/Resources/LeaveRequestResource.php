<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'type' => $this->type,
            'jenis_waktu_izin' => $this->jenis_waktu_izin ?? 'seharian',
            'jam_mulai_id' => $this->jam_mulai_id,
            'jam_selesai_id' => $this->jam_selesai_id,
            'jam_mulai' => $this->whenLoaded('jamMulai', fn () => $this->jamMulai),
            'jam_selesai' => $this->whenLoaded('jamSelesai', fn () => $this->jamSelesai),
            'start_date' => is_object($this->start_date) && method_exists($this->start_date, 'toDateString')
                ? $this->start_date->toDateString()
                : (string) $this->start_date,
            'end_date' => is_object($this->end_date) && method_exists($this->end_date, 'toDateString')
                ? $this->end_date->toDateString()
                : (string) $this->end_date,
            'reason' => $this->reason,
            'file_bukti' => $this->file_bukti ?? $this->attachment,
            'attachment' => $this->file_bukti ?? $this->attachment,
            'has_attachment' => filled($this->file_bukti ?? $this->attachment),
            'attachment_download_url' => filled($this->file_bukti ?? $this->attachment)
                ? url('/api/leaves/' . $this->id . '/attachment')
                : null,
            'target_approver' => $this->target_approver ?? 'wali_kelas',
            'status' => $this->status,
            'alasan_penolakan' => $this->alasan_penolakan,
            'processed_by' => $this->processed_by,
            'processed_at' => $this->processed_at?->toIso8601String(),
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'name' => $this->student->name,
                'login_key' => $this->student->login_key,
                'class_id' => $this->student->class_id,
                'class_name' => $this->student->relationLoaded('schoolClass') ? $this->student->schoolClass?->name : null,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

