<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    protected $table = 'damage_reports';

    protected $guarded = ['id', 'active_guard', 'pending_guard', 'open_user_guard', 'maintenance_guard', 'inspection_guard'];

    public function organization()
    {
        return $this->belongsTo(Organization::class)->withTrashed();
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by')->withTrashed();
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by')->withTrashed();
    }

    public function charge()
    {
        return $this->hasOne(BorrowerCharge::class, 'damage_report_id')->whereIn('status', ['draft', 'issued'])->latest('id');
    }
}
