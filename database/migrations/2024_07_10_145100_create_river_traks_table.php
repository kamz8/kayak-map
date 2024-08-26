<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('river_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_id')->constrained('trails')->onDelete('cascade');
            $table->json('track_points'); // Przechowuje punkty przebiegu jako JSON
            $table->timestamps();
        });
        DB::statement('CREATE INDEX river_tracks_first_lat_index ON river_tracks ((track_points->>\'$[0].lat\'))');
        DB::statement('CREATE INDEX river_tracks_first_lng_index ON river_tracks ((track_points->>\'$[0].lng\'))');
    }

    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS river_tracks_first_lat_index');
        DB::statement('DROP INDEX IF EXISTS river_tracks_first_lng_index');

        Schema::dropIfExists('river_tracks');
    }
};
