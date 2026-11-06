<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Cycle;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\TeachingJournal;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTeachingRemindersCommand extends Command
{
    protected $signature = 'teaching:send-reminders';

    protected $description = 'Kirimkan notifikasi pengingat pengisian jurnal mengajar dan presensi 30 menit sebelum jam pelajaran berakhir.';

    public function handle(): int
    {
        $todayDate = now()->toDateString();
        $currentTime = now()->format('H:i:s');
        $todayDay = match (now()->dayOfWeek) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Minggu',
        };

        $activeRound = Cycle::current()->active_round;
        $schedules = Schedule::with(['teacher', 'class', 'subject', 'jamPelajaran'])
            ->where('round', $activeRound)
            ->where('day', $todayDay)
            ->whereHas('teacher', fn ($q) => $q->where('is_active', true))
            ->get();

        $sentCount = 0;

        foreach ($schedules as $schedule) {
            $endTime = $schedule->jamPelajaran?->jam_selesai ?: $schedule->end_time;
            if (! $endTime) {
                continue;
            }

            // Hitung 30 menit sebelum jam selesai
            $endCarbon = Carbon::parse($endTime);
            $reminderCarbon = $endCarbon->copy()->subMinutes(30);
            $reminderTime = $reminderCarbon->format('H:i:s');

            // Cek apakah waktu saat ini sudah masuk dalam rentang 30 menit sebelum selesai hingga batas jam selesai
            if ($currentTime >= $reminderTime && $currentTime <= $endTime) {
                // Cek apakah jurnal atau presensi sudah diisi hari ini
                $hasJournal = TeachingJournal::where('schedule_id', $schedule->id)->whereDate('date', $todayDate)->exists();
                $hasAttendance = Attendance::where('schedule_id', $schedule->id)->whereDate('date', $todayDate)->exists();

                if ($hasJournal && $hasAttendance) {
                    continue;
                }

                // Cek apakah notifikasi pengingat untuk jadwal ini hari ini sudah dikirim
                $alreadyNotified = Notification::where('user_id', $schedule->teacher_id)
                    ->whereDate('created_at', $todayDate)
                    ->where('message', 'like', "%[jadwal #{$schedule->id}]%")
                    ->exists();

                if ($alreadyNotified) {
                    continue;
                }

                Notification::send(
                    $schedule->teacher_id,
                    'Pengingat KBM: Isi Jurnal & Presensi',
                    "Pembelajaran {$schedule->subject?->name} di kelas {$schedule->class?->name} akan berakhir dalam 30 menit (pukul {$endTime}). Harap segera ambil foto real-time serta lengkapi jurnal mengajar dan presensi siswa hari ini. [jadwal #{$schedule->id}]"
                );

                $sentCount++;
            }
        }

        $this->info("Berhasil mengirim {$sentCount} pengingat pengisian jurnal & presensi.");

        return Command::SUCCESS;
    }
}

