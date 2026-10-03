<?php

namespace App\Listeners;

use App\Events\BorrowingBecameOverdue;
use App\Enums\Role;
use App\Notifications\BorrowingOverdueNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBorrowingOverdueNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(BorrowingBecameOverdue $event): void
    {
        $organization = $event->borrowing->organization;
        
        // Notify the borrower
        $event->borrowing->user->notify(new BorrowingOverdueNotification($event->borrowing, false));

        // Notify Admin and Staff
        $managers = $organization->users()->wherePivotIn('role', [Role::ADMIN->value, Role::STAFF->value])->get();
        
        foreach ($managers as $manager) {
            // If the manager is the borrower, they already received the borrower notification
            if ($manager->id !== $event->borrowing->user_id) {
                $manager->notify(new BorrowingOverdueNotification($event->borrowing, true));
            }
        }
    }
}
