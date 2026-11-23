<?php

namespace App\Livewire;

use App\Actions\BorrowAsset;
use App\Models\Borrowing;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalBorrowings extends Component
{
    use WithPagination;

    public string $status = '';

    public function cancelBorrowing(int $borrowingId): void
    {
        $borrowing = Borrowing::where('user_id', auth()->id())->with('organization')->findOrFail($borrowingId);
        app(BorrowAsset::class)->decide(auth()->user(), $borrowing->organization, $borrowing->id, 'cancel');
        session()->flash('status', 'Pengajuan peminjaman berhasil dibatalkan.');
    }

    public function requestReturn(int $borrowingId): void
    {
        $borrowing = Borrowing::where('user_id', auth()->id())->with('organization')->findOrFail($borrowingId);
        app(BorrowAsset::class)->requestReturn(auth()->user(), $borrowing->organization, $borrowing->id);
        session()->flash('status', 'Pemberitahuan pengembalian terkirim ke Admin/Staff. Silakan serahkan unit fisik agar pengelola mencatat inspeksi pengembalian.');
    }

    public function render()
    {
        return view('livewire.personal-borrowings', ['borrowings' => Borrowing::where('user_id', auth()->id())->when($this->status, fn ($q) => $q->where('status', $this->status))->with('organization', 'asset', 'charges.payments', 'charges.credits')->latest()->paginate(20)])->layout('components.layout');
    }
}
