<?php

namespace App\Console\Commands;

use App\Models\Trail;
use App\Models\River;
use App\Models\RiverTrack;
use App\Services\RiverTrackService;
use App\Services\GeodataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchRiverAndCreateTracksCommand extends Command
{
    protected $signature = 'river:fetch-and-create-tracks {trailId}';
    protected $description = 'Fetch the river route between start and end points of the trail and create river_tracks';

    private $riverTrackService;
    private $geodataService;

    public function __construct(RiverTrackService $riverTrackService, GeodataService $geodataService)
    {
        parent::__construct();
        $this->riverTrackService = $riverTrackService;
        $this->geodataService = $geodataService;
    }

    public function handle()
    {
        $trailId = $this->argument('trailId');
        $trail = Trail::findOrFail($trailId);
        $this->info("Processing trail: {$trail->trail_name}");

        $this->fetchAndSaveRiverRoute($trail);

        $this->info("River tracks created/updated successfully for trail ID: {$trailId}");
        return 0;
    }

    private function fetchAndSaveRiverRoute(Trail $trail)
    {
        try {
            $route = $this->riverTrackService->fetchRiverRoute(
                $trail->start_lat,
                $trail->start_lng,
                $trail->end_lat,
                $trail->end_lng
            );

            // Convert route to graph
            $graph = $this->geodataService->pointsToGraph($route);

            // Find the shortest path using Dijkstra's algorithm
            $start = 'node_0';
            $end = 'node_' . (count($route) - 1);
            $shortestPath = $this->geodataService->dijkstra($graph, $start, $end);

            if ($shortestPath === null) {
                throw new \Exception("No valid path found between start and end points.");
            }

            // Convert path back to coordinates
            $optimalRoute = array_map(function($node) use ($route) {
                $index = (int)substr($node, 5);
                return $route[$index];
            }, $shortestPath);

            // Smooth the route
            $smoothedRoute = $this->geodataService->smoothTrail($optimalRoute);

            RiverTrack::updateOrCreate(
                ['trail_id' => $trail->id],
                ['track_points' => json_encode($smoothedRoute)]
            );

            // Update or create record in rivers table
            $riverPath = 'LINESTRING(' . implode(',', array_map(function($point) {
                    return $point[1] . ' ' . $point[0];
                }, $smoothedRoute)) . ')';

            River::updateOrCreate(
                ['name' => $trail->river_name],
                ['path' => DB::raw("ST_GeomFromText('$riverPath', 4326)")]
            );

            $this->info("River route saved successfully.");

        } catch (\Exception $e) {
            $this->error("Failed to fetch or save river route: " . $e->getMessage());
            throw $e;
        }
    }
}
