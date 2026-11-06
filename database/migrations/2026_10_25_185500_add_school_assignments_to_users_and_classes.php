<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'class_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('class_id')->nullable()->after('role_id')->constrained('classes')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('users', 'guardian_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('guardian_id')->nullable()->after('class_id')->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('classes', 'homeroom_teacher_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->foreignId('homeroom_teacher_id')->nullable()->after('major_id')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('homeroom_teacher_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('guardian_id');
            $table->dropConstrainedForeignId('class_id');
        });
    }
};
