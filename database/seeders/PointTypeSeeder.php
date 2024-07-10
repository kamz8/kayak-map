<?php

namespace Database\Seeders;

use App\Models\PointType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PointTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $pointTypes = [
            'Pole namiotowe',
            'Przeszkoda',
            'Niebezpieczeństwo',
            'Jaz',
            'Blokada na rzece'
        ];

        foreach ($pointTypes as $type) {
            PointType::create(['type' => $type]);
        }
    }
}
