<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Models\Subject;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSubjectsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'school:sync-subjects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi dan perbarui master data mata pelajaran resmi (69 mapel)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi 69 mata pelajaran resmi...');

        $subjects = DatabaseSeeder::getOfficialSubjects();
        $codes = array_column($subjects, 'code');

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $insertedCount = 0;
        $updatedCount = 0;
        $firstSubjectId = null;

        foreach ($subjects as $item) {
            $existing = Subject::where('code', $item['code'])->first();
            if ($existing) {
                $existing->update(['name' => $item['name']]);
                $updatedCount++;
                if (! $firstSubjectId) {
                    $firstSubjectId = $existing->id;
                }
            } else {
                $created = Subject::create($item);
                $insertedCount++;
                if (! $firstSubjectId) {
                    $firstSubjectId = $created->id;
                }
            }
        }

        // Reassign schedules using outdated subjects
        $validIds = Subject::whereIn('code', $codes)->pluck('id');
        $affectedSchedules = Schedule::whereNotIn('subject_id', $validIds)->update([
            'subject_id' => $firstSubjectId,
        ]);

        if ($affectedSchedules > 0) {
            $this->warn("Terdapat {$affectedSchedules} jadwal yang disinkronkan ke mata pelajaran default.");
        }

        // Remove outdated subjects
        $deletedCount = Subject::whereNotIn('code', $codes)->delete();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $total = Subject::count();
        $this->info("Sinkronisasi selesai! Total mata pelajaran saat ini: {$total} mapel.");
        $this->line("- Baru ditambahkan: {$insertedCount}");
        $this->line("- Diperbarui: {$updatedCount}");
        $this->line("- Dihapus (lama/dummy): {$deletedCount}");

        return Command::SUCCESS;
    }
}

