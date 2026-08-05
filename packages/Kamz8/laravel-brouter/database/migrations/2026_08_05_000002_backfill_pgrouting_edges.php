<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'brouter';

    public function up(): void
    {
        DB::connection($this->connection)->statement(<<<'SQL'
UPDATE waterway_edges
SET source = from_node_id,
    target = to_node_id,
    cost = distance_m,
    reverse_cost = CASE WHEN is_bidirectional THEN distance_m ELSE -1 END
WHERE source IS NULL
   OR target IS NULL
   OR cost IS NULL
   OR reverse_cost IS NULL
SQL);
    }

    public function down(): void
    {
        DB::connection($this->connection)->statement(<<<'SQL'
UPDATE waterway_edges
SET source = NULL,
    target = NULL,
    cost = NULL,
    reverse_cost = NULL
SQL);
    }
};
