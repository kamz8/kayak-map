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
        Schema::create('imageables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->constrained('images')->onDelete('cascade');
            $table->morphs('imageable');
            $table->boolean('is_main')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('imageables');
    }
};
