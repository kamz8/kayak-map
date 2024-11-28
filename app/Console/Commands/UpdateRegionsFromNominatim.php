<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Region;
use Carbon\Carbon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class UpdateRegionsFromNominatim extends Command
{
    protected $signature = 'regions:update-from-nominatim {--id=}';
    protected $description = 'Update region data from Nominatim API';

    private $lastApiCall;

    public function __construct()
    {
        parent::__construct();
        $this->lastApiCall = null;
    }

    public function handle()
    {
        $id = $this->option('id');

        if ($id) {
            $regions = Region::where('id', $id)->get();
        } else {
            $regions = Region::whereNull('center_point')
                ->orWhereNull('area')
                ->get();
        }

        foreach ($regions as $region) {
            $this->ensureApiCallDelay();

            try {
                $response = Http::get("https://nominatim.openstreetmap.org/search", [
                    'q' => $region->name,
                    'format' => 'json',
                    'polygon_geojson' => 1,
                    'addressdetails' => 1,
                ]);

                $data = $response->json();

                if (is_array($data) && count($data) > 0) {
                    $firstResult = $data[0];

                    if (isset($firstResult['lat'], $firstResult['lon'])) {
                        $region->center_point = $this->getPointFromLatLon($firstResult['lat'], $firstResult['lon']);
                    } else {
                        $this->error("Missing lat/lon for region: {$region->name}");
                        continue;
                    }

                    if (isset($firstResult['geojson']['coordinates'])) {
                        $region->area = $this->getPolygonFromGeoJson($firstResult['geojson']);
                    } else {
                        $this->error("Missing GeoJSON coordinates for region: {$region->name}");
                        continue;
                    }

                    $region->updated_at = Carbon::now();
                    $region->save();

                    $this->info("Updated region: {$region->name}");
                } else {
                    $this->error("No data found for region: {$region->name}");
                }
            } catch (\Exception $e) {
                $this->error("Error updating region: {$region->name} - {$e->getMessage()}");
            }
        }

        $this->info('Region data updated from Nominatim API');
    }


    private function ensureApiCallDelay()
    {
        if ($this->lastApiCall !== null) {
            $delay = Carbon::now()->diffInSeconds($this->lastApiCall);
            if ($delay < 1) {
                $this->info("Waiting {$delay} seconds before next API call...");
                sleep(1 - $delay);
            }
        }

        $this->lastApiCall = Carbon::now();
    }

    private function getPointFromLatLon($lat, $lon)
    {
        return new Point($lat, $lon);
    }

    private function getPolygonFromGeoJson($geoJson)
    {
        if (isset($geoJson['coordinates'][0]) && is_array($geoJson['coordinates'][0])) {
            $coordinates = $geoJson['coordinates'][0];
            $points = array_map(function ($coordinate) {
                return new Point($coordinate[1], $coordinate[0]);
            }, $coordinates);
            return new Polygon(geometries: [new LineString($points)]);
        }

        return null;
    }
}
