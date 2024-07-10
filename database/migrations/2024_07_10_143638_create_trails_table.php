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
            $table->integer('trail_length');
            $table->string('author');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trails');
    }
}
