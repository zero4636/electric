<x-filament-widgets::widget>
    <x-filament::section heading="Tạo nhanh">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <a href="{{ $this->getCreateOrgUnitUrl() }}" class="fi-btn fi-color-primary">
                <x-heroicon-o-user-group class="w-5 h-5" />
                <span>Tạo Đơn vị/Hộ tiêu thụ</span>
            </a>
            <a href="{{ $this->getCreateMeterUrl() }}" class="fi-btn fi-color-success">
                <x-heroicon-o-light-bulb class="w-5 h-5" />
                <span>Tạo Công tơ điện</span>
            </a>
            <a href="{{ $this->getCreateReadingUrl() }}" class="fi-btn fi-color-info">
                <x-heroicon-o-pencil-square class="w-5 h-5" />
                <span>Ghi chỉ số</span>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
