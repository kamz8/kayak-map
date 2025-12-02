<?php

namespace Kamz\LaravelBRouter\Facades;

use Illuminate\Support\Facades\Facade;

class BRouter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Kamz\LaravelBRouter\Contracts\RouterInterface::class;
    }
}
