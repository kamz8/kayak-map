<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Kamz\LaravelBRouter\Contracts\ImportRepositoryInterface;
use Kamz\LaravelBRouter\Contracts\ImportDataProviderInterface;
use Kamz\LaravelBRouter\Services\OverpassImportService;
use Kamz\LaravelBRouter\Services\WaterwayGraphBuilder;
use Kamz\LaravelBRouter\Services\WaterwayNormalizer;
use Kamz\LaravelBRouter\Tests\TestCase;
use RuntimeException;

class OverpassImportServiceTest extends TestCase
{
    /** @test */
    public function it_publishes_a_valid_import_after_building_the_graph(): void
    {
        $repository = new RecordingImportRepository();
        $provider = new class implements ImportDataProviderInterface
        {
            public function getImportData(array $bbox): array
            {
                return ['elements' => [['type' => 'way', 'id' => 1, 'tags' => ['waterway' => 'river'], 'geometry' => [
                    ['lat' => 51.0, 'lon' => 16.0],
                    ['lat' => 51.1, 'lon' => 16.1],
                ]]]];
            }

        };

        $service = new OverpassImportService($provider, $repository, new WaterwayNormalizer(), new WaterwayGraphBuilder());

        $result = $service->import([
            'south' => 51.0,
            'west' => 16.0,
            'north' => 52.0,
            'east' => 17.0,
        ]);

        $this->assertSame('published', $result['status']);
        $this->assertSame(['staging', 'importing', 'validating', 'published'], $repository->statuses);
        $this->assertSame(1, $repository->publishedImportId);
    }

    /** @test */
    public function it_marks_a_failed_import_without_publishing_it(): void
    {
        $repository = new RecordingImportRepository();
        $repository->throwOnPersist = true;
        $provider = new class implements ImportDataProviderInterface
        {
            public function getImportData(array $bbox): array
            {
                return ['elements' => []];
            }

        };

        $service = new OverpassImportService($provider, $repository, new WaterwayNormalizer(), new WaterwayGraphBuilder());

        $this->expectException(RuntimeException::class);
        try {
            $service->import(['south' => 51.0, 'west' => 16.0, 'north' => 52.0, 'east' => 17.0]);
        } finally {
            $this->assertSame(['staging', 'importing', 'failed'], $repository->statuses);
            $this->assertNull($repository->publishedImportId);
        }
    }

    /** @test */
    public function it_can_leave_an_import_temporary_for_immediate_routing(): void
    {
        $repository = new RecordingImportRepository();
        $provider = new class implements ImportDataProviderInterface
        {
            public function getImportData(array $bbox): array
            {
                return ['elements' => [['type' => 'way', 'id' => 1, 'tags' => ['waterway' => 'river'], 'geometry' => [
                    ['lat' => 51.0, 'lon' => 16.0],
                    ['lat' => 51.1, 'lon' => 16.1],
                ]]]];
            }
        };

        $service = new OverpassImportService($provider, $repository, new WaterwayNormalizer(), new WaterwayGraphBuilder());
        $result = $service->importTemporary(['south' => 51.0, 'west' => 16.0, 'north' => 52.0, 'east' => 17.0]);

        $this->assertSame('temporary', $result['status']);
        $this->assertSame(['staging', 'importing', 'temporary'], $repository->statuses);
        $this->assertNull($repository->publishedImportId);
    }
}

class RecordingImportRepository implements ImportRepositoryInterface
{
    public array $statuses = [];
    public ?int $publishedImportId = null;
    public bool $throwOnPersist = false;

    public function create(array $bbox, array $metadata): int
    {
        $this->statuses[] = 'staging';

        return 1;
    }

    public function setStatus(int $importId, string $status): void
    {
        $this->statuses[] = $status;
    }

    public function persist(int $importId, array $normalized, array $raw): array
    {
        if ($this->throwOnPersist) {
            throw new RuntimeException('persist failed');
        }

        return ['node_count' => count($normalized['nodes']), 'edge_count' => count($normalized['edges'])];
    }

    public function buildGraph(int $importId, array $normalized): array
    {
        return ['node_count' => count($normalized['nodes']), 'edge_count' => count($normalized['edges'])];
    }

    public function validate(int $importId): void
    {
    }

    public function publish(int $importId, array $metadata): void
    {
        $this->publishedImportId = $importId;
        $this->statuses[] = 'published';
    }
}
