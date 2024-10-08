<?php

namespace App\Console\Commands;

use App\Jobs\FetchRiverTrackJob;
use Illuminate\Console\Command;

class FetchRiverTrackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'river:fetch-track {trail_id : ID of the trail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch river track for a given trail ID and store it in the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $trailId = (int) $this->argument('trail_id');

        // Dispatch the job
        FetchRiverTrackJob::dispatch($trailId);

        $this->info("Job dispatched to fetch river track for trail ID: {$trailId}");

        return 0;
    }
}
