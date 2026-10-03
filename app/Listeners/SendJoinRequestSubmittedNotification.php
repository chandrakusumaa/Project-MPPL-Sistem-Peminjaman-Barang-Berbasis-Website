<?php

namespace App\Listeners;

use App\Events\JoinRequestSubmitted;
use App\Models\User;
use App\Enums\Role;
use App\Notifications\JoinRequestSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendJoinRequestSubmittedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(JoinRequestSubmitted $event): void
    {
        $organization = $event->request->organization;
        
        // Notifikasi ke Admin organisasi, kecuali admin yang mungkin mengajukan (meskipun admin harusnya otomatis member)
        $admins = $organization->users()->wherePivot('role', Role::ADMIN->value)->get();
        
        foreach ($admins as $admin) {
            if ($admin->id !== $event->request->user_id) {
                $admin->notify(new JoinRequestSubmittedNotification($event->request));
            }
        }
    }
}
