<?php

namespace App\Listeners;

use App\Events\BorrowingReturned;
use App\Notifications\ReturnProcessedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendReturnProcessedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(BorrowingReturned $event): void
    {
        // Don't notify the person who processed it if they are the borrower themselves (though normally staff process it)
        // Wait, normally borrower is the one who borrows, staff is the one who returns. So we always notify the borrower.
        // What if staff processed their own return? We can skip if processed_by == user_id.
        if ($event->borrowing->processed_by !== $event->borrowing->user_id) {
            $event->borrowing->user->notify(new ReturnProcessedNotification($event->borrowing));
        }
    }
}
