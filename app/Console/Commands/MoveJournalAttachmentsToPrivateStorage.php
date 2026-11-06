<?php

namespace App\Console\Commands;

use App\Models\TeachingJournal;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class MoveJournalAttachmentsToPrivateStorage extends Command
{
    protected $signature = 'scp:move-journal-attachments-private';

    protected $description = 'Move existing journal evidence files from public to private storage';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $moved = 0;
        $failed = 0;

        foreach ($public->allFiles('journal_attachments') as $path) {
            if (! $this->moveFile($path, $public, $private)) {
                $this->error('File bukti jurnal gagal dipindahkan ke penyimpanan privat.');
                $failed++;
                continue;
            }

            $moved++;
        }

        TeachingJournal::query()
            ->whereNotNull('photo_proof')
            ->orderBy('id')
            ->chunkById(100, function ($journals) use ($private, &$failed): void {
                foreach ($journals as $journal) {
                    $path = $journal->photo_proof;

                    if (! str_starts_with($path, 'journal_attachments/')) {
                        $this->error("Jurnal #{$journal->id} memiliki lokasi bukti yang tidak dikenal.");
                        $failed++;
                        continue;
                    }

                    if (! $private->exists($path)) {
                        $this->error("File bukti jurnal #{$journal->id} tidak ditemukan.");
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
