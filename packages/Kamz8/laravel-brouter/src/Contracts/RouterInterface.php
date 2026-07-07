<?php

namespace Kamz\LaravelBRouter\Contracts;

use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Models\RouteResult;

interface RouterInterface
{
    public function findRoute(RouteRequestData $request): RouteResult;
}
