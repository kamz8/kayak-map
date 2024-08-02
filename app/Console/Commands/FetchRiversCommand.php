<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RiverService;

class FetchRiversCommand extends Command
{
    protected $signature = 'fetch:rivers {bbox}';
    protected $description = 'Fetch and store river data from OpenStreetMap';

    private $riverService;

    public function __construct(RiverService $riverService)
    {
        parent::__construct();
        $this->riverService = $riverService;
    }

    public function handle()
    {
        $bbox = $this->argument('bbox');
        $this->riverService->fetchAndStoreRiversData($bbox);
        $this->info('River data has been fetched and stored successfully.');
    }
}
