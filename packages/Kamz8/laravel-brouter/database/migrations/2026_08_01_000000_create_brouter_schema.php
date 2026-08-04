<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'brouter';

    public function up(): void
    {
        Schema::connection($this->connection)->create('imports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('version')->unique();
            $table->string('status', 32)->default('staging');
            $table->boolean('is_active')->default(false);
            $table->geometry('bbox', subtype: 'polygon', srid: 4326)->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('failed_at')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('waterways', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->string('osm_id', 64);
            $table->string('name')->nullable();
            $table->string('waterway', 64)->nullable();
            $table->geometry('geometry', subtype: 'linestring', srid: 4326);
            $table->jsonb('source_tags');
            $table->timestampsTz();
            $table->unique(['id', 'import_id']);
            $table->unique(['import_id', 'osm_id']);
        });

        Schema::connection($this->connection)->create('waterway_nodes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->string('osm_id', 64);
            $table->geometry('geometry', subtype: 'point', srid: 4326);
            $table->jsonb('source_tags')->nullable();
            $table->timestampsTz();
            $table->unique(['id', 'import_id']);
            $table->unique(['import_id', 'osm_id']);
        });

        Schema::connection($this->connection)->create('waterway_edges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->foreignId('waterway_id')->nullable();
            $table->foreignId('from_node_id');
            $table->foreignId('to_node_id');
            $table->string('way_id', 64)->nullable();
            $table->unsignedInteger('component_id')->default(0);
            $table->boolean('is_bidirectional')->default(true);
            $table->boolean('is_water_body_crossing')->default(false);
            $table->geometry('geometry', subtype: 'linestring', srid: 4326);
            $table->double('distance_m');
            $table->jsonb('source_tags');
            $table->timestampsTz();
            $table->foreign(['waterway_id', 'import_id'])->references(['id', 'import_id'])->on('waterways')->nullOnDelete();
            $table->foreign(['from_node_id', 'import_id'])->references(['id', 'import_id'])->on('waterway_nodes')->cascadeOnDelete();
            $table->foreign(['to_node_id', 'import_id'])->references(['id', 'import_id'])->on('waterway_nodes')->cascadeOnDelete();
        });

        Schema::connection($this->connection)->create('waterway_features', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->string('osm_id', 64);
            $table->string('feature_type', 64);
            $table->string('name')->nullable();
            $table->foreignId('nearest_edge_id')->nullable();
            $table->boolean('is_navigable')->nullable();
            $table->double('routing_penalty')->default(0);
            $table->boolean('portage_required')->default(false);
            $table->string('warning_level', 32)->nullable();
            $table->geometry('geometry', srid: 4326);
            $table->jsonb('source_tags');
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();
            $table->unique(['import_id', 'osm_id', 'feature_type']);
            $table->foreign(['nearest_edge_id', 'import_id'])->references(['id', 'import_id'])->on('waterway_edges')->nullOnDelete();
        });

        Schema::connection($this->connection)->create('water_bodies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->string('osm_id', 64);
            $table->string('water_type', 64)->nullable();
            $table->string('name')->nullable();
            $table->geometry('geometry', subtype: 'multipolygon', srid: 4326);
            $table->jsonb('source_tags');
            $table->timestampsTz();
            $table->unique(['import_id', 'osm_id']);
        });

        Schema::connection($this->connection)->create('graphs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->uuid('version');
            $table->unsignedInteger('node_count')->default(0);
            $table->unsignedInteger('edge_count')->default(0);
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('built_at')->nullable();
            $table->timestampsTz();
            $table->unique(['import_id', 'version']);
        });

        Schema::connection($this->connection)->create('routes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_id')->nullable()->constrained('imports')->nullOnDelete();
            $table->foreignId('graph_id')->nullable();
            $table->geometry('start_point', subtype: 'point', srid: 4326);
            $table->geometry('end_point', subtype: 'point', srid: 4326);
            $table->geometry('geometry', subtype: 'linestring', srid: 4326)->nullable();
            $table->double('distance_m')->nullable();
            $table->jsonb('request')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();
            $table->foreign(['graph_id', 'import_id'])->references(['id', 'import_id'])->on('graphs')->nullOnDelete();
        });

        $connection = DB::connection($this->connection);
        $connection->statement('CREATE UNIQUE INDEX imports_one_active_published_idx ON imports (is_active) WHERE status = \'published\' AND is_active = true');
        $connection->statement('CREATE INDEX waterways_geometry_gist_idx ON waterways USING gist (geometry)');
        $connection->statement('CREATE INDEX waterway_nodes_geometry_gist_idx ON waterway_nodes USING gist (geometry)');
        $connection->statement('CREATE INDEX waterway_edges_geometry_gist_idx ON waterway_edges USING gist (geometry)');
        $connection->statement('CREATE INDEX waterway_features_geometry_gist_idx ON waterway_features USING gist (geometry)');
        $connection->statement('CREATE INDEX water_bodies_geometry_gist_idx ON water_bodies USING gist (geometry)');
        $connection->statement('CREATE INDEX routes_geometry_gist_idx ON routes USING gist (geometry)');
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('routes');
        Schema::connection($this->connection)->dropIfExists('graphs');
        Schema::connection($this->connection)->dropIfExists('water_bodies');
        Schema::connection($this->connection)->dropIfExists('waterway_features');
        Schema::connection($this->connection)->dropIfExists('waterway_edges');
        Schema::connection($this->connection)->dropIfExists('waterway_nodes');
        Schema::connection($this->connection)->dropIfExists('waterways');
        Schema::connection($this->connection)->dropIfExists('imports');
    }
};
