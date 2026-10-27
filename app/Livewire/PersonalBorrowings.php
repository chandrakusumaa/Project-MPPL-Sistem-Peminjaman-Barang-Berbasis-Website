<?php

namespace App\Livewire;

use App\Models\Borrowing;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalBorrowings extends Component
{
    use WithPagination;

    public string $status = '';

    public function render()
    {
        return view('livewire.personal-borrowings', ['borrowings' => Borrowing::where('user_id', auth()->id())->when($this->status, fn ($q) => $q->where('status', $this->status))->with('asset', 'charges.payments', 'charges.credits')->latest()->paginate(20)])->layout('components.layout');
    }
}
