<?php

namespace App\Listeners;

use App\Events\MemberRemoved;
use App\Events\MemberRoleChanged;
use App\Notifications\RoleChangedOrRemovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRoleChangedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(object $event): void
    {
        if ($event instanceof MemberRemoved) {
            $event->user->notify(new RoleChangedOrRemovedNotification($event->organization, 'removed'));
        } elseif ($event instanceof MemberRoleChanged) {
            $event->user->notify(new RoleChangedOrRemovedNotification($event->organization, 'changed', $event->newRole->value));
        }
    }
}
