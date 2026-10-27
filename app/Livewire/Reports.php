<?php

namespace App\Livewire;

use App\Models\Organization;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Reports extends Component
{
    #[Locked]
    public int $organizationId;

    public string $from = '';

    public string $to = '';

    public string $status = '';

    public function mount(Organization $organization): void
    {
        Gate::authorize('administer', $organization);
        $this->organizationId = $organization->id;
    }

    public function render()
    {
        $org = Organization::findOrFail($this->organizationId);
        Gate::authorize('administer', $org);

        return view('livewire.reports', ['org' => $org])->layout('components.layout');
    }
}
