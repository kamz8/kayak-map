<?php

use Kamz8\LaravelOverpass\Tests\TestCase;
use Kamz8\LaravelOverpass\Helpers\RouteHelper;

uses(TestCase::class);

it('calculates route length correctly', function () {
    $points = [
        [53.5544308, 16.9887918],
        [53.5997899, 17.181988]
    ];

    $length = RouteHelper::calculateLength($points);

    expect($length)
        ->toBeGreaterThan(0);
});

it('finds route from point A to B', function () {
    $start = [53.5544308, 16.9887918];
    $end = [53.5997899, 17.181988];

    $route = RouteHelper::findRoute($start, $end);

    expect($route)
        ->toBeArray()
        ->and(count($route))->toBeGreaterThan(0)
        ->and($route[0])->toBe($start)
        ->and(end($route))->toBe($end);
});
