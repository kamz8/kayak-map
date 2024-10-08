<?php

namespace kamz8\LaravelOverpass\Helpers;

class BoundingBoxHelper
{
    public function generateBBox(float $lat1, float $lon1, float $lat2, float $lon2, float $marginPercent = 10): string
    {
        $south = min($lat1, $lat2);
        $north = max($lat1, $lat2);
        $west = min($lon1, $lon2);
        $east = max($lon1, $lon2);

        // Obliczanie marginesu
        $latMargin = ($north - $south) * ($marginPercent / 100);
        $lonMargin = ($east - $west) * ($marginPercent / 100);

        // Dodawanie marginesu
        $south -= $latMargin;
        $north += $latMargin;
        $west -= $lonMargin;
        $east += $lonMargin;

        return "({$south},{$west},{$north},{$east})";
    }
}
