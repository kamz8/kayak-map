<?php

namespace Kamz8\LaravelOverpass\Helpers;

use Kamz8\LaravelOverpass\Overpass;
use Exception;

/**
 * Class RouteHelper
 *
 * Helper class for finding routes using the Overpass API with OverpassQL queries.
 *
 * @package Kamz8\LaravelOverpass\Helpers
 */
class RouteHelper
{
    /** @var Overpass The Overpass API client */
    protected Overpass $overpass;

    /**
     * RouteHelper constructor.
     *
     * @param array $config Configuration array for the Overpass client
     */
    public function __construct(array $config)
    {
        $this->overpass = new Overpass($config);
    }

    /**
     * Find a water route between two points using OverpassQL.
     *
     * @param float $lat1 Latitude of the starting point
     * @param float $lon1 Longitude of the starting point
     * @param float $lat2 Latitude of the ending point
     * @param float $lon2 Longitude of the ending point
     * @return array The route data
     * @throws Exception If there's an error finding the route
     */
    public function findRoute(float $lat1, float $lon1, float $lat2, float $lon2): array
    {
        $queryBuilder = $this->overpass->query();
        $query = $this->buildRouteQuery($lat1, $lon1, $lat2, $lon2);

        try {
            // Use the raw query method to set the query and execute it
            return $queryBuilder->raw($query)->get();
        } catch (Exception $e) {
            throw new Exception("Error finding route: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Build the OverpassQL query for finding a water route.
     *
     * @param float $lat1 Latitude of the starting point
     * @param float $lon1 Longitude of the starting point
     * @param float $lat2 Latitude of the ending point
     * @param float $lon2 Longitude of the ending point
     * @return string The OverpassQL query
     */
    private function buildRouteQuery(float $lat1, float $lon1, float $lat2, float $lon2): string
    {
        return <<<OVERPASSQL
        [out:json][timeout:60];
        (
          way(around:1000,{$lat1},{$lon1})['waterway'];
          way(around:1000,{$lat2},{$lon2})['waterway'];
        );
        out body;
        >;
        out skel qt;
        OVERPASSQL;
    }
}
