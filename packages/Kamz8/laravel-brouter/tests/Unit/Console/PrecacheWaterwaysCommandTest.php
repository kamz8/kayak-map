<?php
// tests/Unit/Console/PrecacheWaterwaysCommandTest.php

namespace Kamz\LaravelBRouter\Tests\Unit\Console;

use Tests\TestCase;
use Kamz\LaravelBRouter\Console\PrecacheWaterwaysCommand;
use Illuminate\Support\Facades\Artisan;

class PrecacheWaterwaysCommandTest extends TestCase
{
    /** @test */
    public function command_is_registered()
    {
        $this->assertTrue(
            Artisan::has('brouter:precache')
        );
    }

    /** @test */
    public function command_can_be_executed_with_bbox()
    {
        $this->artisan('brouter:precache', [
            'bbox' => '51.0,16.0,52.0,17.0'
        ])->assertExitCode(0);
    }

    /** @test */
    public function command_accepts_name_option()
    {
        $this->artisan('brouter:precache', [
            'bbox' => '51.0,16.0,52.0,17.0',
            '--name' => 'Odra'
        ])->assertExitCode(0);
    }
}
