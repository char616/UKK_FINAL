<?php

namespace App\Console\Commands;

use App\Mail\OtpResetPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestMailCommand extends Command
{
    protected $signature = 'scp:test-mail {email : Alamat tujuan, mis. akun Gmail Anda}';

    protected $description = 'Kirim email OTP percobaan untuk memastikan konfigurasi SMTP sudah benar';

    public function handle(): int
    {
        $mailer = config('mail.default');
        $username = config('mail.mailers.smtp.username');
        $this->info("Mailer: {$mailer} | Host: " . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port') . " | User: {$username}");

        if (in_array($username, ['emailkamu@gmail.com', 'your-email@gmail.com', null, ''], true)) {
            $this->warn("PERHATIAN: MAIL_USERNAME di file .env masih berisi '{$username}'.");
            $this->warn("Silakan buka file .env dan isi dengan Gmail asli Anda serta App Password 16 karakter dari Google.");
        }

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->warn("MAIL_MAILER={$mailer}: email TIDAK dikirim ke inbox, hanya dicatat. Ubah ke smtp di .env jika ingin mengirim ke Gmail asli.");
        }

        try {
            Mail::to($this->argument('email'))->send(new OtpResetPassword('123456', 'Tes SCP2'));
        } catch (Throwable $e) {
            $this->error('Gagal mengirim: ' . $e->getMessage());

            if (str_contains($e->getMessage(), '534') || str_contains($e->getMessage(), '535') || str_contains($e->getMessage(), 'Authentication')) {
                $this->line('');
                $this->warn('CARA MEMPERBAIKI KONEKSI GMAIL SMTP:');
                $this->line('1. Buka akun Google: https://myaccount.google.com/security');
                $this->line('2. Pastikan Verifikasi 2 Langkah (2-Step Verification) sudah AKTIF.');
                $this->line('3. Buka: https://myaccount.google.com/apppasswords');
                $this->line('4. Buat Sandi Aplikasi (App Password) baru (misal nama: "SCP2 System").');
                $this->line('5. Salin 16 karakter password (tanpa spasi) ke baris MAIL_PASSWORD di file .env.');
                $this->line('6. Pastikan MAIL_USERNAME dan MAIL_FROM_ADDRESS diisi alamat Gmail Anda.');
            }

            return self::FAILURE;
        }

        $this->info('Email percobaan berhasil terkirim! Cek Inbox atau folder Spam di email tujuan.');

        return self::SUCCESS;
    }
}