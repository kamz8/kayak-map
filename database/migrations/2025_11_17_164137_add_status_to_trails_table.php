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
        Schema::table('trails', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'draft', 'archived'])
                ->default('active')
                ->after('rating')
                ->comment('Status szlaku: active, inactive, draft, archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
