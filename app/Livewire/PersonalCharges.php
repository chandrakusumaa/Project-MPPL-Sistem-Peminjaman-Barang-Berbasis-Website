<?php

namespace App\Livewire;

use App\Models\BorrowerCharge;
use App\Models\ChargePayment;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalCharges extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $paymentStatus = '';

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

        return view('livewire.personal-charges', [
            'summary' => $summary,
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
