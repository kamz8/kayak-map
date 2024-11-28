<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS region_trail_statistics");

        DB::statement("
            CREATE VIEW region_trail_statistics AS
            WITH RECURSIVE region_hierarchy AS (
                SELECT id, parent_id, name, slug
                FROM regions
                WHERE parent_id IS NULL

                UNION ALL

                SELECT r.id, r.parent_id, r.name, r.slug
                FROM regions r
                INNER JOIN region_hierarchy rh ON r.parent_id = rh.id
            )
            SELECT
                r.id as region_id,
                r.name as region_name,
                r.slug as region_slug,
                COUNT(DISTINCT t.id) as total_trails,
                COALESCE(ROUND(AVG(t.rating), 2), 0) as avg_rating,
                COALESCE(SUM(t.trail_length), 0) as total_length,
                SUM(CASE WHEN t.difficulty = 'łatwy' THEN 1 ELSE 0 END) as easy_trails,
                SUM(CASE WHEN t.difficulty = 'umiarkowany' THEN 1 ELSE 0 END) as moderate_trails,
                SUM(CASE WHEN t.difficulty = 'trudny' THEN 1 ELSE 0 END) as hard_trails,
                COALESCE(ROUND(AVG(t.scenery), 2), 0) as avg_scenery,
                JSON_ARRAYAGG(
                    DISTINCT
                    CASE
                        WHEN t.rating >= 4.0
                        THEN JSON_OBJECT(
                            'id', t.id,
                            'name', t.trail_name,
                            'rating', t.rating,
                            'slug', t.slug
                        )
                        ELSE NULL
                    END
                ) as top_rated_trails
            FROM region_hierarchy rh
            JOIN regions r ON r.id = rh.id
            LEFT JOIN trail_region tr ON r.id = tr.region_id
            LEFT JOIN trails t ON tr.trail_id = t.id
            GROUP BY r.id, r.name, r.slug
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS region_trail_statistics');
    }
};
