<?php

use Kamz8\LaravelOverpass\Helpers\BoundingBoxHelper;

it('calculates bounding box correctly', function () {
    $startLat = 51.5;
    $startLng = -0.1;
    $endLat = 51.6;
    $endLng = 0.1;

    $bbox = BoundingBoxHelper::calculateBoundingBox($startLat, $startLng, $endLat, $endLng);

    expect($bbox)
        ->toBeArray()
        ->toHaveCount(4)
        ->and($bbox['south'])->toBe($startLat)
        ->and($bbox['west'])->toBe($startLng)
        ->and($bbox['north'])->toBe($endLat)
        ->and($bbox['east'])->toBe($endLng);
});

it('throws an exception for invalid coordinates', function () {
    $startLat = 200; // Nieprawidłowa szerokość geograficzna
    $startLng = -0.1;
    $endLat = 51.6;
    $endLng = 0.1;

    expect(fn() => BoundingBoxHelper::calculateBoundingBox($startLat, $startLng, $endLat, $endLng))
        ->toThrow(InvalidArgumentException::class);
});

it('handles negative longitude correctly', function () {
    $startLat = 51.5;
    $startLng = -0.5;
    $endLat = 51.6;
    $endLng = -0.3;

    $bbox = BoundingBoxHelper::calculateBoundingBox($startLat, $startLng, $endLat, $endLng);

    expect($bbox)
        ->toBeArray()
        ->toHaveCount(4)
        ->and($bbox['south'])->toBe($startLat)
        ->and($bbox['west'])->toBe($startLng)
        ->and($bbox['north'])->toBe($endLat)
        ->and($bbox['east'])->toBe($endLng);
});

it('generates bounding box string correctly', function () {
    $startLat = 51.5;
    $startLng = -0.1;
    $endLat = 51.6;
    $endLng = 0.1;

    $bboxString = BoundingBoxHelper::generateBBox($startLat, $startLng, $endLat, $endLng);

    expect($bboxString)
        ->toBeString()
        ->toBe("(51.500000,-0.100000,51.600000,0.100000)");
});
