<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_id')->constrained('trails')->onDelete('cascade');
            $table->string('name')->index();
            $table->text('description');
            $table->json('polygon_coordinates')->index()->nullable();
            $table->integer('scenery')->default(0);
            $table->string('difficulty')->nullable();
            $table->string('difficulty_detailed')->nullable();
            $table->string('nuisance')->default(0);
            $table->string('cleanliness')->default(0);
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('sections');

    }
};
