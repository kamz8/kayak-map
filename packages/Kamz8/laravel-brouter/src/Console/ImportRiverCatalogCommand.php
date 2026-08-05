<?php

namespace Kamz\LaravelBRouter\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Kamz\LaravelBRouter\Services\OverpassImportService;
use Kamz\LaravelBRouter\Services\RiverMicroGraphService;
use Throwable;

class ImportRiverCatalogCommand extends Command
{
    protected $signature = 'brouter:import-catalog {--river=* : Import only selected river names} {--bbox= : Override the bbox as south,west,north,east}';

    protected $description = 'Import PostGIS micro-graphs for catalogued rivers';

    public function __construct(
        private readonly OverpassImportService $importService,
        private readonly RiverMicroGraphService $microGraphs,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $selected = array_map(fn (string $name): string => $this->microGraphs->riverKey($name), (array) $this->option('river'));
        $presets = array_filter($this->microGraphs->catalog(), function (array $preset) use ($selected): bool {
            return $selected === [] || in_array($preset['key'], $selected, true);
        });
        $imported = 0;

        foreach ($presets as $preset) {
            $bbox = $this->option('bbox') !== null
                ? $this->parseBbox((string) $this->option('bbox'))
                : $this->trailBbox($preset['name']);

            if ($bbox === null) {
                $this->warn("Skipping {$preset['name']}: no trail bbox found. Use --bbox for an explicit corridor.");
                continue;
            }

            try {
                $result = $this->importService->import($bbox, [
                    'name' => $preset['name'],
                    'river_key' => $preset['key'],
                    'country_code' => $preset['country_code'],
                    'source' => 'river-catalog',
                ]);
                $this->info(sprintf('%s: import %d published.', $preset['name'], $result['import_id']));
                $imported++;
            } catch (Throwable $exception) {
                $this->error(sprintf('%s: %s', $preset['name'], $exception->getMessage()));

                return self::FAILURE;
            }
        }

        $this->info("Imported {$imported} river micro-graph(s).");

        return self::SUCCESS;
    }

    private function trailBbox(string $riverName): ?array
    {
        try {
            $row = DB::table('trails')
                ->where('river_name', $riverName)
                ->selectRaw('LEAST(MIN(start_lat), MIN(end_lat)) AS south')
                ->selectRaw('LEAST(MIN(start_lng), MIN(end_lng)) AS west')
                ->selectRaw('GREATEST(MAX(start_lat), MAX(end_lat)) AS north')
                ->selectRaw('GREATEST(MAX(start_lng), MAX(end_lng)) AS east')
                ->first();
        } catch (Throwable) {
            return null;
        }

        if ($row === null || $row->south === null || $row->west === null || $row->north === null || $row->east === null) {
            return null;
        }

        return [
            'south' => (float) $row->south,
            'west' => (float) $row->west,
            'north' => (float) $row->north,
            'east' => (float) $row->east,
        ];
    }

    private function parseBbox(string $value): array
    {
        $values = array_map('trim', explode(',', $value));

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
