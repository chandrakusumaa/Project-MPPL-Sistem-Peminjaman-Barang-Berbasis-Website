<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    protected $table = 'damage_reports';

    protected $guarded = ['id', 'active_guard', 'pending_guard', 'open_user_guard', 'maintenance_guard', 'inspection_guard'];
}
