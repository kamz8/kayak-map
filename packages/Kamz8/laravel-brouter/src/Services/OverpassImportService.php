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
        return $this->runImport($bbox, $metadata, false);
    }

    public function importTemporary(array $bbox, array $metadata = []): array
    {
        return $this->runImport($bbox, $metadata, true);
    }

    private function runImport(array $bbox, array $metadata, bool $temporary): array
    {
        $importId = $this->repository->create($bbox, $metadata);

        try {
            $this->repository->setStatus($importId, 'importing');
            $raw = $this->rawImportData($bbox, $metadata);
            $normalized = $this->normalizer->normalize($raw, (string) ($metadata['name'] ?? ''));
            $graphPayload = $this->graphBuilder->build($normalized);
            $normalized['edges'] = array_values(array_map(
                static fn (array $edge): array => array_diff_key($edge, ['graph_edge' => true]),
                $graphPayload['edges'],
            ));
            $normalized['components'] = $graphPayload['components'];
            $counts = $this->repository->persist($importId, $normalized, $raw);
            $this->repository->buildGraph($importId, $normalized);

            if ($temporary) {
                $this->repository->setStatus($importId, 'temporary');

                return ['import_id' => $importId, 'status' => 'temporary'] + $counts;
            }

            $this->repository->setStatus($importId, 'validating');
            $this->repository->validate($importId);
            $this->repository->publish($importId, $counts + ['metadata' => $metadata]);

            return ['import_id' => $importId, 'status' => 'published'] + $counts;
        } catch (Throwable $exception) {
            $this->repository->setStatus($importId, 'failed');

            throw $exception;
        }
    }

    private function rawImportData(array $bbox, array $metadata): array
    {
        if (isset($metadata['name']) && method_exists($this->dataProvider, 'getNamedImportData')) {
            return $this->dataProvider->getNamedImportData((string) $metadata['name'], $bbox);
        }

        return $this->dataProvider->getImportData($bbox);
    }
}
