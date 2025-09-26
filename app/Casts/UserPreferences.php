<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class UserPreferences implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return [];
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        // Ensure proper boolean casting for virtual columns
        if (is_array($decoded)) {
            return $this->ensureBooleanTypes($decoded);
        }

        return $decoded ?: [];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        // Ensure proper boolean casting for MySQL virtual columns
        $processed = $this->ensureBooleanTypes($value);

        return json_encode($processed, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Convert boolean values to integers (1/0) for MySQL virtual columns compatibility
     */
    private function ensureBooleanTypes(array $data): array
    {
        $processed = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $processed[$key] = $this->ensureBooleanTypes($value);
            } elseif (is_bool($value)) {
                // Convert boolean to integer (1 or 0)
                $processed[$key] = $value ? 1 : 0;
            } elseif (in_array($value, ['true', 'false'], true)) {
                // Convert string booleans to integers
                $processed[$key] = $value === 'true' ? 1 : 0;
            } elseif (is_string($value) && in_array(strtolower($value), ['1', '0'])) {
                // Keep string numbers as integers
                $processed[$key] = (int) $value;
            } else {
                $processed[$key] = $value;
            }
        }

        return $processed;
    }
}
