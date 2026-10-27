<?php

namespace App\Actions;

use App\Jobs\SendInvitation;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InviteMember extends WorkspaceAction
{
    public function send(User $actor, Organization $org, string $email, string $role): void
    {
        $this->transact($actor, $org, 'administer', function ($org) use ($actor, $email, $role) {
            $this->check(in_array($role, ['staff', 'member']), 'Role undangan tidak valid.');
            $email = strtolower(trim($email));
            OrganizationInvitation::where('organization_id', $org->id)->where('email', $email)->where('status', 'pending')->where('expires_at', '<=', now())->update(['status' => 'expired']);
            $token = Str::random(64);
            $i = OrganizationInvitation::updateOrCreate(['organization_id' => $org->id, 'email' => $email, 'status' => 'pending'], ['role' => $role, 'token_hash' => hash('sha256', $token), 'invited_by' => $actor->id, 'expires_at' => now()->addDays(7), 'last_sent_at' => null]);
            SendInvitation::dispatch($i->id, $token)->afterCommit();
            $this->audit($actor, $org, 'invitation.sent', $i);
        });
    }

    public function revoke(User $actor, Organization $org, int $id): void
    {
        $this->transact($actor, $org, 'administer', function ($org) use ($actor, $id) {
            $i = OrganizationInvitation::where('organization_id', $org->id)->lockForUpdate()->findOrFail($id);
            $this->check($i->status === 'pending', 'Undangan tidak pending.');
            $i->update(['status' => 'revoked', 'revoked_by' => $actor->id, 'revoked_at' => now()]);
            $this->audit($actor, $org, 'invitation.revoked', $i);
        });
    }

    public function accept(User $actor, string $token): void
    {
        $found = OrganizationInvitation::where('token_hash', hash('sha256', $token))->firstOrFail();
        $org = Organization::findOrFail($found->organization_id);
        DB::transaction(function () use ($actor, $found, $org) {
            User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            $org = Organization::whereKey($org->id)->lockForUpdate()->firstOrFail();
            $i = OrganizationInvitation::whereKey($found->id)->lockForUpdate()->firstOrFail();
            $this->check(! $org->archived_at && $i->status === 'pending' && now()->lt($i->expires_at) && $actor->hasVerifiedEmail() && $actor->email === $i->email, 'Undangan tidak valid, kedaluwarsa, atau email berbeda.');
            $m = Membership::firstOrNew(['organization_id' => $org->id, 'user_id' => $actor->id]);
            if (! $m->exists || $m->status !== 'active') {
                $m->fill(['role' => $i->role, 'status' => 'active', 'joined_at' => now(), 'revoked_at' => null, 'revoked_by' => null])->save();
            }
            $i->update(['status' => 'accepted', 'accepted_by' => $actor->id, 'accepted_at' => now()]);
            $this->audit($actor, $org, 'invitation.accepted', $i);
        }, 3);
    }
}
