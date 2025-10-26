<?php

namespace App\Filament\Resources\MeterReadings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MeterReadingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin chỉ số')
                    ->columns(2)
                    ->components([
                        Select::make('electric_meter_id')
                            ->label('Công tơ điện')
                            ->relationship('electricMeter','meter_number')
                            ->required(),

                        DatePicker::make('reading_date')
                            ->label('Ngày ghi')
                            ->required(),

                        TextInput::make('reading_value')
                            ->label('Chỉ số')
                            ->numeric()
                            ->required(),

                        TextInput::make('hsn')
                            ->label('Số sê-ri')
                            ->numeric()
                            ->default(1.0),
                    ]),
            ]);
    }
}
