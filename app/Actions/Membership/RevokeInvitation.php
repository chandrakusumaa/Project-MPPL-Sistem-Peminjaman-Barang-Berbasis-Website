<?php

namespace App\Actions\Membership;

use App\Models\OrganizationInvitation;

class RevokeInvitation
{
    /**
     * Revoke a pending invitation.
     */
    public function execute(OrganizationInvitation $invitation): void
    {
        $invitation->update(['status' => 'revoked']);
    }
}
