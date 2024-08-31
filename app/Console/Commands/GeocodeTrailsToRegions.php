<?php

namespace App\Console\Commands;

use App\Models\Trail;
use App\Models\Region;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Query\ReverseQuery;
use Geocoder\StatefulGeocoder;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GeocodeTrailsToRegions extends Command
{
    protected $signature = 'trails:geocode-to-regions
                            {trail_id? : The ID of the trail to process. If not provided, all unprocessed trails will be geocoded}
                            {--api=private : Choose between "private" or "public" Nominatim API}';

    protected $description = 'Geocode trails and assign them to regions';

    protected $geocoder;

    public function handle()
    {
        $apiChoice = $this->option('api');
        $trailId = $this->argument('trail_id');

        $this->setupGeocoder($apiChoice);

        if ($trailId) {
            $trails = Trail::where('id', $trailId)->get();
        } else {
            $trails = Trail::whereDoesntHave('regions')->get();
        }

        foreach ($trails as $trail) {
            $this->processTrail($trail);
            sleep(1); // Respect the 1 request per second limit
        }

        $this->info('Geocoding process completed.');
    }

    protected function setupGeocoder($apiChoice)
    {
        $httpClient = new GuzzleAdapter();
        $url = $apiChoice === 'public'
            ? 'https://nominatim.openstreetmap.org'
            : 'http://nominatim:8080';

        $provider = new Nominatim($httpClient, $url, config('app.name'));
        $this->geocoder = new StatefulGeocoder($provider, 'pl'); // Ustawiono język na polski
    }

    protected function processTrail(Trail $trail)
    {
        $this->info("Processing trail: {$trail->name}");

        try {
            $result = $this->geocoder->reverseQuery(ReverseQuery::fromCoordinates($trail->start_lat, $trail->start_lng));

            if (!$result->isEmpty()) {
                $address = $result->first();

                DB::beginTransaction();

                try {
                    $country = $this->getOrCreateCountry($address->getCountry()->getName());

                    $stateName = null;
                    foreach ($address->getAdminLevels() as $level) {
                        if ($level->getLevel() === 1) {
                            $stateName = $level->getName();
                            break;
                        }
                    }
                    $state = $stateName ? $this->getOrCreateState($stateName, $country->id) : null;

                    $city = $this->getOrCreateRegion($address->getLocality(), 'city', $state ? $state->id : $country->id);

                    $geoArea = $this->findGeographicArea($address);
                    $geoRegion = $geoArea ? $this->getOrCreateRegion($geoArea, 'geographic_area', $state ? $state->id : $country->id) : null;

                    $regionIds = array_filter([
                        $country->id,
                        $state->id ?? null,
                        $city->id ?? null,
                        $geoRegion->id ?? null
                    ]);

                    $trail->regions()->syncWithoutDetaching($regionIds);

                    DB::commit();

                    $this->info("Trail assigned to regions: {$country->name}"
                        . ($state ? ", {$state->name}" : "")
                        . ($city ? ", {$city->name}" : "")
                        . ($geoRegion ? ", {$geoRegion->name}" : ""));
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            } else {
                $this->warn("No geocoding results for trail: {$trail->name}");
            }
        } catch (\Exception $e) {
            $this->error("Error processing trail {$trail->name}: " . $e->getMessage());
        }
    }

    protected function getOrCreateCountry($name)
    {
        return Region::firstOrCreate(
            ['name' => $name, 'type' => 'country'],
            [
                'slug' => Str::slug($name),
                'is_root' => true,
                'center_point' => $name === 'Polska' ? new \MatanYadaev\EloquentSpatial\Objects\Point(52.1268, 19.4008) : null,
            ]
        );
    }

    protected function getOrCreateState($name, $countryId)
    {
        // Usuń przedrostek "województwo" jeśli istnieje
        $name = preg_replace('/^województwo\s+/i', '', $name);

        // Znajdź istniejące województwo
        $state = Region::where('type', 'state')
            ->where('parent_id', $countryId)
            ->where(function ($query) use ($name) {
                $query->where('name', $name)
                    ->orWhere('name', 'like', $name . '%')
                    ->orWhere('name', 'like', '%' . $name);
            })
            ->first();

        if (!$state) {
            // Jeśli nie znaleziono, utwórz nowe
            $state = Region::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'type' => 'state',
                'parent_id' => $countryId,
                'is_root' => false,
                'center_point' => $this->getRegionCenterPoint($name),
            ]);
        }

        return $state;
    }

    protected function getOrCreateRegion($name, $type, $parentId)
    {
        if (!$name) return null;

        return Region::firstOrCreate(
            ['name' => $name, 'type' => $type, 'parent_id' => $parentId],
            [
                'slug' => Str::slug($name),
                'is_root' => false,
                'center_point' => $this->getRegionCenterPoint($name),
            ]
        );
    }

    protected function getRegionCenterPoint($name)
    {
        $result = $this->geocoder->geocode($name);
        if (!$result->isEmpty()) {
            $coordinates = $result->first()->getCoordinates();
            return new \MatanYadaev\EloquentSpatial\Objects\Point($coordinates->getLatitude(), $coordinates->getLongitude());
        }
        return null;
    }

    protected function findGeographicArea($address)
    {
        $possibleAreas = [
            $address->getSubLocality(),
        ];

        foreach ($address->getAdminLevels() as $level) {
            $possibleAreas[] = $level->getName();
        }

        foreach ($possibleAreas as $area) {
            if ($area && $this->isGeographicArea($area)) {
                return $area;
            }
        }

        return null;
    }

    protected function isGeographicArea($name)
    {
        $keywords = ['park narodowy', 'park krajobrazowy', 'puszcza', 'góry', 'pojezierze', 'wyżyna', 'nizina'];
        foreach ($keywords as $keyword) {
            if (Str::contains(Str::lower($name), $keyword)) {
                return true;
            }
        }
        return false;
    }
}
