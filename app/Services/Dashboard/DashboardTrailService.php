<?php

namespace App\Services\Dashboard;

use App\Models\Trail;
use App\Services\TrailService;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardTrailService extends TrailService
{
    /**
     * Pobierz listę szlaków dla dashboardu z filtrami i paginacją
     */
    public function getTrailsForDashboard(array $filters = []): LengthAwarePaginator
    {
        $query = Trail::query();

        // Load only regions relation for dashboard list
        $query->with(['regions']);

        // Count relationships for statistics
        $withCount = $filters['with_count'] ?? ['images', 'sections', 'points', 'regions'];
        if (!empty($withCount) && is_array($withCount)) {
            $query->withCount($withCount);
        }

        // Optional: load additional relationships if requested
        if (!empty($filters['with']) && is_array($filters['with'])) {
            $allowedRelations = ['images', 'sections', 'points', 'riverTrack'];
            $relations = array_intersect($filters['with'], $allowedRelations);
            if (!empty($relations)) {
                $query->with($relations);
            }
        }

        // Apply filters using scopes
        $this->applyDashboardFilters($query, $filters);

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $allowedSorts = [
            'id', 'trail_name', 'river_name', 'difficulty',
            'scenery', 'rating', 'trail_length', 'status',
            'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($filters['per_page'] ?? 15, 100); // Max 100 per page

        return $query->paginate($perPage);
    }

    /**
     * Zastosuj filtry specyficzne dla dashboardu
     */
    private function applyDashboardFilters($query, array $filters): void
    {
        // Search filter (używamy scope z modelu Trail)
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Status filter (używamy scope status z modelu Trail)
        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        } elseif (!empty($filters['statuses']) && is_array($filters['statuses'])) {
            $query->status($filters['statuses']);
        }

        // Difficulty filter (używamy scope difficulty z modelu Trail)
        if (!empty($filters['difficulty'])) {
            $query->difficulty($filters['difficulty']);
        } elseif (!empty($filters['difficulties']) && is_array($filters['difficulties'])) {
            $query->difficulty($filters['difficulties']);
        }

        // Region filter (używamy scope region z modelu Trail)
        if (!empty($filters['region_id'])) {
            $query->region($filters['region_id']);
        } elseif (!empty($filters['region_ids']) && is_array($filters['region_ids'])) {
            $query->region($filters['region_ids']);
        }

        // Date range filter (używamy scope dateRange z modelu Trail)
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $query->dateRange(
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null
            );
        }

        // Scenery rating filter
        if (isset($filters['min_scenery'])) {
            $query->where('scenery', '>=', $filters['min_scenery']);
        }
        if (isset($filters['max_scenery'])) {
            $query->where('scenery', '<=', $filters['max_scenery']);
        }

        // Trail rating filter
        if (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }
        if (isset($filters['max_rating'])) {
            $query->where('rating', '<=', $filters['max_rating']);
        }

        // Trail length filter
        if (isset($filters['min_length'])) {
            $query->where('trail_length', '>=', $filters['min_length']);
        }
        if (isset($filters['max_length'])) {
            $query->where('trail_length', '<=', $filters['max_length']);
        }

        // Author filter
        if (!empty($filters['author'])) {
            $query->where('author', 'LIKE', "%{$filters['author']}%");
        }
    }

    /**
     * Pobierz szlak ze szczegółami dla dashboardu
     */
    public function getTrailForDashboard(Trail $trail): Trail
    {
        return $trail;
    }

    /**
     * Zmień status szlaku
     */
    public function changeStatus(Trail $trail, string $status): Trail
    {
        $allowedStatuses = ['active', 'inactive', 'draft', 'archived'];

        if (!in_array($status, $allowedStatuses)) {
            throw new \InvalidArgumentException('Nieprawidłowy status szlaku.');
        }

        $trail->update(['status' => $status]);
        return $trail->fresh();
    }

    /**
     * Masowa zmiana statusu szlaków przez queue batch
     *
     * @param array $trailIds IDs of trails to update
     * @param string $status New status
     * @param int $userId User who initiated the operation
     * @return string Batch ID for tracking progress
     * @throws \InvalidArgumentException
     */
    public function bulkChangeStatus(array $trailIds, string $status, int $userId): string
    {
        $allowedStatuses = ['active', 'inactive', 'draft', 'archived'];

        if (!in_array($status, $allowedStatuses)) {
            throw new \InvalidArgumentException('Nieprawidłowy status szlaku.');
        }

        // Validate that all trail IDs exist
        $existingCount = Trail::whereIn('id', $trailIds)->count();
        if ($existingCount !== count($trailIds)) {
            throw new \InvalidArgumentException('Niektóre ID szlaków nie istnieją.');
        }

        // Split trail IDs into chunks for batch processing (50 trails per job)
        $chunks = array_chunk($trailIds, 50);

        // Create batch jobs
        $jobs = [];
        foreach ($chunks as $chunk) {
            $jobs[] = new \App\Jobs\BulkTrailStatusChangeJob($chunk, $status, $userId);
        }

        // Dispatch batch with name and callbacks
        $batch = \Illuminate\Support\Facades\Bus::batch($jobs)
            ->name('Bulk Trail Status Change - User ' . $userId)
            ->allowFailures() // Continue even if some jobs fail
            ->onQueue('default')
            ->dispatch();

        \Illuminate\Support\Facades\Log::info('Bulk status change batch dispatched', [
            'batch_id' => $batch->id,
            'total_trails' => count($trailIds),
            'total_jobs' => count($jobs),
            'new_status' => $status,
            'user_id' => $userId,
        ]);

        return $batch->id;
    }

    /**
     * Pobierz statystyki szlaków dla dashboardu
     */
    public function getStatistics(): array
    {
        return [
            'total' => Trail::count(),
            'by_status' => [
                'active' => Trail::where('status', 'active')->count(),
                'inactive' => Trail::where('status', 'inactive')->count(),
                'draft' => Trail::where('status', 'draft')->count(),
                'archived' => Trail::where('status', 'archived')->count(),
            ],
            'by_difficulty' => [
                'easy' => Trail::where('difficulty', 'łatwy')->count(),
                'moderate' => Trail::where('difficulty', 'umiarkowany')->count(),
                'hard' => Trail::where('difficulty', 'trudny')->count(),
            ],
            'averages' => [
                'rating' => round(Trail::where('status', 'active')->avg('rating'), 2),
                'scenery' => round(Trail::where('status', 'active')->avg('scenery'), 2),
            ],
            'total_length_km' => Trail::where('status', 'active')->sum('trail_length'),
            'recent_count' => Trail::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }
}