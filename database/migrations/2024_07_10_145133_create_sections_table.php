<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_id')->constrained('trails')->onDelete('cascade');
            $table->string('name')->index();
            $table->text('description');
            $table->json('polygon_coordinates');
            $table->integer('scenery')->default(0);
            $table->integer('difficulty')->default(0);
            $table->integer('nuisance')->default(0);
            $table->integer('cleanliness')->default(0);
            $table->timestamps();
        });

        // Dodajemy indeksy dla kluczy JSON
        DB::statement('CREATE INDEX sections_first_lat_index ON sections ((polygon_coordinates->>\'$[0].lat\'))');
        DB::statement('CREATE INDEX sections_first_lng_index ON sections ((polygon_coordinates->>\'$[0].lng\'))');
    }

    public function down()
    {
        // Usuwamy indeksy
        DB::statement('DROP INDEX IF EXISTS sections_first_lat_index');
        DB::statement('DROP INDEX IF EXISTS sections_first_lng_index');

        Schema::dropIfExists('sections');
    }
};
