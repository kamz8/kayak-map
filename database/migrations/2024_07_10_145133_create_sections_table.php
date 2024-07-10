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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_id')->constrained('trails')->onDelete('cascade');
            $table->string('name')->index();
            $table->text('description');
            $table->json('polygon_coordinates'); // Przechowuje współrzędne wielokąta jako JSON
            $table->timestamps();
        });;
    }

    public function down()
    {
        Schema::dropIfExists('sections');
    }
};
