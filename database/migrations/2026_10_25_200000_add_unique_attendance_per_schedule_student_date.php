<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('attendances')
            ->select('schedule_id', 'student_id', 'date')
            ->groupBy('schedule_id', 'student_id', 'date')
            ->havingRaw('COUNT(*) > 1')
            ->limit(1)
            ->exists();

        if ($duplicates) {
            throw new RuntimeException(
                'Attendance duplicates exist for the same schedule, student, and date. Review and resolve them before migrating.'
            );
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(
                ['schedule_id', 'student_id', 'date'],
                'attendances_schedule_student_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_schedule_student_date_unique');
        });
    }
};
