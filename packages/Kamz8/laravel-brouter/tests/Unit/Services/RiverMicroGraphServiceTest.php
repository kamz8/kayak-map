<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Kamz\LaravelBRouter\Services\RiverMicroGraphService;
use Kamz\LaravelBRouter\Tests\TestCase;

class RiverMicroGraphServiceTest extends TestCase
{
    /** @test */
    public function it_normalizes_river_keys_and_builds_a_buffered_corridor(): void
    {
        $service = new RiverMicroGraphService();
        $bbox = $service->routeBbox(
            ['lat' => 52.0, 'lng' => 16.0],
            ['lat' => 52.5, 'lng' => 16.5],
        );

        $this->assertSame('czarna-hancza', $service->riverKey(' Czarna Hańcza '));
        $this->assertLessThan(52.0, $bbox['south']);
        $this->assertGreaterThan(52.5, $bbox['north']);
        $this->assertLessThan(16.0, $bbox['west']);
        $this->assertGreaterThan(16.5, $bbox['east']);
    }

    /** @test */
    public function it_exposes_fifty_seed_rivers(): void
    {
        $catalog = (new RiverMicroGraphService())->catalog();

        $this->assertCount(50, $catalog);
        $this->assertContains('Obra', array_column($catalog, 'name'));
        $this->assertContains('Wisła', array_column($catalog, 'name'));
    }
}
