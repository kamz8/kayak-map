<?php

namespace Kamz\LaravelBRouter\Console;

use Illuminate\Console\Command;
use Kamz\LaravelBRouter\Services\OverpassImportService;
use Throwable;

class PrecacheWaterwaysCommand extends Command
{
    protected $signature = 'brouter:precache {bbox} {--name=}';
    protected $description = 'Precache waterways data for specific area';

    public function __construct(private readonly OverpassImportService $importService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $bbox = $this->parseBBox((string) $this->argument('bbox'));
            $name = $this->option('name');

            $this->info('Importing waterways for bbox: '.implode(', ', $bbox));
            $result = $this->importService->import($bbox, array_filter(['name' => $name]));
            $this->info(sprintf('Import %d published with %d nodes and %d edges.', $result['import_id'], $result['node_count'], $result['edge_count']));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Waterway import failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    protected function parseBBox(string $bbox): array
    {
        $values = array_map('trim', explode(',', $bbox));

        if (count($values) !== 4 || count(array_filter($values, 'is_numeric')) !== 4) {
            throw new \InvalidArgumentException('BBox must be south,west,north,east.');
        }

        return [
            'south' => (float) $values[0],
            'west' => (float) $values[1],
            'north' => (float) $values[2],
            'east' => (float) $values[3],
        ];
    }
}
