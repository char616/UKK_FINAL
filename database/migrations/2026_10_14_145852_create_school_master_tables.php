<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Jurusan
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        // Tabel Kelas
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('major_id')->constrained('majors')->onDelete('cascade');
            $table->enum('phase', ['ganjil', 'genap']); // Penentu putaran kelas
            $table->timestamps();
        });

        // Tabel Ruangan
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('rules')->nullable(); // S&K penggunaan ruangan
            $table->timestamps();
        });

        // Tabel Mata Pelajaran
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('majors');
    }
};