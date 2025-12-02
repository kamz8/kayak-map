<?php

namespace Kamz\LaravelBRouter\Contracts;

interface DataProviderInterface
{
    public function getWaterwaysInBBox(array $bbox);
    public function getWaterwayByName(string $name, array $bbox = null);
}
