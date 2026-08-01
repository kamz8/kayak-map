<?php

namespace Kamz\LaravelBRouter\Contracts;

interface ImportRepositoryInterface
{
    public function create(array $bbox, array $metadata): int;

    public function setStatus(int $importId, string $status): void;

    public function persist(int $importId, array $normalized, array $raw): array;

    public function buildGraph(int $importId, array $normalized): array;

    public function validate(int $importId): void;

    public function publish(int $importId, array $metadata): void;
}
