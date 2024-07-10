<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trail;
use App\Models\Section;
use App\Models\Image;
use Illuminate\Support\Facades\DB;

class ImageableSeeder extends Seeder
{
    public function run()
    {
        $trails = Trail::all();
        $sections = Section::all();
        $images = Image::all();

        foreach ($trails as $trail) {
            foreach ($images->random(10) as $index => $image) {
                DB::table('imageables')->insert([
                    'image_id' => $image->id,
                    'imageable_id' => $trail->id,
                    'imageable_type' => 'App\Models\Trail',
                    'is_main' => $index === 0,
                    'order' => $index,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        foreach ($sections as $section) {
            foreach ($images->random(5) as $index => $image) {
                DB::table('imageables')->insert([
                    'image_id' => $image->id,
                    'imageable_id' => $section->id,
                    'imageable_type' => 'App\Models\Section',
                    'is_main' => $index === 0,
                    'order' => $index,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
