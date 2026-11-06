<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $usersWithoutRole = DB::table('users')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->whereNull('roles.id')
            ->count();

        if ($usersWithoutRole > 0) {
            throw new RuntimeException(
                "Cannot add users.role_id foreign key: {$usersWithoutRole} user(s) reference a missing role. Assign valid roles before retrying."
            );
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['role_id']);
        });
    }
};
