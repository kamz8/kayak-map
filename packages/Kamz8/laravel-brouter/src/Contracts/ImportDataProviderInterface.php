<?php

namespace Kamz\LaravelBRouter\Contracts;

interface ImportDataProviderInterface
{
    public function getImportData(array $bbox): array;
}
