<?php

use Kamz8\LaravelOverpass\Tests\TestCase;
use Kamz8\LaravelOverpass\Helpers\BoundingBoxHelper;

uses(TestCase::class);

it('calculates bounding box with 10% buffer', function () {
    $start = [53.5544308, 16.9887918];
    $end = [53.5997899, 17.181988];

    $boundingBox = BoundingBoxHelper::calculateWithBuffer($start, $end, 0.10);

    expect($boundingBox)
        ->toBeArray()
        ->and(count($boundingBox))->toBe(4)
        ->and($boundingBox[0])->toBeLessThan($start[0])
        ->and($boundingBox[1])->toBeLessThan($start[1])
        ->and($boundingBox[2])->toBeGreaterThan($end[0])
        ->and($boundingBox[3])->toBeGreaterThan($end[1]);
});

it('returns correct bounding box without buffer', function () {
    $start = [53.5544308, 16.9887918];
    $end = [53.5997899, 17.181988];

    $boundingBox = BoundingBoxHelper::calculateWithBuffer($start, $end, 0.0);

    expect($boundingBox)
        ->toBe([$start[0], $start[1], $end[0], $end[1]]);
});
