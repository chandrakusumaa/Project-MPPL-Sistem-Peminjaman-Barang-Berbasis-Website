<?php

namespace App\Enums;

enum DamageSeverity: string
{
    case MINOR = 'minor';
    case MAJOR = 'major';

    public function label(): string
    {
        return match ($this) {
            self::MINOR => 'Ringan',
            self::MAJOR => 'Berat',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MINOR => 'yellow',
            self::MAJOR => 'red',
        };
    }
}
