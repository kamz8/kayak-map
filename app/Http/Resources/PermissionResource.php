<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Permission",
 *     @OA\Property(property="id", type="integer", description="Permission ID"),
 *     @OA\Property(property="name", type="string", description="Permission name"),
 *     @OA\Property(property="guard_name", type="string", description="Guard name"),
 *     @OA\Property(property="module", type="string", description="Permission module (e.g., dashboard, users)"),
 *     @OA\Property(property="action", type="string", description="Permission action (e.g., view, create, update)"),
 *     @OA\Property(property="roles", type="array", @OA\Items(ref="#/components/schemas/Role")),
 *     @OA\Property(property="roles_count", type="integer", description="Number of roles with this permission"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $nameParts = explode('.', $this->name);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'module' => $nameParts[0] ?? null,
            'action' => isset($nameParts[1]) ? implode('.', array_slice($nameParts, 1)) : null,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'roles_count' => $this->when($this->relationLoaded('roles'),
                function () {
                    return $this->roles->count();
                }
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}