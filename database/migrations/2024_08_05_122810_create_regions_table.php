<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['country', 'state', 'city', 'geographic_area']);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_root')->default(false);
            $table->geography('center_point',subtype: 'point')->nullable(); // Single point for the center
            $table->geography('area', subtype: 'polygon')->nullable(); // Polygon for the region's area
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('regions')->onDelete('cascade');
        });

        Schema::create('trail_region', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_id')->constrained('trails')->onDelete('cascade');
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trail_region');
        Schema::dropIfExists('regions');
    }
};
