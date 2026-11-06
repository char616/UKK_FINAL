<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jam_pelajaran')) {
            Schema::create('jam_pelajaran', function (Blueprint $table) {
                $table->id();
                $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);
                $table->string('nama_sesi'); // Upacara, Literasi, Jam Ke-1 s/d Jam Ke-10, Istirahat I, Istirahat II
                $table->time('jam_mulai');
                $table->time('jam_selesai');
                $table->boolean('is_istirahat')->default(false); // true: Istirahat/Upacara/Literasi (non-KBM), false: KBM
                $table->unsignedSmallInteger('urutan')->default(1);
                $table->timestamps();

                $table->index(['hari', 'urutan']);
                $table->index(['hari', 'jam_mulai', 'jam_selesai']);
            });
        }

        if (Schema::hasTable('schedules') && ! Schema::hasColumn('schedules', 'jam_pelajaran_id')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->foreignId('jam_pelajaran_id')
                    ->nullable()
                    ->after('day')
                    ->constrained('jam_pelajaran')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schedules') && Schema::hasColumn('schedules', 'jam_pelajaran_id')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropConstrainedForeignId('jam_pelajaran_id');
            });
        }

        Schema::dropIfExists('jam_pelajaran');
    }
};

