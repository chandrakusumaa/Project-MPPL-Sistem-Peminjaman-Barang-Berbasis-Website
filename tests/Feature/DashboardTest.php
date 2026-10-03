<?php

use App\Models\User;
use App\Models\Organization;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Borrowing;
use App\Enums\Role;
use App\Enums\AssetStatus;
use App\Enums\BorrowingStatus;
use Carbon\Carbon;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->staff = User::factory()->create();
    $this->member = User::factory()->create();
    
    $this->organization = Organization::factory()->create();
    
    $this->organization->users()->attach($this->admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $this->organization->users()->attach($this->staff->id, ['role' => Role::STAFF->value, 'joined_at' => now()]);
    $this->organization->users()->attach($this->member->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);
    
    $this->category = AssetCategory::factory()->create(['organization_id' => $this->organization->id, 'name' => 'Test Category']);
    
    $this->asset = Asset::factory()->create([
        'organization_id' => $this->organization->id,
        'asset_category_id' => $this->category->id,
        'code' => \Illuminate\Support\Str::ulid()->toString(),
        'name' => 'Asset 1',
        'status' => AssetStatus::BORROWED->value
    ]);
});

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('member dashboard displays active borrowings and pending requests', function () {
    // Create an active borrowing
    Borrowing::factory()->create([
        'organization_id' => $this->organization->id,
        'asset_id' => $this->asset->id,
        'user_id' => $this->member->id,
        'borrow_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'reason' => 'Test reason',
        'status' => BorrowingStatus::BORROWED->value,
    ]);
    
    // Create a pending request
    $asset2 = Asset::factory()->create([
        'organization_id' => $this->organization->id,
        'asset_category_id' => $this->category->id,
        'code' => \Illuminate\Support\Str::ulid()->toString(),
        'name' => 'Asset 2',
        'status' => AssetStatus::AVAILABLE->value
    ]);
    Borrowing::factory()->create([
        'organization_id' => $this->organization->id,
        'asset_id' => $asset2->id,
        'user_id' => $this->member->id,
        'borrow_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'reason' => 'Test reason',
        'status' => BorrowingStatus::PENDING->value,
    ]);

    $this->actingAs($this->member);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee($this->asset->name); // Active borrowing
    $response->assertSee('pengajuan tertunda'); // Pending request alert
});

test('manage dashboard statistics are correct', function () {
    // Total 2 assets (1 borrowed, 1 available)
    Asset::factory()->create([
        'organization_id' => $this->organization->id,
        'asset_category_id' => $this->category->id,
        'code' => \Illuminate\Support\Str::ulid()->toString(),
        'name' => 'Asset 3',
        'status' => AssetStatus::AVAILABLE->value
    ]);
    
    // Create an overdue borrowing
    Borrowing::factory()->create([
        'organization_id' => $this->organization->id,
        'asset_id' => $this->asset->id,
        'user_id' => $this->member->id,
        'status' => BorrowingStatus::OVERDUE->value,
        'borrow_date' => now()->subDays(8)->format('Y-m-d'),
        'due_date' => now()->subDays(1)->format('Y-m-d'),
        'reason' => 'Test reason overdue',
    ]);

    $this->actingAs($this->admin);
    
    Volt::test('manage.dashboard', ['organization' => $this->organization])
        ->assertSee('Total Aset')
        ->assertSee('2') // 1 borrowed + 1 available
        ->assertSee('Overdue')
        ->assertSee('1')
        ->assertSet('borrowingStats.overdue', 1);
});

test('staff cannot access reports page', function () {
    $this->actingAs($this->staff);
    $response = $this->get(route('manage.reports.index', $this->organization->slug));
    $response->assertStatus(403);
});

test('admin can access reports page and export csv', function () {
    $this->actingAs($this->admin);
    
    $this->withoutExceptionHandling();
    $response = $this->get(route('manage.reports.index', $this->organization->slug));
    
    $response->assertStatus(200);
    
    // Test export
    Volt::test('manage.reports', ['organization' => $this->organization])
        ->call('exportAssets')
        ->assertFileDownloaded("assets_{$this->organization->slug}_" . now()->format('Ymd_His') . ".csv");
});
