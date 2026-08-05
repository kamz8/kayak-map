<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Illuminate\Support\Facades\Http;
use Kamz\LaravelBRouter\Exceptions\OverpassException;
use Kamz\LaravelBRouter\Services\OverpassDataProvider;
use Kamz\LaravelBRouter\Tests\TestCase;

class OverpassDataProviderTest extends TestCase
{
    /** @test */
    public function it_fetches_named_waterway_from_overpass(): void
    {
        config()->set('overpass.endpoint', 'https://overpass.test/api/interpreter');
        config()->set('overpass.timeout', 10);
        config()->set('overpass.app_name', 'KayakMapTest');
        config()->set('overpass.app_author', 'tests@example.com');

        Http::fake([
            'overpass.test/*' => Http::response(['elements' => [['id' => 123]]], 200),
        ]);

        $provider = new OverpassDataProvider();
        $result = $provider->getWaterwayByName('Wda', [
            'south' => 53.1,
            'west' => 18.1,
            'north' => 53.9,
            'east' => 18.9,
        ]);

        $this->assertSame(['elements' => [['id' => 123]]], $result);

        Http::assertSent(function ($request) {
            $data = $request['data'];

            return $request->method() === 'POST'
                && str_contains($request->header('Content-Type')[0] ?? '', 'application/x-www-form-urlencoded')
                && ($request->header('Accept')[0] ?? null) === '*/*'
                && ($request->header('User-Agent')[0] ?? null) === 'KayakMapTest (tests@example.com)'
                && str_contains($data, 'way["waterway"]')
                && str_contains($data, '["name"="Wda"]')
                && str_contains($data, '(53.1,18.1,53.9,18.9)');
        });
    }

    /** @test */
    public function it_throws_overpass_exception_for_failed_response(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'timeout'], 500),
        ]);

        $this->expectException(OverpassException::class);

        (new OverpassDataProvider())->getWaterwayByName('Wda', [
            'south' => 53.1,
            'west' => 18.1,
            'north' => 53.9,
            'east' => 18.9,
        ]);
    }

    /** @test */
    public function it_fetches_way_only_data_for_a_named_import_corridor(): void
    {
        config()->set('overpass.endpoint', 'https://overpass.test/api/interpreter');

        Http::fake([
            'overpass.test/*' => Http::response(['elements' => []], 200),
        ]);

        (new OverpassDataProvider())->getNamedImportData('Obra', [
            'south' => 52.0,
            'west' => 15.0,
            'north' => 52.6,
            'east' => 16.0,
        ]);

        Http::assertSent(function ($request): bool {
            $query = $request['data'];

            return str_contains($query, 'way["waterway"]["name"="Obra"]')
                && ! str_contains($query, 'relation["waterway"]')
                && str_contains($query, '(52,15,52.6,16)');
        });
    }

    /** @test */
    public function it_fetches_all_import_features_and_preserves_the_bbox(): void
    {
        config()->set('overpass.endpoint', 'https://overpass.test/api/interpreter');

        Http::fake([
            'overpass.test/*' => Http::response(['elements' => []], 200),
        ]);

        (new OverpassDataProvider())->getImportData([
            'south' => 51.0,
            'west' => 16.0,
            'north' => 52.0,
            'east' => 17.0,
        ]);

        Http::assertSent(function ($request) {
            $query = $request['data'];

            return str_contains($query, 'node["waterway"]')
                && str_contains($query, 'way["waterway"]')
                && str_contains($query, 'relation["waterway"]')
                && str_contains($query, 'node["waterway"~"^(dam|weir|lock_gate|sluice_gate|watermill|rapids|waterfall)$"]')
                && str_contains($query, 'way["barrier"~"^(dam|weir)$"]')
                && str_contains($query, 'node["lock"="yes"]')
                && str_contains($query, 'way["natural"="water"]')
                && str_contains($query, 'relation["water"~"^(reservoir|lake)$"]')
                && str_contains($query, '(51,16,52,17)');
        });
    }

    /** @test */
    public function it_rejects_an_invalid_import_bbox(): void
    {
        $this->expectException(OverpassException::class);

        (new OverpassDataProvider())->getImportData([
            'south' => 52.0,
            'west' => 17.0,
            'north' => 51.0,
            'east' => 16.0,
        ]);
    }
}
