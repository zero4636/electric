<?php

namespace App\Filament\Resources\ElectricityTariffs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ElectricityTariffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin biểu giá')
                    ->description('Nhập thông tin biểu giá điện')
                    ->icon('heroicon-o-currency-dollar')
                    ->columns(2)
                    ->schema([
                        Select::make('tariff_type_id')
                            ->label('Loại biểu giá')
                            ->relationship('tariffType', 'name')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('code')
                                    ->label('Mã loại')
                                    ->required()
                                    ->unique()
                                    ->maxLength(50)
                                    ->regex('/^[A-Z_]+$/'),
                                TextInput::make('name')
                                    ->label('Tên loại')
                                    ->required()
                                    ->maxLength(100),
                                ColorPicker::make('color')
                                    ->label('Màu sắc')
                                    ->required()
                                    ->helperText('Chọn mã màu (hex), ví dụ: #0ea5e9'),
                            ])
                            ->helperText('Chọn hoặc tạo mới loại biểu giá'),

                        TextInput::make('price_per_kwh')
                            ->label('Giá điện (VNĐ/kWh)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(999999999)
                            ->suffix('VNĐ')
                            ->placeholder('Ví dụ: 2500')
                            ->helperText('Nhập giá tiền trên mỗi kWh'),

                        DatePicker::make('effective_from')
                            ->label('Hiệu lực từ ngày')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection()
                            ->default(now()),

                        DatePicker::make('effective_to')
                            ->label('Hiệu lực đến ngày')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection()
                            ->after('effective_from')
                            ->helperText('Để trống nếu không có ngày kết thúc'),
                    ]),
            ]);
    }
}
