<?php

use App\Actions\Asset\ReportDamage;
use App\Enums\AssetStatus;
use App\Enums\DamageReportStatus;
use App\Enums\Role;
use App\Events\DamageReported;
use App\Http\Requests\Settings\UpdateNotificationPreferencesRequest;
use App\Listeners\SendDamageReportedNotification;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\DamageReport;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\DamageReportedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->staff = User::factory()->create();
    $this->member = User::factory()->create();

    $this->organization = Organization::factory()->create(['created_by' => $this->admin->id]);
    $this->organization->users()->attach($this->admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $this->organization->users()->attach($this->staff->id, ['role' => Role::STAFF->value, 'joined_at' => now()]);
    $this->organization->users()->attach($this->member->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);

    $this->category = AssetCategory::factory()->create([
        'organization_id' => $this->organization->id,
        'name' => 'Kategori Uji',
    ]);

    $this->asset = Asset::factory()->create([
        'organization_id' => $this->organization->id,
        'asset_category_id' => $this->category->id,
        'code' => '01JTESTDAMAGEASSET000000A',
        'name' => 'Aset Uji',
        'description' => 'Aset untuk pengujian',
        'specifications' => 'Spek',
        'location' => 'Gudang',
        'status' => AssetStatus::AVAILABLE,
    ]);
});

test('damage email category cannot be disabled by preferences', function () {
    $this->staff->update(['notification_preferences' => ['membership' => false, 'borrowing' => false, 'damage' => false]]);

    expect($this->staff->fresh()->wantsEmailFor('damage'))->toBeTrue()
        ->and($this->staff->fresh()->wantsEmailFor('membership'))->toBeFalse()
        ->and($this->staff->fresh()->wantsEmailFor('borrowing'))->toBeFalse();
});

test('damage notification always uses database and mail channels', function () {
    $this->staff->update(['notification_preferences' => ['damage' => false]]);

    $report = DamageReport::create([
        'organization_id' => $this->organization->id,
        'asset_id' => $this->asset->id,
        'reported_by' => $this->member->id,
        'description' => 'Layar retak',
        'status' => DamageReportStatus::OPEN,
    ]);

    $channels = (new DamageReportedNotification($report))->via($this->staff->fresh());

    expect($channels)->toContain('database')->and($channels)->toContain('mail');
});

test('damage listener notifies admin and staff but not the reporter', function () {
    Notification::fake();

    $this->staff->update(['notification_preferences' => ['damage' => false]]);

    $report = DamageReport::create([
        'organization_id' => $this->organization->id,
        'asset_id' => $this->asset->id,
        'reported_by' => $this->member->id,
        'description' => 'Layar retak',
        'status' => DamageReportStatus::OPEN,
    ]);

    (new SendDamageReportedNotification)->handle(new DamageReported($report));

    Notification::assertSentTo($this->admin, DamageReportedNotification::class);
    Notification::assertSentTo($this->staff, DamageReportedNotification::class, function ($notification, $channels) {
        return in_array('mail', $channels, true) && in_array('database', $channels, true);
    });
    Notification::assertNotSentTo($this->member, DamageReportedNotification::class);
});

test('cannot report damage on a lost asset', function () {
    $this->asset->update(['status' => AssetStatus::LOST]);

    expect(fn () => (new ReportDamage)->execute($this->asset->fresh(), $this->member, ['description' => 'Layar retak']))
        ->toThrow(Exception::class, 'Hilang');

    $this->assertDatabaseCount('damage_reports', 0);
});

test('catalog damage form is blocked for a lost asset', function () {
    $this->asset->update(['status' => AssetStatus::LOST]);

    $this->actingAs($this->member);

    Volt::test('catalog.show', ['organization' => $this->organization, 'asset' => $this->asset])
        ->set('damage_description', 'Layar retak parah')
        ->call('submitDamageReport')
        ->assertHasErrors(['damage_description']);

    $this->assertDatabaseCount('damage_reports', 0);
});

test('member can submit damage report from catalog detail without severity', function () {
    $this->actingAs($this->member);

    Volt::test('catalog.show', ['organization' => $this->organization, 'asset' => $this->asset])
        ->set('damage_description', 'Layar retak parah')
        ->call('submitDamageReport')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('damage_reports', [
        'asset_id' => $this->asset->id,
        'reported_by' => $this->member->id,
        'status' => 'open',
        'severity' => null,
    ]);
});

test('profile forces damage email preference to true and stores complete schema', function () {
    $this->actingAs($this->staff);

    Volt::test('settings.profile')
        ->set('notify_membership', false)
        ->set('notify_borrowing', false)
        ->set('notify_damage', false)
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($this->staff->fresh()->notification_preferences)->toBe([
        'membership' => false,
        'borrowing' => false,
        'damage' => true,
    ]);
});

test('notification preference request validates schema', function () {
    expect(UpdateNotificationPreferencesRequest::validatePreferences([
        'membership' => true,
        'borrowing' => false,
        'damage' => true,
    ]))->toBe(['membership' => true, 'borrowing' => false, 'damage' => true]);

    // damage dimatikan -> ditolak
    expect(fn () => UpdateNotificationPreferencesRequest::validatePreferences([
        'membership' => true, 'borrowing' => true, 'damage' => false,
    ]))->toThrow(ValidationException::class);

    // key hilang -> ditolak
    expect(fn () => UpdateNotificationPreferencesRequest::validatePreferences([
        'membership' => true, 'damage' => true,
    ]))->toThrow(ValidationException::class);

    // tipe malformed -> ditolak
    expect(fn () => UpdateNotificationPreferencesRequest::validatePreferences([
        'membership' => 'abc', 'borrowing' => true, 'damage' => true,
    ]))->toThrow(ValidationException::class);
});

test('malformed stored preferences fall back safely', function () {
    $this->staff->forceFill(['notification_preferences' => ['membership' => 'garbage']])->save();

    expect($this->staff->fresh()->wantsEmailFor('membership'))->toBeTrue()
        ->and($this->staff->fresh()->wantsEmailFor('borrowing'))->toBeTrue();
});
