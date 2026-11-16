<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('receipts:publish')]
#[Description('Copy existing transaction receipts from private to public storage without deleting originals')]
class PublishReceipts extends Command
{
    public function handle(): int
    {
        $source = Storage::disk('local');
        $target = Storage::disk('public');
        $failed = false;
        $copied = 0;

        foreach (Transaction::whereNotNull('attachment')->pluck('attachment')->unique() as $path) {
            if (! str_starts_with($path, 'receipts/') || str_contains($path, '..')) {
                $this->error('Invalid receipt path: '.$path);
                $failed = true;

                continue;
            }
            if (! $source->exists($path)) {
                if (! $target->exists($path)) {
                    $this->error('Missing receipt: '.$path);
                    $failed = true;
                }

                continue;
            }
            if ($target->exists($path)) {
                if (hash_file('sha256', $source->path($path)) !== hash_file('sha256', $target->path($path))) {
                    $this->error('Different file already exists, skipped: '.$path);
                    $failed = true;
                }

                continue;
            }
            $stream = $source->readStream($path);
            if ($stream === false) {
                $this->error('Cannot read receipt: '.$path);
                $failed = true;

                continue;
            }
            try {
                if (! $target->writeStream($path, $stream)) {
                    throw new \RuntimeException('Cannot copy receipt: '.$path);
                }
            } finally {
                fclose($stream);
            }
            if (hash_file('sha256', $source->path($path)) !== hash_file('sha256', $target->path($path))) {
                $this->error('Verification failed: '.$path);
                $failed = true;

                continue;
            }
            $copied++;
        }

        $this->info($copied.' receipt(s) copied. Originals retained as backup.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
