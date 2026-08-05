<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'brouter';

    public function up(): void
    {
        Schema::connection($this->connection)->create('graph_tiles', function (Blueprint $table): void {
            $table->id();
            $table->string('river_key', 160);
            $table->string('country_code', 2)->nullable();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->string('bbox_hash', 64);
            $table->uuid('version')->unique();
            $table->string('status', 32)->default('temporary');
            $table->geometry('bbox', subtype: 'polygon', srid: 4326);
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();
            $table->unique(['river_key', 'bbox_hash', 'version']);
        });

        Schema::connection($this->connection)->table('waterway_edges', function (Blueprint $table): void {
            $table->unsignedBigInteger('source')->nullable();
            $table->unsignedBigInteger('target')->nullable();
            $table->double('cost')->nullable();
            $table->double('reverse_cost')->nullable();
        });

        $connection = DB::connection($this->connection);
        $connection->statement('CREATE INDEX graph_tiles_bbox_gist_idx ON graph_tiles USING gist (bbox)');
        $connection->statement('CREATE INDEX graph_tiles_river_status_idx ON graph_tiles (river_key, status)');
        $connection->statement('CREATE INDEX waterway_edges_source_idx ON waterway_edges (import_id, source)');
        $connection->statement('CREATE INDEX waterway_edges_target_idx ON waterway_edges (import_id, target)');
        $connection->statement('CREATE INDEX waterway_edges_import_geometry_geography_gist_idx ON waterway_edges USING gist ((geometry::geography))');
    }

    public function down(): void
    {
        DB::connection($this->connection)->statement('DROP INDEX IF EXISTS waterway_edges_import_geometry_geography_gist_idx');
        DB::connection($this->connection)->statement('DROP INDEX IF EXISTS waterway_edges_target_idx');
        DB::connection($this->connection)->statement('DROP INDEX IF EXISTS waterway_edges_source_idx');
        DB::connection($this->connection)->statement('DROP INDEX IF EXISTS graph_tiles_river_status_idx');
        DB::connection($this->connection)->statement('DROP INDEX IF EXISTS graph_tiles_bbox_gist_idx');

        Schema::connection($this->connection)->table('waterway_edges', function (Blueprint $table): void {
            $table->dropColumn(['source', 'target', 'cost', 'reverse_cost']);
        });
        Schema::connection($this->connection)->dropIfExists('graph_tiles');
    }
};
