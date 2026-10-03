<?php

namespace App\Actions\Membership;

use App\Events\InvitationAccepted;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptInvitation
{
    /**
     * Accept an invitation and join the organization.
     */
    public function execute(OrganizationInvitation $invitation, User $user): void
    {
        DB::transaction(function () use ($invitation, $user) {
            $invitation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            $organization = $invitation->organization;

            if (! $user->isMemberOf($organization)) {
                $organization->users()->attach($user->id, [
                    'role' => $invitation->role,
                    'joined_at' => now(),
                ]);
            }
        });

        event(new InvitationAccepted($invitation));
    }
}
