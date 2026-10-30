<?php

namespace App\Livewire;

use App\Actions\BorrowAsset;
use App\Actions\MaintainAsset;
use App\Actions\ManageAssets;
use App\Actions\ManageMembers;
use App\Actions\ManageWorkspace;
use App\Actions\SettleCharge;
use App\Livewire\Concerns\SwitchesWorkspaces;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AuditLog;
use App\Models\BorrowerCharge;
use App\Models\Borrowing;
use App\Models\ChargePayment;
use App\Models\DamageReport;
use App\Models\Membership;
use App\Models\Organization;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Workspace extends Component
{
    use SwitchesWorkspaces,WithFileUploads, WithPagination;

    #[Locked]
    public int $organizationId;

    #[Locked]
    public int $focusedAsset = 0;

    public string $assetSort = 'name';

    public string $borrowFrom = '';

    public string $borrowTo = '';

    public string $borrowSearch = '';

    public string $reportSeverity = '';

    public string $reportSource = '';

    public int $filterCategory = 0;

    #[Url(except: 'dashboard')]
    public string $tab = 'dashboard';

    public bool $assetFormOpen = false;

    public string $search = '';

    public string $categoryName = '';

    public string $assetName = '';

    public string $location = '';

    public string $reason = '';

    public string $borrowDate = '';

    public string $dueDate = '';

    public string $notes = '';

    public string $condition = 'good';

    public string $amount = '';

    public string $chargeType = 'damage';

    public string $method = 'cash';

    public string $reference = '';

    public string $paymentNotes = '';

    public string $policy = '';

    public string $idempotencyKey = '';

    public int $categoryId = 0;

    public int $assetId = 0;

    public int $borrowingId = 0;

    public int $reportId = 0;

    public int $chargeId = 0;

    public bool $acknowledged = false;

    public $evidence;

    public $assetPhoto;

    public string $returnedAt = '';

    public string $fineOverride = '';

    public string $fineOverrideReason = '';

    public string $paidAt = '';

    public string $filterStatus = '';

    public string $borrowStatus = '';

    public string $chargeStatus = '';

    public string $reportStatus = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public string $deleteConfirmation = '';

    public string $assetDescription = '';

    public string $assetSpecifications = '';

    public function renameCategory(int $id): void
    {
        $this->validate(['categoryName' => 'required|string|max:120']);
        app(ManageWorkspace::class)->categoryRename(auth()->user(), $this->org(), $id, $this->categoryName);
        $this->success();
    }

    public function deleteCategory(int $id): void
    {
        app(ManageWorkspace::class)->categoryDelete(auth()->user(), $this->org(), $id);
        $this->success();
    }

    public function deleteOrganization()
    {
        app(ManageMembers::class)->delete(auth()->user(), $this->org(), $this->deleteConfirmation);

        return redirect('/dashboard');
    }

    public int $editingAsset = 0;

    public function editAsset(int $id): void
    {
        $org = $this->org();
        Gate::authorize('operate', $org);
        $a = Asset::where('organization_id', $org->id)->findOrFail($id);
        $this->editingAsset = $a->id;
        $this->assetName = $a->name;
        $this->location = $a->location;
        $this->categoryId = $a->asset_category_id;
        $this->assetDescription = $a->description ?? '';
        $this->assetSpecifications = $a->specifications ?? '';
    }

    public function deleteAsset(int $id): void
    {
        app(ManageAssets::class)->delete(auth()->user(), $this->org(), $id);
        $this->success();
    }

    public function found(int $id): void
    {
        app(ManageAssets::class)->found(auth()->user(), $this->org(), $id, $this->condition, $this->notes);
        $this->success();
    }

    public string $severity = 'minor';

    public string $settingName = '';

    public string $settingPolicy = '';

    public int $maxDays = 7;

    public int $dailyFine = 0;

    public function report(int $assetId): void
    {
        $org = $this->org();
        $path = null;
        if ($this->evidence) {
            $this->validate(['evidence' => 'file|mimes:jpg,jpeg,png|max:2048']);
            $path = $this->evidence->store('evidence/'.$org->id, 'local');
        }app(MaintainAsset::class)->report(auth()->user(), $org, $assetId, $this->notes, $path, $this->borrowingId ?: null);
        $this->success();
    }

    public function maintain(int $id, string $action): void
    {
        abort_unless(in_array($action, ['maintain', 'note', 'finish']), 422);
        app(MaintainAsset::class)->handle(auth()->user(), $this->org(), $id, $action, ['notes' => $this->notes, 'severity' => $this->severity, 'condition' => $this->condition]);
        $this->success();
    }

    public function member(int $id, string $role): void
    {
        app(ManageMembers::class)->change(auth()->user(), $this->org(), $id, $role);
        $this->success();
    }

    public function settings(): void
    {
        $this->validate(['settingName' => 'required|string|max:150', 'settingPolicy' => 'nullable|string|max:10000', 'maxDays' => 'required|integer|min:1|max:365', 'dailyFine' => 'required|integer|min:0|max:999999999999']);
        app(ManageMembers::class)->settings(auth()->user(), $this->org(), ['name' => $this->settingName, 'damage_charge_policy' => $this->settingPolicy ?: null, 'max_borrow_days' => $this->maxDays, 'late_fine_per_day' => $this->dailyFine]);
        $this->success();
    }

    public function archive(): void
    {
        app(ManageMembers::class)->archive(auth()->user(), $this->org());
        $this->success();
    }

    public function mount(Organization $organization, ?Asset $asset = null): void
    {
        if (! Gate::allows('view', $organization)) {
            abort(response()->view('join-required', ['org' => $organization], 403));
        }
        if ($asset?->exists) {
            abort_unless($asset->organization_id === $organization->id, 404);
            $this->focusedAsset = $asset->id;
        }
        if ($asset?->exists) {
            $this->tab = 'inventory';
        } elseif (! Gate::allows('operate', $organization) && ! request()->has('tab')) {
            $this->tab = 'inventory';
        }
        $this->organizationId = $organization->id;
        $this->settingName = $organization->name;
        $this->settingPolicy = $organization->damage_charge_policy ?? '';
        $this->maxDays = $organization->max_borrow_days;
        $this->dailyFine = (int) $organization->late_fine_per_day;
        $this->borrowDate = now('Asia/Jakarta')->toDateString();
        $this->dueDate = now('Asia/Jakarta')->addDay()->toDateString();
        $this->idempotencyKey = (string) Str::uuid();
    }

    public function org(): Organization
    {
        $o = Organization::findOrFail($this->organizationId);
        Gate::authorize('view', $o);

        return $o;
    }

    public function category(): void
    {
        $this->validate(['categoryName' => 'required|string|max:120']);
        app(ManageWorkspace::class)->category(auth()->user(), $this->org(), $this->categoryName);
        $this->success();
    }

    public function asset(): void
    {
        $this->validate(['assetName' => 'required|string|max:180', 'categoryId' => 'required|integer|min:1', 'location' => 'required|string|max:180', 'assetPhoto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048']);
        $photo = $this->assetPhoto ? $this->assetPhoto->store('assets', 'public') : null;
        if ($this->editingAsset) {
            app(ManageAssets::class)->update(auth()->user(), $this->org(), $this->editingAsset, ['name' => $this->assetName, 'asset_category_id' => $this->categoryId, 'location' => $this->location, 'description' => $this->assetDescription, 'specifications' => $this->assetSpecifications] + ($photo ? ['photo' => $photo] : []));
        } else {
            app(ManageWorkspace::class)->asset(auth()->user(), $this->org(), ['name' => $this->assetName, 'asset_category_id' => $this->categoryId, 'location' => $this->location, 'description' => $this->assetDescription, 'specifications' => $this->assetSpecifications] + ($photo ? ['photo' => $photo] : []));
        }$this->editingAsset = 0;
        $this->success();
    }

    public function requestBorrow(int $id): void
    {
        $this->validate(['borrowDate' => 'required|date_format:Y-m-d', 'dueDate' => 'required|date_format:Y-m-d', 'reason' => 'required|min:10|max:3000']);
        app(BorrowAsset::class)->request(auth()->user(), $this->org(), $id, ['borrow_date' => $this->borrowDate, 'due_date' => $this->dueDate, 'reason' => $this->reason, 'acknowledged' => $this->acknowledged]);
        $this->success();
    }

    public function decide(int $id, string $decision): void
    {
        abort_unless(in_array($decision, ['approve', 'reject', 'cancel']), 422);
        app(BorrowAsset::class)->decide(auth()->user(), $this->org(), $id, $decision, $this->notes);
        $this->success();
    }

    public function inspect(int $id): void
    {
        app(BorrowAsset::class)->inspect(auth()->user(), $this->org(), $id, ['condition' => $this->condition, 'notes' => $this->notes] + ($this->returnedAt ? ['returned_at' => CarbonImmutable::parse($this->returnedAt, 'Asia/Jakarta')->utc()] : []) + ($this->fineOverride !== '' ? ['fine_amount' => $this->validate(['fineOverride' => 'required|integer|min:0|max:99999999999999'])['fineOverride'], 'fine_override_reason' => $this->fineOverrideReason] : []));
        $this->success();
    }

    public function draft(): void
    {
        $this->validate(['borrowingId' => 'required|integer|min:1', 'reportId' => 'required|integer|min:1', 'amount' => 'required|integer|min:1|max:99999999999999', 'reason' => 'required|min:10', 'chargeType' => 'required|in:damage,loss', 'evidence' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048']);
        $org = $this->org();
        Gate::authorize('operate', $org);
        $path = $this->evidence->store('evidence/'.$org->id, 'local');
        app(SettleCharge::class)->draft(auth()->user(), $org, $this->borrowingId, ['damage_report_id' => $this->reportId, 'charge_type' => $this->chargeType, 'amount' => $this->amount, 'reason' => $this->reason, 'policy_basis' => $this->policy, 'evidence_path' => $path, 'idempotency_key' => $this->idempotencyKey]);
        $this->success();
    }

    public function charge(int $id, string $action, int $paymentId = 0): void
    {
        abort_unless(in_array($action, ['issue', 'cancel', 'payment', 'confirm', 'reject', 'void', 'credit', 'acknowledge', 'edit']), 422);
        if (in_array($action, ['payment', 'credit', 'edit'])) {
            $this->validate(['amount' => 'required|integer|min:1|max:99999999999999']);
        }
        app(SettleCharge::class)->change(auth()->user(), $this->org(), $id, $action, ['amount' => $this->amount, 'reason' => $this->notes, 'payment_id' => $paymentId, 'method' => $this->method, 'reference' => $this->reference, 'notes' => $this->paymentNotes, 'paid_at' => $this->paidAt ? CarbonImmutable::parse($this->paidAt, 'Asia/Jakarta')->utc() : now()->toDateTimeString(), 'idempotency_key' => $this->idempotencyKey]);
        $this->success();
    }

    protected function success(): void
    {
        $this->idempotencyKey = (string) Str::uuid();
        session()->flash('status', 'Perubahan berhasil disimpan.');
        $this->resetValidation();
    }

    public function openAssetForm(string $field = 'assetName'): void
    {
        Gate::authorize('operate', $this->org());
        abort_unless(in_array($field, ['assetName', 'categoryName', 'location']), 422);
        $this->tab = 'inventory';
        $this->assetFormOpen = true;
        $this->dispatch('oi-focus-field', field: $field);
    }

    public function reviewBorrowing(int $id): void
    {
        Gate::authorize('operate', $this->org());
        Borrowing::where('organization_id', $this->organizationId)->where('status', 'pending')->findOrFail($id);
        $this->reset('borrowFrom', 'borrowTo', 'borrowSearch');
        $this->tab = 'borrowings';
        $this->borrowStatus = 'pending';
        $this->resetPage('borrowingsPage');
        $this->dispatch('oi-review-borrowing', id: $id);
    }

    public function showOverdue(): void
    {
        Gate::authorize('operate', $this->org());
        $this->tab = 'borrowings';
        $this->borrowStatus = 'overdue';
        $this->resetPage('borrowingsPage');
    }

    public function showDamageReports(): void
    {
        $this->tab = 'maintenance';
        $this->reportStatus = '';
    }

    public function showMaintenance(): void
    {
        Gate::authorize('operate', $this->org());
        $this->tab = 'maintenance';
        $this->reportStatus = 'in_maintenance';
    }

    public function render()
    {
        $org = $this->org();
        $operator = Gate::allows('operate', $org);
        $admin = Gate::allows('administer', $org);
        abort_unless(in_array($this->tab, ['dashboard', 'inventory', 'borrowings', 'charges', 'members', 'audit', 'maintenance', 'settings']), 404);
        if (in_array($this->tab, ['members', 'audit', 'settings'])) {
            Gate::authorize('administer', $org);
        }

        if ($this->tab === 'dashboard') {
            Gate::authorize('operate', $org);
        }

        $monthly = collect();
        $start = now('Asia/Jakarta')->startOfMonth()->subMonths(5);
        $actual = Borrowing::where('organization_id', $org->id)->whereNotNull('approved_at')->where('approved_at', '>=', $start->copy()->utc())->selectRaw("DATE_FORMAT(CONVERT_TZ(approved_at,'+00:00','+07:00'),'%Y-%m') as month, COUNT(*) as total")->groupBy('month')->pluck('total', 'month');
        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i)->format('Y-m');
            $monthly[$month] = $actual[$month] ?? 0;
        }
        $top = DB::table('borrowings')->join('assets', 'assets.id', '=', 'borrowings.asset_id')->where('borrowings.organization_id', $org->id)->whereNotNull('borrowings.approved_at')->groupBy('assets.id', 'assets.name')->selectRaw('assets.name, COUNT(*) as total')->orderByDesc('total')->limit(5)->pluck('total', 'name');

        $summary = [];
        if ($operator && $this->tab === 'dashboard') {
            $borrowingCounts = Borrowing::where('organization_id', $org->id)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
            $issued = BorrowerCharge::where('organization_id', $org->id)->where('status', 'issued')->withSum(['payments as confirmed_total' => fn ($q) => $q->where('status', 'confirmed')], 'amount')->withSum('credits', 'amount')->get(['id', 'amount']);
            $summary = [
                'pending' => (int) ($borrowingCounts['pending'] ?? 0),
                'borrowed' => (int) ($borrowingCounts['borrowed'] ?? 0) + (int) ($borrowingCounts['overdue'] ?? 0),
                'overdue' => (int) ($borrowingCounts['overdue'] ?? 0),
                'pendingRequests' => Borrowing::where('organization_id', $org->id)->where('status', 'pending')->with('asset', 'borrower')->latest()->limit(4)->get(),
                'overdueBorrowings' => Borrowing::where('organization_id', $org->id)->where('status', 'overdue')->with('asset', 'borrower')->orderBy('due_date')->limit(5)->get(),
                'recentAssets' => Asset::where('organization_id', $org->id)->latest()->orderByDesc('id')->limit(5)->get(),
                'reports' => DamageReport::where('organization_id', $org->id)->whereIn('status', ['open', 'in_maintenance'])->count(),
                'maintenance' => DamageReport::where('organization_id', $org->id)->where('status', 'in_maintenance')->count(),
                'members' => Membership::where('organization_id', $org->id)->where('status', 'active')->count(),
                'balance' => $issued->sum(fn ($charge) => (int) $charge->amount - (int) $charge->confirmed_total - (int) $charge->credits_sum_amount),
                'pendingPayments' => ChargePayment::where('organization_id', $org->id)->where('status', 'pending')->whereIn('charge_id', BorrowerCharge::where('organization_id', $org->id)->where('status', 'issued')->select('id'))->count(),
            ];
        }

        return view('livewire.workspace', ['summary' => $summary, 'workspaces' => $this->availableWorkspaces(), 'monthly' => $monthly, 'top' => $top, 'reports' => DamageReport::where('organization_id', $org->id)->when(! $operator, fn ($q) => $q->where('reported_by', auth()->id()))->when($this->reportStatus, fn ($q) => $q->where('status', $this->reportStatus))->when($this->reportSeverity, fn ($q) => $q->where('severity', $this->reportSeverity))->when($this->reportSource, fn ($q) => $q->where('source', $this->reportSource))->latest()->paginate(20, ['*'], 'reportsPage'), 'org' => $org, 'operator' => $operator, 'admin' => $admin, 'assets' => Asset::where('organization_id', $org->id)->when($this->focusedAsset, fn ($q) => $q->whereKey($this->focusedAsset))->where('name', 'like', '%'.$this->search.'%')->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))->when($this->filterCategory, fn ($q) => $q->where('asset_category_id', $this->filterCategory))->orderBy($this->assetSort === 'newest' ? 'created_at' : 'name', $this->assetSort === 'newest' ? 'desc' : 'asc')->with('category')->paginate(12), 'categories' => AssetCategory::where('organization_id', $org->id)->get(), 'borrowings' => Borrowing::where('organization_id', $org->id)->when(! $operator, fn ($q) => $q->where('user_id', auth()->id()))->when($this->borrowStatus, fn ($q) => $q->where('status', $this->borrowStatus))->when($this->borrowFrom, fn ($q) => $q->where('borrow_date', '>=', $this->borrowFrom))->when($this->borrowTo, fn ($q) => $q->where('borrow_date', '<=', $this->borrowTo))->when($this->borrowSearch, fn ($q) => $q->where(fn ($q) => $q->whereHas('asset', fn ($a) => $a->where('name', 'like', '%'.$this->borrowSearch.'%'))->orWhereHas('borrower', fn ($u) => $u->where('name', 'like', '%'.$this->borrowSearch.'%'))))->with('asset', 'borrower')->latest()->paginate(20, ['*'], 'borrowingsPage'), 'charges' => BorrowerCharge::where('organization_id', $org->id)->when(! $operator, fn ($q) => $q->where('borrower_id', auth()->id()))->when($this->chargeStatus, fn ($q) => $q->where('status', $this->chargeStatus))->with('payments', 'credits')->latest()->paginate(20, ['*'], 'chargesPage'), 'members' => $admin ? Membership::where('organization_id', $org->id)->with('user')->get() : collect(), 'audits' => $admin ? AuditLog::where('organization_id', $org->id)->latest('id')->limit(50)->get() : collect(), 'counts' => Asset::where('organization_id', $org->id)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')])->layout('components.layout', ['context' => 'workspace']);
    }
}
