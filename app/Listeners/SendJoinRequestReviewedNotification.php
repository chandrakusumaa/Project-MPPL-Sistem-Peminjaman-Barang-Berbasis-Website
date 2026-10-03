<?php

namespace App\Listeners;

use App\Events\JoinRequestReviewed;
use App\Notifications\JoinRequestReviewedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendJoinRequestReviewedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(JoinRequestReviewed $event): void
    {
        // Notifikasi ke user yang mengajukan (tidak perlu mengecualikan admin karena ini khusus ke user)
        $event->request->user->notify(new JoinRequestReviewedNotification($event->request));
    }
}
