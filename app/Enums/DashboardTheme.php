<?php

declare(strict_types=1);

namespace App\Enums;

enum DashboardTheme: string
{
    case SunsetEmber = 'sunset-ember';
    case NorthernLights = 'northern-lights';
    case TropicalDusk = 'tropical-dusk';

    public function label(): string
    {
        return match ($this) {
            self::SunsetEmber => 'Sunset Ember',
            self::NorthernLights => 'Northern Lights',
            self::TropicalDusk => 'Tropical Dusk',
        };
    }
}
