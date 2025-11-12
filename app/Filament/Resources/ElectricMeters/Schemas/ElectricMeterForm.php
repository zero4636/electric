<?php

namespace App\Filament\Resources\ElectricMeters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Components\Section;
use App\Models\TariffType;
use App\Models\ElectricityTariff;

class ElectricMeterForm
{
    public static function schema(): array
    {
        return [
            Section::make('Thông tin cơ bản')
                ->description('Thông tin công tơ điện và đơn vị quản lý')
                ->columns(2)
                ->schema([
                    TextInput::make('meter_number')
                        ->label('Mã công tơ')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),

                    Select::make('organization_unit_id')
                        ->label('Hộ tiêu thụ điện')
                        ->relationship('organizationUnit', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('substation_id')
                        ->label('Trạm biến áp')
                        ->relationship('substation', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Select::make('tariff_type_id')
                        ->label('Loại hình tiêu thụ')
                        ->options(function () {
                            return TariffType::query()
                                ->where('status', 'ACTIVE')
                                ->orderBy('sort_order')
                                ->get()
                                ->mapWithKeys(function ($tariffType) {
                                    $activeTariff = ElectricityTariff::getActiveTariff($tariffType->id, now());
                                    $price = $activeTariff 
                                        ? number_format((float) $activeTariff->price_per_kwh, 0, ',', '.') . ' ₫/kWh'
                                        : 'Chưa có giá';
                                    return [$tariffType->id => $tariffType->name . ' - ' . $price];
                                });
                        })
                        ->searchable()
                        ->required()
                        ->helperText('Chọn loại biểu giá điện áp dụng (giá hiện hành được hiển thị)'),

                    Select::make('phase_type')
                        ->label('Loại công tơ (pha)')
                        ->options([
                            '1_PHASE' => '1 pha',
                            '3_PHASE' => '3 pha',
                        ])
                        ->nullable(),

                    Select::make('status')
                        ->label('Trạng thái')
                        ->options([
                            'ACTIVE' => 'Hoạt động',
                            'INACTIVE' => 'Ngừng hoạt động',
                        ])
                        ->default('ACTIVE')
                        ->required(),
                ]),

            Section::make('Vị trí lắp đặt')
                ->description('Thông tin chi tiết về vị trí công tơ')
                ->columns(3)
                ->schema([
                    TextInput::make('building')
                        ->label('Nhà/Tòa nhà')
                        ->maxLength(100)
                        ->placeholder('VD: B1, D5, A17'),

                    TextInput::make('floor')
                        ->label('Tầng')
                        ->maxLength(50)
                        ->placeholder('VD: T1, T2, T3'),

                    TextInput::make('installation_location')
                        ->label('Vị trí đặt công tơ')
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->placeholder('VD: Tủ tổng T1, KTĐ B1'),
                ]),

            Section::make('Thông số kỹ thuật')
                ->description('Hệ số nhân và điện bao cấp')
                ->columns(2)
                ->schema([
                    TextInput::make('hsn')
                        ->label('Hệ số nhân (HSN)')
                        ->numeric()
                        ->default(1.0)
                        ->minValue(0)
                        ->step(0.01)
                        ->suffix('x')
                        ->helperText('Hệ số để tính toán điện năng thực tế'),

                    TextInput::make('subsidized_kwh')
                        ->label('Điện bao cấp (kWh/tháng)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->suffix('kWh')
                        ->helperText('Số kWh được bao cấp mỗi tháng'),
                ]),
        ];
    }
}
