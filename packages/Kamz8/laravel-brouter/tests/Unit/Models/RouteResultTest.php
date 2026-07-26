<?php
// tests/Unit/Models/RouteResultTest.php

namespace Kamz\LaravelBRouter\Tests\Unit\Models;

use Kamz\LaravelBRouter\Models\RouteResult;
use Kamz\LaravelBRouter\Tests\TestCase;

class RouteResultTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $routeResult = new RouteResult(
            path: [[18.2, 53.9], [18.1, 53.8]],
            startSnap: [
                'input' => [18.2, 53.9],
                'snapped' => [18.201, 53.901],
                'distance_m' => 42.0,
            ],
            endSnap: [
                'input' => [18.1, 53.8],
                'snapped' => [18.102, 53.802],
                'distance_m' => 55.0,
            ],
            distanceMeters: 12400.0,
            warnings: ['test warning'],
            cache: ['osm' => 'hit', 'graph' => 'hit', 'route' => 'miss'],
        );

        $this->assertInstanceOf(RouteResult::class, $routeResult);
    }

    /** @test */
    public function it_has_expected_properties()
    {
        $routeResult = new RouteResult();

        $this->assertTrue(property_exists($routeResult, 'path'));
        $this->assertTrue(property_exists($routeResult, 'startSnap'));
        $this->assertTrue(property_exists($routeResult, 'endSnap'));
        $this->assertTrue(property_exists($routeResult, 'distanceMeters'));
        $this->assertTrue(property_exists($routeResult, 'warnings'));
        $this->assertTrue(property_exists($routeResult, 'cache'));
    }

    /** @test */
    public function it_returns_array_from_to_array_method()
    {
        $routeResult = new RouteResult(
            path: [[18.2, 53.9], [18.1, 53.8]],
            startSnap: ['distance_m' => 42.0],
            endSnap: ['distance_m' => 55.0],
            distanceMeters: 12400.0,
            warnings: [],
            cache: ['osm' => 'hit', 'graph' => 'hit', 'route' => 'miss'],
        );

        $result = $routeResult->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertSame([[18.2, 53.9], [18.1, 53.8]], $result['data']['path']);
        $this->assertSame(['distance_m' => 42.0], $result['data']['start_snap']);
        $this->assertSame(['distance_m' => 55.0], $result['data']['end_snap']);
        $this->assertSame(12400.0, $result['data']['distance_m']);
        $this->assertSame(['osm' => 'hit', 'graph' => 'hit', 'route' => 'miss'], $result['data']['cache']);
    }

    /** @test */
    public function it_returns_geojson_from_to_geojson_method()
    {
        $routeResult = new RouteResult(path: [[18.2, 53.9], [18.1, 53.8]]);

        $result = $routeResult->toGeoJSON();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('geometry', $result);
        $this->assertEquals('Feature', $result['type']);
        $this->assertEquals('LineString', $result['geometry']['type']);
    }
}
