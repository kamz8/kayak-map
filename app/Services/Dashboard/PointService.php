<?php

namespace App\Services\Dashboard;

use App\Models\PointType;
use Illuminate\Database\Eloquent\Collection;

class PointService
{
    /**
     * Get all point types
     *
     * @return Collection
     */
    public function getAllPointTypes(): Collection
    {
        return PointType::select('id', 'type', 'key', 'icon')
            ->orderBy('type')
            ->get();
    }
}