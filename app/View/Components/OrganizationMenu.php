<?php

namespace App\View\Components;

use App\Models\Organization;
use Illuminate\View\Component;

class OrganizationMenu extends Component
{
    public $organizations;

    public function __construct(public ?Organization $current = null)
    {
        $this->organizations = Organization::whereHas('memberships', fn ($query) => $query->where('user_id', auth()->id())->where('status', 'active'))
            ->with(['memberships' => fn ($query) => $query->where('user_id', auth()->id())->where('status', 'active')])
            ->orderBy('name')->get();
    }

    public function render()
    {
        return view('components.organization-menu');
    }
}
