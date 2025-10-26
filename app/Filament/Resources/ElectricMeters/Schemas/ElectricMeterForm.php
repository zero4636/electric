<?php

namespace App\Filament\Resources\ElectricMeters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;


class ElectricMeterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin chung')
                    ->columns(2)
                    ->components([
                        TextInput::make('meter_number')
                            ->label('Số công tơ')
                            ->required()
                            ->maxLength(100),

                        Select::make('organization_unit_id')
                            ->label('Đơn vị')
                            ->relationship('organizationUnit','name')
                            ->required(),

                        Select::make('substation_id')
                            ->label('Trạm điện')
                            ->relationship('substation','name')
                            ->nullable(),

                        Select::make('meter_type')
                            ->label('Loại công tơ')
                            ->options([
                                'ANALOG' => 'Cơ khí',
                                'DIGITAL' => 'Điện tử',
                                'SMART' => 'Thông minh',
                            ])
                            ->required(),

                        TextInput::make('hsn')
                            ->label('Số sê-ri')
                            ->numeric()
                            ->default(1.0),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'ACTIVE' => 'Hoạt động',
                                'INACTIVE' => 'Ngừng hoạt động',
                            ])
                            ->default('ACTIVE'),
                    ]),
            ]);
    }
}
