<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    }

    public function down()
    {
        Schema::dropIfExists('river_tracks');
    }
};
