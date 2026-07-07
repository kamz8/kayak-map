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
}
