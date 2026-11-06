<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan kolom jurnal mengajar jika belum ada pada teaching_journals
        Schema::table('teaching_journals', function (Blueprint $table) {
            if (! Schema::hasColumn('teaching_journals', 'catatan_kejadian')) {
                $table->text('catatan_kejadian')->nullable()->after('activity_summary');
            }

            if (! Schema::hasColumn('teaching_journals', 'foto_bukti_kbm')) {
                $table->string('foto_bukti_kbm')->nullable()->after('photo_proof');
            }

            if (! Schema::hasColumn('teaching_journals', 'jam_pelajaran_id')) {
                $table->foreignId('jam_pelajaran_id')
                    ->nullable()
                    ->after('date')
                    ->constrained('jam_pelajaran')
                    ->nullOnDelete();
            }
        });

        // Backfill data foto_bukti_kbm dan catatan_kejadian dari kolom legacy
        if (Schema::hasColumn('teaching_journals', 'photo_proof')) {
            DB::statement('UPDATE teaching_journals SET foto_bukti_kbm = photo_proof WHERE foto_bukti_kbm IS NULL AND photo_proof IS NOT NULL');
        }
        if (Schema::hasColumn('teaching_journals', 'notes')) {
            DB::statement('UPDATE teaching_journals SET catatan_kejadian = notes WHERE catatan_kejadian IS NULL AND notes IS NOT NULL');
        }

        // 2. Tambahkan foreign key teaching_journal_id pada attendances untuk menyatukan presensi & jurnal
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'teaching_journal_id')) {
                $table->foreignId('teaching_journal_id')
                    ->nullable()
                    ->after('schedule_id')
                    ->constrained('teaching_journals')
                    ->cascadeOnDelete();
            }
        });

        // 3. Hubungkan data presensi lama ke teaching_journals berdasarkan (schedule_id, date)
        $journals = DB::table('teaching_journals')->select('id', 'schedule_id', 'date')->get();
        foreach ($journals as $journal) {
            DB::table('attendances')
                ->where('schedule_id', $journal->schedule_id)
                ->whereDate('date', $journal->date)
                ->whereNull('teaching_journal_id')
                ->update(['teaching_journal_id' => $journal->id]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'teaching_journal_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropConstrainedForeignId('teaching_journal_id');
            });
        }

        if (Schema::hasTable('teaching_journals')) {
            Schema::table('teaching_journals', function (Blueprint $table) {
                if (Schema::hasColumn('teaching_journals', 'jam_pelajaran_id')) {
                    $table->dropConstrainedForeignId('jam_pelajaran_id');
                }
                $table->dropColumn(['catatan_kejadian', 'foto_bukti_kbm']);
            });
        }
    }
};

