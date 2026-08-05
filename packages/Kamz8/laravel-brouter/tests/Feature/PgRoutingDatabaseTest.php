<?php

namespace Kamz\LaravelBRouter\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Kamz\LaravelBRouter\Tests\TestCase;

class PgRoutingDatabaseTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.brouter', [
            'driver' => 'pgsql',
            'host' => getenv('BROUTER_DB_HOST') ?: 'brouter-db',
            'port' => getenv('BROUTER_DB_PORT') ?: 5432,
            'database' => getenv('BROUTER_DB_DATABASE') ?: 'brouter',
            'username' => getenv('BROUTER_DB_USERNAME') ?: 'brouter',
            'password' => getenv('BROUTER_DB_PASSWORD') ?: 'brouter',
            'charset' => 'utf8',
            'prefix' => '',
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]);
    }

    /** @test */
    public function brouter_database_has_pgrouting_enabled(): void
    {
        $extension = DB::connection('brouter')->selectOne(
            "select count(*) as count from pg_extension where extname = 'pgrouting'"
        );

        $version = DB::connection('brouter')->selectOne('select pgr_version()');

        $this->assertSame(1, (int) $extension->count);
        $this->assertNotEmpty($version->pgr_version);
    }

    /** @test */
    public function waterway_edges_have_pgrouting_columns_and_graph_tiles(): void
    {
        $columns = DB::connection('brouter')->select(
            "select column_name from information_schema.columns where table_name = 'waterway_edges' and column_name in ('source', 'target', 'cost', 'reverse_cost') order by column_name"
        );

        $this->assertSame(['cost', 'reverse_cost', 'source', 'target'], array_map(
            static fn (object $column): string => $column->column_name,
            $columns,
        ));
        $this->assertTrue(DB::connection('brouter')->getSchemaBuilder()->hasTable('graph_tiles'));
    }
}
