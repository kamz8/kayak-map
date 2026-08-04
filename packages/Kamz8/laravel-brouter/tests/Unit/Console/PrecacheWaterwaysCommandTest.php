<?php
// tests/Unit/Console/PrecacheWaterwaysCommandTest.php

namespace Kamz\LaravelBRouter\Tests\Unit\Console;

use Tests\TestCase;
use Kamz\LaravelBRouter\Console\PrecacheWaterwaysCommand;
use Illuminate\Support\Facades\Artisan;
use Kamz\LaravelBRouter\Services\OverpassImportService;

class PrecacheWaterwaysCommandTest extends TestCase
{
    /** @test */
    public function command_is_registered()
    {
        $this->assertTrue(
            array_key_exists('brouter:precache', Artisan::all())
        );
    }

    /** @test */
    public function command_can_be_executed_with_bbox()
    {
        $this->mock(OverpassImportService::class, function ($mock) {
            $mock->shouldReceive('import')->once()->andReturn([
                'import_id' => 1,
                'node_count' => 1,
                'edge_count' => 1,
            ]);
        });

        $this->artisan('brouter:precache', [
            'bbox' => '51.0,16.0,52.0,17.0'
        ])->assertExitCode(0);
    }

    /** @test */
    public function command_accepts_name_option()
    {
        $this->mock(OverpassImportService::class, function ($mock) {
            $mock->shouldReceive('import')->once()->andReturn([
                'import_id' => 1,
                'node_count' => 1,
                'edge_count' => 1,
            ]);
        });

        $this->artisan('brouter:precache', [
            'bbox' => '51.0,16.0,52.0,17.0',
            '--name' => 'Odra'
        ])->assertExitCode(0);
    }
}
