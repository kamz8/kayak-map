<?php

namespace App\Utils;

use App\Enums\Difficulty;
use Illuminate\Support\Collection;

class DifficultyCalculator
{
    private static $difficultyMap = [
        'ZWA' => 1,
        'ZWB' => 2,
        'ZWC' => 3,
        'WW I' => 4,
        'WW II' => 5,
        'WW III' => 6,
        'WW IV' => 7,
        'WW V' => 8,
        'WW VI' => 9
    ];

    public static function calculateAverageDifficulty(Collection $difficultyCodes): int
    {
        $numericValues = $difficultyCodes->map(function($code) {
            return self::$difficultyMap[$code] ?? 0;
        });

        $average = $numericValues->avg();

        // Zaokrąglamy średnią do najbliższej liczby całkowitej w zakresie 1-9
        return max(1, min(9, round($average)));
    }

    public static function castToDifficultyEnum(float $averageDifficulty): Difficulty
    {
        if ($averageDifficulty <= 3) {
            return Difficulty::EASY;
        } elseif ($averageDifficulty <= 6) {
            return Difficulty::MODERATE;
        } else {
            return Difficulty::HARD;
        }
    }
}
