<?php

namespace App\Services;

class ImageService
{
    public function formatImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return config('app.url') . '/storage/' . $path;
    }
}
