<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Console;

use Illuminate\Support\Facades\Artisan;
use Kamz\LaravelBRouter\Services\OverpassImportService;
use Kamz\LaravelBRouter\Services\RiverMicroGraphService;
use Kamz\LaravelBRouter\Tests\TestCase;

class ImportRiverCatalogCommandTest extends TestCase
{
    /** @test */
    public function command_is_registered(): void
    {
        $this->assertArrayHasKey('brouter:import-catalog', Artisan::all());
    }

    /** @test */
    public function command_imports_a_selected_river_with_explicit_bbox(): void
    {
        $this->mock(RiverMicroGraphService::class, function ($mock): void {
            $mock->shouldReceive('riverKey')->once()->with('Obra')->andReturn('obra');
            $mock->shouldReceive('catalog')->once()->andReturn([
                ['key' => 'obra', 'name' => 'Obra', 'aliases' => [], 'country_code' => 'PL'],
            ]);
        });
        $this->mock(OverpassImportService::class, function ($mock): void {
            $mock->shouldReceive('import')->once()->withArgs(function (array $bbox, array $metadata): bool {
                return $bbox === ['south' => 52.0, 'west' => 15.0, 'north' => 52.6, 'east' => 16.0]
                    && $metadata['river_key'] === 'obra';
            })->andReturn(['import_id' => 1]);
        });

        $this->artisan('brouter:import-catalog', [
            '--river' => ['Obra'],
            '--bbox' => '52.0,15.0,52.6,16.0',
        ])->assertExitCode(0);
    }
}
