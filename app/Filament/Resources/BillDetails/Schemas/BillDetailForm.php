<?php

namespace App\Filament\Resources\BillDetails\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BillDetailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Chi tiết')
                    ->columns(2)
                    ->components([
                        Select::make('bill_id')
                            ->label('Hóa đơn')
                            ->relationship('bill','id')
                            ->required(),

                        Select::make('electric_meter_id')
                            ->label('Công tơ')
                            ->relationship('electricMeter','meter_number')
                            ->required(),

                        TextInput::make('consumption')->label('Tiêu thụ (kWh)')->numeric()->required(),
                        TextInput::make('price_per_kwh')->label('Đơn giá')->numeric()->required(),
                        TextInput::make('hsn')->label('Số sê-ri')->numeric()->required(),
                        TextInput::make('amount')->label('Thành tiền')->numeric()->required(),
                    ]),
            ]);
    }
}
