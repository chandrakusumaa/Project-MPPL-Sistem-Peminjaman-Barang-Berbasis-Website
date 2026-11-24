<?php

namespace App\Livewire;

use App\Actions\SettleCharge;
use App\Models\BorrowerCharge;
use App\Models\ChargePayment;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PersonalCharges extends Component
{
    use WithFileUploads, WithPagination;

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $paymentStatus = '';

    #[Locked]
    public int $selectedChargeId = 0;

    public string $amount = '';

    public string $method = 'bank_transfer';

    public string $reference = '';

    public string $paymentNotes = '';

    public $evidence;

    public string $idempotencyKey = '';

    public function mount(): void
    {
        $this->idempotencyKey = (string) Str::uuid();
    }

    public function openPaymentModal(int $chargeId): void
    {
        $charge = BorrowerCharge::where('borrower_id', auth()->id())
            ->where('status', 'issued')
            ->with('organization', 'payments', 'credits')
            ->findOrFail($chargeId);

        if ($charge->organization->archived_at || $charge->balance <= 0) {
            throw ValidationException::withMessages(['action' => 'Tagihan ini sudah lunas atau organisasi sedang diarsipkan.']);
        }

        $this->resetValidation();
        $this->selectedChargeId = $charge->id;
        $this->amount = (string) max(1, (int) $charge->balance);
        $this->method = 'bank_transfer';
        $this->reference = '';
        $this->paymentNotes = '';
        $this->evidence = null;
    }

    public function closePaymentModal(): void
    {
        $this->reset('selectedChargeId', 'amount', 'method', 'reference', 'paymentNotes', 'evidence');
        $this->method = 'bank_transfer';
        $this->resetValidation();
    }

    public function submitPayment(): void
    {
        $charge = BorrowerCharge::where('borrower_id', auth()->id())
            ->where('status', 'issued')
            ->with('organization', 'payments', 'credits')
            ->findOrFail($this->selectedChargeId);

        $maxAmount = max(1, (int) $charge->balance);
        $this->validate([
            'amount' => 'required|integer|min:1|max:'.$maxAmount,
            'method' => 'required|in:cash,bank_transfer,other',
            'reference' => 'nullable|string|max:191',
            'paymentNotes' => 'nullable|string|max:1000',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.min' => 'Nominal pembayaran minimal Rp1.',
            'amount.max' => 'Nominal tidak boleh melebihi sisa tagihan (Rp'.number_format($charge->balance, 0, ',', '.').').',
            'evidence.mimes' => 'Bukti pembayaran harus berformat JPG, PNG, atau PDF.',
            'evidence.max' => 'Ukuran bukti pembayaran maksimal 2 MB.',
        ]);

        $reference = trim($this->reference);
        $notes = trim($this->paymentNotes);
        if ($this->method === 'bank_transfer' && $reference === '' && ! $this->evidence) {
            $this->addError('reference', 'Isi nomor referensi transfer atau unggah bukti pembayaran.');

            return;
        }
        if ($this->method !== 'bank_transfer' && $notes === '') {
            $notes = 'Pembayaran diajukan oleh peminjam';
        }

        $evidencePath = $this->evidence ? $this->evidence->store('evidence/'.$charge->organization_id, 'local') : null;

        app(SettleCharge::class)->change(auth()->user(), $charge->organization, $charge->id, 'payment', [
            'amount' => (int) $this->amount,
            'method' => $this->method,
            'reference' => $reference !== '' ? $reference : null,
            'evidence_path' => $evidencePath,
            'notes' => $notes !== '' ? $notes : null,
            'paid_at' => now('UTC')->format('Y-m-d H:i:s.u'),
            'idempotency_key' => $this->idempotencyKey,
        ]);

        $this->idempotencyKey = (string) Str::uuid();
        $this->closePaymentModal();
        session()->flash('status', 'Pembayaran berhasil dikirim dan menunggu konfirmasi Admin.');
    }

    public function updatedStatus(): void
    {
        $this->resetPage('chargesPage');
    }

    public function updatedType(): void
    {
        $this->resetPage('chargesPage');
    }

    public function updatedPaymentStatus(): void
    {
        $this->resetPage('paymentsPage');
    }

    private function balances(): Builder
    {
        // Aggregate the current borrower's records in SQL; pending/voided payments
        // never reduce the balance, and credits remain separate from payments.
        $charges = BorrowerCharge::where('borrower_id', auth()->id())->select('id', 'status', 'amount')
            ->withSum(['payments as confirmed_total' => fn ($query) => $query->where('status', 'confirmed')], 'amount')
            ->withSum('credits as credited_total', 'amount');

        return DB::query()->fromSub($charges, 'personal_balances');
    }

    public function render()
    {
        $balance = 'amount - COALESCE(confirmed_total, 0) - COALESCE(credited_total, 0)';
        $summary = $this->balances()->where('status', 'issued')->selectRaw("COALESCE(SUM(amount), 0) AS total, COALESCE(SUM(confirmed_total), 0) AS paid, COALESCE(SUM(credited_total), 0) AS credited, COALESCE(SUM($balance), 0) AS outstanding")->first();
        $ids = $this->balances();

        match ($this->status) {
            'outstanding' => $ids->where('status', 'issued')->whereRaw("$balance > 0"),
            'paid' => $ids->where('status', 'issued')->whereRaw("$balance = 0 AND amount > COALESCE(credited_total, 0)"),
            'waived' => $ids->where('status', 'issued')->whereRaw('amount = COALESCE(credited_total, 0)'),
            'draft', 'cancelled' => $ids->where('status', $this->status),
            default => null,
        };

        $selectedCharge = $this->selectedChargeId
            ? BorrowerCharge::where('borrower_id', auth()->id())
                ->where('status', 'issued')
                ->with('organization', 'asset', 'payments', 'credits')
                ->find($this->selectedChargeId)
            : null;

        $outstandingByOrg = BorrowerCharge::where('borrower_id', auth()->id())
            ->where('status', 'issued')
            ->with('organization', 'payments', 'credits')
            ->get()
            ->filter(fn ($charge) => $charge->balance > 0)
            ->groupBy('organization_id')
            ->map(fn ($items) => (object) [
                'organization' => $items->first()->organization,
                'outstanding' => $items->sum(fn ($charge) => $charge->balance),
            ])
            ->values();

        return view('livewire.personal-charges', [
            'summary' => $summary,
            'outstandingByOrg' => $outstandingByOrg,
            'selectedCharge' => $selectedCharge,
            'charges' => BorrowerCharge::whereIn('id', $ids->select('id'))
                ->when($this->type, fn ($query) => $query->where('charge_type', $this->type))
                ->with('organization', 'asset', 'payments', 'credits')->latest('id')->paginate(10, pageName: 'chargesPage'),
            'pendingPayments' => ChargePayment::where('borrower_id', auth()->id())->where('status', 'pending')->sum('amount'),
            'payments' => ChargePayment::where('borrower_id', auth()->id())
                ->when($this->paymentStatus, fn ($query) => $query->where('status', $this->paymentStatus))
                ->with('charge.organization', 'charge.asset')->latest('paid_at')->latest('id')->paginate(10, pageName: 'paymentsPage'),
        ])->layout('components.layout');
    }
}
