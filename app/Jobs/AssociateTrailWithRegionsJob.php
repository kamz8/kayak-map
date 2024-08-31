<?php

namespace App\Jobs;

use App\Models\Trail;
use App\Models\Region;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AssociateTrailWithRegionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trail;
    protected $regionData;

    public function __construct(Trail $trail, array $regionData)
    {
        $this->trail = $trail;
        $this->regionData = $regionData;
    }

    public function handle()
    {
        $region = Region::firstOrCreate(
            ['slug' => $this->createSlug()],
            [
                'name' => $this->regionData['city'] ?? $this->regionData['state'] ?? $this->regionData['country'],
                'type' => $this->determineRegionType(),
                'center_point' => new \MatanYadaev\EloquentSpatial\Objects\Point($this->regionData['lat'], $this->regionData['lng']),
            ]
        );

        $this->trail->regions()->syncWithoutDetaching([$region->id]);

        // Jeśli mamy dane o państwie i stanie, tworzymy/aktualizujemy również te regiony
        if (isset($this->regionData['country'])) {
            $countryRegion = $this->createOrUpdateRegion($this->regionData['country'], 'country');
            $this->trail->regions()->syncWithoutDetaching([$countryRegion->id]);

            if (isset($this->regionData['state'])) {
                $stateRegion = $this->createOrUpdateRegion($this->regionData['state'], 'state', $countryRegion->id);
                $this->trail->regions()->syncWithoutDetaching([$stateRegion->id]);
            }
        }
    }

    protected function createSlug()
    {
        $parts = array_filter([
            $this->regionData['country'] ?? null,
            $this->regionData['state'] ?? null,
            $this->regionData['city'] ?? null
        ]);
        return strtolower(implode('/', array_map('str_slug', $parts)));
    }

    protected function determineRegionType()
    {
        if (isset($this->regionData['city'])) return 'city';
        if (isset($this->regionData['state'])) return 'state';
        return 'country';
    }

    protected function createOrUpdateRegion($name, $type, $parentId = null)
    {
        return Region::firstOrCreate(
            ['name' => $name, 'type' => $type],
            [
                'slug' => str_slug($name),
                'parent_id' => $parentId,
                'center_point' => $type === 'country' ? null : new \MatanYadaev\EloquentSpatial\Objects\Point($this->regionData['lat'], $this->regionData['lng']),
            ]
        );
    }
}
