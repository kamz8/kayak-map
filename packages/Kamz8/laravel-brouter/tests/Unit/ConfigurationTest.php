<?php
// tests/Unit/ConfigurationTest.php

namespace Kamz\LaravelBRouter\Tests\Unit;

use Tests\TestCase;

class ConfigurationTest extends TestCase
{
    /** @test */
    public function config_file_is_publishable()
    {
        $this->artisan('vendor:publish', [
            '--provider' => 'Kamz\LaravelBRouter\BRouterServiceProvider',
            '--tag' => 'brouter-config'
        ])->assertExitCode(0);

        $this->assertFileExists(config_path('brouter.php'));
    }

    /** @test */
    public function config_values_are_correct()
    {
        $config = config('brouter');

        $this->assertEquals('river', $config['default_profile']);
        $this->assertTrue($config['cache']['enabled']);
        $this->assertEquals(3600, $config['cache']['duration']);
        $this->assertEquals(1000, $config['routing']['max_snap_distance']);
    }

    /** @test */
    public function environment_variables_override_config()
    {
        putenv('BROUTER_CACHE_STORE=array');
        putenv('OVERPASS_ENDPOINT=https://custom-overpass.example.com/api');

        $this->refreshApplication();

        $config = config('brouter');

        $this->assertEquals('array', $config['cache']['store']);
        $this->assertEquals('https://custom-overpass.example.com/api', $config['overpass']['endpoint']);
    }
}
