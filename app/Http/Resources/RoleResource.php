<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Role",
 *     @OA\Property(property="id", type="integer", description="Role ID"),
 *     @OA\Property(property="name", type="string", description="Role name"),
 *     @OA\Property(property="guard_name", type="string", description="Guard name"),
 *     @OA\Property(property="permissions", type="array", @OA\Items(ref="#/components/schemas/Permission")),
 *     @OA\Property(property="permissions_count", type="integer", description="Number of permissions"),
 *     @OA\Property(property="users_count", type="integer", description="Number of users with this role"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'permissions_count' => $this->when($this->relationLoaded('permissions'),
                function () {
                    return $this->permissions->count();
                }
            ),
            'users_count' => $this->when(
                isset($this->users_count),
                $this->users_count
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}