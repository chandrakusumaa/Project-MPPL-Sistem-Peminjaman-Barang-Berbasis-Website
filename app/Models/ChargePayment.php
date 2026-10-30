<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChargePayment extends Model
{
    protected $table = 'charge_payments';

    protected $guarded = ['id', 'active_guard', 'pending_guard', 'open_user_guard', 'maintenance_guard', 'inspection_guard'];

    public function charge()
    {
        return $this->belongsTo(BorrowerCharge::class, 'charge_id');
    }

    protected function casts(): array
    {
        return ['paid_at' => 'immutable_datetime'];
    }
}
