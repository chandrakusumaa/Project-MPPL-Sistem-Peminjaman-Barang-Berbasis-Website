<?php

namespace App\Livewire\Concerns;

use App\Models\Organization;
use Illuminate\Support\Facades\Gate;

trait SwitchesWorkspaces
{
    protected function availableWorkspaces()
    {
        return Organization::whereHas('memberships', fn ($q) => $q->where('user_id', auth()->id())->where('status', 'active'))->orderBy('name')->get(['id', 'name', 'slug']);
    }

    public function switchWorkspace(string $slug)
    {
        validator(['slug' => $slug], ['slug' => 'required|string|max:160'])->validate();
        $organization = $this->availableWorkspaces()->firstWhere('slug', $slug);
        abort_unless($organization, 404);
        Gate::authorize('view', $organization);

        return $this->redirectRoute('workspace', ['organization' => $organization->slug]);
    }
}
