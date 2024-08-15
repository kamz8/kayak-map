<?php

namespace App\Jobs;

use App\Models\Trail;
use App\Services\RegionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AssociateTrailWithRegionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trail;

    /**
     * Create a new job instance.
     *
     * @param Trail $trail
     */
    public function __construct(Trail $trail)
    {
        $this->trail = $trail;
    }


    /**
     * Execute the job.
     *
     * @param RegionService $regionService
     * @return void
     */
    public function handle(RegionService $regionService)
    {
        $regionService->associateTrailWithRegions($this->trail);
    }
}
