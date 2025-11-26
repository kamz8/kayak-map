<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DashboardLinkResource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="url", type="string"),
 *     @OA\Property(property="meta_data", type="object",
 *         @OA\Property(property="title", type="string"),
 *         @OA\Property(property="description", type="string"),
 *         @OA\Property(property="icon", type="string")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class LinkResource extends JsonResource
{
    /**
     * Transform the resource into an array for dashboard display.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Parse meta_data JSON string to object
        $metaData = $this->parseMetaData();

        return [
            'id' => $this->id,
            'url' => $this->url,
            'meta_data' => $metaData,

            // Parsed meta fields for convenience
            'title' => $metaData['title'] ?? '',
            'description' => $metaData['description'] ?? '',
            'icon' => $metaData['icon'] ?? '',

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Domain extraction for display
            'domain' => $this->extractDomain(),
        ];
    }

    /**
     * Parse meta_data JSON string to array
     *
     * @return array
     */
    private function parseMetaData(): array
    {
        if (empty($this->meta_data)) {
            return [
                'title' => '',
                'description' => '',
                'icon' => ''
            ];
        }

        if (is_string($this->meta_data)) {
            try {
                $decoded = json_decode($this->meta_data, true);
                return is_array($decoded) ? $decoded : [
                    'title' => '',
                    'description' => '',
                    'icon' => ''
                ];
            } catch (\Exception $e) {
                return [
                    'title' => '',
                    'description' => '',
                    'icon' => ''
                ];
            }
        }

        return is_array($this->meta_data) ? $this->meta_data : [
            'title' => '',
            'description' => '',
            'icon' => ''
        ];
    }

    /**
     * Extract domain from URL for display purposes
     *
     * @return string|null
     */
    private function extractDomain(): ?string
    {
        if (empty($this->url)) {
            return null;
        }

        try {
            $parsed = parse_url($this->url);
            return $parsed['host'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
