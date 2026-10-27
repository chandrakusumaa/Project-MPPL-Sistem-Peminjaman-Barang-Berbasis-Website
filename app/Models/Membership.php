<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    protected $table = 'organization_user';

    protected $guarded = ['id', 'active_guard', 'pending_guard', 'open_user_guard', 'maintenance_guard', 'inspection_guard'];
}
