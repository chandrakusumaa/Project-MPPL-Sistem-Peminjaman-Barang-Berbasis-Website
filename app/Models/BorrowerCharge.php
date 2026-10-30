<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowerCharge extends Model
{
    protected $table = 'borrower_charges';

    protected $guarded = ['id', 'active_guard', 'pending_guard', 'open_user_guard', 'maintenance_guard', 'inspection_guard'];

    public function organization()
    {
        return $this->belongsTo(Organization::class)->withTrashed();
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(ChargePayment::class, 'charge_id');
    }

    public function credits()
    {
        return $this->hasMany(ChargeCredit::class, 'charge_id');
    }

    public function getBalanceAttribute(): int
    {
        return (int) $this->amount - (int) ($this->relationLoaded('payments') ? $this->payments->where('status', 'confirmed')->sum('amount') : $this->payments()->where('status', 'confirmed')->sum('amount')) - (int) ($this->relationLoaded('credits') ? $this->credits->sum('amount') : $this->credits()->sum('amount'));
    }

    public function getSettlementAttribute(): string
    {
        if ($this->status !== 'issued') {
            return $this->status;
        } $net = (int) $this->amount - (int) ($this->relationLoaded('credits') ? $this->credits->sum('amount') : $this->credits()->sum('amount'));
        $paid = (int) ($this->relationLoaded('payments') ? $this->payments->where('status', 'confirmed')->sum('amount') : $this->payments()->where('status', 'confirmed')->sum('amount'));

        return $net === 0 ? 'waived' : ($paid === $net ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'));
    }
}
