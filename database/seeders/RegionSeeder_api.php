<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Services\GeocodingService;
use App\Helpers\GeoHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class RegionSeeder_api extends Seeder
{
    protected GeocodingService $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    public function run(): void
    {
        DB::transaction(function () {
            $this->seedCountry();
            $this->seedStates();
            $this->seedGeographicRegions();
        });
    }

    private function seedCountry(): void
    {
        Region::create([
            'name' => 'Polska',
            'slug' => 'polska',
            'type' => 'country',
            'is_root' => true,
            'center_point' => GeoHelper::pointToGeography(19.1451, 51.9194),
        ]);
        Log::info("Added country: Polska");
    }

    private function seedStates(): void
    {
        $country = Region::where('type', 'country')->first();
        $states = [
            ['name' => 'Dolnośląskie', 'slug' => 'dolnoslaskie', 'center_point' => 'POINT(16.9262, 51.1200)'],
            ['name' => 'Kujawsko-Pomorskie', 'slug' => 'kujawsko-pomorskie', 'center_point' => 'POINT(18.3153, 53.0138)'],
            ['name' => 'Lubelskie', 'slug' => 'lubelskie', 'center_point' => 'POINT(22.5722, 51.2458)'],
            ['name' => 'Lubuskie', 'slug' => 'lubuskie', 'center_point' => 'POINT(15.5350, 52.4304)'],
            ['name' => 'Łódzkie', 'slug' => 'lodzkie', 'center_point' => 'POINT(19.4560, 51.5044)'],
            ['name' => 'Małopolskie', 'slug' => 'malopolskie', 'center_point' => 'POINT(20.0770, 49.9298)'],
            ['name' => 'Mazowieckie', 'slug' => 'mazowieckie', 'center_point' => 'POINT(20.9333, 52.1919)'],
            ['name' => 'Opolskie', 'slug' => 'opolskie', 'center_point' => 'POINT(17.3775, 50.7675)'],
            ['name' => 'Podkarpackie', 'slug' => 'podkarpackie', 'center_point' => 'POINT(22.0000, 50.0647)'],
            ['name' => 'Podlaskie', 'slug' => 'podlaskie', 'center_point' => 'POINT(23.1688, 53.3334)'],
            ['name' => 'Pomorskie', 'slug' => 'pomorskie', 'center_point' => 'POINT(18.5314, 54.2927)'],
            ['name' => 'Śląskie', 'slug' => 'slaskie', 'center_point' => 'POINT(19.0216, 50.3008)'],
            ['name' => 'Świętokrzyskie', 'slug' => 'swietokrzyskie', 'center_point' => 'POINT(20.7654, 50.7182)'],
            ['name' => 'Warmińsko-Mazurskie', 'slug' => 'warminsko-mazurskie', 'center_point' => 'POINT(20.8147, 53.8673)'],
            ['name' => 'Wielkopolskie', 'slug' => 'wielkopolskie', 'center_point' => 'POINT(17.3940, 52.4095)'],
            ['name' => 'Zachodniopomorskie', 'slug' => 'zachodniopomorskie', 'center_point' => 'POINT(15.4078, 53.4245)'],
        ];

        foreach ($states as $state) {
            $stateModel = Region::create([
                'name' => $state['name'],
                'slug' => $state['slug'],
                'type' => 'state',
                'parent_id' => $country->id,
                'center_point' => DB::raw($state['center_point']),
            ]);
            Log::info("Added state: {$state['name']}");

            $this->seedCitiesForState($stateModel);
        }
    }

    private function seedCitiesForState(Region $state): void
    {
        $cities = $this->getCitiesForState($state->name);
        foreach ($cities as $city) {
            try {
                $geoData = $this->geocodingService->geocode($city . ', ' . $state->name . ', Polska');
                if (!empty($geoData)) {
                    $centerPoint = GeoHelper::pointToGeography($geoData[0]['lon'], $geoData[0]['lat']);
                    $boundaries = $this->geocodingService->fetchAreaBoundaries($geoData[0]['osm_id'], $geoData[0]['osm_type']);
                    $polygonPoints = GeoHelper::formatPolygonPoints($boundaries);

                    Region::create([
                        'name' => $city,
                        'slug' => Str::slug($city),
                        'type' => 'city',
                        'parent_id' => $state->id,
                        'center_point' => $centerPoint,
                        'area' => GeoHelper::polygonToGeography($polygonPoints),
                    ]);
                    Log::info("Added city: {$city} to {$state->name}");
                }
            } catch (Exception $e) {
                Log::error("Error adding city {$city}: " . $e->getMessage());
            }

            sleep(1); // Opóźnienie między miastami
        }

        sleep(2); // Zwiększone opóźnienie między województwami
    }

    private function getCitiesForState(string $stateName): array
    {
        $cities = [
            'Dolnośląskie' => ['Wrocław', 'Wałbrzych', 'Legnica', 'Jelenia Góra'],
            'Kujawsko-Pomorskie' => ['Bydgoszcz', 'Toruń', 'Włocławek', 'Grudziądz'],
            'Lubelskie' => ['Lublin', 'Zamość', 'Chełm', 'Biała Podlaska'],
            'Lubuskie' => ['Zielona Góra', 'Gorzów Wielkopolski'],
            'Łódzkie' => ['Łódź', 'Piotrków Trybunalski', 'Pabianice', 'Tomaszów Mazowiecki'],
            'Małopolskie' => ['Kraków', 'Tarnów', 'Nowy Sącz', 'Oświęcim'],
            'Mazowieckie' => ['Warszawa', 'Radom', 'Płock', 'Siedlce'],
            'Opolskie' => ['Opole', 'Kędzierzyn-Koźle', 'Nysa'],
            'Podkarpackie' => ['Rzeszów', 'Przemyśl', 'Stalowa Wola', 'Mielec'],
            'Podlaskie' => ['Białystok', 'Suwałki', 'Łomża'],
            'Pomorskie' => ['Gdańsk', 'Gdynia', 'Słupsk', 'Tczew'],
            'Śląskie' => ['Katowice', 'Częstochowa', 'Sosnowiec', 'Gliwice'],
            'Świętokrzyskie' => ['Kielce', 'Ostrowiec Świętokrzyski', 'Starachowice'],
            'Warmińsko-Mazurskie' => ['Olsztyn', 'Elbląg', 'Ełk'],
            'Wielkopolskie' => ['Poznań', 'Kalisz', 'Konin', 'Piła'],
            'Zachodniopomorskie' => ['Szczecin', 'Koszalin', 'Stargard'],
        ];

        return $cities[$stateName] ?? [];
    }

    private function seedGeographicRegions(): void
    {
        $geographicRegions = [
            ['name' => 'Dolina Baryczy', 'slug' => 'dolina-baryczy'],
            ['name' => 'Dolina Bugu', 'slug' => 'dolina-bugu'],
            ['name' => 'Dolina Narwi', 'slug' => 'dolina-narwi'],
            ['name' => 'Dolina Pilicy', 'slug' => 'dolina-pilicy'],
            ['name' => 'Dolina Sanu', 'slug' => 'dolina-sanu'],
            ['name' => 'Dolina Wisły', 'slug' => 'dolina-wisly'],
            ['name' => 'Jezioro Solińskie', 'slug' => 'jezioro-solinskie'],
            ['name' => 'Krutynia', 'slug' => 'krutynia'],
            ['name' => 'Mazury', 'slug' => 'mazury'],
            ['name' => 'Pojezierze Drawskie', 'slug' => 'pojezierze-drawskie'],
            ['name' => 'Roztocze', 'slug' => 'roztocze'],
            ['name' => 'Wielkie Jeziora Mazurskie', 'slug' => 'wielkie-jeziora-mazurskie'],
        ];

        $country = Region::where('type', 'country')->first();

        foreach ($geographicRegions as $region) {
            try {
                $geoData = $this->geocodingService->geocode($region['name'] . ', Polska');
                if (!empty($geoData)) {
                    $centerPoint = GeoHelper::pointToGeography($geoData[0]['lon'], $geoData[0]['lat']);
                    $boundaries = $this->geocodingService->fetchAreaBoundaries($geoData[0]['osm_id'], $geoData[0]['osm_type']);
                    $polygonPoints = GeoHelper::formatPolygonPoints($boundaries);

                    Region::create([
                        'name' => $region['name'],
                        'slug' => $region['slug'],
                        'type' => 'geographic_area',
                        'parent_id' => $country->id,
                        'center_point' => $centerPoint,
                        'area' => GeoHelper::polygonToGeography($polygonPoints),
                    ]);
                    Log::info("Added geographic region: {$region['name']}");
                }
            } catch (Exception $e) {
                Log::error("Error adding geographic region {$region['name']}: " . $e->getMessage());
            }

            sleep(2); // Opóźnienie między regionami geograficznymi
        }
    }
}
