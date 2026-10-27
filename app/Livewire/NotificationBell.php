<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public function render()
    {
        return view('livewire.notification-bell', ['count' => auth()->user()->unreadNotifications()->count(), 'recent' => auth()->user()->notifications()->limit(5)->get()]);
    }
}
