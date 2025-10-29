<?php

namespace App\Filament\Resources\MeterReadings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MeterReadingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin chỉ số')
                    ->description('Nhập thông tin chỉ số công tơ')
                    ->icon('heroicon-o-chart-bar')
                    ->columns(2)
                    ->schema([
                        Select::make('electric_meter_id')
                            ->label('Công tơ điện')
                            ->relationship('electricMeter', 'meter_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText('Chọn công tơ cần ghi chỉ số'),

                        DatePicker::make('reading_date')
                            ->label('Ngày ghi')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection()
                            ->default(now())
                            ->maxDate(now())
                            ->helperText('Ngày ghi chỉ số'),

                        TextInput::make('reading_value')
                            ->label('Chỉ số (kWh)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix('kWh')
                            ->placeholder('Ví dụ: 1234.56')
                            ->helperText('Nhập chỉ số hiện tại trên công tơ'),

                        TextInput::make('reader_name')
                            ->label('Người ghi')
                            ->maxLength(255)
                            ->placeholder('Tên người ghi chỉ số')
                            ->helperText('Người thực hiện ghi chỉ số'),

                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(3)
                            ->columnSpanFull()
                            ->maxLength(1000)
                            ->placeholder('Ghi chú về lần đọc này (nếu có)')
                            ->helperText('Các ghi chú đặc biệt về lần ghi chỉ số'),
                    ]),
            ]);
    }
}
