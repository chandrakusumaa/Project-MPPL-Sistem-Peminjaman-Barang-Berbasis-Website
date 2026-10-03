<?php

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\MembershipRequest;
use App\Enums\Role;
use App\Notifications\JoinRequestSubmittedNotification;
use App\Notifications\InvitationSentNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['name' => 'Admin User']);
    $this->member = User::factory()->create(['name' => 'Member User']);
    $this->other = User::factory()->create();

    $this->organization = Organization::factory()->create([
        'created_by' => $this->admin->id,
    ]);
    
    $this->organization->users()->attach($this->admin->id, ['role' => Role::ADMIN->value]);
    $this->organization->users()->attach($this->member->id, ['role' => Role::MEMBER->value]);
});

test('join request submitted notifies admin', function () {
    Notification::fake();

    $request = MembershipRequest::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->other->id,
        'message' => 'I want to join',
        'status' => 'pending',
    ]);

    event(new \App\Events\JoinRequestSubmitted($request));

    Notification::assertSentTo(
        [$this->admin],
        JoinRequestSubmittedNotification::class
    );
});

test('invitation sent notifies email', function () {
    Notification::fake();

    $invitation = OrganizationInvitation::create([
        'organization_id' => $this->organization->id,
        'email' => 'newuser@example.com',
        'role' => Role::MEMBER,
        'token' => \Illuminate\Support\Str::random(32),
        'invited_by' => $this->admin->id,
        'expires_at' => now()->addDays(7),
    ]);

    event(new \App\Events\InvitationSent($invitation));

    // For anonymous route
    Notification::assertSentOnDemand(
        InvitationSentNotification::class,
        function (InvitationSentNotification $notification, array $channels, object $notifiable) {
            return $notifiable->routes['mail'] === 'newuser@example.com';
        }
    );
});

test('user preference disables email but keeps in-app', function () {
    // Disable membership email
    $this->admin->update([
        'notification_preferences' => ['membership' => false]
    ]);

    $request = MembershipRequest::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->other->id,
        'message' => 'Test msg',
        'status' => 'pending',
    ]);

    $notification = new JoinRequestSubmittedNotification($request);
    $channels = $notification->via($this->admin);

    expect($channels)->toContain('database')
        ->and($channels)->not->toContain('mail');
});

test('user can view notifications index', function () {
    $this->member->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => JoinRequestSubmittedNotification::class,
        'data' => [
            'title' => 'Test Title',
            'message' => 'Test Message',
        ],
        'read_at' => null,
    ]);

    $this->actingAs($this->member)
        ->get('/notifications')
        ->assertStatus(200)
        ->assertSee('Test Title')
        ->assertSee('Test Message');
});

test('user can view notification detail and it gets marked as read', function () {
    $notification = $this->member->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => JoinRequestSubmittedNotification::class,
        'data' => [
            'title' => 'Important Update',
            'message' => 'Please read this',
            'url' => '/some/url',
        ],
        'read_at' => null,
    ]);

    expect($notification->read_at)->toBeNull();

    $this->actingAs($this->member)
        ->get('/notifications/' . $notification->id)
        ->assertStatus(200)
        ->assertSee('Important Update')
        ->assertSee('/some/url');

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();
});
