<?php

namespace App\Services;

use App\Models\Trail;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class TrailMapGeneratorService
{
    private const CACHE_PREFIX = 'trail_map_';
    private const CACHE_TTL = 86400;

    public function getMapImage(string $slug): string
    {
        try {
            $trail = Trail::with(['riverTrack', 'points'])
                ->where('slug', $slug)
                ->firstOrFail();
            $cacheKey = $this->getCacheKey($trail);
            $storagePath = "maps/trails/map-{$trail->slug}.png";

            if (Cache::has($cacheKey)) {
                if (Storage::exists($storagePath)) {
                    $imageContent = Storage::get($storagePath);
                    if (!empty($imageContent)) {
                        return $imageContent;
                    }
                }
            }

            return $this->generateMapImage($trail, $storagePath);
        } catch (Exception $e) {
            Log::error('Map generation failed', [
                'trail_id' => $trail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function generateMapImage(Trail $trail, string $storagePath): string
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $tempPath = $tempDir . '/map-' . $trail->slug . '.png';
        $tempHtmlPath = $tempDir . '/map-' . $trail->slug . '.html';

        try {
            // Przygotuj dane
            $trailArray = $trail->toArray();
            $trailArray['difficulty'] = $trail->difficulty->value;
            $trailArray['riverTrack'] = $trail->riverTrack?->toArray();
            $trailArray['points'] = $trail->points?->toArray() ?? [];

            // Generuj HTML
            $html = view('maps.maps-map', [
                'trail' => (object)$trailArray,
                'assetUrl' => asset(''),
            ])->render();

            file_put_contents($tempHtmlPath, $html);

            Log::info('Generating map for trail', [
                'trail_id' => $trail->id,
                'slug' => $trail->slug,
                'temp_html' => $tempHtmlPath,
                'temp_path' => $tempPath
            ]);

            // Konfiguracja Browsershot
            $browsershot = Browsershot::html($html)
                ->waitUntilNetworkIdle()
                ->setNodeBinary(config('browsershot.node_binary'))
                ->setNpmBinary(config('browsershot.npm_binary'))
                ->setOption('args', [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-gpu'
                ])
                ->windowSize(800, 600)
                ->timeout(60000)
                ->setDelay(2000); // Zwiększone opóźnienie do 2s

            // Generuj screenshot
            $browsershot->save($tempPath);

            if (!file_exists($tempPath)) {
                throw new Exception('Screenshot file was not created');
            }

            $imageContent = file_get_contents($tempPath);
            if (empty($imageContent)) {
                throw new Exception('Generated image is empty');
            }

            Log::info('Image generated successfully', [
                'size' => strlen($imageContent),
                'storage_path' => $storagePath
            ]);

            // Zapisz w storage
            Storage::put($storagePath, $imageContent);
            Cache::put(
                $this->getCacheKey($trail),
                $storagePath,
                now()->addSeconds(self::CACHE_TTL)
            );

            return $imageContent;

        } catch (Exception $e) {
            Log::error('Screenshot generation failed', [
                'error' => $e->getMessage(),
                'trail_id' => $trail->id,
                'html' => $html ?? null,
            ]);
            throw $e;
        } finally {
            // Cleanup
            foreach ([$tempPath, $tempHtmlPath] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    private function getCacheKey(Trail $trail): string
    {
        return self::CACHE_PREFIX . $trail->slug;
    }
}
