<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'brouter';

    public function up(): void
    {
        DB::connection($this->connection)->statement('CREATE EXTENSION IF NOT EXISTS postgis');
        DB::connection($this->connection)->statement('CREATE EXTENSION IF NOT EXISTS pgrouting');
    }

    public function down(): void
    {
        DB::connection($this->connection)->statement('DROP EXTENSION IF EXISTS pgrouting');
    }
};
