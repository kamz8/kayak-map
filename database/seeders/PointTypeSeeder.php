<?php

namespace Database\Seeders;

use App\Enums\PointType;
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
