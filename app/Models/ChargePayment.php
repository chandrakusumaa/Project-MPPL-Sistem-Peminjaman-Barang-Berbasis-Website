<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChargePayment extends Model
{
    protected $table = 'charge_payments';

    protected $guarded = ['id', 'active_guard', 'pending_guard', 'open_user_guard', 'maintenance_guard', 'inspection_guard'];
}
