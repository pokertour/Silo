<?php

namespace App\Livewire\Admin;

// Admin version of ShareLinks simply points to the global ShareLinks component
// which already handles admin logic (shows all links if user has admin role)
use App\Livewire\ShareLinks as BaseShareLinks;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ShareLinks extends BaseShareLinks
{
    // Inherits all behaviour from the user ShareLinks component.
    // The parent component already shows all links for admins.
}
