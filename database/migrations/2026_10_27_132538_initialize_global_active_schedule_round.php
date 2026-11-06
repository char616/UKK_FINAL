<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cycles', function (Blueprint $table): void {
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
        });

        DB::table('cycles')->insertOrIgnore([
            'id' => 1,
            'active_round' => 1,
            'start_date' => null,
            'end_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('cycles')
            ->where('id', 1)
            ->whereNull('start_date')
            ->whereNull('end_date')
            ->delete();

        Schema::table('cycles', function (Blueprint $table): void {
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
        });
    }
};
