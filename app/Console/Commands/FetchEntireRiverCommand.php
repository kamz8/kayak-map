<?php

namespace App\Console\Commands;

use App\Jobs\FetchEntireRiverJob;
use Illuminate\Console\Command;

class FetchEntireRiverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'river:fetch {name : Name of the river}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the entire river by name and store it in the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        // Dispatch the job
        FetchEntireRiverJob::dispatch($name);

        $this->info("Job dispatched to fetch the river: {$name}");

        return 0;
    }
}
