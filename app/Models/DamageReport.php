<?php

namespace App\Models;

use App\Enums\DamageReportStatus;
use App\Enums\DamageSeverity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'asset_id',
        'borrowing_id',
        'reported_by',
        'severity',
        'description',
        'photo',
        'status',
        'handled_by',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'status' => DamageReportStatus::class,
        'severity' => DamageSeverity::class,
        'resolved_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
