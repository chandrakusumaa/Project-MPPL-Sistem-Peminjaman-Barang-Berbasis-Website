<?php

namespace App\Livewire;

use App\Actions\ManageWorkspace;
use App\Models\Borrowing;
use App\Models\Membership;
use App\Models\MembershipRequest;
use App\Models\Organization;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Home extends Component
{
    use WithPagination;

    #[Url(except: 'all')]
    public string $listing = 'all';

    public string $filterCategory = '';

    public string $name = '';

    public string $description = '';

    public string $category = 'lainnya';

    public string $search = '';

    public function create(): void
    {
        if (! $this->requireVerifiedAccount()) {
            return;
        }
        $data = $this->validate(['name' => 'required|string|max:150', 'description' => 'nullable|string|max:3000', 'category' => 'required|in:kampus,sekolah,komunitas,perusahaan,lainnya']);
        app(ManageWorkspace::class)->create(auth()->user(), $data);
        $this->reset('name', 'description');
        session()->flash('status', 'Ruang berhasil dibuat. Anda menjadi Admin.');
    }

    public function join(int $id): void
    {
        if (! $this->requireVerifiedAccount()) {
            return;
        }
        app(ManageWorkspace::class)->join(auth()->user(), Organization::findOrFail($id));
        session()->flash('status', 'Pengajuan bergabung dikirim.');
    }

    public function cancelJoin(int $id): void
    {
        if (! $this->requireVerifiedAccount()) {
            return;
        }
        app(ManageWorkspace::class)->cancelJoin(auth()->user(), Organization::findOrFail($id));
        session()->flash('status', 'Pengajuan dibatalkan.');
    }

    protected function requireVerifiedAccount(): bool
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return false;
        }
        if (! auth()->user()->hasVerifiedEmail()) {
            $this->redirectRoute('verification.notice');

            return false;
        }

        return true;
    }

    public function render()
    {
        return view('livewire.home', ['pending' => auth()->check() ? MembershipRequest::where('user_id', auth()->id())->where('status', 'pending')->pluck('organization_id')->all() : [], 'organizations' => Organization::query()->when($this->listing === 'mine', fn ($q) => $q->whereIn('id', Membership::where('user_id', auth()->id())->where('status', 'active')->select('organization_id')), fn ($q) => $q->whereNull('archived_at'))->when($this->filterCategory, fn ($q) => $q->where('category', $this->filterCategory))->where('name', 'like', '%'.$this->search.'%')->paginate(12), 'memberships' => auth()->check() ? Membership::where('user_id', auth()->id())->where('status', 'active')->get()->keyBy('organization_id') : collect(), 'borrowings' => auth()->check() ? Borrowing::with('asset')->where('user_id', auth()->id())->latest()->limit(5)->get() : collect()])->layout('components.layout');
    }
}
