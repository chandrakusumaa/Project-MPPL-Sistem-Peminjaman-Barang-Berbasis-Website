<?php

namespace App\Livewire;

use App\Models\Organization;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class OrganizationMenu extends Component
{
    #[Locked]
    public ?int $currentId = null;

    #[Locked]
    public bool $accountPage = false;

    public function mount(): void
    {
        $this->accountPage = request()->routeIs('personal.organizations');
    }

    #[On('organization-membership-updated')]
    public function refreshMemberships(): void {}

    public function render()
    {
        $organizations = Organization::whereHas('memberships', fn ($query) => $query->where('user_id', auth()->id())->where('status', 'active'))
            ->with(['memberships' => fn ($query) => $query->where('user_id', auth()->id())->where('status', 'active')])
            ->orderBy('name')->get();
        $current = $organizations->firstWhere('id', $this->currentId);

        return view('livewire.organization-menu', ['organizations' => $organizations, 'current' => $current, 'expanded' => $this->accountPage || $current]);
    }
}
