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
//            $table->foreignId('parent_section_id')->default(null)->index()->constrained('sections')->nullOnDelete(); prawdopodobnie przyda się zagnieżdzanie regionów
            $table->string('name')->index();
            $table->text('description');
            $table->json('polygon_coordinates');
            $table->integer('scenery')->default(0); // wartość domyślna 0
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sections');
    }
};
