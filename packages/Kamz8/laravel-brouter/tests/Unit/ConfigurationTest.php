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
            '--tag' => 'brouter-config',
        ])->assertExitCode(0);

        $this->assertFileExists(config_path('brouter.php'));
    }

    /** @test */
    public function config_values_are_correct()
    {
        $config = config('brouter');

        $this->assertEquals('river', $config['default_profile']);
        $this->assertTrue($config['cache']['enabled']);
        $this->assertEquals(60 * 60 * 24 * 30, $config['cache']['osm_ttl']);
        $this->assertEquals(500, $config['routing']['max_snap_distance']);
    }

    /** @test */
    public function runtime_config_can_override_package_defaults()
    {
        config()->set('brouter.cache.store', 'array');
        config()->set('brouter.overpass.endpoint', 'https://custom-overpass.example.com/api');

        $config = config('brouter');

        $this->assertEquals('array', $config['cache']['store']);
        $this->assertEquals('https://custom-overpass.example.com/api', $config['overpass']['endpoint']);
    }
}
