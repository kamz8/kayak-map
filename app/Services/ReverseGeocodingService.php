<?php

namespace App\Services;

use App\Models\Region;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Nominatim\Nominatim;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Geocoder\Exception\Exception as GeocoderException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReverseGeocodingService
{
    protected Nominatim $geocoder;

    public function __construct()
    {
        $httpClient = new GuzzleAdapter();
        $this->geocoder = new Nominatim($httpClient, 'https://nominatim.openstreetmap.org', env('APP_NAME').'/0.1');
    }

    protected function createOrUpdateRegion($address): Region
    {
        $adminLevels = $address->getAdminLevels();
        $country = $address->getCountry();
        $locality = $address->getLocality();

        $state = $adminLevels->get(1) ? $adminLevels->get(1)->getName() : null;
        $county = $adminLevels->get(2) ? $adminLevels->get(2)->getName() : null;

        return Region::updateOrCreate(
            ['name' => $state ?? $county ?? $locality ?? $country],
            [
                'type' => $state ? 'state' : ($county ? 'county' : ($locality ? 'city' : 'country')),
                'slug' => Str::slug($state ?? $county ?? $locality ?? $country),
                'center_point' => DB::raw("ST_GeomFromText('POINT({$address->getCoordinates()->getLongitude()} {$address->getCoordinates()->getLatitude()})')"),
                'parent_id' => $this->getParentRegionId($address),
            ]
        );
    }

    protected function getParentRegionId($address): ?int
    {
        $adminLevels = $address->getAdminLevels();
        $country = $address->getCountry();

        if ($adminLevels->get(1)) {
            // If we have a state, the parent is the country
            return Region::firstOrCreate(
                ['name' => $country],
                [
                    'type' => 'country',
                    'slug' => Str::slug($country),
                ]
            )->id;
        } elseif ($adminLevels->get(2)) {
            // If we have a county, the parent is the state or country
            return Region::firstOrCreate(
                ['name' => $adminLevels->get(1)->getName() ?? $country],
                [
                    'type' => $adminLevels->get(1) ? 'state' : 'country',
                    'slug' => Str::slug($adminLevels->get(1)->getName() ?? $country),
                ]
            )->id;
        }

        return null;
    }

    public function getRegionData(float $latitude, float $longitude): ?array
    {
        return $this->getCachedGeocodingResult($latitude, $longitude, function($address) {
            $adminLevels = $address->getAdminLevels();

            return [
                'country' => $address->getCountry()->getName(),
                'state' => $adminLevels->get(1)?->getName(),
                'city' => $address->getLocality() ?? $adminLevels->get(2)?->getName(),
                'lat' => $address->getCoordinates()->getLatitude(),
                'lng' => $address->getCoordinates()->getLongitude(),
            ];
        });
    }

    public function getLocationNameAndSlug(float $latitude, float $longitude): ?array
    {
        return $this->getCachedGeocodingResult($latitude, $longitude, function($address) {
            $adminLevels = $address->getAdminLevels();

            $country = $address->getCountry()->getName();
            $state = $adminLevels->get(1)?->getName();
            $city = $address->getLocality() ?? $adminLevels->get(2)?->getName();

            $name = implode(', ', array_filter([$city, $country]));
            $slug = implode('/', array_filter([
                Str::slug($country),
                $state ? Str::slug($state) : null,
                $city ? Str::slug($city) : null
            ]));

            return [
                'name' => $name,
                'slug' => $slug
            ];
        });
    }

    protected function getCachedGeocodingResult(float $latitude, float $longitude, callable $formatter): ?array
    {
        $cacheKey = "geocode_result:{$latitude},{$longitude}";

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($latitude, $longitude, $formatter) {
            $result = $this->geocoder->reverseQuery(ReverseQuery::fromCoordinates($latitude, $longitude));

            if ($result->isEmpty()) {
                return null;
            }

            return $formatter($result->first());
        });
    }
}
