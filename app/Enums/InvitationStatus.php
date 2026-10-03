<?php

namespace App\Enums;

enum InvitationStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::ACCEPTED => 'Diterima',
            self::EXPIRED => 'Kadaluarsa',
            self::REVOKED => 'Ditarik',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::ACCEPTED => 'green',
            self::EXPIRED => 'gray',
            self::REVOKED => 'red',
        };
    }
}
