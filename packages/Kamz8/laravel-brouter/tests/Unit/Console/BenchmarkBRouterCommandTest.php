<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Console;

use Illuminate\Support\Facades\Artisan;
use Kamz\LaravelBRouter\Console\BenchmarkBRouterCommand;
use Kamz\LaravelBRouter\Tests\TestCase;

class BenchmarkBRouterCommandTest extends TestCase
{
    /** @test */
    public function command_is_registered(): void
    {
        $this->assertTrue(array_key_exists('brouter:benchmark', Artisan::all()));
    }

    /** @test */
    public function command_defines_metrics_output_contract(): void
    {
        $source = file_get_contents((new \ReflectionClass(BenchmarkBRouterCommand::class))->getFileName());

        $this->assertStringContainsString('route_time_ms', $source);
        $this->assertStringContainsString('memory_mb', $source);
        $this->assertStringContainsString('EXPLAIN ANALYZE', $source);
        $this->assertStringContainsString('ST_DWithin', $source);
    }
}
