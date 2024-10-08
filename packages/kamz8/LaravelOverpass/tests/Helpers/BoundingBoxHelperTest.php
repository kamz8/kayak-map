<?php

namespace Kamz8\LaravelOverpass\Tests\Helpers;

use Kamz8\LaravelOverpass\Helpers\BoundingBoxHelper;
use PHPUnit\Framework\TestCase;

class BoundingBoxHelperTest extends TestCase
{
    /** @test */
    public function it_correctly_generates_bbox_with_margin()
    {
        $helper = new BoundingBoxHelper();

        $lat1 = 51.5;
        $lon1 = -0.1;
        $lat2 = 51.6;
        $lon2 = 0.1;
        $marginPercent = 10;

        $bbox = $helper->generateBBox($lat1, $lon1, $lat2, $lon2, $marginPercent);

        $south = min($lat1, $lat2);
        $north = max($lat1, $lat2);
        $west = min($lon1, $lon2);
        $east = max($lon1, $lon2);

        $latMargin = ($north - $south) * ($marginPercent / 100);
        $lonMargin = ($east - $west) * ($marginPercent / 100);

        $expectedSouth = $south - $latMargin;
        $expectedNorth = $north + $latMargin;
        $expectedWest = $west - $lonMargin;
        $expectedEast = $east + $lonMargin;

        $expectedBBox = sprintf(
            '(%f,%f,%f,%f)',
            $expectedSouth,
            $expectedWest,
            $expectedNorth,
            $expectedEast
        );

        $this->assertEquals($expectedBBox, $bbox);
    }
}
