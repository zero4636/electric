<?php

namespace App\Filament\Pages\Schemas;

use App\Models\ElectricMeter;
use App\Models\OrganizationUnit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GenerateBillsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin tạo hóa đơn')
                    ->columns(2)
                    ->components([
                        DatePicker::make('billing_month')
                            ->label('Tháng lập hóa đơn')
                            ->displayFormat('m/Y')
                            ->format('Y-m-01')
                            ->required()
                            ->default(now()->startOfMonth()),

                        DatePicker::make('due_date')
                            ->label('Hạn thanh toán')
                            ->required()
                            ->default(now()->addDays(30)),

                        Select::make('organization_unit_id')
                            ->label('Đơn vị')
                            ->options(OrganizationUnit::where('status', 'ACTIVE')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Tất cả đơn vị')
                            ->native(false),

                        Select::make('electric_meter_ids')
                            ->label('Công tơ')
                            ->options(ElectricMeter::where('status', 'ACTIVE')->pluck('meter_number', 'id'))
                            ->searchable()
                            ->multiple()
                            ->placeholder('Tất cả công tơ')
                            ->native(false),
                    ]),
            ]);
    }
}
