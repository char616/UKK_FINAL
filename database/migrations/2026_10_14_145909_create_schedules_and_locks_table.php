<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Pengaturan Siklus Putaran Global
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            $table->integer('active_round')->default(1); // Putaran 1 atau 2
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        // Tabel Lock Schedule oleh WAKA
        Schema::create('schedule_locks', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Tabel Jadwal Mengajar Guru
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('round'); // Putaran 1 / 2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('schedule_locks');
        Schema::dropIfExists('cycles');
    }
};