<?php

namespace App\Actions\Membership;

use App\Enums\Role;
use App\Events\JoinRequestReviewed;
use App\Models\MembershipRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveJoinRequest
{
    /**
     * Approve a join request and add the user as a member.
     */
    public function execute(MembershipRequest $request, User $reviewer): void
    {
        DB::transaction(function () use ($request, $reviewer) {
            $request->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            // Add as member if not already
            $organization = $request->organization;
            if (! $request->user->isMemberOf($organization)) {
                $organization->users()->attach($request->user_id, [
                    'role' => Role::MEMBER->value,
                    'joined_at' => now(),
                ]);
            }
        });

        event(new JoinRequestReviewed($request));
    }
}
