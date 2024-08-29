<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrailsTable extends Migration
{
    public function up(): void
    {
        Schema::create('trails', function (Blueprint $table) {
            $table->id();
            $table->string('river_name')->index();
            $table->string('trail_name')->index();
            $table->string('slug')->nullable('')->index();
            $table->longText('description')->default('');
            $table->decimal('start_lat', 17, 14)->index();
            $table->decimal('start_lng', 17, 14)->index();
            $table->decimal('end_lat', 17, 14)->index();
            $table->decimal('end_lng', 17, 14)->index();
            $table->integer('trail_length')->default(0)->index();
            $table->decimal('rating', 3, 1)->comment('Ocena własna trasy')->index()->default(0);
            $table->string('author');
            $table->enum('difficulty', ['łatwy', 'umiarkowany', 'trudny'])->default('łatwy')->index();
            $table->string('difficulty_detailed')->index();
            $table->integer('scenery')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trails');
    }
}
