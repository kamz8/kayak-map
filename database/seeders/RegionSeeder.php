<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Helpers\GeoHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegionSeeder extends Seeder
{
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
        $polandBoundaries = [
            [14.1228, 52.8907], [14.1448, 52.8242], [14.6448, 52.5719], [14.6832, 52.3851],
            [14.4105, 52.1743], [14.5295, 51.7454], [15.0466, 51.1059], [15.2904, 51.0068],
            [15.7588, 50.7851], [16.2397, 50.4042], [16.6764, 50.3234], [16.8881, 50.4587],
            [17.7185, 50.3182], [18.5962, 49.9096], [18.8531, 49.4966], [19.3202, 49.5715],
            [19.8251, 49.4171], [20.0719, 49.1915], [20.8079, 49.2019], [22.7578, 49.0199],
            [22.8682, 49.0357], [22.7684, 49.2307], [23.5918, 50.4214], [23.9275, 50.7853],
            [24.1457, 50.8551], [23.9984, 51.6518], [23.1922, 52.2681], [23.7417, 52.3912],
            [23.8043, 52.6913], [23.5275, 53.0071], [23.5344, 53.2002], [22.7311, 54.3671],
            [21.2686, 55.2741], [19.6605, 54.4659], [19.2701, 54.2865], [18.6203, 54.4397],
            [16.9241, 54.8313], [14.2603, 53.9280], [14.1228, 52.8907]
        ];

        Region::create([
            'name' => 'Polska',
            'slug' => 'polska',
            'type' => 'country',
            'is_root' => true,
            'center_point' => GeoHelper::pointToGeography(19.1451, 51.9194),
            'area' => GeoHelper::polygonToGeography(GeoHelper::formatPolygonPoints($polandBoundaries)),
        ]);
        Log::info("Added country: Polska");
    }

    private function seedStates(): void
    {
        $country = Region::where('type', 'country')->first();
        $states = [
            ['name' => 'Dolnośląskie', 'slug' => 'dolnoslaskie', 'center' => [16.9262, 51.1200]],
            ['name' => 'Kujawsko-Pomorskie', 'slug' => 'kujawsko-pomorskie', 'center' => [18.3153, 53.0138]],
            ['name' => 'Lubelskie', 'slug' => 'lubelskie', 'center' => [22.5722, 51.2458]],
            ['name' => 'Lubuskie', 'slug' => 'lubuskie', 'center' => [15.5350, 52.4304]],
            ['name' => 'Łódzkie', 'slug' => 'lodzkie', 'center' => [19.4560, 51.5044]],
            ['name' => 'Małopolskie', 'slug' => 'malopolskie', 'center' => [20.0770, 49.9298]],
            ['name' => 'Mazowieckie', 'slug' => 'mazowieckie', 'center' => [20.9333, 52.1919]],
            ['name' => 'Opolskie', 'slug' => 'opolskie', 'center' => [17.3775, 50.7675]],
            ['name' => 'Podkarpackie', 'slug' => 'podkarpackie', 'center' => [22.0000, 50.0647]],
            ['name' => 'Podlaskie', 'slug' => 'podlaskie', 'center' => [23.1688, 53.3334]],
            ['name' => 'Pomorskie', 'slug' => 'pomorskie', 'center' => [18.5314, 54.2927]],
            ['name' => 'Śląskie', 'slug' => 'slaskie', 'center' => [19.0216, 50.3008]],
            ['name' => 'Świętokrzyskie', 'slug' => 'swietokrzyskie', 'center' => [20.7654, 50.7182]],
            ['name' => 'Warmińsko-Mazurskie', 'slug' => 'warminsko-mazurskie', 'center' => [20.8147, 53.8673]],
            ['name' => 'Wielkopolskie', 'slug' => 'wielkopolskie', 'center' => [17.3940, 52.4095]],
            ['name' => 'Zachodniopomorskie', 'slug' => 'zachodniopomorskie', 'center' => [15.4078, 53.4245]],
        ];

        foreach ($states as $state) {
            $stateModel = Region::create([
                'name' => $state['name'],
                'slug' => $state['slug'],
                'type' => 'state',
                'parent_id' => $country->id,
                'center_point' => GeoHelper::pointToGeography($state['center'][0], $state['center'][1]),
            ]);
            Log::info("Added state: {$state['name']}");

            $this->seedCitiesForState($stateModel);
        }
    }

    private function seedCitiesForState(Region $state): void
    {
        $cities = $this->getCitiesForState($state->name);
        foreach ($cities as $city) {
            Region::create([
                'name' => $city['name'],
                'slug' => Str::slug($city['name']),
                'type' => 'city',
                'parent_id' => $state->id,
                'center_point' => GeoHelper::pointToGeography($city['lon'], $city['lat']),
            ]);
            Log::info("Added city: {$city['name']} to {$state->name}");
        }
    }

    private function getCitiesForState(string $stateName): array
    {
        $cities = [
            'Dolnośląskie' => [
                ['name' => 'Wrocław', 'lat' => 51.1079, 'lon' => 17.0385],
                ['name' => 'Wałbrzych', 'lat' => 50.7714, 'lon' => 16.2845],
                ['name' => 'Legnica', 'lat' => 51.2070, 'lon' => 16.1619],
                ['name' => 'Jelenia Góra', 'lat' => 50.9044, 'lon' => 15.7197],
            ],
            'Kujawsko-Pomorskie' => [
                ['name' => 'Bydgoszcz', 'lat' => 53.1235, 'lon' => 18.0084],
                ['name' => 'Toruń', 'lat' => 53.0138, 'lon' => 18.5981],
                ['name' => 'Włocławek', 'lat' => 52.6483, 'lon' => 19.0677],
                ['name' => 'Grudziądz', 'lat' => 53.4837, 'lon' => 18.7536],
            ],
            'Lubelskie' => [
                ['name' => 'Lublin', 'lat' => 51.2465, 'lon' => 22.5684],
                ['name' => 'Zamość', 'lat' => 50.7230, 'lon' => 23.2519],
                ['name' => 'Chełm', 'lat' => 51.1436, 'lon' => 23.4711],
                ['name' => 'Biała Podlaska', 'lat' => 52.0323, 'lon' => 23.1445],
            ],
            'Lubuskie' => [
                ['name' => 'Zielona Góra', 'lat' => 51.9356, 'lon' => 15.5062],
                ['name' => 'Gorzów Wielkopolski', 'lat' => 52.7325, 'lon' => 15.2369],
            ],
            'Łódzkie' => [
                ['name' => 'Łódź', 'lat' => 51.7592, 'lon' => 19.4560],
                ['name' => 'Piotrków Trybunalski', 'lat' => 51.4047, 'lon' => 19.7030],
                ['name' => 'Pabianice', 'lat' => 51.6652, 'lon' => 19.3548],
                ['name' => 'Tomaszów Mazowiecki', 'lat' => 51.5307, 'lon' => 20.0087],
            ],
            'Małopolskie' => [
                ['name' => 'Kraków', 'lat' => 50.0647, 'lon' => 19.9450],
                ['name' => 'Tarnów', 'lat' => 50.0121, 'lon' => 20.9858],
                ['name' => 'Nowy Sącz', 'lat' => 49.6249, 'lon' => 20.6915],
                ['name' => 'Oświęcim', 'lat' => 50.0343, 'lon' => 19.2098],
            ],
            'Mazowieckie' => [
                ['name' => 'Warszawa', 'lat' => 52.2297, 'lon' => 21.0122],
                ['name' => 'Radom', 'lat' => 51.4027, 'lon' => 21.1471],
                ['name' => 'Płock', 'lat' => 52.5463, 'lon' => 19.7065],
                ['name' => 'Siedlce', 'lat' => 52.1677, 'lon' => 22.2901],
            ],
            'Opolskie' => [
                ['name' => 'Opole', 'lat' => 50.6683, 'lon' => 17.9236],
                ['name' => 'Kędzierzyn-Koźle', 'lat' => 50.3497, 'lon' => 18.2082],
                ['name' => 'Nysa', 'lat' => 50.4747, 'lon' => 17.3328],
            ],
            'Podkarpackie' => [
                ['name' => 'Rzeszów', 'lat' => 50.0412, 'lon' => 21.9991],
                ['name' => 'Przemyśl', 'lat' => 49.7838, 'lon' => 22.7678],
                ['name' => 'Stalowa Wola', 'lat' => 50.5820, 'lon' => 22.0536],
                ['name' => 'Mielec', 'lat' => 50.2874, 'lon' => 21.4219],
            ],
            'Podlaskie' => [
                ['name' => 'Białystok', 'lat' => 53.1325, 'lon' => 23.1688],
                ['name' => 'Suwałki', 'lat' => 54.1115, 'lon' => 22.9307],
                ['name' => 'Łomża', 'lat' => 53.1787, 'lon' => 22.0590],
            ],
            'Pomorskie' => [
                ['name' => 'Gdańsk', 'lat' => 54.3520, 'lon' => 18.6466],
                ['name' => 'Gdynia', 'lat' => 54.5189, 'lon' => 18.5305],
                ['name' => 'Słupsk', 'lat' => 54.4641, 'lon' => 17.0285],
                ['name' => 'Tczew', 'lat' => 54.0922, 'lon' => 18.7772],
            ],
            'Śląskie' => [
                ['name' => 'Katowice', 'lat' => 50.2649, 'lon' => 19.0238],
                ['name' => 'Częstochowa', 'lat' => 50.8118, 'lon' => 19.1203],
                ['name' => 'Sosnowiec', 'lat' => 50.2863, 'lon' => 19.1042],
                ['name' => 'Gliwice', 'lat' => 50.2945, 'lon' => 18.6714],
            ],
            'Świętokrzyskie' => [
                ['name' => 'Kielce', 'lat' => 50.8661, 'lon' => 20.6286],
                ['name' => 'Ostrowiec Świętokrzyski', 'lat' => 50.9294, 'lon' => 21.3855],
                ['name' => 'Starachowice', 'lat' => 51.0375, 'lon' => 21.0745],
            ],
            'Warmińsko-Mazurskie' => [
                ['name' => 'Olsztyn', 'lat' => 53.7785, 'lon' => 20.4942],
                ['name' => 'Elbląg', 'lat' => 54.1558, 'lon' => 19.4044],
                ['name' => 'Ełk', 'lat' => 53.8268, 'lon' => 22.3648],
            ],
            'Wielkopolskie' => [
                ['name' => 'Poznań', 'lat' => 52.4064, 'lon' => 16.9252],
                ['name' => 'Kalisz', 'lat' => 51.7577, 'lon' => 18.0862],
                ['name' => 'Konin', 'lat' => 52.2230, 'lon' => 18.2511],
                ['name' => 'Piła', 'lat' => 53.1515, 'lon' => 16.7385],
            ],
            'Zachodniopomorskie' => [
                ['name' => 'Szczecin', 'lat' => 53.4285, 'lon' => 14.5528],
                ['name' => 'Koszalin', 'lat' => 54.1938, 'lon' => 16.1720],
                ['name' => 'Stargard', 'lat' => 53.3364, 'lon' => 15.0506],
                ['name' => 'Kołobrzeg', 'lat' => 54.1760, 'lon' => 15.5769],
            ],
        ];

        return $cities[$stateName] ?? [];
    }

    private function seedGeographicRegions(): void
    {
        $country = Region::where('type', 'country')->first();
        $geographicRegions = [
            [
                'name' => 'Dolina Baryczy',
                'slug' => 'dolina-baryczy',
                'center' => [17.4667, 51.5333],
                'area' => [
                    [17.2000, 51.4000], [17.3000, 51.4500], [17.5000, 51.5000],
                    [17.7000, 51.5500], [17.6000, 51.6000], [17.4000, 51.6500],
                    [17.2000, 51.6000], [17.2000, 51.4000]
                ]
            ],
            [
                'name' => 'Dolina Bugu',
                'slug' => 'dolina-bugu',
                'center' => [23.1667, 52.0833],
                'area' => [
                    [22.8000, 51.8000], [23.2000, 51.9000], [23.5000, 52.1000],
                    [23.4000, 52.3000], [23.1000, 52.2000], [22.8000, 52.0000],
                    [22.8000, 51.8000]
                ]
            ],
            [
                'name' => 'Dolina Narwi',
                'slug' => 'dolina-narwi',
                'center' => [21.5833, 53.1333],
                'area' => [
                    [21.2000, 52.9000], [21.5000, 53.0000], [21.8000, 53.2000],
                    [22.0000, 53.3000], [21.8000, 53.4000], [21.5000, 53.3000],
                    [21.2000, 53.1000], [21.2000, 52.9000]
                ]
            ],
            [
                'name' => 'Dolina Pilicy',
                'slug' => 'dolina-pilicy',
                'center' => [20.4667, 51.5167],
                'area' => [
                    [20.2000, 51.3000], [20.5000, 51.4000], [20.7000, 51.5000],
                    [20.6000, 51.7000], [20.4000, 51.6000], [20.2000, 51.5000],
                    [20.2000, 51.3000]
                ]
            ],
            [
                'name' => 'Dolina Sanu',
                'slug' => 'dolina-sanu',
                'center' => [22.8000, 50.0667],
                'area' => [
                    [22.5000, 49.8000], [22.8000, 49.9000], [23.1000, 50.1000],
                    [23.0000, 50.3000], [22.7000, 50.2000], [22.5000, 50.0000],
                    [22.5000, 49.8000]
                ]
            ],
            [
                'name' => 'Dolina Wisły',
                'slug' => 'dolina-wisly',
                'center' => [18.8024, 54.1039],
                'area' => [
                    [18.5000, 53.9000], [18.8000, 54.0000], [19.1000, 54.2000],
                    [19.0000, 54.4000], [18.7000, 54.3000], [18.5000, 54.1000],
                    [18.5000, 53.9000]
                ]
            ],
            [
                'name' => 'Jezioro Solińskie',
                'slug' => 'jezioro-solinskie',
                'center' => [22.4667, 49.3833],
                'area' => [
                    [22.3000, 49.3000], [22.5000, 49.3500], [22.6000, 49.4000],
                    [22.5000, 49.4500], [22.4000, 49.4000], [22.3000, 49.3500],
                    [22.3000, 49.3000]
                ]
            ],
            [
                'name' => 'Krutynia',
                'slug' => 'krutynia',
                'center' => [21.4833, 53.7000],
                'area' => [
                    [21.3000, 53.5000], [21.5000, 53.6000], [21.7000, 53.7000],
                    [21.6000, 53.9000], [21.4000, 53.8000], [21.3000, 53.7000],
                    [21.3000, 53.5000]
                ]
            ],
            [
                'name' => 'Mazury',
                'slug' => 'mazury',
                'center' => [21.5700, 53.8700],
                'area' => [
                    [21.0000, 53.5000], [22.0000, 53.7000], [22.5000, 54.0000],
                    [22.0000, 54.3000], [21.0000, 54.1000], [21.0000, 53.5000]
                ]
            ],
            [
                'name' => 'Pojezierze Drawskie',
                'slug' => 'pojezierze-drawskie',
                'center' => [16.0833, 53.5333],
                'area' => [
                    [15.8000, 53.3000], [16.2000, 53.4000], [16.4000, 53.6000],
                    [16.3000, 53.8000], [16.0000, 53.7000], [15.8000, 53.5000],
                    [15.8000, 53.3000]
                ]
            ],
            [
                'name' => 'Roztocze',
                'slug' => 'roztocze',
                'center' => [23.0000, 50.5833],
                'area' => [
                    [22.7000, 50.3000], [23.1000, 50.4000], [23.3000, 50.6000],
                    [23.2000, 50.8000], [22.9000, 50.7000], [22.7000, 50.5000],
                    [22.7000, 50.3000]
                ]
            ],
            [
                'name' => 'Wielkie Jeziora Mazurskie',
                'slug' => 'wielkie-jeziora-mazurskie',
                'center' => [21.7500, 53.9167],
                'area' => [
                    [21.4000, 53.7000], [21.8000, 53.8000], [22.1000, 54.0000],
                    [22.0000, 54.2000], [21.6000, 54.1000], [21.4000, 53.9000],
                    [21.4000, 53.7000]
                ]
            ],
        ];

        foreach ($geographicRegions as $region) {
            Region::create([
                'name' => $region['name'],
                'slug' => $region['slug'],
                'type' => 'geographic_area',
                'parent_id' => $country->id,
                'center_point' => GeoHelper::pointToGeography($region['center'][0], $region['center'][1]),
                'area' => GeoHelper::polygonToGeography(GeoHelper::formatPolygonPoints($region['area'])),
            ]);
            Log::info("Added geographic region: {$region['name']}");
        }
    }
}
