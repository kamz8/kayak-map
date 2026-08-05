<?php

namespace Kamz\LaravelBRouter\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Kamz\LaravelBRouter\Jobs\ImportRiverMicroGraphJob;
use Kamz\LaravelBRouter\Jobs\IndexRiverMicroGraphJob;

class RiverMicroGraphService
{
    private const CONNECTION = 'brouter';

    public function __construct(private readonly ?OverpassImportService $importService = null)
    {
    }

    public function findPublishedTile(string $riverKey, array $start, array $end): ?object
    {
        $bbox = $this->routeBbox($start, $end);
        $key = $this->tileCacheKey($riverKey, $bbox);
        $cached = $this->cacheStore()->get($key);

        if (is_array($cached)) {
            return (object) $cached;
        }

        $tile = DB::connection(self::CONNECTION)->table('graph_tiles')
            ->where('river_key', $this->riverKey($riverKey))
            ->where('status', 'published')
            ->whereRaw(
                'ST_Intersects(bbox, ST_MakeEnvelope(?, ?, ?, ?, 4326))',
                [$bbox['west'], $bbox['south'], $bbox['east'], $bbox['north']]
            )
            ->orderByDesc('updated_at')
            ->first();

        if ($tile === null) {
            return null;
        }

        $value = [
            'id' => (int) $tile->id,
            'import_id' => (int) $tile->import_id,
            'version' => (string) $tile->version,
            'status' => (string) $tile->status,
            'river_key' => (string) $tile->river_key,
        ];
        $this->cacheStore()->put($key, $value, now()->addDay());

        return (object) $value;
    }

    public function routeBbox(array $start, array $end): array
    {
        $bufferKm = (float) config('river-catalog.tile_buffer_km', 2);
        $bufferLat = $bufferKm / 111.32;
        $centerLat = ($start['lat'] + $end['lat']) / 2;
        $bufferLng = $bufferKm / (111.32 * max(cos(deg2rad($centerLat)), 0.01));

        return [
            'south' => min($start['lat'], $end['lat']) - $bufferLat,
            'west' => min($start['lng'], $end['lng']) - $bufferLng,
            'north' => max($start['lat'], $end['lat']) + $bufferLat,
            'east' => max($start['lng'], $end['lng']) + $bufferLng,
        ];
    }

    public function riverKey(string $riverName): string
    {
        return Str::slug(Str::ascii(trim($riverName)));
    }

    public function ensureTemporaryTile(string $riverKey, array $bbox, array $metadata = []): object
    {
        if ($this->importService === null) {
            throw new \LogicException('River import service is not configured.');
        }

        $riverKey = $this->riverKey($riverKey);
        $lock = $this->cacheStore()->lock('brouter:tile:'.$riverKey.':'.hash('sha256', json_encode($bbox, JSON_THROW_ON_ERROR)).':lock', 60);

        if (! $lock->get()) {
            throw new \RuntimeException('River micro-graph import is already in progress.');
        }

        try {
            $result = $this->importService->importTemporary($bbox, $metadata + ['river_key' => $riverKey]);
        } finally {
            $lock->release();
        }

        return $this->registerTemporaryTile($riverKey, $bbox, $result, $metadata);
    }

    public function registerTemporaryTile(string $riverKey, array $bbox, array $result, array $metadata = []): object
    {
        $import = DB::connection(self::CONNECTION)->table('imports')->where('id', $result['import_id'])->first();
        $version = (string) ($import->version ?? str()->uuid());
        $bboxHash = hash('sha256', json_encode($bbox, JSON_THROW_ON_ERROR));
        $riverKey = $this->riverKey($riverKey);

        DB::connection(self::CONNECTION)->table('graph_tiles')->insert([
            'river_key' => $riverKey,
            'country_code' => $metadata['country_code'] ?? null,
            'import_id' => $result['import_id'],
            'bbox_hash' => $bboxHash,
            'version' => $version,
            'status' => 'temporary',
            'bbox' => DB::raw(sprintf(
                'ST_MakeEnvelope(%F, %F, %F, %F, 4326)',
                $bbox['west'],
                $bbox['south'],
                $bbox['east'],
                $bbox['north'],
            )),
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (object) [
            'import_id' => (int) $result['import_id'],
            'version' => $version,
            'status' => 'temporary',
            'river_key' => $riverKey,
        ];
    }

    public function queueImport(string $riverKey, array $bbox, array $metadata = []): void
    {
        ImportRiverMicroGraphJob::dispatch($riverKey, $bbox, $metadata);
    }

    public function queueIndexing(object $temporaryTile): void
    {
        IndexRiverMicroGraphJob::dispatch((int) $temporaryTile->import_id);
    }

    private function tileCacheKey(string $riverKey, array $bbox): string
    {
        return 'brouter:tile:'.$this->riverKey($riverKey).':'.hash('sha256', json_encode($bbox, JSON_THROW_ON_ERROR)).':published';
    }

    private function cacheStore(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store(config('brouter.cache.store', 'redis'));
    }

    /**
     * @return array<int, array{key: string, name: string, aliases: array<int, string>, country_code: string}>
     */
    public function catalog(): array
    {
        return config('river-catalog.rivers', []);
    }
}
