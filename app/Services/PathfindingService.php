<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PathfindingService
{
    private GeodataService $geodataService;

    public function __construct(GeodataService $geodataService)
    {
        $this->geodataService = $geodataService;
    }

    public function findOptimalPath(array $rivers, array $waypoints): array
    {
        Log::channel('river_tracking')->info("Finding optimal path with " . count($rivers) . " rivers and " . count($waypoints) . " waypoints");

        $graph = $this->buildGraph($rivers);
        $path = $this->dijkstra($graph, $waypoints[0], $waypoints[count($waypoints) - 1]);

        if (empty($path)) {
            Log::channel('river_tracking')->error("Failed to find a path between start and end points");
            throw new \Exception("No valid path found between start and end points");
        }

        $smoothedPath = $this->smoothPath($path);
        Log::channel('river_tracking')->info("Found optimal path with " . count($smoothedPath) . " points");

        return $smoothedPath;
    }

    private function buildGraph(array $rivers): array
    {
        $graph = [];
        foreach ($rivers as $river) {
            for ($i = 0; $i < count($river['geometry']) - 1; $i++) {
                $start = $this->pointToString($river['geometry'][$i]);
                $end = $this->pointToString($river['geometry'][$i + 1]);
                $distance = $this->geodataService->calculateDistance(
                    $river['geometry'][$i][0], $river['geometry'][$i][1],
                    $river['geometry'][$i + 1][0], $river['geometry'][$i + 1][1]
                );
                $graph[$start][$end] = $distance;
                $graph[$end][$start] = $distance;
            }
        }
        return $graph;
    }

    private function dijkstra(array $graph, array $start, array $end): array
    {
        $distances = [];
        $previous = [];
        $queue = new \SplPriorityQueue();

        $startStr = $this->pointToString($start);
        $endStr = $this->pointToString($end);

        foreach ($graph as $vertex => $adj) {
            $distances[$vertex] = INF;
            $previous[$vertex] = null;
        }

        $distances[$startStr] = 0;
        $queue->insert($startStr, 0);

        while (!$queue->isEmpty()) {
            $current = $queue->extract();

            if ($current === $endStr) {
                break;
            }

            if (!isset($graph[$current])) {
                continue;
            }

            foreach ($graph[$current] as $neighbor => $cost) {
                $alt = $distances[$current] + $cost;
                if ($alt < $distances[$neighbor]) {
                    $distances[$neighbor] = $alt;
                    $previous[$neighbor] = $current;
                    $queue->insert($neighbor, -$alt);
                }
            }
        }

        $path = [];
        $current = $endStr;
        while ($current !== null) {
            array_unshift($path, $this->stringToPoint($current));
            $current = $previous[$current];
        }

        return $path;
    }

    private function smoothPath(array $path): array
    {
        return $this->geodataService->smoothTrail($path, 0.0001);
    }

    private function pointToString(array $point): string
    {
        return implode(',', $point);
    }

    private function stringToPoint(string $str): array
    {
        return array_map('floatval', explode(',', $str));
    }
}
