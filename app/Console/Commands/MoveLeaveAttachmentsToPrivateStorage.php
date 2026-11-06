<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class MoveLeaveAttachmentsToPrivateStorage extends Command
{
    protected $signature = 'scp:move-leave-attachments-private';

    protected $description = 'Move existing leave evidence files from public to private storage';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $moved = 0;
        $failed = 0;

        foreach ($public->allFiles('leave_attachments') as $path) {
            if (! $this->moveFile($path, $public, $private)) {
                $this->error('File bukti izin gagal dipindahkan ke penyimpanan privat.');
                $failed++;
                continue;
            }

            $moved++;
        }

        LeaveRequest::query()
            ->whereNotNull('attachment')
            ->orderBy('id')
            ->chunkById(100, function ($leaveRequests) use ($private, &$failed): void {
                foreach ($leaveRequests as $leaveRequest) {
                    $path = $leaveRequest->attachment;

                    if (! str_starts_with($path, 'leave_attachments/')) {
                        $this->error("Pengajuan izin #{$leaveRequest->id} memiliki lokasi lampiran yang tidak dikenal.");
                        $failed++;
                        continue;
                    }

                    if (! $private->exists($path)) {
                        $this->error("File lampiran pengajuan izin #{$leaveRequest->id} tidak ditemukan.");
                        $failed++;
                    }
                }
            });

        $this->info("File dipindahkan: {$moved}. Gagal: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function moveFile(string $path, FilesystemAdapter $public, FilesystemAdapter $private): bool
    {
        if ($private->exists($path)) {
            return ! $public->exists($path) || $public->delete($path);
        }

        $stream = $public->readStream($path);

        if ($stream === false) {
            return false;
        }

        try {
            if (! $private->writeStream($path, $stream)) {
                return false;
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $public->delete($path);
    }
}
