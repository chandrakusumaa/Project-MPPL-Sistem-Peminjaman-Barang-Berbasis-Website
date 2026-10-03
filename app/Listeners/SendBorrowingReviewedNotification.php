<?php

namespace App\Listeners;

use App\Notifications\BorrowingReviewedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBorrowingReviewedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(object $event): void
    {
        // $event will be either BorrowingApproved or BorrowingRejected
        $event->borrowing->user->notify(new BorrowingReviewedNotification($event->borrowing));
    }
}
