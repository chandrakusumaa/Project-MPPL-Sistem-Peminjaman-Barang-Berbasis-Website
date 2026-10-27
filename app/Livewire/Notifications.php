<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;

    public function read(string $id): void
    {
        auth()->user()->notifications()->whereKey($id)->update(['read_at' => now()]);
    }

    public function allRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.notifications', ['notifications' => auth()->user()->notifications()->paginate(20)])->layout('components.layout');
    }
}
