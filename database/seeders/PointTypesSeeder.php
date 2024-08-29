<?php

namespace Database\Seeders;

use App\Models\PointType;
use Illuminate\Database\Seeder;

class PointTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pointTypes = [
            ['type' => 'biwak', 'key' => 'campsite', 'icon' => 'mdi-tent'],
            ['type' => 'jaz', 'key' => 'weir', 'icon' => 'mdi-dam'],
            ['type' => 'most', 'key' => 'bridge', 'icon' => 'mdi-bridge'],
            ['type' => 'przenoska', 'key' => 'portage', 'icon' => 'mdi-hand-pointing-right'],
            ['type' => 'sklep', 'key' => 'shop', 'icon' => 'mdi-store'],
            ['type' => 'stanica', 'key' => 'marina', 'icon' => 'mdi-home-group'],
            ['type' => 'ujście', 'key' => 'estuary', 'icon' => 'mdi-water-outline'],
            ['type' => 'uwaga', 'key' => 'warning', 'icon' => 'mdi-alert'],
            ['type' => 'wypływ', 'key' => 'outflow', 'icon' => 'mdi-water'],
            ['type' => 'wypożyczalnia', 'key' => 'rental', 'icon' => 'mdi-kayaking'],
            ['type' => 'niebezpieczeństwo', 'key' => 'danger', 'icon' => 'mdi-alert-octagon'],
            ['type' => 'lekarz', 'key' => 'doctor', 'icon' => 'mdi-medical-bag'],
            ['type' => 'bar', 'key' => 'bar', 'icon' => 'mdi-glass-mug-variant'],
            ['type' => 'śluza', 'key' => 'lock', 'icon' => 'mdi-gate'],
            ['type' => 'inne', 'key' => 'mixed', 'icon' => 'mdi-help-circle-outline'],
            ['type' => 'miasto', 'key' => 'city', 'icon' => 'mdi-city'],
        ];

        foreach ($pointTypes as $pointType) {
            PointType::create($pointType);
        }
    }
}
