<?php

namespace App\Enums;

enum ReturnCondition: string
{
    case GOOD = 'good';
    case MINOR_DAMAGE = 'minor_damage';
    case MAJOR_DAMAGE = 'major_damage';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::GOOD => 'Baik',
            self::MINOR_DAMAGE => 'Rusak Ringan',
            self::MAJOR_DAMAGE => 'Rusak Berat',
            self::LOST => 'Hilang',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::GOOD => 'green',
            self::MINOR_DAMAGE => 'yellow',
            self::MAJOR_DAMAGE => 'orange',
            self::LOST => 'red',
        };
    }
}
