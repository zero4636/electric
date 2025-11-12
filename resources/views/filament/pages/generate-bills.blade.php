<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form chọn tháng/năm --}}
        <x-filament-panels::form>
            {{ $this->form }}
        </x-filament-panels::form>

        {{-- Table danh sách hóa đơn --}}
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
