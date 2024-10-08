<?php

namespace Kamz8\LaravelOverpass\Tests;

use Kamz8\LaravelOverpass\Facades\Overpass;
use Kamz8\LaravelOverpass\OverpassServiceProvider;
use Orchestra\Testbench\TestCase;

class OverpassTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            OverpassServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Overpass' => Overpass::class,
        ];
    }

    /** @test */
    public function it_builds_and_executes_a_simple_query()
    {
        $data = Overpass::query()
            ->node()
            ->where('amenity', 'cafe')
            ->bbox(51.5, -0.1, 51.6, 0.1)
            ->limit(1)
            ->get();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('elements', $data);
        $this->assertNotEmpty($data['elements']);
    }

    /** @test */
    public function it_applies_multiple_where_filters()
    {
        $data = Overpass::query()
            ->node()
            ->where('amenity', 'restaurant')
            ->where('cuisine', 'italian')
            ->bbox(51.5, -0.1, 51.6, 0.1)
            ->get();

        $this->assertIsArray($data);
        // Additional assertions can be made based on expected data
    }

    /** @test */
    public function it_applies_or_where_filters()
    {
        $data = Overpass::query()
            ->node()
            ->where('amenity', 'cafe')
            ->orWhere('amenity', 'restaurant')
            ->bbox(51.5, -0.1, 51.6, 0.1)
            ->limit(5)
            ->get();

        $this->assertIsArray($data);
        // Verify that returned elements have either amenity 'cafe' or 'restaurant'
        foreach ($data['elements'] as $element) {
            $this->assertContains($element['tags']['amenity'], ['cafe', 'restaurant']);
        }
    }

    /** @test */
    public function it_executes_a_raw_query()
    {
        $query = '
        [out:json];
        node["amenity"="cafe"](51.5,-0.1,51.6,0.1);
        out;
        ';

        $data = Overpass::raw($query)->get();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('elements', $data);
        $this->assertNotEmpty($data['elements']);
    }

    /** @test */
    public function it_uses_bbox_from_points_with_margin()
    {
        $lat1 = 51.5;
        $lon1 = -0.1;
        $lat2 = 51.6;
        $lon2 = 0.1;
        $marginPercent = 10;

        $data = Overpass::query()
            ->node()
            ->where('amenity', 'parking')
            ->bboxFromPoints($lat1, $lon1, $lat2, $lon2, $marginPercent)
            ->get();

        $this->assertIsArray($data);
        // Additional assertions can be made based on expected data
    }

    /** @test */
    public function it_uses_around_method()
    {
        $lat = 51.5;
        $lon = -0.1;
        $radius = 1000; // in meters

        $data = Overpass::query()
            ->node()
            ->where('amenity', 'fuel')
            ->around($radius, $lat, $lon)
            ->get();

        $this->assertIsArray($data);
        // Verify that elements are within the specified radius
    }

    /** @test */
    public function it_uses_recurse_method()
    {
        $data = Overpass::query()
            ->way()
            ->where('highway', 'bus_route')
            ->bbox(51.5, -0.1, 51.6, 0.1)
            ->recurse()
            ->get();

        $this->assertIsArray($data);
        // Verify that related elements are included
    }

    /** @test */
    public function it_limits_the_number_of_results()
    {
        $limit = 3;

        $data = Overpass::query()
            ->node()
            ->where('amenity', 'bicycle_parking')
            ->bbox(51.5, -0.1, 51.6, 0.1)
            ->limit($limit)
            ->get();

        $this->assertIsArray($data);
        $this->assertCount($limit, $data['elements']);
    }

    /** @test */
    public function it_handles_invalid_query_error()
    {
        $this->expectException(\Exception::class);

        Overpass::raw('invalid query')->get();
    }

    /** @test */
    public function it_handles_connection_error()
    {
        // Simulate API being unavailable by setting an invalid endpoint
        config(['overpass.endpoint' => 'http://invalid-endpoint']);

        $this->expectException(\Exception::class);

        Overpass::query()
            ->node()
            ->where('amenity', 'cafe')
            ->get();
    }

    /** @test */
    public function it_honors_throttle_limits()
    {
        config(['overpass.throttle_limit' => 1]);

        $startTime = microtime(true);

        for ($i = 0; $i < 2; $i++) {
            Overpass::query()
                ->node()
                ->where('amenity', 'cafe')
                ->limit(1)
                ->get();
        }

        $endTime = microtime(true);
        $elapsedTime = $endTime - $startTime;

        $this->assertGreaterThanOrEqual(1.0, $elapsedTime, 'Throttle limit not enforced properly.');
    }

    /** @test */
    public function it_allows_disabling_throttle()
    {
        config(['overpass.throttle' => false]);

        $startTime = microtime(true);

        for ($i = 0; $i < 2; $i++) {
            Overpass::query()
                ->node()
                ->where('amenity', 'cafe')
                ->limit(1)
                ->get();
        }

        $endTime = microtime(true);
        $elapsedTime = $endTime - $startTime;

        $this->assertLessThan(1.0, $elapsedTime, 'Throttle should be disabled.');
    }

    /** @test */
    public function it_uses_custom_api_endpoint()
    {
        $customEndpoint = 'https://overpass.kami.org/api/interpreter';

        config(['overpass.endpoint' => $customEndpoint]);

        $overpass = new \Kamz8\LaravelOverpass\Overpass();

        $this->assertEquals($customEndpoint, (string) $overpass->getClient()->getConfig('base_uri'));
    }

    /** @test */
    public function it_sets_custom_user_agent()
    {
        $appName = 'TestApp';
        $appAuthor = 'Tester';

        config(['overpass.app_name' => $appName]);
        config(['overpass.app_author' => $appAuthor]);

        $overpass = new \Kamz8\LaravelOverpass\Overpass();

        $userAgent = $overpass->getClient()->getConfig('headers')['User-Agent'];

        $this->assertEquals("{$appName} ({$appAuthor})", $userAgent);
    }
}
