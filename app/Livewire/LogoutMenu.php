<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;

class LogoutMenu extends Component
{
    public function render(): View
    {
        return view('livewire.logout-menu');
    }
}
