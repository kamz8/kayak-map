<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Kamz\LaravelBRouter\Services\PostgisEdgeSnapper;
use Kamz\LaravelBRouter\Tests\TestCase;

class PostgisEdgeSnapperTest extends TestCase
{
    /** @test */
    public function it_uses_postgis_nearest_edge_functions(): void
    {
        $source = file_get_contents((new \ReflectionClass(PostgisEdgeSnapper::class))->getFileName());

        $this->assertStringContainsString('ST_DWithin', $source);
        $this->assertStringContainsString('ST_ClosestPoint', $source);
        $this->assertStringContainsString('ST_LineLocatePoint', $source);
        $this->assertStringContainsString('edge_distance_m', $source);
    }
}
