<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $table = 'borrowings';

    protected $guarded = ['id', 'active_guard', 'pending_guard', 'open_user_guard', 'maintenance_guard', 'inspection_guard'];

    public function charges()
    {
        return $this->hasMany(BorrowerCharge::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class)->withTrashed();
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function borrower()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    protected function casts(): array
    {
        return ['borrow_date' => 'immutable_date', 'due_date' => 'immutable_date', 'approved_at' => 'immutable_datetime', 'returned_at' => 'immutable_datetime'];
    }
}
