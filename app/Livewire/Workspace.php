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
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Workspace extends Component
{
    use SwitchesWorkspaces, WithFileUploads, WithPagination;

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

    public bool $categoryFormOpen = false;

    #[Locked]
    public int $borrowAssetId = 0;

    #[Locked]
    public int $selectedBorrowingId = 0;

    #[Locked]
    public string $borrowingAction = '';

    public string $borrowingNotes = '';

    public string $inspectionCondition = 'good';

    #[Locked]
    public int $reportAssetId = 0;

    public string $reportDescription = '';

    public int $reportBorrowingId = 0;

    public $reportPhoto;

    #[Locked]
    public int $selectedReportId = 0;

    public bool $giveChargeOption = false;

    public bool $startMaintenanceOption = false;

    #[Locked]
    public int $maintenanceReportId = 0;

    public string $maintenanceCondition = 'good';

    public string $resolutionNotes = '';

    #[Locked]
    public int $selectedChargeId = 0;

    public string $chargeModalAction = 'payment';

    public string $search = '';

    public string $categoryName = '';

    public string $assetName = '';

    public string $location = '';

    public string $reason = '';

    public string $borrowDate = '';

    public string $dueDate = '';

    public string $borrowReason = '';

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
        $this->showAssetForm();
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

    public function openDamageReport(int $assetId): void
    {
        Asset::where('organization_id', $this->org()->id)->findOrFail($assetId);
        $this->reset('reportDescription', 'reportBorrowingId', 'reportPhoto');
        $this->resetValidation();
        $this->reportAssetId = $assetId;
        $this->dispatch('oi-focus-field', field: 'reportDescription');
    }

    public function closeDamageReport(): void
    {
        $assetId = $this->reportAssetId;
        $this->reset('reportAssetId', 'reportDescription', 'reportBorrowingId', 'reportPhoto');
        $this->resetValidation();
        $this->dispatch('oi-focus-field', field: 'report-button-'.$assetId);
    }

    public function report(): void
    {
        $org = $this->org();
        Asset::where('organization_id', $org->id)->findOrFail($this->reportAssetId);
        $this->reportDescription = trim($this->reportDescription);
        $this->validate([
            'reportDescription' => 'required|string|min:10',
            'reportBorrowingId' => 'required|integer|min:0',
            'reportPhoto' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ], [
            'reportDescription.required' => 'Uraian kerusakan wajib diisi.',
            'reportDescription.min' => 'Uraian kerusakan minimal 10 karakter.',
        ]);
        $path = $this->reportPhoto?->store('evidence/'.$org->id, 'local');
        app(MaintainAsset::class)->report(auth()->user(), $org, $this->reportAssetId, $this->reportDescription, $path, $this->reportBorrowingId ?: null);
        $this->closeDamageReport();
        $this->success();
        session()->flash('status', 'Laporan kerusakan berhasil dikirim.');
    }

    /**
     * Buka popup detail laporan kerusakan dan form opsi biaya kerusakan.
     */
    public function openReportModal(int $reportId): void
    {
        $org = $this->org();
        $r = DamageReport::where('organization_id', $org->id)
            ->when(! Gate::allows('operate', $org), fn ($q) => $q->where('reported_by', auth()->id()))
            ->with('charge')
            ->findOrFail($reportId);

        if ($this->tab !== 'damage-reports') {
            $this->showDamageReports();
        }
        $this->resetValidation();
        $this->selectedReportId = $r->id;
        $this->severity = $r->severity ?: 'minor';
        $this->notes = $r->assessment_notes ?: $r->description;
        $this->borrowingId = (int) ($r->borrowing_id ?: 0);
        $this->chargeType = $r->charge?->charge_type ?: 'damage';
        $this->amount = $r->charge ? (string) ((int) $r->charge->amount) : '';
        $this->reason = $r->charge?->reason ?: $r->description;
        $this->policy = $r->charge?->policy_basis ?: ($org->damage_charge_policy ?? '');
        $this->giveChargeOption = $r->charge !== null;
        $this->startMaintenanceOption = $r->status === 'in_maintenance';
        $this->evidence = null;
    }

    public function closeReportModal(): void
    {
        $this->reset('selectedReportId', 'giveChargeOption', 'startMaintenanceOption', 'evidence');
        $this->resetValidation();
    }

    /**
     * Simpan tindak lanjut laporan kerusakan dari popup beserta opsi biaya kerusakan (jika dipilih).
     */
    public function saveReportDecision(): void
    {
        $org = $this->org();
        Gate::authorize('operate', $org);
        $r = DamageReport::where('organization_id', $org->id)->findOrFail($this->selectedReportId);

        if ($this->giveChargeOption) {
            $this->validate([
                'amount' => 'required|integer|min:1|max:999999999999',
                'reason' => 'required|string|min:10',
                'chargeType' => 'required|in:damage,loss',
                'severity' => 'required|in:minor,major',
                'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ], [
                'amount.required' => 'Nominal biaya kerusakan wajib diisi.',
                'amount.min' => 'Nominal biaya kerusakan harus lebih dari Rp0.',
                'reason.required' => 'Alasan / rincian biaya minimal 10 karakter.',
                'reason.min' => 'Alasan / rincian biaya minimal 10 karakter.',
            ]);

            $path = $this->evidence ? $this->evidence->store('evidence/'.$org->id, 'local') : $r->photo;
            app(SettleCharge::class)->assignFromReport(auth()->user(), $org, $r->id, [
                'borrowing_id' => $this->borrowingId ?: $r->borrowing_id,
                'severity' => $this->severity,
                'charge_type' => $this->chargeType,
                'amount' => $this->amount,
                'reason' => $this->reason,
                'policy_basis' => $this->policy,
                'evidence_path' => $path,
                'start_maintenance' => $this->startMaintenanceOption,
                'idempotency_key' => $this->idempotencyKey,
            ]);
            $this->closeReportModal();
            $this->success();
            session()->flash('status', 'Laporan kerusakan diproses dan biaya kerusakan berhasil ditetapkan.');

            return;
        }

        if ($r->status === 'open') {
            $this->validate([
                'notes' => 'required|string|min:3',
                'severity' => 'required|in:minor,major',
            ], [
                'notes.required' => 'Catatan penilaian wajib diisi.',
                'notes.min' => 'Catatan penilaian minimal 3 karakter.',
            ]);
            $action = ($this->startMaintenanceOption || $this->severity === 'major') ? 'maintain' : 'note';
            app(MaintainAsset::class)->handle(auth()->user(), $org, $r->id, $action, [
                'notes' => $this->notes,
                'severity' => $this->severity,
                'condition' => $this->condition,
            ]);
        }

        $this->closeReportModal();
        $this->success();
    }

    /**
     * Buka popup modal untuk Catat Pembayaran, Beri Pembebasan, atau Batalkan Tagihan pada tagihan tertentu.
     */
    public function openChargeModal(int $chargeId, string $action = 'payment'): void
    {
        abort_unless(in_array($action, ['payment', 'credit', 'cancel']), 422);
        $org = $this->org();
        Gate::authorize('operate', $org);
        $c = BorrowerCharge::where('organization_id', $org->id)->with('payments', 'credits')->findOrFail($chargeId);

        $this->resetValidation();
        $this->selectedChargeId = $c->id;
        $this->chargeModalAction = $action;
        $this->amount = $action === 'cancel' ? '' : (string) max(1, (int) $c->balance);
        $this->method = 'cash';
        $this->reference = '';
        $this->paidAt = '';
        $this->paymentNotes = '';
        $this->notes = '';
    }

    public function closeChargeModal(): void
    {
        $this->reset('selectedChargeId', 'chargeModalAction', 'amount', 'reference', 'paidAt', 'paymentNotes', 'notes');
        $this->resetValidation();
    }

    /**
     * Simpan pencatatan pembayaran / pembebasan / pembatalan dari popup modal tagihan.
     */
    public function submitChargeModal(): void
    {
        $org = $this->org();
        Gate::authorize('operate', $org);
        $c = BorrowerCharge::where('organization_id', $org->id)->with('payments', 'credits')->findOrFail($this->selectedChargeId);

        if ($this->chargeModalAction === 'payment') {
            $this->validate([
                'amount' => 'required|integer|min:1|max:'.max(1, (int) $c->balance),
                'method' => 'required|in:cash,bank_transfer,other',
            ], [
                'amount.required' => 'Nominal pembayaran wajib diisi.',
                'amount.min' => 'Nominal pembayaran minimal Rp1.',
                'amount.max' => 'Nominal tidak boleh melebihi sisa tagihan (Rp'.number_format($c->balance, 0, ',', '.').').',
            ]);

            if ($this->method === 'bank_transfer' && trim($this->reference) === '') {
                $this->reference = 'TF-'.now('Asia/Jakarta')->format('YmdHis');
            }
            $noteText = trim($this->paymentNotes) !== '' ? trim($this->paymentNotes) : 'Pembayaran diterima oleh pengelola';

            app(SettleCharge::class)->change(auth()->user(), $org, $c->id, 'payment', [
                'amount' => (int) $this->amount,
                'method' => $this->method,
                'reference' => $this->reference ?: null,
                'notes' => $noteText,
                'paid_at' => $this->paidAt ? CarbonImmutable::parse($this->paidAt, 'Asia/Jakarta')->utc() : now()->toDateTimeString(),
                'idempotency_key' => $this->idempotencyKey,
            ]);
            $this->closeChargeModal();
            $this->success();
            session()->flash('status', 'Pembayaran berhasil dicatat.');

            return;
        }

        if ($this->chargeModalAction === 'credit') {
            $this->validate([
                'amount' => 'required|integer|min:1|max:'.max(1, (int) $c->balance),
                'notes' => 'required|string|min:3',
            ], [
                'amount.required' => 'Nominal pembebasan wajib diisi.',
                'amount.max' => 'Nominal pembebasan tidak boleh melebihi sisa tagihan.',
                'notes.required' => 'Alasan pembebasan wajib diisi (minimal 3 karakter).',
                'notes.min' => 'Alasan pembebasan minimal 3 karakter.',
            ]);

            app(SettleCharge::class)->change(auth()->user(), $org, $c->id, 'credit', [
                'amount' => (int) $this->amount,
                'reason' => trim($this->notes),
                'idempotency_key' => $this->idempotencyKey,
            ]);
            $this->closeChargeModal();
            $this->success();
            session()->flash('status', 'Pembebasan tagihan berhasil disimpan.');

            return;
        }

        if ($this->chargeModalAction === 'cancel') {
            $this->validate([
                'notes' => 'required|string|min:3',
            ], [
                'notes.required' => 'Alasan pembatalan tagihan wajib diisi (minimal 3 karakter).',
                'notes.min' => 'Alasan pembatalan minimal 3 karakter.',
            ]);

            app(SettleCharge::class)->change(auth()->user(), $org, $c->id, 'cancel', [
                'reason' => trim($this->notes),
                'idempotency_key' => $this->idempotencyKey,
            ]);
            $this->closeChargeModal();
            $this->success();
            session()->flash('status', 'Tagihan berhasil dibatalkan.');
        }
    }

    public function openMaintenanceModal(int $id): void
    {
        $org = $this->org();
        Gate::authorize('operate', $org);
        if ($org->archived_at) {
            throw ValidationException::withMessages(['action' => 'Organization diarsipkan dan hanya dapat dibaca.']);
        }
        DamageReport::where('organization_id', $org->id)->where('status', 'in_maintenance')->findOrFail($id);
        $this->closeReportModal();
        $this->closeMaintenanceModal();
        $this->tab = 'maintenance';
        $this->maintenanceReportId = $id;
    }

    public function closeMaintenanceModal(): void
    {
        $reportId = $this->maintenanceReportId;
        $this->reset('maintenanceReportId', 'maintenanceCondition', 'resolutionNotes');
        $this->resetValidation();
        if ($reportId) {
            $this->dispatch('oi-focus-field', field: 'finish-maintenance-'.$reportId);
        }
    }

    public function finishMaintenance(): void
    {
        $org = $this->org();
        Gate::authorize('operate', $org);
        abort_unless($this->maintenanceReportId, 404);
        $this->resolutionNotes = trim($this->resolutionNotes);
        $this->validate([
            'maintenanceCondition' => 'required|in:good,minor_damage',
            'resolutionNotes' => 'required|string|min:3',
        ], [
            'maintenanceCondition.in' => 'Pilih kondisi akhir aset.',
            'resolutionNotes.required' => 'Isi catatan perbaikan.',
            'resolutionNotes.min' => 'Catatan perbaikan minimal 3 karakter.',
        ]);
        app(MaintainAsset::class)->handle(auth()->user(), $org, $this->maintenanceReportId, 'finish', [
            'notes' => $this->resolutionNotes,
            'condition' => $this->maintenanceCondition,
        ]);
        $this->closeMaintenanceModal();
        $this->resetPage('maintenancePage');
        $this->success();
        session()->flash('status', 'Perbaikan selesai. Aset kembali tersedia.');
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
        $this->validate(['categoryName' => 'required|string|max:120'], [
            'categoryName.required' => 'Isi nama kategori.',
            'categoryName.max' => 'Nama kategori maksimal 120 karakter.',
        ]);
        app(ManageWorkspace::class)->category(auth()->user(), $this->org(), $this->categoryName);
        $this->closeCategoryForm();
        $this->success();
    }

    public function asset(): void
    {
        $this->validate(['assetName' => 'required|string|max:180', 'categoryId' => 'required|integer|min:1', 'location' => 'required|string|max:180', 'assetPhoto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'], [
            'assetName.required' => 'Isi nama aset.',
            'assetName.max' => 'Nama aset maksimal 180 karakter.',
            'categoryId.min' => 'Pilih kategori aset.',
            'location.required' => 'Isi lokasi penyimpanan aset.',
            'location.max' => 'Lokasi maksimal 180 karakter.',
            'assetPhoto.image' => 'Pilih foto dalam format JPG atau PNG.',
            'assetPhoto.mimes' => 'Pilih foto dalam format JPG atau PNG.',
            'assetPhoto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);
        $photo = $this->assetPhoto ? $this->assetPhoto->store('assets', 'public') : null;
        if ($this->editingAsset) {
            app(ManageAssets::class)->update(auth()->user(), $this->org(), $this->editingAsset, ['name' => $this->assetName, 'asset_category_id' => $this->categoryId, 'location' => $this->location, 'description' => $this->assetDescription, 'specifications' => $this->assetSpecifications] + ($photo ? ['photo' => $photo] : []));
        } else {
            app(ManageWorkspace::class)->asset(auth()->user(), $this->org(), ['name' => $this->assetName, 'asset_category_id' => $this->categoryId, 'location' => $this->location, 'description' => $this->assetDescription, 'specifications' => $this->assetSpecifications] + ($photo ? ['photo' => $photo] : []));
        }
        $this->closeAssetForm();
        $this->success();
    }

    public function openBorrowForm(int $id): void
    {
        $org = $this->org();
        Gate::authorize('requestBorrowing', $org);
        $asset = Asset::where('organization_id', $org->id)->findOrFail($id);
        if ($org->archived_at || $asset->status !== 'available') {
            throw ValidationException::withMessages(['action' => 'Aset tidak tersedia untuk dipinjam.']);
        }

        $today = CarbonImmutable::today('Asia/Jakarta');
        $this->borrowAssetId = $asset->id;
        $this->borrowDate = $today->toDateString();
        $this->dueDate = $today->addDay()->toDateString();
        $this->reset('borrowReason', 'acknowledged', 'assetFormOpen', 'categoryFormOpen');
        $this->resetValidation();
        $this->tab = 'inventory';
    }

    public function closeBorrowForm(): void
    {
        $assetId = $this->borrowAssetId;
        $this->reset('borrowAssetId', 'borrowDate', 'dueDate', 'borrowReason', 'acknowledged');
        $this->resetValidation();
        if ($assetId && $this->tab === 'inventory') {
            $this->dispatch('oi-focus-field', field: 'borrow-asset-'.$assetId);
        }
    }

    public function requestBorrow(): void
    {
        abort_unless($this->borrowAssetId, 404);
        $this->borrowReason = trim($this->borrowReason);
        $this->validate([
            'borrowDate' => 'required|date_format:Y-m-d',
            'dueDate' => 'required|date_format:Y-m-d',
            'borrowReason' => 'required|string|min:10|max:3000',
        ], [
            'borrowDate.required' => 'Isi tanggal mulai peminjaman.',
            'borrowDate.date_format' => 'Masukkan tanggal mulai yang valid.',
            'dueDate.required' => 'Isi tanggal pengembalian.',
            'dueDate.date_format' => 'Masukkan tanggal pengembalian yang valid.',
            'borrowReason.required' => 'Isi alasan peminjaman.',
            'borrowReason.min' => 'Alasan peminjaman minimal 10 karakter.',
            'borrowReason.max' => 'Alasan peminjaman maksimal 3.000 karakter.',
        ]);
        app(BorrowAsset::class)->request(auth()->user(), $this->org(), $this->borrowAssetId, [
            'borrow_date' => $this->borrowDate,
            'due_date' => $this->dueDate,
            'reason' => $this->borrowReason,
            'acknowledged' => $this->acknowledged,
        ]);
        $this->closeBorrowForm();
        $this->success();
        session()->flash('status', 'Pengajuan peminjaman terkirim dan menunggu persetujuan.');
    }

    public function openBorrowingAction(int $id, string $action): void
    {
        $org = $this->org();
        Gate::authorize('operate', $org);
        abort_unless(in_array($action, ['approve', 'reject', 'inspect'], true), 422);

        $borrowing = Borrowing::where('organization_id', $org->id)->findOrFail($id);
        $statuses = $action === 'inspect' ? ['borrowed', 'overdue'] : ['pending'];
        if ($org->archived_at || ! in_array($borrowing->status, $statuses, true)) {
            throw ValidationException::withMessages(['action' => 'Tindakan ini sudah tidak tersedia untuk peminjaman tersebut.']);
        }

        $this->resetBorrowingAction();
        $this->tab = 'borrowings';
        $this->selectedBorrowingId = $borrowing->id;
        $this->borrowingAction = $action;
    }

    public function closeBorrowingAction(): void
    {
        $trigger = $this->borrowingAction.'-'.$this->selectedBorrowingId;
        $this->resetBorrowingAction();
        $this->dispatch('oi-focus-field', field: 'borrowing-'.$trigger);
    }

    protected function resetBorrowingAction(): void
    {
        $this->reset('selectedBorrowingId', 'borrowingAction', 'borrowingNotes', 'inspectionCondition', 'returnedAt', 'fineOverride', 'fineOverrideReason');
        $this->resetValidation();
    }

    public function saveBorrowingAction(): void
    {
        $org = $this->org();
        Gate::authorize('operate', $org);
        abort_unless($this->selectedBorrowingId, 404);
        $service = app(BorrowAsset::class);
        $notes = trim($this->borrowingNotes);

        if ($this->borrowingAction === 'inspect') {
            $this->validate([
                'inspectionCondition' => 'required|in:good,minor_damage,major_damage,lost',
                'returnedAt' => 'nullable|date_format:Y-m-d\TH:i',
                'fineOverride' => 'nullable|integer|min:0|max:999999999999',
            ], [
                'returnedAt.date_format' => 'Masukkan tanggal dan jam pengembalian yang valid.',
                'fineOverride.integer' => 'Denda harus berupa angka bulat dalam rupiah.',
                'fineOverride.min' => 'Denda minimal Rp0.',
                'fineOverride.max' => 'Nominal denda terlalu besar.',
            ]);

            $data = ['condition' => $this->inspectionCondition, 'notes' => $notes];
            if ($this->returnedAt !== '') {
                $data['returned_at'] = CarbonImmutable::parse($this->returnedAt, 'Asia/Jakarta')->utc();
            }
            if ($this->fineOverride !== '') {
                $data['fine_amount'] = (int) $this->fineOverride;
                $data['fine_override_reason'] = trim($this->fineOverrideReason);
            }
            $service->inspect(auth()->user(), $org, $this->selectedBorrowingId, $data);
        } else {
            $service->decide(auth()->user(), $org, $this->selectedBorrowingId, $this->borrowingAction, $notes);
        }

        $message = match ($this->borrowingAction) {
            'approve' => 'Pengajuan disetujui. Peminjaman sekarang aktif.',
            'reject' => 'Pengajuan ditolak dan alasannya tersimpan.',
            'inspect' => 'Inspeksi dan pengembalian berhasil dicatat.',
        };
        $this->resetBorrowingAction();
        $this->resetPage('borrowingsPage');
        $this->success();
        session()->flash('status', $message);
        $this->dispatch('oi-focus-field', field: 'borrowing-list');
    }

    public function cancelBorrowing(int $id): void
    {
        app(BorrowAsset::class)->decide(auth()->user(), $this->org(), $id, 'cancel');
        $this->success();
    }

    public function draft(): void
    {
        $this->validate(['borrowingId' => 'required|integer|min:1', 'reportId' => 'required|integer|min:1', 'amount' => 'required|integer|min:1|max:999999999999', 'reason' => 'required|min:10', 'chargeType' => 'required|in:damage,loss', 'evidence' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048']);
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
            $this->validate(['amount' => 'required|integer|min:1|max:999999999999']);
        }
        app(SettleCharge::class)->change(auth()->user(), $this->org(), $id, $action, ['amount' => $this->amount, 'reason' => $this->notes ?: 'Diproses oleh pengelola', 'payment_id' => $paymentId, 'method' => $this->method, 'reference' => $this->reference, 'notes' => $this->paymentNotes ?: 'Pembayaran dicatat pengelola', 'paid_at' => $this->paidAt ? CarbonImmutable::parse($this->paidAt, 'Asia/Jakarta')->utc() : now()->toDateTimeString(), 'idempotency_key' => $this->idempotencyKey]);
        $this->success();
    }

    protected function success(): void
    {
        $this->idempotencyKey = (string) Str::uuid();
        session()->flash('status', 'Perubahan berhasil disimpan.');
        $this->resetValidation();
    }

    public function openAssetForm(): void
    {
        Gate::authorize('operate', $this->org());
        $this->showAssetForm();
    }

    protected function showAssetForm(): void
    {
        $this->resetAssetForm();
        $this->tab = 'inventory';
        $this->categoryFormOpen = false;
        $this->borrowAssetId = 0;
        $this->assetFormOpen = true;
    }

    public function closeAssetForm(): void
    {
        $focus = $this->editingAsset ? 'edit-asset-'.$this->editingAsset : 'add-asset';
        $this->resetAssetForm();
        if ($this->tab === 'inventory') {
            $this->dispatch('oi-focus-field', field: $focus);
        }
    }

    protected function resetAssetForm(): void
    {
        $this->reset('assetFormOpen', 'editingAsset', 'assetName', 'categoryId', 'location', 'assetDescription', 'assetSpecifications', 'assetPhoto');
        $this->resetValidation();
    }

    public function openCategoryForm(): void
    {
        Gate::authorize('operate', $this->org());
        $this->reset('categoryName');
        $this->resetValidation();
        $this->tab = 'inventory';
        $this->assetFormOpen = false;
        $this->borrowAssetId = 0;
        $this->categoryFormOpen = true;
    }

    public function closeCategoryForm(): void
    {
        $this->reset('categoryFormOpen', 'categoryName');
        $this->resetValidation();
        if ($this->tab === 'inventory') {
            $this->dispatch('oi-focus-field', field: 'add-category');
        }
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
        $this->closeMaintenanceModal();
        $this->tab = 'damage-reports';
        $this->reportStatus = '';
        $this->resetPage('reportsPage');
    }

    public function showMaintenance(): void
    {
        Gate::authorize('operate', $this->org());
        $this->closeReportModal();
        $this->tab = 'maintenance';
    }

    public function render()
    {
        $org = $this->org();
        $operator = Gate::allows('operate', $org);
        $admin = Gate::allows('administer', $org);
        abort_unless(in_array($this->tab, ['dashboard', 'inventory', 'borrowings', 'charges', 'members', 'audit', 'damage-reports', 'maintenance', 'settings']), 404);
        if (in_array($this->tab, ['members', 'audit', 'settings'])) {
            Gate::authorize('administer', $org);
        }

        if (in_array($this->tab, ['dashboard', 'maintenance'])) {
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

        $canBorrow = $this->tab === 'inventory' && Gate::allows('requestBorrowing', $org);
        $borrowAsset = $canBorrow && $this->borrowAssetId
            ? Asset::where('organization_id', $org->id)->find($this->borrowAssetId)
            : null;
        $reportAsset = $this->tab === 'inventory' && $this->reportAssetId
            ? Asset::where('organization_id', $org->id)->find($this->reportAssetId)
            : null;
        $reportBorrowings = $reportAsset
            ? Borrowing::where('organization_id', $org->id)->where('asset_id', $reportAsset->id)
                ->when(! $operator, fn ($query) => $query->where('user_id', auth()->id()))
                ->with('borrower')->latest()->get()
            : collect();

        $selectedMaintenance = $this->tab === 'maintenance' && $this->maintenanceReportId
            ? DamageReport::where('organization_id', $org->id)->with('asset')->find($this->maintenanceReportId)
            : null;

        $selectedBorrowing = $this->tab === 'borrowings' && $operator && $this->selectedBorrowingId
            ? Borrowing::where('organization_id', $org->id)->with(['asset', 'borrower'])->find($this->selectedBorrowingId)
            : null;

        $selectedReport = $this->tab === 'damage-reports' && $this->selectedReportId
            ? DamageReport::where('organization_id', $org->id)
                ->when(! $operator, fn ($q) => $q->where('reported_by', auth()->id()))
                ->with(['asset', 'reporter', 'borrowing.borrower', 'charge.payments', 'charge.credits'])
                ->find($this->selectedReportId)
            : null;

        $selectedReportBorrowings = $selectedReport
            ? Borrowing::where('organization_id', $org->id)
                ->where('asset_id', $selectedReport->asset_id)
                ->with('borrower')
                ->latest('id')
                ->limit(15)
                ->get()
            : collect();

        $selectedCharge = $this->selectedChargeId
            ? BorrowerCharge::where('organization_id', $org->id)
                ->with(['asset', 'payments', 'credits'])
                ->find($this->selectedChargeId)
            : null;

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
                'reports' => DamageReport::where('organization_id', $org->id)->where('status', 'open')->count(),
                'maintenance' => DamageReport::where('organization_id', $org->id)->where('status', 'in_maintenance')->count(),
                'members' => Membership::where('organization_id', $org->id)->where('status', 'active')->count(),
                'balance' => $issued->sum(fn ($charge) => (int) $charge->amount - (int) $charge->confirmed_total - (int) $charge->credits_sum_amount),
                'pendingPayments' => ChargePayment::where('organization_id', $org->id)->where('status', 'pending')->whereIn('charge_id', BorrowerCharge::where('organization_id', $org->id)->where('status', 'issued')->select('id'))->count(),
            ];
        }

        return view('livewire.workspace', [
            'borrowAsset' => $borrowAsset,
            'canBorrow' => $canBorrow,
            'reportAsset' => $reportAsset,
            'reportBorrowings' => $reportBorrowings,
            'selectedReport' => $selectedReport,
            'selectedReportBorrowings' => $selectedReportBorrowings,
            'selectedCharge' => $selectedCharge,
            'selectedMaintenance' => $selectedMaintenance,
            'selectedBorrowing' => $selectedBorrowing,
            'summary' => $summary,
            'workspaces' => $this->availableWorkspaces(),
            'monthly' => $monthly,
            'top' => $top,
            'maintenanceReports' => $this->tab === 'maintenance' ? DamageReport::where('organization_id', $org->id)
                ->where('status', 'in_maintenance')
                ->with(['asset', 'handler'])
                ->orderBy('maintenance_started_at')->orderBy('id')
                ->paginate(12, ['*'], 'maintenancePage') : null,
            'reports' => $this->tab === 'damage-reports' ? DamageReport::where('organization_id', $org->id)
                ->when(! $operator, fn ($q) => $q->where('reported_by', auth()->id()))
                ->when($this->reportStatus, fn ($q) => $q->where('status', $this->reportStatus))
                ->when($this->reportSeverity, fn ($q) => $q->where('severity', $this->reportSeverity))
                ->when($this->reportSource, fn ($q) => $q->where('source', $this->reportSource))
                ->with(['asset', 'reporter', 'borrowing.borrower', 'charge'])
                ->latest()
                ->paginate(20, ['*'], 'reportsPage') : null,
            'org' => $org,
            'operator' => $operator,
            'admin' => $admin,
            'assets' => Asset::where('organization_id', $org->id)->when($this->focusedAsset, fn ($q) => $q->whereKey($this->focusedAsset))->where('name', 'like', '%'.$this->search.'%')->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))->when($this->filterCategory, fn ($q) => $q->where('asset_category_id', $this->filterCategory))->orderBy($this->assetSort === 'newest' ? 'created_at' : 'name', $this->assetSort === 'newest' ? 'desc' : 'asc')->with('category')->paginate(12),
            'categories' => AssetCategory::where('organization_id', $org->id)->get(),
            'borrowings' => $this->tab === 'borrowings' ? Borrowing::where('organization_id', $org->id)
                ->when(! $operator, fn ($q) => $q->where('user_id', auth()->id()))
                ->when($operator, fn ($q) => $q->orderByRaw("status = 'pending' DESC"))
                ->when($this->borrowStatus, fn ($q) => $q->where('status', $this->borrowStatus))
                ->when($this->borrowFrom, fn ($q) => $q->where('borrow_date', '>=', $this->borrowFrom))
                ->when($this->borrowTo, fn ($q) => $q->where('borrow_date', '<=', $this->borrowTo))
                ->when($this->borrowSearch, fn ($q) => $q->where(fn ($q) => $q
                    ->whereHas('asset', fn ($a) => $a->where('name', 'like', '%'.$this->borrowSearch.'%'))
                    ->orWhereHas('borrower', fn ($u) => $u->where('name', 'like', '%'.$this->borrowSearch.'%'))))
                ->with('asset', 'borrower')->latest()->orderByDesc('id')->paginate(20, ['*'], 'borrowingsPage') : null,
            'charges' => BorrowerCharge::where('organization_id', $org->id)->when(! $operator, fn ($q) => $q->where('borrower_id', auth()->id()))->when($this->chargeStatus, fn ($q) => $q->where('status', $this->chargeStatus))->with('asset', 'payments', 'credits')->latest()->paginate(20, ['*'], 'chargesPage'),
            'members' => $admin ? Membership::where('organization_id', $org->id)->with('user')->get() : collect(),
            'audits' => $admin ? AuditLog::where('organization_id', $org->id)->latest('id')->limit(50)->get() : collect(),
            'counts' => Asset::where('organization_id', $org->id)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ])->layout('components.layout', ['context' => 'workspace']);
    }
}
