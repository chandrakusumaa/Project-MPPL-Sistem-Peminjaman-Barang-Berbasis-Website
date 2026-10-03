<?php

namespace App\Enums;

enum BorrowingStatus: string
{
    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case BORROWED = 'borrowed';
    case OVERDUE = 'overdue';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
            self::BORROWED => 'Dipinjam',
            self::OVERDUE => 'Terlambat',
            self::RETURNED => 'Dikembalikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::REJECTED => 'red',
            self::CANCELLED => 'gray',
            self::BORROWED => 'blue',
            self::OVERDUE => 'orange',
            self::RETURNED => 'green',
        };
    }
}
