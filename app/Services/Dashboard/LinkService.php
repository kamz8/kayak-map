<?php

namespace App\Services\Dashboard;

use App\Models\Link;
use App\Models\Trail;
use App\Models\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LinkService
{
    /**
     * Get all links for a specific model (Trail or Section)
     *
     * @param Model $linkable
     * @return Collection
     */
    public function getLinksForModel(Model $linkable): Collection
    {
        return $linkable->links()->get();
    }

    /**
     * Get links with parent model info (Trail or Section)
     *
     * @param string $modelType 'trail' or 'section'
     * @param int $modelId
     * @param array $columns Columns to select from parent model
     * @return array ['links' => Collection, 'model' => Model]
     */
    public function getLinksWithModel(string $modelType, int $modelId, array $columns = ['id']): array
    {
        $model = $this->resolveModel($modelType, $modelId, $columns);

        if (!$model) {
            return ['links' => collect(), 'model' => null];
        }

        $links = $this->getLinksForModel($model);

        return [
            'links' => $links,
            'model' => $model
        ];
    }

    /**
     * Create a new link for a model
     *
     * @param Model $linkable
     * @param array $data
     * @return Link
     */
    public function createLink(Model $linkable, array $data): Link
    {
        return DB::transaction(function () use ($linkable, $data) {
            // Create the link
            $link = Link::create([
                'url' => $data['url'],
                'meta_data' => $data['meta_data'] ?? json_encode([
                    'title' => '',
                    'description' => '',
                    'icon' => ''
                ])
            ]);

            // Attach to the linkable model (Trail or Section)
            $linkable->links()->attach($link->id);

            return $link->fresh();
        });
    }

    /**
     * Update an existing link
     *
     * @param Link $link
     * @param array $data
     * @return Link
     */
    public function updateLink(Link $link, array $data): Link
    {
        $link->update([
            'url' => $data['url'] ?? $link->url,
            'meta_data' => $data['meta_data'] ?? $link->meta_data
        ]);

        return $link->fresh();
    }

    /**
     * Delete a link
     *
     * @param Link $link
     * @return bool
     */
    public function deleteLink(Link $link): bool
    {
        return DB::transaction(function () use ($link) {
            // Detach from all related models (cascade delete in linkables table)
            // This will be handled automatically by foreign key constraint
            return $link->delete();
        });
    }

    /**
     * Validate that a link belongs to a specific model
     *
     * @param Link $link
     * @param Model $linkable
     * @return bool
     */
    public function linkBelongsToModel(Link $link, Model $linkable): bool
    {
        return $linkable->links()->where('links.id', $link->id)->exists();
    }

    /**
     * Resolve model instance from type and ID
     *
     * @param string $modelType 'trail' or 'section'
     * @param int $modelId
     * @param array $columns Columns to select (default: only 'id')
     * @return Model|null
     * @throws \InvalidArgumentException
     */
    private function resolveModel(string $modelType, int $modelId, array $columns = ['id']): ?Model
    {
        return match ($modelType) {
            'trail' => Trail::find($modelId, $columns),
            'section' => Section::find($modelId, $columns),
            default => throw new \InvalidArgumentException("Invalid model type: {$modelType}")
        };
    }

    /**
     * Bulk update link order (for drag & drop)
     * Future implementation when 'order' column is added to linkables table
     *
     * @param Model $linkable
     * @param array $orderedLinkIds
     * @return void
     */
    public function updateLinksOrder(Model $linkable, array $orderedLinkIds): void
    {
        DB::transaction(function () use ($linkable, $orderedLinkIds) {
            foreach ($orderedLinkIds as $index => $linkId) {
                // Update order in pivot table
                // This will be implemented when 'order' column is added
                $linkable->links()->updateExistingPivot($linkId, [
                    // 'order' => $index + 1
                ]);
            }
        });
    }
}
