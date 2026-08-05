<?php

namespace Kamz\LaravelBRouter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kamz\LaravelBRouter\Contracts\ImportRepositoryInterface;
use Throwable;

class IndexRiverMicroGraphJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $importId)
    {
    }

    public function handle(ImportRepositoryInterface $repository): void
    {
        try {
            $repository->setStatus($this->importId, 'indexing');
            $repository->validate($this->importId);
            $repository->publish($this->importId, [
                'indexed_by' => self::class,
                'indexed_at' => now()->toIso8601String(),
            ]);
        } catch (Throwable $exception) {
            $repository->setStatus($this->importId, 'failed');

            throw $exception;
        }
    }
}
