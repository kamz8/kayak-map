<?php

namespace App\Casts;

use App\Enums\Difficulty;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class DifficultyEnumCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get($model, string $key, $value, array $attributes): ?Difficulty
    {
        // Zamień wartość z bazy na enum
        return $value ? Difficulty::from($value) : null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value instanceof Difficulty ? $value->value : $value;
    }
}
