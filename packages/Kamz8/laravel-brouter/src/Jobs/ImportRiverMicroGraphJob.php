<?php

namespace Kamz\LaravelBRouter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kamz\LaravelBRouter\Services\OverpassImportService;
use Kamz\LaravelBRouter\Services\RiverMicroGraphService;

class ImportRiverMicroGraphJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $riverKey,
        public array $bbox,
        public array $metadata = [],
    ) {
    }

    public function handle(OverpassImportService $importService, RiverMicroGraphService $microGraphs): void
    {
        $result = $importService->importTemporary($this->bbox, $this->metadata + [
            'name' => $this->riverKey,
            'source' => 'lazy-route',
        ]);

        $tile = $microGraphs->registerTemporaryTile($this->riverKey, $this->bbox, $result, $this->metadata);
        $microGraphs->queueIndexing($tile);
    }
}
