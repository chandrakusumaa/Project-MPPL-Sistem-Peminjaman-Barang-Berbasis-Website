<?php

namespace App\Models;

use App\Enums\AssetLogEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'organization_id',
        'asset_id',
        'user_id',
        'event',
        'description',
        'metadata',
    ];

    protected $casts = [
        'event' => AssetLogEvent::class,
        'metadata' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
