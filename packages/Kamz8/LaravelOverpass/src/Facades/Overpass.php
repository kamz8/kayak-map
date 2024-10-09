<?php

namespace Kamz8\LaravelOverpass\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static raw(string $query)
 * @method static query()
 */
class Overpass extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'overpass';
    }
}
