<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RiverService;

class FetchRiversForCity extends Command
{
    protected $signature = 'rivers:fetch {city}';
    protected $description = 'Fetch rivers for a given city';
    protected $riverService;

    public function __construct(RiverService $riverService)
    {
        parent::__construct();
        $this->riverService = $riverService;
    }

    public function handle()
    {
//        $city = $this->argument('city');
        $city = "Wrocław";
        $this->info("Fetching rivers for city: $city");

        try {
            $this->riverService->fetchAndStoreRiversInTown($city);
            $this->info('Rivers fetched and stored successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }

        return 0;
    }
}
