<?php

namespace App\Console\Commands;

use App\Jobs\FetchRiverTrackJob;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RectorPrefix202410\Illuminate\Contracts\Queue\Job;

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

        try {
            // Dispatch the job immediately in the current process
            FetchRiverTrackJob::dispatchSync($trailId);

            $this->info("Successfully fetched river track for trail ID: {$trailId}");

            return Command::SUCCESS;
        } catch (Exception $e) {
            // Log and display error message
            $this->error("Error fetching river track for trail ID: {$trailId}. Error: " . $e->getMessage());

            return Command::FAILURE;
        }

        return 0;
    }
}
