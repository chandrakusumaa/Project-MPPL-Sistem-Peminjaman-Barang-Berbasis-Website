<?php

use App\Actions\BorrowAsset;
use App\Actions\DeleteAccount;
use App\Actions\InviteMember;
use App\Actions\MaintainAsset;
use App\Actions\ManageAssets;
use App\Actions\ManageMembers;
use App\Actions\ManageWorkspace;
use App\Actions\SettleCharge;
use App\Livewire\Home;
use App\Livewire\Profile;
use App\Livewire\Workspace;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLog;
use App\Models\AuditLog;
use App\Models\BorrowerCharge;
use App\Models\Borrowing;
use App\Models\ChargePayment;
use App\Models\DamageReport;
use App\Models\Membership;
use App\Models\MembershipRequest;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
    $this->seed();
    $this->admin = User::where('email', 'admin@example.test')->firstOrFail();
    $this->member = User::where('email', 'member@example.test')->firstOrFail();
    $this->staff = User::where('email', 'staff@example.test')->firstOrFail();
    $this->other = User::where('email', 'other@example.test')->firstOrFail();
    $this->org = Organization::firstOrFail();
    $this->asset = Asset::firstOrFail();
});
function requestFor($t, ?User $user = null): Borrowing
{
    return app(BorrowAsset::class)->request($user ?? $t->member, $t->org, $t->asset->id, ['borrow_date' => now('Asia/Jakarta')->toDateString(), 'due_date' => now('Asia/Jakarta')->addDays(2)->toDateString(), 'reason' => 'Dokumentasi kegiatan kampus', 'acknowledged' => true]);
}
function chargeFor($t): BorrowerCharge
{
    $b = requestFor($t);
    app(BorrowAsset::class)->decide($t->admin, $t->org, $b->id, 'approve');

    return BorrowerCharge::create(['organization_id' => $t->org->id, 'borrowing_id' => $b->id, 'asset_id' => $t->asset->id, 'borrower_id' => $t->member->id, 'charge_type' => 'late_fee', 'amount' => 150000, 'status' => 'issued', 'reason' => 'Fixture finansial', 'policy_basis' => 'Fixture tarif', 'issued_at' => now()->subMinute(), 'idempotency_key' => 'test:charge']);
}
function paymentFor($t, $c, int $amount, string $key): ChargePayment
{
    app(SettleCharge::class)->change($t->staff, $t->org, $c->id, 'payment', ['amount' => $amount, 'method' => 'cash', 'notes' => 'Diterima di sekretariat', 'paid_at' => now(), 'idempotency_key' => $key]);

    return ChargePayment::where('idempotency_key', $key)->firstOrFail();
}
it('runs on real MySQL and creates all sixteen domain tables', function () {
    expect(DB::getDriverName())->toBe('mysql');
    foreach (['users', 'organizations', 'organization_user', 'membership_requests', 'organization_invitations', 'asset_categories', 'assets', 'borrowings', 'damage_reports', 'asset_logs', 'audit_logs', 'notifications', 'password_reset_tokens', 'borrower_charges', 'charge_payments', 'charge_credits'] as $table) {
        expect(Schema::hasTable($table))->toBeTrue();
    }
});
it('renders public and authenticated pages from the database', function () {
    $this->get('/')->assertOk()->assertSee('Barang tertata');
    $this->get('/organizations')->assertOk()->assertSee('Peralatan Raka');
    $this->actingAs($this->admin)->get('/dashboard')->assertOk()->assertSee('Raka');
    $this->get('/o/'.$this->org->slug)->assertOk()->assertSee('Kamera Canon EOS');
});
it('registers normalized email and blocks unverified users', function () {
    $this->post('/register', ['name' => 'Baru', 'email' => ' NEW@example.test ', 'password' => 'Password123!', 'password_confirmation' => 'Password123!'])->assertRedirect('/login');
    $u = User::where('email', 'new@example.test')->firstOrFail();
    expect($u->email_verified_at)->toBeNull();
    $this->actingAs($u)->get('/dashboard')->assertRedirect('/email/verify');
});
it('creates a workspace and its local admin atomically through Livewire', function () {
    $this->actingAs($this->other);
    Livewire::test(Home::class)->set('name', 'Ruang Nadia')->call('create')->assertHasNoErrors();
    $org = Organization::where('name', 'Ruang Nadia')->firstOrFail();
    expect(Membership::where('organization_id', $org->id)->where('user_id', $this->other->id)->value('role'))->toBe('admin');
    expect(Gate::forUser($this->other)->allows('operate', $this->org))->toBeFalse();
});
it('rejects nonmembers and member attempts to mutate inventory', function () {
    $this->actingAs($this->other)->get('/o/'.$this->org->slug)->assertForbidden();
    $this->actingAs($this->member);
    Livewire::test(Workspace::class, ['organization' => $this->org])->set('categoryName', 'Illegal')->call('category')->assertForbidden();
});
it('does not reserve an asset and permits exactly one approval', function () {
    $b = requestFor($this);
    $second = requestFor($this, $this->staff);
    expect($this->asset->fresh()->status)->toBe('available');
    app(BorrowAsset::class)->decide($this->admin, $this->org, $b->id, 'approve');
    expect($b->fresh()->status)->toBe('borrowed');
    expect($this->asset->fresh()->status)->toBe('borrowed');
    expect(fn () => app(BorrowAsset::class)->decide($this->admin, $this->org, $second->id, 'approve'))->toThrow(ValidationException::class);
});
it('allows own borrowing approval by the explicit OD17 decision', function () {
    $b = requestFor($this, $this->admin);
    app(BorrowAsset::class)->decide($this->admin, $this->org, $b->id, 'approve');
    expect($b->fresh()->status)->toBe('borrowed');
});
it('rejects premature approval and duplicate open requests', function () {
    $b = app(BorrowAsset::class)->request($this->member, $this->org, $this->asset->id, ['borrow_date' => now('Asia/Jakarta')->addDay()->toDateString(), 'due_date' => now('Asia/Jakarta')->addDays(2)->toDateString(), 'reason' => 'Kegiatan dokumentasi kampus', 'acknowledged' => true]);
    expect(fn () => app(BorrowAsset::class)->decide($this->admin, $this->org, $b->id, 'approve'))->toThrow(ValidationException::class);
    expect(fn () => requestFor($this))->toThrow(ValidationException::class);
});
it('inspects once and keeps physical return separate from financial settlement', function () {
    $b = requestFor($this);
    app(BorrowAsset::class)->decide($this->admin, $this->org, $b->id, 'approve');
    app(BorrowAsset::class)->inspect($this->staff, $this->org, $b->id, ['condition' => 'major_damage', 'notes' => 'Lensa pecah saat digunakan']);
    expect($b->fresh()->status)->toBe('returned')->and($this->asset->fresh()->status)->toBe('maintenance');
    expect(DamageReport::where('borrowing_id', $b->id)->count())->toBe(1);
    expect(BorrowerCharge::count())->toBe(0);
    expect(fn () => app(BorrowAsset::class)->inspect($this->staff, $this->org, $b->id, ['condition' => 'good']))->toThrow(ValidationException::class);
    $r = DamageReport::firstOrFail();
    app(MaintainAsset::class)->handle($this->staff, $this->org, $r->id, 'finish', ['condition' => 'good', 'notes' => 'Lensa telah diganti']);
    expect($this->asset->fresh()->status)->toBe('available');
});
it('calculates partial pending paid and void without deleting history', function () {
    $c = chargeFor($this);
    $p = paymentFor($this, $c, 50000, 'p1');
    $s = app(SettleCharge::class);
    $s->change($this->admin, $this->org, $c->id, 'confirm', ['payment_id' => $p->id]);
    expect($c->balance)->toBe(100000)->and($c->settlement)->toBe('partial');
    $p2 = paymentFor($this, $c, 100000, 'p2');
    expect($c->balance)->toBe(100000);
    $s->change($this->admin, $this->org, $c->id, 'confirm', ['payment_id' => $p2->id]);
    expect($c->balance)->toBe(0)->and($c->settlement)->toBe('paid');
    $s->change($this->admin, $this->org, $c->id, 'void', ['payment_id' => $p->id, 'reason' => 'Salah pencatatan']);
    expect($c->balance)->toBe(50000)->and(ChargePayment::count())->toBe(2);
});
it('serializes balance changes and rejects excess payment confirmation', function () {
    $c = chargeFor($this);
    $p = paymentFor($this, $c, 100000, 'p1');
    $p2 = paymentFor($this, $c, 100000, 'p2');
    $s = app(SettleCharge::class);
    $s->change($this->admin, $this->org, $c->id, 'confirm', ['payment_id' => $p->id]);
    expect(fn () => $s->change($this->admin, $this->org, $c->id, 'confirm', ['payment_id' => $p2->id]))->toThrow(ValidationException::class);
    expect($c->balance)->toBe(50000);
});
it('enforces idempotency and credits only the current outstanding amount', function () {
    $c = chargeFor($this);
    $p = paymentFor($this, $c, 50000, 'p1');
    paymentFor($this, $c, 50000, 'p1');
    expect(ChargePayment::count())->toBe(1);
    $s = app(SettleCharge::class);
    $s->change($this->admin, $this->org, $c->id, 'confirm', ['payment_id' => $p->id]);
    $s->change($this->admin, $this->org, $c->id, 'credit', ['amount' => 100000, 'reason' => 'Kebijakan pembebasan', 'idempotency_key' => 'credit1']);
    expect($c->balance)->toBe(0)->and($c->settlement)->toBe('paid');
});
it('denies staff financial decisions and self financial approval', function () {
    $c = chargeFor($this);
    $p = paymentFor($this, $c, 50000, 'p1');
    $s = app(SettleCharge::class);
    expect(fn () => $s->change($this->staff, $this->org, $c->id, 'confirm', ['payment_id' => $p->id]))->toThrow(AuthorizationException::class);
    Membership::where('organization_id', $this->org->id)->where('user_id', $this->member->id)->update(['role' => 'admin']);
    expect(fn () => $s->change($this->member, $this->org, $c->id, 'confirm', ['payment_id' => $p->id]))->toThrow(ValidationException::class);
});
it('blocks revoking the last admin and borrowers with obligations', function () {
    $a = app(ManageMembers::class);
    $m = Membership::where('user_id', $this->admin->id)->firstOrFail();
    expect(fn () => $a->change($this->admin, $this->org, $m->id, 'member'))->toThrow(ValidationException::class);
    chargeFor($this);
    $m = Membership::where('user_id', $this->member->id)->firstOrFail();
    expect(fn () => $a->change($this->admin, $this->org, $m->id, 'revoked'))->toThrow(ValidationException::class);
});
it('rejects cross workspace categories at the database boundary', function () {
    $org = app(ManageWorkspace::class)->create($this->other, ['name' => 'Other', 'category' => 'lainnya']);
    $cat = AssetCategory::create(['organization_id' => $org->id, 'name' => 'Other']);
    expect(fn () => DB::table('assets')->insert(['organization_id' => $this->org->id, 'asset_category_id' => $cat->id, 'code' => (string) Str::ulid(), 'name' => 'Invalid', 'location' => 'X', 'status' => 'available', 'physical_condition' => 'good']))->toThrow(QueryException::class);
});
it('does not disclose private catalog on public organization profiles', function () {
    $this->get('/ruang/'.$this->org->slug)->assertOk()->assertSee('Peralatan Raka')->assertDontSee('Kamera Canon EOS');
});
it('reads stable QR and asset details only as an active member', function () {
    $this->actingAs($this->admin)->get(route('asset.qr', [$this->org, $this->asset]))->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
    $this->get(route('asset.show', [$this->org, $this->asset]))->assertOk()->assertSee($this->asset->name);
    $this->actingAs($this->other)->get(route('asset.show', [$this->org, $this->asset]))->assertForbidden();
});
it('keeps code and ownership unchanged through asset editing', function () {
    $old = $this->asset->code;
    app(ManageAssets::class)->update($this->admin, $this->org, $this->asset->id, ['name' => 'Nama baru', 'location' => 'Lemari baru', 'asset_category_id' => $this->asset->asset_category_id, 'code' => 'INJECTED', 'organization_id' => 999]);
    expect($this->asset->fresh()->code)->toBe($old)->and($this->asset->fresh()->organization_id)->toBe($this->org->id);
});
it('generates administrator CSV and forbids staff exports', function () {
    $this->actingAs($this->admin)->get(route('export', [$this->org, 'assets']))->assertOk()->assertDownload('assets.csv');
    $this->actingAs($this->staff)->get(route('export', [$this->org, 'assets']))->assertForbidden();
});
it('shows payment metadata to its owner and refuses cross account evidence', function () {
    $c = chargeFor($this);
    Storage::fake('local');
    Storage::disk('local')->put('evidence/private.pdf', 'fixture');
    $c->update(['evidence_path' => 'evidence/private.pdf']);
    $this->actingAs($this->member)->get(route('evidence', $c))->assertDownload();
    $this->actingAs($this->other)->get(route('evidence', $c))->assertForbidden();
});
it('enforces one pending join request and preserves admin role on repeat review', function () {
    $a = app(ManageWorkspace::class);
    $a->join($this->other, $this->org);
    $a->join($this->other, $this->org);
    $requests = MembershipRequest::where('user_id', $this->other->id)->get();
    expect($requests)->toHaveCount(1);
    $a->review($this->admin, $this->org, $requests[0]->id, true);
    expect(Gate::forUser($this->other)->allows('view', $this->org))->toBeTrue();
    expect(fn () => $a->review($this->admin, $this->org, $requests[0]->id, true))->toThrow(ValidationException::class);
});
it('prevents editing audit or credit history through models', function () {
    $b = requestFor($this);
    $audit = AuditLog::firstOrFail();
    expect(fn () => $audit->update(['action' => 'changed']))->toThrow(LogicException::class);
});
it('expires and rejects invitations for the wrong email', function () {
    Mail::fake();
    $token = str_repeat('a', 64);
    $i = OrganizationInvitation::create(['organization_id' => $this->org->id, 'email' => $this->other->email, 'role' => 'member', 'token_hash' => hash('sha256', $token), 'invited_by' => $this->admin->id, 'expires_at' => now()->addDay(), 'status' => 'pending']);
    expect(fn () => app(InviteMember::class)->accept($this->member, $token))->toThrow(ValidationException::class);
    app(InviteMember::class)->accept($this->other, $token);
    expect($i->fresh()->status)->toBe('accepted');
    expect(fn () => app(InviteMember::class)->accept($this->other, $token))->toThrow(ValidationException::class);
});
it('blocks deleting sole administrators and soft deletes an account without obligations', function () {
    expect(fn () => app(DeleteAccount::class)->execute($this->admin))->toThrow(ValidationException::class);
    app(DeleteAccount::class)->execute($this->other);
    expect($this->other->fresh()->trashed())->toBeTrue();
});
it('completes the financial example using Livewire forms and renders the computed balance', function () {
    $c = chargeFor($this);
    $this->actingAs($this->staff);
    Livewire::test(Workspace::class, ['organization' => $this->org])->set('amount', '50000')->set('paymentNotes', 'Diterima tunai')->call('charge', $c->id, 'payment')->assertHasNoErrors();
    $p = $c->payments()->firstOrFail();
    expect($p->status)->toBe('pending');
    $this->actingAs($this->admin);
    Livewire::test(Workspace::class, ['organization' => $this->org])->set('tab', 'charges')->call('charge', $c->id, 'confirm', $p->id)->assertHasNoErrors()->assertSee('100.000');
});
it('makes a full credit waived and disallows cancelling confirmed or credited charges', function () {
    $c = chargeFor($this);
    $s = app(SettleCharge::class);
    $s->change($this->admin, $this->org, $c->id, 'credit', ['amount' => 150000, 'reason' => 'Pembebasan penuh', 'idempotency_key' => 'waive']);
    expect($c->balance)->toBe(0)->and($c->settlement)->toBe('waived');
    expect(fn () => $s->change($this->admin, $this->org, $c->id, 'cancel', ['reason' => 'Tidak sah']))->toThrow(ValidationException::class);
});
it('retains rejected payment history when a charge is cancelled', function () {
    $c = chargeFor($this);
    paymentFor($this, $c, 100000, 'pending');
    app(SettleCharge::class)->change($this->admin, $this->org, $c->id, 'cancel', ['reason' => 'Salah penerbitan']);
    expect($c->fresh()->status)->toBe('cancelled')->and($c->payments()->firstOrFail()->status)->toBe('rejected')->and(ChargePayment::count())->toBe(1);
});
it('issues a damage charge only with assessment evidence and acknowledged policy', function () {
    $b = requestFor($this);
    app(BorrowAsset::class)->decide($this->admin, $this->org, $b->id, 'approve');
    app(BorrowAsset::class)->inspect($this->staff, $this->org, $b->id, ['condition' => 'minor_damage', 'notes' => 'Gores pada lensa kamera']);
    $r = DamageReport::firstOrFail();
    $s = app(SettleCharge::class);
    $data = ['damage_report_id' => $r->id, 'charge_type' => 'damage', 'amount' => 150000, 'reason' => 'Biaya penggantian lensa berdasarkan kuitansi', 'idempotency_key' => 'damage1'];
    expect(fn () => $s->draft($this->staff, $this->org, $b->id, $data))->toThrow(ValidationException::class);
    $c = $s->draft($this->staff, $this->org, $b->id, $data + ['evidence_path' => 'evidence/fixture.jpg']);
    expect($c->status)->toBe('draft');
    $s->change($this->admin, $this->org, $c->id, 'issue');
    expect($c->fresh()->status)->toBe('issued');
    expect(fn () => $s->draft($this->staff, $this->org, $b->id, array_replace($data, ['charge_type' => 'loss', 'idempotency_key' => 'loss', 'evidence_path' => 'evidence/fixture.jpg'])))->toThrow(ValidationException::class);
});
it('uses approval snapshots for late fees and requires an override reason', function () {
    $b = requestFor($this);
    app(BorrowAsset::class)->decide($this->admin, $this->org, $b->id, 'approve');
    $this->org->update(['late_fine_per_day' => 9000]);
    $this->travel(4)->days();
    expect(fn () => app(BorrowAsset::class)->inspect($this->staff, $this->org, $b->id, ['condition' => 'good', 'fine_amount' => 1]))->toThrow(ValidationException::class);
    app(BorrowAsset::class)->inspect($this->staff, $this->org, $b->id, ['condition' => 'good']);
    expect((int) $b->fresh()->fine_amount)->toBe(10000)->and((int) BorrowerCharge::firstOrFail()->amount)->toBe(10000);
    $this->travelBack();
});
it('marks overdue once with the business calendar and a unique history event', function () {
    $b = requestFor($this);
    app(BorrowAsset::class)->decide($this->admin, $this->org, $b->id, 'approve');
    $this->travel(4)->days();
    $this->artisan('inventory:overdue')->assertSuccessful();
    $this->artisan('inventory:overdue')->assertSuccessful();
    expect($b->fresh()->status)->toBe('overdue')->and(AssetLog::where('event', 'overdue')->count())->toBe(1);
    $this->travelBack();
});
it('verifies signed email links and rejects a forged link', function () {
    $u = User::factory()->unverified()->create();
    $this->actingAs($u);
    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), ['id' => $u->id, 'hash' => sha1($u->email)]);
    $this->get($url.'forged')->assertForbidden();
    $this->get($url)->assertRedirect('/dashboard');
    expect($u->fresh()->hasVerifiedEmail())->toBeTrue();
});
it('resets a password through the Laravel broker and accepts the new login', function () {
    $token = Password::createToken($this->member);
    $this->post('/reset-password', ['email' => $this->member->email, 'token' => $token, 'password' => 'ChangedPassword123!', 'password_confirmation' => 'ChangedPassword123!'])->assertRedirect('/login');
    $this->post('/login', ['email' => $this->member->email, 'password' => 'ChangedPassword123!'])->assertRedirect('/dashboard');
});
it('keeps the old email until the signed replacement is verified', function () {
    Mail::fake();
    $old = $this->member->email;
    $this->actingAs($this->member);
    Livewire::test(Profile::class)->set('email', 'replacement@example.test')->call('emailChange')->assertHasNoErrors();
    $u = $this->member->fresh();
    expect($u->email)->toBe($old);
    $url = URL::temporarySignedRoute('profile.email.verify', now()->addHour(), ['user' => $u->id, 'hash' => hash('sha256', $u->pending_email), 'version' => $u->pending_email_requested_at->getTimestamp()]);
    $this->get($url)->assertRedirect('/profile');
    expect($u->fresh()->email)->toBe('replacement@example.test');
});
it('enforces read only archives and keeps admin membership for unarchive', function () {
    app(ManageMembers::class)->archive($this->admin, $this->org);
    $org = $this->org->fresh();
    expect($org->archived_at)->not->toBeNull();
    expect(fn () => app(ManageWorkspace::class)->category($this->admin, $org, 'Forbidden'))->toThrow(ValidationException::class);
    app(ManageMembers::class)->archive($this->admin, $org);
    expect($org->fresh()->archived_at)->toBeNull();
});

