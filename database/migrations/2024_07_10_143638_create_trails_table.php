<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrailsTable extends Migration
{
    public function up()
    {
        Schema::create('trails', function (Blueprint $table) {
            $table->id();
            $table->string('river_name')->index();
            $table->string('trail_name')->index();
            $table->text('description');
            $table->decimal('start_lat', 10, 7);
            $table->decimal('start_lng', 10, 7);
            $table->decimal('end_lat', 10, 7);
            $table->decimal('end_lng', 10, 7);
            $table->integer('trail_length')->index();
            $table->string('author');
            $table->enum('difficulty', ['łatwy', 'umiarkowany', 'trudny'])->index();
            $table->integer('scenery')->default(0)->index(); // wartość domyślna 0
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trails');
    }
}
