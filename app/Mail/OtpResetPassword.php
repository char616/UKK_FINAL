<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $userName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode OTP Reset Password SCP',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.otp-reset-password',
            with: [
                'otp' => $this->otp,
                'userName' => $this->userName,
            ],
        );
    }
}
