<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_requests', 'jenis_waktu_izin')) {
                $table->enum('jenis_waktu_izin', ['seharian', 'jam_pelajaran'])
                    ->default('seharian')
                    ->after('type');
            }

            if (! Schema::hasColumn('leave_requests', 'jam_mulai_id')) {
                $table->foreignId('jam_mulai_id')
                    ->nullable()
                    ->after('end_date')
                    ->constrained('jam_pelajaran')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('leave_requests', 'jam_selesai_id')) {
                $table->foreignId('jam_selesai_id')
                    ->nullable()
                    ->after('jam_mulai_id')
                    ->constrained('jam_pelajaran')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('leave_requests', 'file_bukti')) {
                $table->string('file_bukti')->nullable()->after('reason');
            }

            if (! Schema::hasColumn('leave_requests', 'target_approver')) {
                $table->enum('target_approver', ['wali_kelas', 'bk'])
                    ->default('wali_kelas')
                    ->after('file_bukti');
            }

            if (! Schema::hasColumn('leave_requests', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable()->after('status');
            }
        });

        // Backfill data file_bukti dari attachment lama jika ada
        if (Schema::hasColumn('leave_requests', 'attachment')) {
            DB::statement('UPDATE leave_requests SET file_bukti = attachment WHERE file_bukti IS NULL AND attachment IS NOT NULL');
        }

        // Pastikan target_approver terisi sesuai aturan:
        // 'wali_kelas' jika seharian, 'bk' jika jam_pelajaran
        DB::table('leave_requests')
            ->where('jenis_waktu_izin', 'jam_pelajaran')
            ->update(['target_approver' => 'bk']);

        DB::table('leave_requests')
            ->where('jenis_waktu_izin', 'seharian')
            ->update(['target_approver' => 'wali_kelas']);
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'jam_selesai_id')) {
                $table->dropConstrainedForeignId('jam_selesai_id');
            }
            if (Schema::hasColumn('leave_requests', 'jam_mulai_id')) {
                $table->dropConstrainedForeignId('jam_mulai_id');
            }

            $table->dropColumn([
                'jenis_waktu_izin',
                'file_bukti',
                'target_approver',
                'alasan_penolakan',
            ]);
        });
    }
};

