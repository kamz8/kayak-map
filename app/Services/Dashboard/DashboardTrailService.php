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
        $trail->load([
            'images',
            'regions',
            'sections',
            'points',
            'riverTrack'
        ]);

        $trail->loadCount([
            'images',
            'sections',
            'points',
            'regions'
        ]);

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