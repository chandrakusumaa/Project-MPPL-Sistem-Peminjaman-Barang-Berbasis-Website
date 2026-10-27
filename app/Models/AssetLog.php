<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetLog extends Model
{
    protected $table = 'asset_logs';

    protected $guarded = ['id', 'active_guard', 'pending_guard', 'open_user_guard', 'maintenance_guard', 'inspection_guard'];

    public const UPDATED_AT = null;

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Histori bersifat append-only.'));
        static::deleting(fn () => throw new \LogicException('Histori tidak dapat dihapus.'));
    }
}
