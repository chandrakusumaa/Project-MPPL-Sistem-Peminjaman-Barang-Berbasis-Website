<?php

namespace App\Actions\Membership;

use App\Events\InvitationSent;
use App\Mail\OrganizationInvitationMail;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteMember
{
    /**
     * Send an invitation to an email address.
     */
    public function execute(Organization $organization, User $inviter, string $email, string $role): OrganizationInvitation
    {
        // Delete any existing pending invitations for this email in this org
        OrganizationInvitation::where('organization_id', $organization->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->delete();

        $invitation = OrganizationInvitation::create([
            'organization_id' => $organization->id,
            'email' => $email,
            'role' => $role,
            'token' => Str::random(32),
            'invited_by' => $inviter->id,
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
        ]);

        Mail::to($email)->queue(new OrganizationInvitationMail($invitation));

        event(new InvitationSent($invitation));

        return $invitation;
    }
}
