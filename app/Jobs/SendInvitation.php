<?php

namespace App\Jobs;

use App\Mail\InvitationMail;
use App\Models\OrganizationInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendInvitation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $invitationId, public string $token)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $i = OrganizationInvitation::find($this->invitationId);
        if (! $i || $i->status !== 'pending' || now()->gte($i->expires_at) || ! hash_equals($i->token_hash, hash('sha256', $this->token))) {
            return;
        }
        Mail::to($i->email)->send(new InvitationMail(url('/invitations/'.$this->token)));
        $i->update(['last_sent_at' => now()]);
    }
}
