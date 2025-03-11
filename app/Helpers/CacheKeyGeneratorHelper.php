<?php

namespace App\Helpers;

/*
 * Helps to manage cache key for route
 * */

class CacheKeyGeneratorHelper
{
    public static function forRequest(): string
    {
        $url = request()->url();
        $queryParams = request()->query();

        ksort($queryParams);
        $queryString = http_build_query($queryParams);

        return sha1("{$url}?{$queryString}");
    }

    public static function forCustomKey(string $prefix, array $params = []): string
    {
        ksort($params);
        $serializedParams = json_encode($params);

        return sha1("{$prefix}:{$serializedParams}");
    }
}
