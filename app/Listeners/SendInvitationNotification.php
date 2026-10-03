<?php

namespace App\Listeners;

use App\Events\InvitationSent;
use App\Models\User;
use App\Notifications\InvitationSentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendInvitationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(InvitationSent $event): void
    {
        $user = User::where('email', $event->invitation->email)->first();

        if ($user) {
            $user->notify(new InvitationSentNotification($event->invitation));
        } else {
            Notification::route('mail', $event->invitation->email)
                ->notify(new InvitationSentNotification($event->invitation));
        }
    }
}
