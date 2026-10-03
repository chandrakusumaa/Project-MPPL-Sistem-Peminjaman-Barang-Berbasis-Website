<?php

namespace App\Actions\Membership;

use App\Events\JoinRequestReviewed;
use App\Models\MembershipRequest;
use App\Models\User;

class RejectJoinRequest
{
    /**
     * Reject a join request.
     */
    public function execute(MembershipRequest $request, User $reviewer): void
    {
        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        event(new JoinRequestReviewed($request));
    }
}
