<?php

namespace App\Console\Commands;

use App\Models\Trail;
use App\Models\River;
use App\Services\RiverTrackService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchRiverAndCreateTracksCommand extends Command
{
    protected $signature = 'river:fetch-and-create-tracks {trailId?}';
    protected $description = 'Fetch the river route from Overpass API, save it, and create river tracks for a trail or all trails';

    private $riverTrackService;

    public function __construct(RiverTrackService $riverTrackService)
    {
        parent::__construct();
        $this->riverTrackService = $riverTrackService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $trailId = $this->argument('trailId');

        if ($trailId) {
            $this->processTrail($trailId);
        } else {
            $this->processAllTrails();
        }

        return 0;
    }

    /**
     * Process a single trail.
     *
     * @param int $trailId The ID of the trail to process
     */
    private function processTrail(int $trailId)
    {
        $trail = Trail::find($trailId);

        if (!$trail) {
            $this->error("Trail with ID {$trailId} not found.");
            return;
        }

        $this->info("Processing trail: {$trail->trail_name}");

        // Fetch river data from Overpass API and save to database
        $river = $this->fetchAndSaveRiverData($trail);

        if (!$river) {
            $this->error("Failed to fetch and save river data for trail ID: {$trailId}");
            return;
        }

        // Generate river track
        $riverTrack = $this->riverTrackService->generateRiverTrack($trail, $river);

        if ($riverTrack) {
            $this->info("River track created/updated successfully for trail ID: {$trailId}");
        } else {
            $this->error("Failed to create/update river track for trail ID: {$trailId}");
        }
    }

    /**
     * Process all trails.
     */
    private function processAllTrails()
    {
        $trails = Trail::all();

        $this->info("Processing all trails...");
        $bar = $this->output->createProgressBar(count($trails));

        foreach ($trails as $trail) {
            // Fetch river data from Overpass API and save to database
            $river = $this->fetchAndSaveRiverData($trail);

            if ($river) {
                // Generate river track
                $riverTrack = $this->riverTrackService->generateRiverTrack($trail, $river);

                if (!$riverTrack) {
                    Log::warning("Failed to create/update river track for trail ID: {$trail->id}");
                }
            } else {
                Log::error("Failed to fetch and save river data for trail ID: {$trail->id}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nAll trails processed.");
    }

    /**
     * Fetch river data from Overpass API and save to database.
     *
     * @param Trail $trail
     * @return River|null
     */
    private function fetchAndSaveRiverData(Trail $trail): ?River
    {
        $this->info("Fetching river data for: {$trail->river_name}");

        $riverData = $this->riverTrackService->fetchRiverData(
            $trail->river_name,
            $trail->start_lat,
            $trail->start_lng,
            $trail->end_lat,
            $trail->end_lng
        );

        if (empty($riverData)) {
            $this->error("No river data found for: {$trail->river_name}");
            return null;
        }

        $river = River::updateOrCreate(
            ['name' => $trail->river_name],
            ['path' => json_encode($riverData)]
        );

        $this->info("River data saved successfully for: {$trail->river_name}");

        return $river;
    }
}
