<?php

namespace App\Livewire;

use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;

class ExploreOrganizations extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterCategory = '';

    public string $category = '';

    public function mount(): void
    {
        // Preserve links to the account area published before it had its own URL.
        if (request('listing') === 'mine' || request()->boolean('create')) {
            $this->redirectRoute('personal.organizations', array_filter([
                'create' => request()->boolean('create') ? 1 : null,
            ]));
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $selectedCategory = $this->category ?: $this->filterCategory;

        return view('livewire.explore-organizations', [
            'category' => $selectedCategory,
            'filterCategory' => $selectedCategory,
            'organizations' => Organization::whereNull('archived_at')
                ->when($selectedCategory, fn ($query) => $query->where('category', $selectedCategory))
                ->where('name', 'like', '%'.$this->search.'%')->withCount('assets')->latest('id')->paginate(12),
        ])->layout('components.layout', ['context' => 'public']);
    }
}
