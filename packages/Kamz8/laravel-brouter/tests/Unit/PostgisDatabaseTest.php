<?php

namespace Kamz\LaravelBRouter\Tests\Unit;

use Kamz\LaravelBRouter\Tests\TestCase;

class PostgisDatabaseTest extends TestCase
{
    /** @test */
    public function it_configures_brouter_without_replacing_the_default_connection(): void
    {
        $config = require dirname(__DIR__, 5).'/config/database.php';

        $this->assertNotSame('brouter', $config['default']);
        $this->assertSame('pgsql', $config['connections']['brouter']['driver']);
        $this->assertSame('brouter', $config['connections']['brouter']['database']);
        $this->assertSame('brouter-db', $config['connections']['brouter']['host']);
    }

    /** @test */
    public function it_loads_the_brouter_migrations(): void
    {
        $providerSource = file_get_contents(dirname(__DIR__, 2).'/src/BRouterServiceProvider.php');

        $this->assertStringContainsString('loadMigrationsFrom', $providerSource);
        $this->assertStringContainsString('../database/migrations', $providerSource);
    }

    /** @test */
    public function migration_files_define_the_postgis_schema_contract(): void
    {
        $migrationDirectory = realpath(__DIR__.'/../../database/migrations');
        $migrationFiles = glob($migrationDirectory.'/*.php');
        $migrationSource = implode('', array_map(
            static fn (string $file): string => file_get_contents($file),
            $migrationFiles
        ));

        foreach ([
            'imports',
            'waterways',
            'waterway_nodes',
            'waterway_edges',
            'waterway_features',
            'water_bodies',
            'graphs',
            'routes',
        ] as $table) {
            $this->assertStringContainsString("'{$table}'", $migrationSource);
        }

        $this->assertStringContainsString("connection = 'brouter'", $migrationSource);
        $this->assertStringContainsString('srid: 4326', $migrationSource);
        $this->assertStringContainsString('using gist', strtolower($migrationSource));
        $this->assertStringContainsString("jsonb('source_tags')", $migrationSource);
        $this->assertStringContainsString("status = \\'published\\'", $migrationSource);
    }
}
