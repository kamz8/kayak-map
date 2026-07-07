<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Illuminate\Support\Facades\Cache;
use Kamz\LaravelBRouter\Services\RouteCache;
use Kamz\LaravelBRouter\Tests\TestCase;

class RouteCacheTest extends TestCase
{
    /** @test */
    public function it_caches_osm_data_by_normalized_river_name_and_bbox(): void
    {
        config()->set('brouter.cache.enabled', true);
        config()->set('brouter.cache.store', 'array');
        Cache::store('array')->flush();

        $cache = new RouteCache();
        $calls = 0;
        $bbox = ['south' => 53.123456, 'west' => 18.123456, 'north' => 53.987654, 'east' => 18.987654];

        $first = $cache->rememberOsm('  Wda River ', $bbox, function () use (&$calls) {
            $calls++;

            return ['elements' => [['id' => 1]]];
        });

        $second = $cache->rememberOsm('wda-river', $bbox, function () use (&$calls) {
            $calls++;

            return ['elements' => [['id' => 2]]];
        });

        $this->assertSame(['elements' => [['id' => 1]]], $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, $calls);
    }

    /** @test */
    public function it_bypasses_cache_when_disabled(): void
    {
        config()->set('brouter.cache.enabled', false);

        $cache = new RouteCache();
        $calls = 0;

        $cache->rememberOsm('Wda', ['south' => 1, 'west' => 2, 'north' => 3, 'east' => 4], function () use (&$calls) {
            $calls++;

            return ['first'];
        });

        $second = $cache->rememberOsm('Wda', ['south' => 1, 'west' => 2, 'north' => 3, 'east' => 4], function () use (&$calls) {
            $calls++;

            return ['second'];
        });

        $this->assertSame(['second'], $second);
        $this->assertSame(2, $calls);
    }
}
