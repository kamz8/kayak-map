<?php

namespace Kamz\LaravelBRouter\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Services\BrouterGraphRepository;
use Kamz\LaravelBRouter\Services\RoutingEngine;
use Throwable;

class BenchmarkBRouterCommand extends Command
{
    protected $signature = 'brouter:benchmark {start} {end} {--river=} {--snap=500}';

    protected $description = 'Benchmark BRouter graph loading, snapping, and routing.';

    public function __construct(
        private readonly RoutingEngine $router,
        private readonly BrouterGraphRepository $graphs,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $startedAt = hrtime(true);
            $graphVersion = $this->graphs->activeGraphVersion() ?? 'runtime';
            $start = $this->point((string) $this->argument('start'));
            $end = $this->point((string) $this->argument('end'));

            $route = $this->router->findRoute(new RouteRequestData(
                riverName: (string) ($this->option('river') ?: 'benchmark'),
                start: $start,
                end: $end,
                snapToleranceMeters: (float) $this->option('snap'),
            ));

            $elapsedMs = (hrtime(true) - $startedAt) / 1_000_000;
            $this->line(json_encode([
                'graph_version' => $graphVersion,
                'route_time_ms' => round($elapsedMs, 3),
                'distance_m' => $route->distanceMeters,
                'points' => count($route->path),
                'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'cache' => $route->cache,
            ], JSON_THROW_ON_ERROR));

            $this->explainNearestEdge($start, (float) $this->option('snap'));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Benchmark failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @return array{lat: float, lng: float}
     */
    private function point(string $value): array
    {
        $parts = array_map('trim', explode(',', $value));

        if (count($parts) !== 2 || ! is_numeric($parts[0]) || ! is_numeric($parts[1])) {
            throw new \InvalidArgumentException('Point must be lat,lng.');
        }

        return ['lat' => (float) $parts[0], 'lng' => (float) $parts[1]];
    }

    private function explainNearestEdge(array $point, float $snapToleranceMeters): void
    {
        $queryPoint = 'ST_SetSRID(ST_MakePoint(?, ?), 4326)';
        $plan = DB::connection('brouter')->select(<<<SQL
EXPLAIN ANALYZE
SELECT e.id
FROM waterway_edges e
JOIN imports i ON i.id = e.import_id
WHERE i.status = 'published'
  AND i.is_active = true
  AND ST_DWithin(e.geometry::geography, {$queryPoint}::geography, ?)
ORDER BY e.geometry <-> {$queryPoint}
LIMIT 1
SQL, [$point['lng'], $point['lat'], $snapToleranceMeters, $point['lng'], $point['lat']]);

        $this->line(json_encode(['nearest_edge_explain' => array_map(fn (object $row): string => (string) $row->{'QUERY PLAN'}, $plan)], JSON_THROW_ON_ERROR));
    }
}
