<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Kamz\LaravelBRouter\Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    if (env('BROUTER_REAL_IMPORT_TEST') !== '1') {
        $this->markTestSkipped('Set BROUTER_REAL_IMPORT_TEST=1 to run the real Overpass/PostGIS import test.');
    }

    config()->set('database.connections.brouter', [
        'driver' => 'pgsql',
        'host' => env('BROUTER_DB_HOST', 'brouter-db'),
        'port' => env('BROUTER_DB_PORT', '5432'),
        'database' => env('BROUTER_DB_DATABASE', 'brouter'),
        'username' => env('BROUTER_DB_USERNAME', 'brouter'),
        'password' => env('BROUTER_DB_PASSWORD', 'brouter'),
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
    ]);

    DB::connection('brouter')->statement('DROP SCHEMA public CASCADE');
    DB::connection('brouter')->statement('CREATE SCHEMA public');
    DB::connection('brouter')->statement('CREATE EXTENSION IF NOT EXISTS postgis');

    $migrationStatus = Artisan::call('migrate', [
        '--database' => 'brouter',
        '--path' => realpath(__DIR__.'/../../database/migrations'),
        '--realpath' => true,
        '--force' => true,
    ]);

    expect($migrationStatus)->toBe(0);
});

it('imports real Overpass waterways into PostGIS through the precache command', function (): void {
    $importStatus = Artisan::call('brouter:precache', [
        'bbox' => '51.105,17.000,51.130,17.060',
        '--name' => 'Odra',
    ]);

    $this->assertSame(0, $importStatus, Artisan::output());

    expect(DB::connection('brouter')->table('imports')->where('status', 'published')->where('is_active', true)->count())->toBe(1)
        ->and(DB::connection('brouter')->table('waterway_edges')->count())->toBeGreaterThan(0)
        ->and(DB::connection('brouter')->table('graphs')->count())->toBeGreaterThan(0);
});
