<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case MEMBER = 'member';

    /**
     * Get human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::STAFF => 'Staff',
            self::MEMBER => 'Anggota',
        };
    }

    /**
     * Check if role has administrative privileges.
     */
    public function isAdministrative(): bool
    {
        return in_array($this, [self::ADMIN, self::STAFF], true);
    }
}
