<?php

namespace Kamz\LaravelBRouter\Services;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RouteCache
{
    public function rememberOsm(string $riverName, array $bbox, Closure $callback): array
    {
        if (! (bool) config('brouter.cache.enabled', true)) {
            return $callback();
        }

        return Cache::store((string) config('brouter.cache.store', 'redis'))->remember(
            $this->osmKey($riverName, $bbox),
            (int) config('brouter.cache.osm_ttl', 60 * 60 * 24 * 30),
            $callback,
        );
    }

    public function rememberRoute(string $graphVersion, array $request, Closure $callback): array
    {
        if (! (bool) config('brouter.cache.enabled', true)) {
            return $callback();
        }

        return Cache::store((string) config('brouter.cache.store', 'redis'))->remember(
            $this->routeKey($graphVersion, $request),
            (int) config('brouter.cache.route_ttl', 60 * 60 * 24 * 7),
            $callback,
        );
    }

    public function osmKey(string $riverName, array $bbox): string
    {
        return 'brouter:osm:'.$this->slug($riverName).':'.$this->bboxHash($bbox);
    }

    public function routeKey(string $graphVersion, array $request): string
    {
        return 'brouter:route:'.$graphVersion.':'.sha1(json_encode($request));
    }

    private function slug(string $riverName): string
    {
        return Str::slug(trim($riverName));
    }

    private function bboxHash(array $bbox): string
    {
        $rounded = [];

        foreach (['south', 'west', 'north', 'east'] as $key) {
            $rounded[$key] = round((float) $bbox[$key], 4);
        }

        return sha1(json_encode($rounded));
    }
}
