<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Tạo nhanh
        </x-slot>

        {{-- All Actions in one grid --}}
        <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            {{ ($this->createOrgUnitAction)([]) }}
            {{ ($this->createMeterAction)([]) }}
            {{ ($this->createReadingAction)([]) }}
            
            @if($this->canShowImportButton())
                {{ ($this->importAction)([]) }}
                {{ ($this->exportAction)([]) }}
            @endif
        </div>

        @if($this->canShowImportButton())
            <x-filament-actions::modals />
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
