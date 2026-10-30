<?php

namespace App\Livewire;

use App\Models\Borrowing;
use App\Models\Organization;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalDashboard extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $borrowings = Borrowing::where('user_id', auth()->id());
        $counts = (clone $borrowings)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $organizations = Organization::whereHas('memberships', fn ($query) => $query->where('user_id', auth()->id())->where('status', 'active'))
            ->with(['memberships' => fn ($query) => $query->where('user_id', auth()->id())->where('status', 'active')])
            ->withCount('assets')->latest('id')->get();

        return view('livewire.personal-dashboard', [
            'organizations' => $organizations,
            'summary' => [
                'organizations' => $organizations->whereNull('archived_at')->count(),
                'borrowings' => $counts->sum(),
                'active' => (int) $counts->get('borrowed', 0) + (int) $counts->get('overdue', 0),
                'pending' => (int) $counts->get('pending', 0),
            ],
            'activeBorrowings' => (clone $borrowings)->whereIn('status', ['borrowed', 'overdue'])->with('asset', 'organization')->orderBy('due_date')->limit(5)->get(),
            'borrowings' => (clone $borrowings)->when($this->status, fn ($query) => $query->where('status', $this->status))
                ->with('asset', 'organization')->latest('updated_at')->latest('id')->paginate(10),
        ])->layout('components.layout');
    }
}
