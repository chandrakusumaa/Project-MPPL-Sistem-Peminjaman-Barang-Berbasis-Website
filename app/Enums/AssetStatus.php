<?php

namespace App\Enums;

enum AssetStatus: string
{
    case AVAILABLE = 'available';
    case BORROWED = 'borrowed';
    case MAINTENANCE = 'maintenance';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Tersedia',
            self::BORROWED => 'Dipinjam',
            self::MAINTENANCE => 'Perbaikan',
            self::LOST => 'Hilang',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE => 'green',
            self::BORROWED => 'blue',
            self::MAINTENANCE => 'orange',
            self::LOST => 'red',
        };
    }
}
