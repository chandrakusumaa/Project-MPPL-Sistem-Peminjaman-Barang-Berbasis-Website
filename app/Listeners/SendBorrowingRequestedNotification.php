<?php

namespace App\Listeners;

use App\Events\BorrowingRequested;
use App\Enums\Role;
use App\Notifications\BorrowingRequestedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBorrowingRequestedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(BorrowingRequested $event): void
    {
        $organization = $event->borrowing->organization;
        
        // Admin and Staff receive this notification
        $managers = $organization->users()->wherePivotIn('role', [Role::ADMIN->value, Role::STAFF->value])->get();
        
        foreach ($managers as $manager) {
            // Avoid notifying the person requesting, in case they are also a manager
            if ($manager->id !== $event->borrowing->user_id) {
                $manager->notify(new BorrowingRequestedNotification($event->borrowing));
            }
        }
    }
}
