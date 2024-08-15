<?php

namespace App\Enums;

enum RegionType: string
{
    case COUNTRY = 'country';
    case STATE = 'state';
    case CITY = 'city';
    case GEOGRAPHIC_AREA = 'geographic_area';

    public function label(): string
    {
        return match($this) {
            self::COUNTRY => 'Kraj',
            self::STATE => 'Województwo',
            self::CITY => 'Miasto',
            self::GEOGRAPHIC_AREA => 'Obszar geograficzny',
        };
    }
}
