<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roleName = $this->role?->name;
        $loginAs = $this->last_login_as ?: $roleName;

        $activeIdentityName = $this->name;
        $activeIdentityLabel = $this->role?->display_name ?? 'Pengguna';

        if ($roleName === 'siswa') {
            if ($loginAs === 'orang_tua') {
                $activeIdentityName = $this->parent_name ?: ('Orang Tua dari ' . $this->name);
                $activeIdentityLabel = 'Orang Tua / Wali Siswa (Ananda: ' . $this->name . ')';
            } else {
                $activeIdentityName = $this->name;
                $activeIdentityLabel = 'Siswa (NIS: ' . ($this->nis ?: $this->login_key) . ')';
            }
        } elseif ($roleName === 'guru') {
            $activeIdentityLabel = 'Guru (NIP: ' . ($this->nip ?: $this->login_key) . ')';
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'login_key' => $this->login_key,
            'nip' => $this->nip,
            'nis' => $this->nis,
            'parent_name' => $this->parent_name,
            'parent_login_key' => $this->parent_login_key,
            'last_login_as' => $loginAs,
            'active_identity_name' => $activeIdentityName,
            'active_identity_label' => $activeIdentityLabel,
            'email' => $this->email,
            'recovery_email' => $this->recovery_email,
            'recovery_email_verified_at' => $this->recovery_email_verified_at?->toIso8601String(),
            'role_id' => $this->role_id,
            'role' => $this->whenLoaded('role', fn () => [
                'id' => $this->role->id,
                'name' => $this->role->name,
                'display_name' => $this->role->display_name,
            ], $this->role?->name),
            'role_name' => $roleName,
            'class_id' => $this->class_id,
            'school_class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass ? [
                'id' => $this->schoolClass->id,
                'name' => $this->schoolClass->name,
                'phase' => $this->schoolClass->phase,
                'major_id' => $this->schoolClass->major_id,
                'major' => $this->schoolClass->relationLoaded('major') && $this->schoolClass->major ? [
                    'id' => $this->schoolClass->major->id,
                    'code' => $this->schoolClass->major->code,
                    'name' => $this->schoolClass->major->name,
                ] : null,
                'homeroom_teacher' => $this->schoolClass->relationLoaded('homeroomTeacher') && $this->schoolClass->homeroomTeacher ? [
                    'id' => $this->schoolClass->homeroomTeacher->id,
                    'name' => $this->schoolClass->homeroomTeacher->name,
                    'login_key' => $this->schoolClass->homeroomTeacher->login_key,
                    'nip' => $this->schoolClass->homeroomTeacher->nip,
                ] : null,
            ] : null),
            'homeroom_classes' => $this->whenLoaded('homeroomClasses', fn () => $this->homeroomClasses->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'major_id' => $c->major_id,
                'phase' => $c->phase,
            ])->values()),
            'students' => $this->whenLoaded('students', fn () => $this->students->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'login_key' => $s->login_key,
                'nis' => $s->nis,
                'class_id' => $s->class_id,
                'class_name' => $s->schoolClass?->name,
            ])->values()),
            'parents' => $this->whenLoaded('parents', fn () => $this->parents->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'login_key' => $p->login_key,
                'recovery_email' => $p->recovery_email,
            ])->values()),
            'is_active' => (bool) $this->is_active,
            'must_change_password' => (bool) $this->must_change_password,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}