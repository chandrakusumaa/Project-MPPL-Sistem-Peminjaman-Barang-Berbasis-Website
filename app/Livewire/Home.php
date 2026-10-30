<?php

namespace App\Livewire;

use App\Actions\ManageWorkspace;
use App\Models\Membership;
use App\Models\Organization;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Home extends Component
{
    use WithPagination;

    #[Url(except: 'mine')]
    public string $listing = 'mine';

    public string $filterCategory = '';

    public string $name = '';

    public string $description = '';

    public string $category = 'lainnya';

    public string $search = '';

    #[Locked]
    public int $joinedOrganizationId = 0;

    #[Url(as: 'create', except: false)]
    public bool $creating = false;

    public function showListing(string $listing): void
    {
        validator(['listing' => $listing], ['listing' => 'required|in:mine,all'])->validate();
        $this->listing = $listing;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        if (! $this->requireVerifiedAccount()) {
            return;
        }
        $data = $this->validate(['name' => 'required|string|max:150', 'description' => 'nullable|string|max:3000', 'category' => 'required|in:kampus,sekolah,komunitas,perusahaan,lainnya']);
        app(ManageWorkspace::class)->create(auth()->user(), $data);
        $this->reset('name', 'description');
        $this->creating = false;
        $this->resetPage();
        session()->flash('status', 'Organization berhasil dibuat. Anda menjadi Admin.');
        $this->redirectRoute('personal.organizations');
    }

    public function join(int $id): void
    {
        if (! $this->requireVerifiedAccount()) {
            return;
        }
        $key = 'organization-join:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages(['action' => 'Terlalu banyak percobaan bergabung. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.']);
        }
        RateLimiter::hit($key, 60);
        $organization = Organization::findOrFail($id);
        $membership = app(ManageWorkspace::class)->join(auth()->user(), $organization);
        $this->joinedOrganizationId = $organization->id;
        session()->flash('status', $membership->wasRecentlyCreated
            ? 'Berhasil bergabung ke '.$organization->name.' sebagai Member.'
            : 'Anda sudah menjadi anggota '.$organization->name.'.');
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
        return view('livewire.home', [
            'revoked' => auth()->check() ? Membership::where('user_id', auth()->id())->where('status', 'revoked')->pluck('organization_id')->all() : [],
            'organizations' => Organization::query()
                ->when($this->listing === 'mine', fn ($query) => $query->whereIn('id', Membership::where('user_id', auth()->id())->where('status', 'active')->select('organization_id')), fn ($query) => $query->whereNull('archived_at'))
                ->when($this->filterCategory, fn ($query) => $query->where('category', $this->filterCategory))
                ->where('name', 'like', '%'.$this->search.'%')->withCount('assets')->latest('id')->paginate(12),
            'memberships' => auth()->check() ? Membership::where('user_id', auth()->id())->where('status', 'active')->get()->keyBy('organization_id') : collect(),
        ])->layout('components.layout', ['context' => 'account', 'showFeedback' => false]);
    }
}
