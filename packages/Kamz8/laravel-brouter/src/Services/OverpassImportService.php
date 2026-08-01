<?php

namespace Kamz\LaravelBRouter\Services;

use Kamz\LaravelBRouter\Contracts\ImportDataProviderInterface;
use Kamz\LaravelBRouter\Contracts\ImportRepositoryInterface;
use Throwable;

class OverpassImportService
{
    public function __construct(
        private readonly ImportDataProviderInterface $dataProvider,
        private readonly ImportRepositoryInterface $repository,
        private readonly WaterwayNormalizer $normalizer,
        private readonly WaterwayGraphBuilder $graphBuilder,
    ) {
    }

    public function import(array $bbox, array $metadata = []): array
    {
        $importId = $this->repository->create($bbox, $metadata);

        try {
            $this->repository->setStatus($importId, 'importing');
            $raw = $this->dataProvider->getImportData($bbox);
            $normalized = $this->normalizer->normalize($raw, (string) ($metadata['name'] ?? ''));
            $this->graphBuilder->build($normalized);
            $counts = $this->repository->persist($importId, $normalized, $raw);
            $this->repository->buildGraph($importId, $normalized);
            $this->repository->setStatus($importId, 'validating');
            $this->repository->validate($importId);
            $this->repository->publish($importId, $counts + ['metadata' => $metadata]);

            return ['import_id' => $importId, 'status' => 'published'] + $counts;
        } catch (Throwable $exception) {
            $this->repository->setStatus($importId, 'failed');

            throw $exception;
        }
    }
}
