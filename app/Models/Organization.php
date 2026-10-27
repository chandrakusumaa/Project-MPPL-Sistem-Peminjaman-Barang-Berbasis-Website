<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $table = 'organizations';

    protected $guarded = ['id', 'active_guard', 'pending_guard', 'open_user_guard', 'maintenance_guard', 'inspection_guard'];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