it('requires explicit consent for a historical borrowing without a policy snapshot', function () {
    $b = requestFor($this);
    app(BorrowAsset::class)->decide($this->admin, $this->org, $b->id, 'approve');
    // Model an imported historical row; never infer consent from a new policy.
    $b->update(['damage_policy_snapshot' => null, 'damage_policy_acknowledged_at' => null]);
    app(BorrowAsset::class)->inspect($this->staff, $this->org, $b->id, ['condition' => 'minor_damage', 'notes' => 'Lensa mengalami goresan']);
    $r = DamageReport::firstOrFail();
    $service = app(SettleCharge::class);
    $c = $service->draft($this->staff, $this->org, $b->id, ['damage_report_id' => $r->id, 'charge_type' => 'damage', 'amount' => 150000, 'reason' => 'Penggantian komponen sesuai kuitansi', 'policy_basis' => 'Usulan persetujuan tanggung jawab khusus', 'evidence_path' => 'fixture.jpg', 'idempotency_key' => 'historical']);
    expect(fn () => $service->change($this->admin, $this->org, $c->id, 'issue'))->toThrow(ValidationException::class);
    $service->change($this->member, $this->org, $c->id, 'acknowledge');
    $service->change($this->admin, $this->org, $c->id, 'issue');
    expect($c->fresh()->status)->toBe('issued')->and($c->fresh()->liability_acknowledged_at)->not->toBeNull();
});
it('exports the derived balance without counting a pending payment as revenue', function () {
    $c = chargeFor($this);
    paymentFor($this, $c, 100000, 'pending-csv');
    $response = $this->actingAs($this->admin)->get(route('export', [$this->org, 'charges']))->assertOk();
    $csv = $response->streamedContent();
    expect(str_starts_with($csv, "\xEF\xBB\xBF"))->toBeTrue()->and($csv)->toContain('Dibayar confirmed')->toContain('150000,issued,0,0,150000,unpaid');
});
it('renders a printable stable QR and an actual PNG on the PHP image', function () {
    $this->actingAs($this->admin);
    $this->get(route('asset.qr', [$this->org, $this->asset, 'format' => 'sticker']))->assertOk()->assertSee($this->asset->code);
    $png = $this->get(route('asset.qr', [$this->org, $this->asset, 'format' => 'png']))->assertOk()->assertHeader('Content-Type', 'image/png')->getContent();
    expect(substr($png, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});
it('audits draft revisions and keeps issued charge amounts immutable', function () {
    $c = chargeFor($this);
    $c->update(['status' => 'draft', 'issued_at' => null]);
    $s = app(SettleCharge::class);
    $s->change($this->staff, $this->org, $c->id, 'edit', ['amount' => 120000, 'reason' => 'Penyesuaian nominal berdasarkan kuitansi']);
    expect((int) $c->fresh()->amount)->toBe(120000);
    $audit = AuditLog::where('action', 'charge.edit')->firstOrFail();
    expect(json_decode($audit->old_values, true)['amount'])->toBe('150000');
    $s->change($this->admin, $this->org, $c->id, 'issue');
    expect(fn () => $s->change($this->admin, $this->org, $c->id, 'edit', ['amount' => 1, 'reason' => 'Tidak boleh mengubah histori']))->toThrow(ValidationException::class);
    expect((int) $c->fresh()->amount)->toBe(120000);
});
it('documents loss without automatic damage billing or inventing a known physical condition', function () {
    $b = requestFor($this);
    app(BorrowAsset::class)->decide($this->admin, $this->org, $b->id, 'approve');
    app(BorrowAsset::class)->inspect($this->staff, $this->org, $b->id, ['condition' => 'lost', 'notes' => 'Kamera hilang saat perjalanan']);
    expect($this->asset->fresh()->status)->toBe('lost')->and($this->asset->fresh()->physical_condition)->toBe('unknown')->and(DamageReport::count())->toBe(0)->and(BorrowerCharge::count())->toBe(0);
    app(MaintainAsset::class)->report($this->staff, $this->org, $this->asset->id, 'Laporan kehilangan terkait peminjaman', 'evidence/loss.jpg', $b->id);
    $r = DamageReport::firstOrFail();
    app(MaintainAsset::class)->handle($this->staff, $this->org, $r->id, 'note', ['severity' => 'major', 'notes' => 'Hilang berdasarkan laporan kegiatan', 'condition' => 'good']);
    expect($this->asset->fresh()->physical_condition)->toBe('unknown');
    $c = app(SettleCharge::class)->draft($this->staff, $this->org, $b->id, ['damage_report_id' => $r->id, 'charge_type' => 'loss', 'amount' => 150000, 'reason' => 'Penilaian kewajiban kehilangan berdasarkan bukti', 'idempotency_key' => 'loss-final']);
    app(SettleCharge::class)->change($this->admin, $this->org, $c->id, 'issue');
    expect($c->fresh()->status)->toBe('issued');
});
