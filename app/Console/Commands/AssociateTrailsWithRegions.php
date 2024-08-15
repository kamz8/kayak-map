<?php

namespace App\Console\Commands;

use App\Jobs\AssociateTrailWithRegionJob;
use App\Models\Trail;
use App\Services\RegionService;
use Illuminate\Console\Command;

class AssociateTrailsWithRegions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trails:associate-with-regions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Associate trails with regions';


    protected $regionService;

    public function __construct(RegionService $regionService)
    {
        parent::__construct();
        $this->regionService = $regionService;
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $trails = Trail::all();

        $this->output->progressStart($trails->count());

        foreach ($trails as $trail) {
            AssociateTrailWithRegionJob::dispatch($trail);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info('All trails have been dispatched for association with regions.');
        return 0;
    }
}
