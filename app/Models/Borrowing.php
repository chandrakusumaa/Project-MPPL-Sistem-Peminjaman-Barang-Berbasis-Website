<?php

namespace App\Models;

use App\Enums\BorrowingStatus;
use App\Enums\ReturnCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'asset_id',
        'user_id',
        'borrow_date',
        'due_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'overdue_at',
        'returned_at',
        'processed_by',
        'return_condition',
        'return_notes',
        'fine_amount',
    ];

    protected $casts = [
        'status' => BorrowingStatus::class,
        'return_condition' => ReturnCondition::class,
        'borrow_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'overdue_at' => 'datetime',
        'returned_at' => 'datetime',
        'fine_amount' => 'integer',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class);
    }
}
