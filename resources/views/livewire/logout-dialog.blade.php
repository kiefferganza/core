<div>
    <x-filament::modal id="logout-modal" icon="heroicon-o-exclamation-triangle" icon-color="danger" alignment="center" :closeable="true" :dismissible="true" width="lg">
        <x-slot name="heading">
            {{ __('Confirm Logout') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Are you sure you want to log out?') }}
        </x-slot>

        <x-slot name="footer">
            <div style="display: flex; gap: 0.75rem; width: 100%;">
                <x-filament::button color="gray" wire:click="$dispatch('close-modal', { id: 'logout-modal' })" style="flex: 1; justify-content: center;">
                    {{ __('Cancel') }}
                </x-filament::button>

                <x-filament::button color="danger" wire:click="logout" style="flex: 1; justify-content: center;">
                    {{ __('Logout') }}
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>
