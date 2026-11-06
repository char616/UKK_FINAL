<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_emails', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'email']);
            $table->index('email');
        });

        // Pindahkan email pemulihan lama ke tabel email tertaut agar tidak hilang.
        DB::table('users')
            ->whereNotNull('recovery_email')
            ->where('recovery_email', '!=', '')
            ->orderBy('id')
            ->each(function ($user): void {
                DB::table('user_emails')->insertOrIgnore([
                    'user_id' => $user->id,
                    'email' => mb_strtolower(trim($user->recovery_email)),
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        // Fitur verifikasi OTP email dihapus, tabelnya tidak dipakai lagi.
        Schema::dropIfExists('recovery_email_verifications');
    }

    public function down(): void
    {
        Schema::dropIfExists('user_emails');

        Schema::create('recovery_email_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('otp_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'expires_at']);
        });
    }
};