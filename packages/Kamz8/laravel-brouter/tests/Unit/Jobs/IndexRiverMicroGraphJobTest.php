<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Jobs;

use Kamz\LaravelBRouter\Contracts\ImportRepositoryInterface;
use Kamz\LaravelBRouter\Jobs\IndexRiverMicroGraphJob;
use Kamz\LaravelBRouter\Tests\TestCase;

class IndexRiverMicroGraphJobTest extends TestCase
{
    /** @test */
    public function it_validates_and_promotes_a_temporary_import(): void
    {
        $repository = new class implements ImportRepositoryInterface
        {
            public array $statuses = [];
            public ?int $publishedId = null;

            public function create(array $bbox, array $metadata): int { return 1; }
            public function setStatus(int $importId, string $status): void { $this->statuses[] = $status; }
            public function persist(int $importId, array $normalized, array $raw): array { return []; }
            public function buildGraph(int $importId, array $normalized): array { return []; }
            public function validate(int $importId): void { $this->statuses[] = 'validated'; }
            public function publish(int $importId, array $metadata): void { $this->publishedId = $importId; }
        };

        (new IndexRiverMicroGraphJob(7))->handle($repository);

        $this->assertSame(['indexing', 'validated'], $repository->statuses);
        $this->assertSame(7, $repository->publishedId);
    }
}
