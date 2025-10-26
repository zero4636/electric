<?php

namespace App\Filament\Resources\ElectricityTariffs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ElectricityTariffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Biểu giá')
                    ->columns(2)
                    ->components([
                        Select::make('tariff_type')
                            ->label('Loại biểu giá')
                            ->options([
                                'RESIDENTIAL' => 'Sinh hoạt',
                                'COMMERCIAL' => 'Kinh doanh',
                                'INDUSTRIAL' => 'Sản xuất',
                            ])
                            ->required(),

                        TextInput::make('price_per_kwh')
                            ->label('Giá/kWh')
                            ->numeric()
                            ->required(),

                        DatePicker::make('effective_from')
                            ->label('Hiệu lực từ')
                            ->required(),

                        DatePicker::make('effective_to')
                            ->label('Hiệu lực đến')
                            ->nullable(),
                    ]),
            ]);
    }
}
