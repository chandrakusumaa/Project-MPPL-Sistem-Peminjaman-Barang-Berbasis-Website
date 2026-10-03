<?php

namespace App\Enums;

enum DamageReportStatus: string
{
    case OPEN = 'open';
    case IN_MAINTENANCE = 'in_maintenance';
    case RESOLVED = 'resolved';
    case NOTED = 'noted';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Terbuka',
            self::IN_MAINTENANCE => 'Sedang Diperbaiki',
            self::RESOLVED => 'Selesai',
            self::NOTED => 'Dicatat',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'red',
            self::IN_MAINTENANCE => 'orange',
            self::RESOLVED => 'green',
            self::NOTED => 'gray',
        };
    }
}
