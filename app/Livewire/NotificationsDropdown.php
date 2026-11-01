<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationsDropdown extends Component
{
    public function markAsRead($notificationId)
    {
        auth()->user()->unreadNotifications->where('id', $notificationId)->markAsRead();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function clearAll()
    {
        auth()->user()->notifications()->delete();
    }

    public function render()
    {
        return view('livewire.notifications-dropdown', [
            'notifications' => auth()->user()->notifications()->limit(10)->get(),
            'unreadCount' => auth()->user()->unreadNotifications()->count(),
        ]);
    }
}
