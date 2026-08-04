<?php

namespace Kamz\LaravelBRouter\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class GraphCache
{
    public function remember(int|string $version, Closure $callback): array
    {
        if (! (bool) config('brouter.cache.enabled', true)) {
            return $callback();
        }

        return Cache::store((string) config('brouter.cache.store', 'redis'))->remember(
            $this->key($version),
            (int) config('brouter.cache.graph_ttl', 60 * 60 * 24 * 30),
            $callback,
        );
    }

    public function key(int|string $version): string
    {
        return 'brouter:graph:'.(string) $version;
    }
}
