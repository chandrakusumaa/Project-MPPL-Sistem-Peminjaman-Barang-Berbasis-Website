<?php

namespace App\Actions\Membership;

use App\Events\JoinRequestSubmitted;
use App\Models\MembershipRequest;
use App\Models\Organization;
use App\Models\User;

class SubmitJoinRequest
{
    /**
     * Submit a join request to an organization.
     */
    public function execute(User $user, Organization $organization, ?string $message = null): MembershipRequest
    {
        $request = MembershipRequest::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'message' => $message,
            'status' => 'pending',
        ]);

        event(new JoinRequestSubmitted($request));

        return $request;
    }
}
