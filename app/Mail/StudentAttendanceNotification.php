<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentAttendanceNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $studentName,
        public string $className,
        public string $subjectName,
        public string $date,
        public string $status,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notifikasi Kehadiran Siswa (' . $this->status . ') - ' . $this->studentName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.student-attendance-notification',
            with: [
                'recipientName' => $this->recipientName,
                'studentName' => $this->studentName,
                'className' => $this->className,
                'subjectName' => $this->subjectName,
                'date' => $this->date,
                'status' => $this->status,
            ],
        );
    }
}
