<x-filament-panels::sidebar.item
    :active="false"
    url="#"
    :icon="'heroicon-o-arrow-right-on-rectangle'"
    wire:click="$dispatch('open-modal', { id: 'logout-modal' })"
    tag="button"
    class="w-full"
>
    Logout
</x-filament-panels::sidebar.item>

@script
<script>
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-logout-trigger]');
        if (trigger) {
            event.preventDefault();
            event.stopPropagation();
            Livewire.dispatch('open-modal', { id: 'logout-modal' });
        }
    }, true);
</script>
@endscript
