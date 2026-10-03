<?php

namespace App\Enums;

enum AssetLogEvent: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case BORROWED = 'borrowed';
    case RETURNED = 'returned';
    case OVERDUE = 'overdue';
    case DAMAGE_REPORTED = 'damage_reported';
    case MAINTENANCE_STARTED = 'maintenance_started';
    case MAINTENANCE_FINISHED = 'maintenance_finished';
    case MARKED_LOST = 'marked_lost';
    case MARKED_FOUND = 'marked_found';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Dibuat',
            self::UPDATED => 'Diperbarui',
            self::BORROWED => 'Dipinjam',
            self::RETURNED => 'Dikembalikan',
            self::OVERDUE => 'Terlambat',
            self::DAMAGE_REPORTED => 'Kerusakan Dilaporkan',
            self::MAINTENANCE_STARTED => 'Perbaikan Dimulai',
            self::MAINTENANCE_FINISHED => 'Perbaikan Selesai',
            self::MARKED_LOST => 'Ditandai Hilang',
            self::MARKED_FOUND => 'Ditandai Ditemukan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CREATED, self::MARKED_FOUND => 'green',
            self::UPDATED, self::RETURNED, self::MAINTENANCE_FINISHED => 'blue',
            self::BORROWED, self::MAINTENANCE_STARTED => 'indigo',
            self::OVERDUE, self::DAMAGE_REPORTED => 'orange',
            self::MARKED_LOST => 'red',
        };
    }
}
