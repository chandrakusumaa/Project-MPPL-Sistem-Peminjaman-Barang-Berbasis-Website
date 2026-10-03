<?php

namespace App\Listeners;

use App\Events\DamageReported;
use App\Enums\Role;
use App\Notifications\DamageReportedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDamageReportedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(DamageReported $event): void
    {
        $organization = $event->report->organization;
        
        // Notify Admin and Staff
        $managers = $organization->users()->wherePivotIn('role', [Role::ADMIN->value, Role::STAFF->value])->get();
        
        foreach ($managers as $manager) {
            // Avoid notifying the person who reported it, if they happen to be a manager
            if ($manager->id !== $event->report->reported_by) {
                $manager->notify(new DamageReportedNotification($event->report));
            }
        }
    }
}
