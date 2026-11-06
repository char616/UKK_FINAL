<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('parent_student')) {
            Schema::create('parent_student', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['parent_id', 'student_id']);
            });
        }

        DB::table('users')
            ->whereNotNull('guardian_id')
            ->orderBy('id')
            ->chunk(100, function ($users): void {
                $timestamp = now();
                $relationships = $users->map(fn ($user) => [
                    'parent_id' => $user->guardian_id,
                    'student_id' => $user->id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])->all();

                DB::table('parent_student')->insertOrIgnore($relationships);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_student');
    }
};
