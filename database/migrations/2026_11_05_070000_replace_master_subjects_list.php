<?php

use App\Models\Schedule;
use App\Models\Subject;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $subjects = DatabaseSeeder::getOfficialSubjects();
        $codes = array_column($subjects, 'code');

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // Upsert all 69 subjects
        $insertedMap = [];
        foreach ($subjects as $subject) {
            $record = Subject::query()->updateOrCreate(
                ['code' => $subject['code']],
                ['name' => $subject['name']]
            );
            $insertedMap[$subject['code']] = $record->id;
        }

        $defaultSubjectId = reset($insertedMap);

        // Reassign any schedules using old deleted subjects to the first new subject
        $validSubjectIds = Subject::query()->whereIn('code', $codes)->pluck('id');
        Schedule::query()->whereNotIn('subject_id', $validSubjectIds)->update([
            'subject_id' => $defaultSubjectId,
        ]);

        // Delete any old subjects not in the new 69 list
        Subject::query()->whereNotIn('code', $codes)->delete();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback
    }
};
