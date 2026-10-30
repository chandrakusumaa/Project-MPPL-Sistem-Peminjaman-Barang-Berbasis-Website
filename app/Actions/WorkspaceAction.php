<?php

namespace App\Actions;

use App\Models\AssetLog;
use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\ActivityMail;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

abstract class WorkspaceAction
{
    protected function transact(User $actor, Organization $organization, string $ability, Closure $action): mixed
    {
        return DB::transaction(function () use ($actor, $organization, $ability, $action) {
            User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            $organization = Organization::whereKey($organization->id)->lockForUpdate()->firstOrFail();
            Membership::where('organization_id', $organization->id)->where('user_id', $actor->id)->lockForUpdate()->first();
            Gate::forUser($actor)->authorize($ability, $organization);
            $this->check(! $organization->archived_at, 'Organization diarsipkan dan hanya dapat dibaca.');

            return $action($organization);
        }, 3);
    }

    protected function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['action' => $message]);
        }
    }

    protected function audit(User $actor, Organization $org, string $event, $subject): void
    {
        AuditLog::create(['organization_id' => $org->id, 'actor_id' => $actor->id, 'action' => $event, 'subject_type' => class_basename($subject), 'subject_id' => $subject->id, 'old_values' => json_encode(array_intersect_key($subject->getPrevious(), array_flip(['status', 'role', 'name', 'amount', 'physical_condition', 'max_borrow_days', 'late_fine_per_day']))), 'new_values' => json_encode(array_intersect_key($subject->getAttributes(), array_flip(['status', 'role', 'name', 'amount', 'physical_condition', 'max_borrow_days', 'late_fine_per_day'])))]);
        $recipientIds = Membership::where('organization_id', $org->id)->where('status', 'active')->whereIn('role', ['admin', 'staff'])->pluck('user_id')->all();
        $subjectUser = $subject->borrower_id ?? $subject->user_id ?? null;
        if ($subjectUser) {
            $recipientIds[] = $subjectUser;
        }
        foreach (array_unique($recipientIds) as $id) {
            if ($id === $actor->id) {
                continue;
            }
            $recipient = User::find($id);
            if (! $recipient) {
                continue;
            }
            DB::table('notifications')->insert(['id' => (string) Str::uuid(), 'type' => 'activity', 'notifiable_type' => User::class, 'notifiable_id' => $id, 'organization_id' => $org->id, 'data' => json_encode(['title' => $event, 'url' => url('/o/'.$org->slug)]), 'created_at' => now(), 'updated_at' => now()]);
            if (($recipient->notification_preferences[str_starts_with($event, 'membership.') || str_starts_with($event, 'invitation.') ? 'membership' : (str_starts_with($event, 'damage.') ? 'damage' : 'borrowing')] ?? true)) {
                $recipient->notify(new ActivityMail($event, url('/o/'.$org->slug)));
            }
        }
    }

    protected function log(User $actor, Organization $org, $asset, string $event, ?int $borrowing = null): void
    {
        AssetLog::create(['organization_id' => $org->id, 'asset_id' => $asset->id, 'user_id' => $actor->id, 'borrowing_id' => $borrowing, 'event' => $event, 'description' => $event]);
    }
}
