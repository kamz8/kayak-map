<?php

namespace App\Services;

use App\Models\PointType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PointTypeService
{
    /**
     * Znajduje id PointType na podstawie podanego typu.
     *
     * @param string $type
     * @return int|null
     */
    public function findIdByType(string $type): ?int
    {

            $pointType = PointType::where('type', $type)->first();
            return $pointType ? $pointType->id : null;

    }

    /**
     * Pobiera wszystkie typy punktów.
     *
     * @return Collection
     */
    public function getAllTypes(): Collection
    {
        return Cache::remember('all_point_types', now()->addDay(), function () {
            return PointType::all();
        });
    }

    /**
     * Tworzy nowy typ punktu.
     *
     * @param array $data
     * @return PointType
     */
    public function createPointType(array $data): PointType
    {
        $pointType = PointType::create($data);
        $this->clearCache();
        return $pointType;
    }

    /**
     * Aktualizuje istniejący typ punktu.
     *
     * @param int $id
     * @param array $data
     * @return PointType
     */
    public function updatePointType(int $id, array $data): PointType
    {
        $pointType = PointType::findOrFail($id);
        $pointType->update($data);
        $this->clearCache();
        return $pointType;
    }

    /**
     * Usuwa typ punktu.
     *
     * @param int $id
     * @return bool
     */
    public function deletePointType(int $id): bool
    {
        $result = PointType::destroy($id);
        $this->clearCache();
        return $result;
    }

    /**
     * Czyści cache związany z typami punktów.
     */
    private function clearCache(): void
    {
        Cache::forget('all_point_types');
        // Możesz dodać więcej kluczy cache do wyczyszczenia, jeśli są używane
    }
}
