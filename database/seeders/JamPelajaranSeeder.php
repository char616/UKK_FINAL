<?php

namespace Database\Seeders;

use App\Models\JamPelajaran;
use Illuminate\Database\Seeder;

class JamPelajaranSeeder extends Seeder
{
    public function run(): void
    {
        JamPelajaran::query()->delete();

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        foreach ($days as $day) {
            $sessions = $this->getSessionsForDay($day);

            foreach ($sessions as $session) {
                JamPelajaran::query()->updateOrCreate(
                    [
                        'hari' => $day,
                        'nama_sesi' => $session['nama_sesi'],
                    ],
                    [
                        'jam_mulai' => $session['jam_mulai'],
                        'jam_selesai' => $session['jam_selesai'],
                        'is_istirahat' => $session['is_istirahat'],
                        'urutan' => $session['urutan'],
                    ]
                );
            }
        }
    }

    private function getSessionsForDay(string $day): array
    {
        if ($day === 'Senin') {
            return [
                ['nama_sesi' => 'Upacara', 'jam_mulai' => '07:00:00', 'jam_selesai' => '08:00:00', 'is_istirahat' => true, 'urutan' => 1],
                ['nama_sesi' => 'Jam 1', 'jam_mulai' => '08:00:00', 'jam_selesai' => '08:35:00', 'is_istirahat' => false, 'urutan' => 2],
                ['nama_sesi' => 'Jam 2', 'jam_mulai' => '08:35:00', 'jam_selesai' => '09:10:00', 'is_istirahat' => false, 'urutan' => 3],
                ['nama_sesi' => 'Jam 3', 'jam_mulai' => '09:10:00', 'jam_selesai' => '09:45:00', 'is_istirahat' => false, 'urutan' => 4],
                ['nama_sesi' => 'Istirahat I', 'jam_mulai' => '09:45:00', 'jam_selesai' => '10:00:00', 'is_istirahat' => true, 'urutan' => 5],
                ['nama_sesi' => 'Jam 4', 'jam_mulai' => '10:00:00', 'jam_selesai' => '10:35:00', 'is_istirahat' => false, 'urutan' => 6],
                ['nama_sesi' => 'Jam 5', 'jam_mulai' => '10:35:00', 'jam_selesai' => '11:10:00', 'is_istirahat' => false, 'urutan' => 7],
                ['nama_sesi' => 'Jam 6', 'jam_mulai' => '11:10:00', 'jam_selesai' => '11:45:00', 'is_istirahat' => false, 'urutan' => 8],
                ['nama_sesi' => 'Istirahat II', 'jam_mulai' => '11:45:00', 'jam_selesai' => '12:45:00', 'is_istirahat' => true, 'urutan' => 9],
                ['nama_sesi' => 'Jam 7', 'jam_mulai' => '12:45:00', 'jam_selesai' => '13:25:00', 'is_istirahat' => false, 'urutan' => 10],
                ['nama_sesi' => 'Jam 8', 'jam_mulai' => '13:25:00', 'jam_selesai' => '14:00:00', 'is_istirahat' => false, 'urutan' => 11],
                ['nama_sesi' => 'Jam 9', 'jam_mulai' => '14:00:00', 'jam_selesai' => '14:35:00', 'is_istirahat' => false, 'urutan' => 12],
                ['nama_sesi' => 'Jam 10', 'jam_mulai' => '14:35:00', 'jam_selesai' => '15:10:00', 'is_istirahat' => false, 'urutan' => 13],
            ];
        }

        if ($day === 'Selasa') {
            return [
                ['nama_sesi' => 'Literasi', 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:30:00', 'is_istirahat' => true, 'urutan' => 1],
                ['nama_sesi' => 'Jam 1', 'jam_mulai' => '07:30:00', 'jam_selesai' => '08:10:00', 'is_istirahat' => false, 'urutan' => 2],
                ['nama_sesi' => 'Jam 2', 'jam_mulai' => '08:10:00', 'jam_selesai' => '08:50:00', 'is_istirahat' => false, 'urutan' => 3],
                ['nama_sesi' => 'Jam 3', 'jam_mulai' => '08:50:00', 'jam_selesai' => '09:30:00', 'is_istirahat' => false, 'urutan' => 4],
                ['nama_sesi' => 'Istirahat I', 'jam_mulai' => '09:30:00', 'jam_selesai' => '09:45:00', 'is_istirahat' => true, 'urutan' => 5],
                ['nama_sesi' => 'Jam 4', 'jam_mulai' => '09:45:00', 'jam_selesai' => '10:25:00', 'is_istirahat' => false, 'urutan' => 6],
                ['nama_sesi' => 'Jam 5', 'jam_mulai' => '10:25:00', 'jam_selesai' => '11:05:00', 'is_istirahat' => false, 'urutan' => 7],
                ['nama_sesi' => 'Jam 6', 'jam_mulai' => '11:05:00', 'jam_selesai' => '11:45:00', 'is_istirahat' => false, 'urutan' => 8],
                ['nama_sesi' => 'Istirahat II', 'jam_mulai' => '11:45:00', 'jam_selesai' => '12:45:00', 'is_istirahat' => true, 'urutan' => 9],
                ['nama_sesi' => 'Jam 7', 'jam_mulai' => '12:45:00', 'jam_selesai' => '13:25:00', 'is_istirahat' => false, 'urutan' => 10],
                ['nama_sesi' => 'Jam 8', 'jam_mulai' => '13:25:00', 'jam_selesai' => '14:00:00', 'is_istirahat' => false, 'urutan' => 11],
                ['nama_sesi' => 'Jam 9', 'jam_mulai' => '14:00:00', 'jam_selesai' => '14:35:00', 'is_istirahat' => false, 'urutan' => 12],
                ['nama_sesi' => 'Jam 10', 'jam_mulai' => '14:35:00', 'jam_selesai' => '15:10:00', 'is_istirahat' => false, 'urutan' => 13],
            ];
        }

        if ($day === 'Rabu' || $day === 'Kamis') {
            return [
                ['nama_sesi' => 'Jam 1', 'jam_mulai' => '07:15:00', 'jam_selesai' => '07:55:00', 'is_istirahat' => false, 'urutan' => 1],
                ['nama_sesi' => 'Jam 2', 'jam_mulai' => '07:55:00', 'jam_selesai' => '08:35:00', 'is_istirahat' => false, 'urutan' => 2],
                ['nama_sesi' => 'Jam 3', 'jam_mulai' => '08:35:00', 'jam_selesai' => '09:15:00', 'is_istirahat' => false, 'urutan' => 3],
                ['nama_sesi' => 'Istirahat I', 'jam_mulai' => '09:15:00', 'jam_selesai' => '09:30:00', 'is_istirahat' => true, 'urutan' => 4],
                ['nama_sesi' => 'Jam 4', 'jam_mulai' => '09:30:00', 'jam_selesai' => '10:10:00', 'is_istirahat' => false, 'urutan' => 5],
                ['nama_sesi' => 'Jam 5', 'jam_mulai' => '10:10:00', 'jam_selesai' => '10:50:00', 'is_istirahat' => false, 'urutan' => 6],
                ['nama_sesi' => 'Jam 6', 'jam_mulai' => '10:50:00', 'jam_selesai' => '11:30:00', 'is_istirahat' => false, 'urutan' => 7],
                ['nama_sesi' => 'Istirahat II', 'jam_mulai' => '11:30:00', 'jam_selesai' => '12:30:00', 'is_istirahat' => true, 'urutan' => 8],
                ['nama_sesi' => 'Jam 7', 'jam_mulai' => '12:30:00', 'jam_selesai' => '13:10:00', 'is_istirahat' => false, 'urutan' => 9],
                ['nama_sesi' => 'Jam 8', 'jam_mulai' => '13:10:00', 'jam_selesai' => '13:50:00', 'is_istirahat' => false, 'urutan' => 10],
                ['nama_sesi' => 'Jam 9', 'jam_mulai' => '13:50:00', 'jam_selesai' => '14:30:00', 'is_istirahat' => false, 'urutan' => 11],
                ['nama_sesi' => 'Jam 10', 'jam_mulai' => '14:30:00', 'jam_selesai' => '15:10:00', 'is_istirahat' => false, 'urutan' => 12],
            ];
        }

        // Jumat
        return [
            ['nama_sesi' => 'Jam 1', 'jam_mulai' => '07:15:00', 'jam_selesai' => '07:55:00', 'is_istirahat' => false, 'urutan' => 1],
            ['nama_sesi' => 'Jam 2', 'jam_mulai' => '07:55:00', 'jam_selesai' => '08:35:00', 'is_istirahat' => false, 'urutan' => 2],
            ['nama_sesi' => 'Jam 3', 'jam_mulai' => '08:35:00', 'jam_selesai' => '09:15:00', 'is_istirahat' => false, 'urutan' => 3],
            ['nama_sesi' => 'Istirahat I', 'jam_mulai' => '09:15:00', 'jam_selesai' => '09:30:00', 'is_istirahat' => true, 'urutan' => 4],
            ['nama_sesi' => 'Jam 4', 'jam_mulai' => '09:30:00', 'jam_selesai' => '10:10:00', 'is_istirahat' => false, 'urutan' => 5],
            ['nama_sesi' => 'Jam 5', 'jam_mulai' => '10:10:00', 'jam_selesai' => '10:50:00', 'is_istirahat' => false, 'urutan' => 6],
            ['nama_sesi' => 'Jam 6', 'jam_mulai' => '10:50:00', 'jam_selesai' => '11:25:00', 'is_istirahat' => false, 'urutan' => 7],
            ['nama_sesi' => 'Istirahat II', 'jam_mulai' => '11:25:00', 'jam_selesai' => '12:40:00', 'is_istirahat' => true, 'urutan' => 8],
            ['nama_sesi' => 'Jam 7', 'jam_mulai' => '12:40:00', 'jam_selesai' => '13:15:00', 'is_istirahat' => false, 'urutan' => 9],
            ['nama_sesi' => 'Jam 8', 'jam_mulai' => '13:15:00', 'jam_selesai' => '13:50:00', 'is_istirahat' => false, 'urutan' => 10],
        ];
    }
}