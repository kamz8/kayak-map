<?php

namespace App\OpenApi\Schemas\User;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     title="User Resource",
 *     description="Schemat danych użytkownika"
 * )
 */
class UserResource
{
    /**
     * @OA\Property(property="id", type="integer", example=1)
     */
    public $id;

    /**
     * @OA\Property(property="email", type="string", format="email", example="user@example.com")
     */
    public $email;

    /**
     * @OA\Property(property="first_name", type="string", example="Jan")
     */
    public $first_name;

    /**
     * @OA\Property(property="last_name", type="string", example="Kowalski")
     */
    public $last_name;

    /**
     * @OA\Property(property="created_at", type="string", format="date-time")
     */
    public $created_at;
}
