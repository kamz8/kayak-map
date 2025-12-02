<?php
// tests/Unit/Models/RouteResultTest.php

namespace Kamz\LaravelBRouter\Tests\Unit\Models;

use Tests\TestCase;
use Kamz\LaravelBRouter\Models\RouteResult;

class RouteResultTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $routeResult = new RouteResult();

        $this->assertInstanceOf(RouteResult::class, $routeResult);
    }

    /** @test */
    public function it_has_expected_properties()
    {
        $routeResult = new RouteResult();

        $this->assertObjectHasAttribute('path', $routeResult);
        $this->assertObjectHasAttribute('startSnap', $routeResult);
        $this->assertObjectHasAttribute('endSnap', $routeResult);
        $this->assertObjectHasAttribute('distance', $routeResult);
        $this->assertObjectHasAttribute('duration', $routeResult);
    }

    /** @test */
    public function it_returns_array_from_to_array_method()
    {
        $routeResult = new RouteResult();

        $result = $routeResult->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('features', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertEquals('FeatureCollection', $result['type']);
    }

    /** @test */
    public function it_returns_geojson_from_to_geojson_method()
    {
        $routeResult = new RouteResult();

        $result = $routeResult->toGeoJSON();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('geometry', $result);
        $this->assertEquals('Feature', $result['type']);
        $this->assertEquals('LineString', $result['geometry']['type']);
    }
}
