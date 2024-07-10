<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Image;
use Faker\Factory as Faker;
use GuzzleHttp\Client;

class ImageSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $client = new Client();

        for ($i = 0; $i < 100; $i++) {
            $response = $client->get('https://api.pexels.com/v1/search', [
                'query' => ['query' => 'river', 'per_page' => 1, 'page' => $faker->numberBetween(1, 1000)],
                'headers' => ['Authorization' => env('PEXELS_API_KEY')]
            ]);

            $body = json_decode($response->getBody(), true);
            $imageUrl = $body['photos'][0]['src']['original'] ?? null;

            if ($imageUrl) {
                Image::create(['path' => $imageUrl]);
            }
        }
    }
}
