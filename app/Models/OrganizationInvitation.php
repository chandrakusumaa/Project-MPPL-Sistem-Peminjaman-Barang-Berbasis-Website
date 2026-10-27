<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationInvitation extends Model
{
    protected $table = 'organization_invitations';

    protected $guarded = ['id', 'active_guard', 'pending_guard', 'open_user_guard', 'maintenance_guard', 'inspection_guard'];
}
