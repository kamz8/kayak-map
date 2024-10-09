<?php

use Kamz8\LaravelOverpass\Helpers\RouteHelper;

it('generates route correctly between two valid points', function () {
    $startLat = 51.5;
    $startLng = -0.1;
    $endLat = 51.6;
    $endLng = 0.1;

    $route = RouteHelper::generateRoute($startLat, $startLng, $endLat, $endLng);

    expect($route)
        ->toBeArray()
        ->not->toBeEmpty()
        ->and($route[0])->toBe('51.5,-0.1')
        ->and(end($route))->toBe('51.6,0.1');
});

it('throws an exception when generating route with invalid coordinates', function () {
    $startLat = 200; // Nieprawidłowa szerokość geograficzna
    $startLng = -0.1;
    $endLat = 51.6;
    $endLng = 0.1;

    expect(fn() => RouteHelper::generateRoute($startLat, $startLng, $endLat, $endLng))
        ->toThrow(InvalidArgumentException::class);
});

it('generates empty route for identical start and end points', function () {
    $startLat = 51.5;
    $startLng = -0.1;
    $endLat = 51.5;
    $endLng = -0.1;

    $route = RouteHelper::generateRoute($startLat, $startLng, $endLat, $endLng);

    expect($route)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($route[0])->toBe('51.5,-0.1');
});
