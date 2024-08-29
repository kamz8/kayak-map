<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DBInitialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PointTypesSeeder::class,
            RegionSeeder::class,
            TrailDifficultyDefinitionSeeder::class
        ]);
    }
}
