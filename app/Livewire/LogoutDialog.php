<?php

namespace App\Livewire;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class LogoutDialog extends Component
{
    public function logout(): Redirector|RedirectResponse|null
    {
        $currentPanel = Filament::getCurrentPanel();

        if (! $currentPanel) {
            return null;
        }

        $authGuard = $currentPanel?->getAuthGuard();
        $panelId = $currentPanel?->getId();

        auth($authGuard)->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route("filament.{$panelId}.auth.login");
    }

    public function render(): View
    {
        return view('livewire.logout-dialog');
    }
}
