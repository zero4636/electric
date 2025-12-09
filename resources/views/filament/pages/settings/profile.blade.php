<x-filament-panels::page>
    {{-- Form --}}
    <form wire:submit="save">
        <div class="space-y-6">
            {{ $this->form }}
        </div>

        <div class="flex items-center gap-3" style="margin-top: 1rem; padding-top: 1rem;">
            <x-filament::button type="submit">
                Lưu thay đổi
            </x-filament::button>
            
            <x-filament::button type="button" color="gray" tag="a" href="{{ route('filament.admin.pages.dashboard') }}">
                Hủy
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
